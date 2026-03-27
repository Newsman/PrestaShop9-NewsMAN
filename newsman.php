<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use PrestaShop\Module\Newsman\Action\Order\Save as OrderSaveAction;
use PrestaShop\Module\Newsman\Action\Order\Status as OrderStatusAction;
use PrestaShop\Module\Newsman\Action\Subscribe\Email as SubscribeEmailAction;
use PrestaShop\Module\Newsman\Config;
use PrestaShop\Module\Newsman\Logger;
use PrestaShop\Module\Newsman\Remarketing\Renderer as RemarketingRenderer;

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * @see ModuleCore
 */
class Newsman extends Module
{
    public const HOOKS = [
        'actionCustomerAccountAdd',
        'actionObjectCustomerUpdateBefore',
        'actionObjectCustomerDeleteBefore',
        'actionNewsletterRegistrationAfter',
        'actionValidateOrder',
        'actionOrderStatusPostUpdate',
        'actionFrontControllerSetMedia',
        'displayAfterBodyOpeningTag',
        'displayBeforeBodyClosingTag',
        'displayOrderConfirmation',
        'displayBackOfficeHeader',
        // Custom hooks for 3rd party developers — Action layer.
        'actionNewsmanBeforeSubscribe',
        'actionNewsmanAfterSubscribe',
        'actionNewsmanBeforeUnsubscribe',
        'actionNewsmanAfterUnsubscribe',
        'actionNewsmanBeforeOrderSave',
        'actionNewsmanAfterOrderSave',
        'actionNewsmanWebhookEvent',
        'actionNewsmanBeforeExport',
        'actionNewsmanAfterExport',
        'actionNewsmanRemarketingRender',
        // Service layer hooks (before API calls).
        'actionNewsmanServiceSubscribeEmailBefore',
        'actionNewsmanServiceUnsubscribeEmailBefore',
        'actionNewsmanServiceInitSubscribeEmailBefore',
        'actionNewsmanServiceExportCsvSubscribersBefore',
        'actionNewsmanServiceAddSubscriberBefore',
        'actionNewsmanServiceSaveOrderBefore',
        'actionNewsmanServiceSaveOrdersBefore',
        'actionNewsmanServiceSetPurchaseStatusBefore',
        'actionNewsmanServiceUpdateFeedBefore',
        'actionNewsmanServiceSetFeedOnListBefore',
        'actionNewsmanServiceGetListAllBefore',
        'actionNewsmanServiceGetSegmentAllBefore',
        'actionNewsmanServiceGetSettingsBefore',
        'actionNewsmanServiceSaveListIntegrationSetupBefore',
        // API client hooks.
        'actionNewsmanApiClientGetParamsBefore',
        'actionNewsmanApiClientPostParamsBefore',
        'actionNewsmanApiClientRequestParamsBefore',
        'actionNewsmanApiClientExecuteCurlOptionsBefore',
        // Remarketing script hooks.
        'actionNewsmanRemarketingTrackingScriptAfter',
        'actionNewsmanRemarketingTrackingAttributesBefore',
        // Export retriever hooks.
        'actionNewsmanExportRetrieverProcessListParamsBefore',
        'actionNewsmanExportRetrieverProcessListParamsAfter',
        'actionNewsmanExportRetrieverGetStoreUrlBefore',
        'actionNewsmanExportRetrieverPoolGetRetrieverListBefore',
        'actionNewsmanExportRequestGetAllKnownParametersAfter',
        'actionNewsmanExportRetrieverCustomersProcessFetchAfter',
        'actionNewsmanExportRetrieverCustomersProcessCustomerAfter',
        'actionNewsmanExportRetrieverSubscribersProcessSubscriberAfter',
        'actionNewsmanExportRetrieverOrdersProcessFetchAfter',
        'actionNewsmanExportRetrieverOrdersProcessOrderAfter',
        'actionNewsmanExportRetrieverProductsFeedProcessFetchAfter',
        'actionNewsmanExportRetrieverProductsFeedProcessProductAfter',
        'actionNewsmanExportRetrieverSendSubscribersProcessSubscriberAfter',
        'actionNewsmanExportRetrieverCouponsProcessCouponBefore',
    ];

    public const MODULE_ADMIN_CONTROLLERS = [
        [
            'class_name' => 'NewsmanConfigurationAdminParentController',
            'visible' => false,
            'parent_class_name' => 'AdminParentModulesSf',
            'name' => 'Newsman',
        ],
        [
            'class_name' => 'NewsmanConfigurationAdminController',
            'visible' => true,
            'parent_class_name' => 'NewsmanConfigurationAdminParentController',
            'name' => 'Settings',
        ],
        [
            'class_name' => 'NewsmanLogViewerAdminController',
            'visible' => true,
            'parent_class_name' => 'NewsmanConfigurationAdminParentController',
            'name' => 'Logs',
        ],
    ];

    public function __construct()
    {
        $this->name = 'newsman';
        $this->tab = 'advertising_marketing';
        $this->version = '9.0.0';
        $this->author = 'Newsman by Dazoot';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        $this->displayName = $this->trans('Newsman', [], 'Modules.Newsman.Admin');
        $this->description = $this->trans(
            'Newsman email marketing and SMS integration for PrestaShop.',
            [],
            'Modules.Newsman.Admin'
        );

        $this->controllers = ['api', 'cart'];
    }

    /**
     * @return bool
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook(static::HOOKS)
            && $this->installTab()
            && $this->installDefaultConfiguration();
    }

    /**
     * Set default configuration values.
     *
     * @return bool
     */
    public function installDefaultConfiguration(): bool
    {
        /** @var PrestaShop\PrestaShop\Adapter\Configuration $configuration */
        $configuration = $this->get('prestashop.adapter.legacy.configuration');

        $configuration->set(Config::KEY_ACTIVE, 1);
        $configuration->set(Config::KEY_SEND_USER_IP, 1);
        $configuration->set(Config::KEY_SERVER_IP, '');
        $configuration->set(Config::KEY_REMARKETING_STATUS, 1);
        $configuration->set(Config::KEY_REMARKETING_SEND_TELEPHONE, 1);
        $configuration->set(Config::KEY_LOG_SEVERITY, Config::LOG_ERROR);
        $configuration->set(Config::KEY_LOG_CLEAN_DAYS, Config::DEFAULT_LOG_CLEAN_DAYS);
        $configuration->set(Config::KEY_API_TIMEOUT, Config::DEFAULT_API_TIMEOUT);
        $configuration->set(Config::KEY_DEV_ACTIVE_USER_IP, 0);
        $configuration->set(Config::KEY_DEV_USER_IP, '');

        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return $this->uninstallTab()
            && $this->removeConfiguration()
            && parent::uninstall();
    }

    /**
     * Redirect to Symfony admin controller.
     */
    public function getContent()
    {
        Tools::redirectAdmin(
            $this->get('router')->generate('newsman_configuration')
        );

        return '';
    }

    /**
     * Install admin tabs for Symfony controllers.
     *
     * @return bool
     */
    protected function installTab(): bool
    {
        foreach (static::MODULE_ADMIN_CONTROLLERS as $controller) {
            $tab = new Tab();
            $tab->class_name = $controller['class_name'];
            $tab->module = $this->name;
            $tab->active = true;

            $parentId = Tab::getIdFromClassName($controller['parent_class_name']);
            $tab->id_parent = $parentId ?: -1;

            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $tab->name[$lang['id_lang']] = $controller['name'];
            }

            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove admin tabs.
     *
     * @return bool
     */
    protected function uninstallTab(): bool
    {
        foreach (array_reverse(static::MODULE_ADMIN_CONTROLLERS) as $controller) {
            $tabId = Tab::getIdFromClassName($controller['class_name']);
            if ($tabId) {
                $tab = new Tab($tabId);
                if (!$tab->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Remove all module configuration values.
     *
     * @return bool
     */
    protected function removeConfiguration(): bool
    {
        $keys = Config::getAllKeys();
        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    /**
     * Hook: remarketing page events before body closing tag.
     *
     * Area: Front
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAfterBodyOpeningTag(array $params): string
    {
        try {
            $renderer = $this->get(RemarketingRenderer::class);

            return $renderer->renderTrackingScript();
        } catch (Exception $e) {
            $logger = $this->get(Logger::class);
            $logger->logException($e);

            return '';
        }
    }

    public function hookDisplayBeforeBodyClosingTag(array $params): string
    {
        try {
            $renderer = $this->get(RemarketingRenderer::class);

            return $renderer->renderBodyClosingTag();
        } catch (Exception $e) {
            $logger = $this->get(Logger::class);
            $logger->logException($e);

            return '';
        }
    }

    /**
     * Hook: purchase tracking on order confirmation page.
     *
     * Area: Front
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayOrderConfirmation(array $params): string
    {
        if (!isset($params['order'])) {
            return '';
        }

        try {
            /** @var Order $order */
            $order = $params['order'];
            $shopId = (int) $order->id_shop ?: null;

            $renderer = $this->get(RemarketingRenderer::class);

            return $renderer->renderPurchaseTracking($order, Config::shopConstraint($shopId));
        } catch (Exception $e) {
            $logger = $this->get(Logger::class);
            $logger->logException($e);

            return '';
        }
    }

    /**
     * Hook: admin CSS/JS.
     *
     * Area: Admin
     *
     * @param array $params
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader(array $params): void
    {
        $controller = Tools::getValue('controller');

        if ('AdminDashboard' === $controller) {
            try {
                $this->get(PrestaShop\Module\Newsman\Util\LogFileReader::class)->cleanOldLogs();
            } catch (Exception $e) {
                // Silently ignore — cleanup is best-effort.
            }
        }
    }

    /**
     * Hook: new customer registration -> subscribe to Newsman.
     *
     * Area: Front, Admin
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionCustomerAccountAdd(array $params): void
    {
        if (!isset($params['newCustomer'])) {
            return;
        }

        /** @var Customer $customer */
        $customer = $params['newCustomer'];

        if (empty($customer->email) || !$customer->newsletter) {
            return;
        }

        $shopConstraint = Config::shopConstraint((int) $customer->id_shop ?: null);
        /** @var Config $config */
        $config = $this->get(Config::class);
        if (!$config->isEnabled($shopConstraint)) {
            return;
        }

        $action = $this->get(SubscribeEmailAction::class);
        $action->subscribe(
            $customer->email,
            $customer->firstname,
            $customer->lastname,
            [],
            [],
            $shopConstraint
        );
    }

    /**
     * Hook: customer update (newsletter toggle, active toggle, soft delete) -> subscribe/unsubscribe.
     *
     * Area: Front, Admin
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionObjectCustomerUpdateBefore(array $params): void
    {
        if (!isset($params['object'])) {
            return;
        }

        /** @var Customer $customer */
        $customer = $params['object'];

        if (empty($customer->id) || empty($customer->email)) {
            return;
        }

        $existingCustomer = new Customer($customer->id);
        if (!Validate::isLoadedObject($existingCustomer)) {
            return;
        }

        $shopConstraint = Config::shopConstraint((int) $customer->id_shop ?: null);
        /** @var Config $config */
        $config = $this->get(Config::class);
        if (!$config->isEnabled($shopConstraint)) {
            return;
        }

        $action = $this->get(SubscribeEmailAction::class);

        $oldNewsletter = (bool) $existingCustomer->newsletter;
        $newNewsletter = (bool) $customer->newsletter;

        if ($oldNewsletter !== $newNewsletter) {
            if ($newNewsletter && !$oldNewsletter) {
                $action->subscribe(
                    $customer->email,
                    $customer->firstname,
                    $customer->lastname,
                    [],
                    [],
                    $shopConstraint
                );
            } elseif (!$newNewsletter && $oldNewsletter) {
                $action->unsubscribe($customer->email, $shopConstraint);
            }

            return;
        }

        $oldDeleted = (bool) $existingCustomer->deleted;
        $newDeleted = (bool) $customer->deleted;

        if (!$oldDeleted && $newDeleted && $oldNewsletter) {
            $action->unsubscribe($customer->email, $shopConstraint);
        }
    }

    /**
     * Hook: customer hard delete -> unsubscribe from Newsman.
     *
     * Area: Front, Admin
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionObjectCustomerDeleteBefore(array $params): void
    {
        if (!isset($params['object'])) {
            return;
        }

        /** @var Customer $customer */
        $customer = $params['object'];

        if (empty($customer->email) || !$customer->newsletter) {
            return;
        }

        $shopConstraint = Config::shopConstraint((int) $customer->id_shop ?: null);
        /** @var Config $config */
        $config = $this->get(Config::class);
        if (!$config->isEnabled($shopConstraint)) {
            return;
        }

        $action = $this->get(SubscribeEmailAction::class);
        $action->unsubscribe($customer->email, $shopConstraint);
    }

    /**
     * Hook: ps_emailsubscription newsletter form subscribe/unsubscribe.
     *
     * Handles guest visitors and customers who subscribe/unsubscribe via the
     * newsletter form (ps_emailsubscription uses direct SQL, so ObjectModel
     * hooks do not fire).
     *
     * Area: Front
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionNewsletterRegistrationAfter(array $params): void
    {
        if (!empty($params['error']) || empty($params['email'])) {
            return;
        }

        /** @var Config $config */
        $config = $this->get(Config::class);
        if (!$config->isEnabled()) {
            return;
        }

        $email = (string) $params['email'];
        $actionType = isset($params['action']) ? (int) $params['action'] : -1;

        $action = $this->get(SubscribeEmailAction::class);

        if (0 === $actionType) {
            $action->subscribeSingleOptin($email, '', '', []);
        } elseif (1 === $actionType) {
            $action->unsubscribe($email);
        }
    }

    /**
     * Hook: new order placed -> send to Newsman remarketing.
     *
     * Area: Front, Admin
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionValidateOrder(array $params): void
    {
        if (!isset($params['order'])) {
            return;
        }

        /** @var Order $order */
        $order = $params['order'];

        if (empty($order->id)) {
            return;
        }

        $shopConstraint = Config::shopConstraint((int) $order->id_shop ?: null);
        /** @var Config $config */
        $config = $this->get(Config::class);
        if (!$config->isEnabled($shopConstraint)) {
            return;
        }

        $saveAction = $this->get(OrderSaveAction::class);
        $saveAction->execute((int) $order->id, true, $shopConstraint);

        // Subscribe customer to Newsman if they opted in for newsletter during checkout.
        // Only subscribe if newsletter_date_add is within the last hour to avoid
        // re-subscribing customers who were already synced from a previous order.
        if (isset($params['customer']) && $params['customer'] instanceof Customer) {
            /** @var Customer $customer */
            $customer = $params['customer'];
            if (!empty($customer->email) && $customer->newsletter) {
                $newsletterDate = (!empty($customer->newsletter_date_add) && $customer->newsletter_date_add !== '0000-00-00 00:00:00')
                    ? strtotime($customer->newsletter_date_add)
                    : time();
                $oneHourAgo = time() - 3600;
                if ($newsletterDate !== false && $newsletterDate >= $oneHourAgo) {
                    $subscribeAction = $this->get(SubscribeEmailAction::class);
                    $subscribeAction->subscribe(
                        $customer->email,
                        $customer->firstname,
                        $customer->lastname,
                        [],
                        [],
                        $shopConstraint
                    );
                }
            }
        }
    }

    /**
     * Hook: order status change -> update Newsman.
     *
     * Area: Front, Admin
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionOrderStatusPostUpdate(array $params): void
    {
        if (!isset($params['id_order']) || !isset($params['newOrderStatus'])) {
            return;
        }

        $orderId = (int) $params['id_order'];
        /** @var OrderState $newOrderStatus */
        $newOrderStatus = $params['newOrderStatus'];

        if (empty($orderId) || empty($newOrderStatus->id)) {
            return;
        }

        $order = new Order($orderId);
        $shopConstraint = Config::shopConstraint(
            Validate::isLoadedObject($order) ? (int) $order->id_shop : null
        );
        /** @var Config $config */
        $config = $this->get(Config::class);
        if (!$config->isEnabled($shopConstraint)) {
            return;
        }

        // $statusAction = $this->get(OrderStatusAction::class);
        // $statusAction->execute($orderId, (int) $newOrderStatus->id, $shopConstraint);

        $saveAction = $this->get(OrderSaveAction::class);
        $saveAction->execute($orderId, false, $shopConstraint);
    }

    /**
     * Hook: enqueue front-end assets.
     *
     * Area: Front
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionFrontControllerSetMedia(array $params): void
    {
    }
}
