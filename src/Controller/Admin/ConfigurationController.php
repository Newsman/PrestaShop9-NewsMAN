<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\Newsman\Controller\Admin;

use PrestaShop\Module\Newsman\Config;
use PrestaShop\Module\Newsman\Logger;
use PrestaShop\Module\Newsman\Service\Configuration\Remarketing\GetSettings as RemarketingGetSettings;
use PrestaShop\Module\Newsman\Service\Context\Configuration\EmailList as EmailListContext;
use PrestaShop\Module\Newsman\Util\LogFileReader;
use PrestaShop\Module\Newsman\Util\Version;
use PrestaShop\PrestaShop\Core\Context\ShopContext;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends PrestaShopAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message: 'Access denied.')]
    public function indexAction(
        Request $request,
        Config $config,
        LogFileReader $logFileReader,
        Logger $logger,
        RemarketingGetSettings $remarketingGetSettings,
        ShopContext $shopContext,
        #[Autowire(service: 'newsman.form.configuration_handler')]
        FormHandlerInterface $formHandler,
    ): Response {
        $logFileReader->cleanOldLogs();

        if (!$config->hasApiAccess()) {
            return $this->redirectToRoute('newsman_oauth_step1');
        }

        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $formHandler->save($form->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Successful update.', [], 'Admin.Notifications.Success'));
            } else {
                $this->addFlashErrors($errors);
            }

            return $this->redirectToRoute('newsman_configuration');
        }

        $listId = $config->getListId();
        $crossGroupInfo = !empty($listId) ? $config->getCrossGroupInfo($listId) : [];

        $authenticateToken = $config->getAuthenticateToken();
        $maskedToken = '';
        if (!empty($authenticateToken)) {
            $len = strlen($authenticateToken);
            $maskedToken = $len > 7
                ? substr($authenticateToken, 0, 3) . '****' . substr($authenticateToken, -4)
                : str_repeat('*', $len);
        }

        return $this->render('@Modules/newsman/views/templates/admin/configure.html.twig', [
            'maskedAuthenticateToken' => $maskedToken,
            'configurationForm' => $form->createView(),
            'isConnected' => $config->isEnabledWithApiOnly() && !empty($form->get('list_id')->getConfig()->getOption('choices')),
            'isRemarketingConnected' => $this->isRemarketingConnected($config, $remarketingGetSettings, $logger),
            'moduleVersion' => Version::getModuleVersion(),
            'crossGroupInfo' => $crossGroupInfo,
            'isMultistore' => $shopContext->isMultiShopUsed(),
            'enableSidebar' => true,
            'help_link' => false,
        ]);
    }

    protected function isRemarketingConnected(
        Config $config,
        RemarketingGetSettings $remarketingGetSettings,
        Logger $logger,
    ): bool {
        $storedId = $config->getRemarketingId();
        if (empty($storedId)) {
            return false;
        }

        try {
            $context = (new EmailListContext())
                ->setUserId($config->getUserId())
                ->setApiKey($config->getApiKey())
                ->setListId($config->getListId());

            $result = $remarketingGetSettings->execute($context);
            if (!is_array($result) || empty($result['site_id']) || empty($result['form_id'])) {
                return false;
            }

            $expectedId = $result['site_id'] . '-' . $config->getListId() . '-' . $result['form_id'] . '-' . ($result['control_list_hash'] ?? '');

            return $storedId === $expectedId;
        } catch (\Throwable $e) {
            $logger->logException($e);

            return false;
        }
    }
}
