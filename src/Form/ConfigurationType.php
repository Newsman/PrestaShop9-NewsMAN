<?php
/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @author Newsman by Dazoot <support@newsman.com>
 * @copyright Copyright © Dazoot Software S.R.L. All rights reserved.
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website https://www.newsman.ro/
 */
declare(strict_types=1);

namespace PrestaShop\Module\Newsman\Form;

use PrestaShop\Module\Newsman\Config;
use PrestaShop\Module\Newsman\Logger;
use PrestaShop\Module\Newsman\Service\Configuration\GetListAll;
use PrestaShop\Module\Newsman\Service\Configuration\GetSegmentAll;
use PrestaShop\Module\Newsman\Service\Context\Configuration\EmailList as EmailListContext;
use PrestaShop\Module\Newsman\Service\Context\Configuration\User as UserContext;
use PrestaShopBundle\Form\Admin\Type\MultistoreConfigurationType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConfigurationType extends TranslatorAwareType
{
    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        protected Config $config,
        protected GetListAll $getListAll,
        protected GetSegmentAll $getSegmentAll,
        protected Logger $logger,
    ) {
        parent::__construct($translator, $locales);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $lists = $this->fetchEmailLists();
        $segments = $this->fetchSegments();

        $listChoices = [];
        foreach ($lists as $list) {
            $listChoices[$list['list_name']] = (string) $list['list_id'];
        }

        $segmentChoices = [];
        foreach ($segments as $segment) {
            $segmentChoices[$segment['segment_name']] = (string) $segment['segment_id'];
        }

        $builder
            ->add('active', SwitchType::class, [
                'label' => $this->trans('Enable Newsman', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_ACTIVE,
            ])
            ->add('user_id', TextType::class, [
                'label' => $this->trans('User ID', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_USER_ID,
            ])
            ->add('api_key', TextType::class, [
                'label' => $this->trans('API Key', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_API_KEY,
            ])
            ->add('list_id', ChoiceType::class, [
                'label' => $this->trans('Email List', 'Modules.Newsman.Admin'),
                'required' => false,
                'choices' => $listChoices,
                'placeholder' => '-- ' . $this->trans('Select a list', 'Modules.Newsman.Admin') . ' --',
                'multistore_configuration_key' => Config::KEY_LIST_ID,
            ])
            ->add('segment_id', ChoiceType::class, [
                'label' => $this->trans('Segment', 'Modules.Newsman.Admin'),
                'required' => false,
                'choices' => $segmentChoices,
                'placeholder' => '-- ' . $this->trans('No segment', 'Modules.Newsman.Admin') . ' --',
                'multistore_configuration_key' => Config::KEY_SEGMENT_ID,
            ])
            ->add('double_optin', SwitchType::class, [
                'label' => $this->trans('Double Opt-in', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_DOUBLE_OPTIN,
            ])
            ->add('send_user_ip', SwitchType::class, [
                'label' => $this->trans('Send User IP', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_SEND_USER_IP,
            ])
            ->add('server_ip', TextType::class, [
                'label' => $this->trans('Server IP', 'Modules.Newsman.Admin'),
                'required' => false,
                'help' => $this->trans('Override automatic server IP detection.', 'Modules.Newsman.Admin'),
                'multistore_configuration_key' => Config::KEY_SERVER_IP,
            ])
            ->add('export_auth_header_name', TextType::class, [
                'label' => $this->trans('Header Name', 'Modules.Newsman.Admin'),
                'required' => false,
                'help' => $this->trans('Authorization in HTTP header as name. Format alphanumeric separated by hyphen-minus. Please also set it in Newsman App > E-Commerce > Coupons > Authorisation Header name, Newsman App > E-Commerce > Feed > a feed > Header Authorization, etc.', 'Modules.Newsman.Admin'),
                'multistore_configuration_key' => Config::KEY_EXPORT_AUTH_HEADER_NAME,
            ])
            ->add('export_auth_header_key', TextType::class, [
                'label' => $this->trans('Header Key', 'Modules.Newsman.Admin'),
                'required' => false,
                'help' => $this->trans('Authorization in HTTP header as value. Format alphanumeric separated by hyphen-minus. Please also set it in Newsman App > E-Commerce > Coupons > Authorisation Header value, Newsman App > E-Commerce > Feed > a feed > Header Authorization, etc.', 'Modules.Newsman.Admin'),
                'multistore_configuration_key' => Config::KEY_EXPORT_AUTH_HEADER_KEY,
            ])
            ->add('remarketing_status', SwitchType::class, [
                'label' => $this->trans('Enable Remarketing', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_REMARKETING_STATUS,
            ])
            ->add('remarketing_id', TextType::class, [
                'label' => $this->trans('Remarketing ID', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_REMARKETING_ID,
            ])
            ->add('remarketing_anonymize_ip', SwitchType::class, [
                'label' => $this->trans('Anonymize IP', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_REMARKETING_ANONYMIZE_IP,
            ])
            ->add('remarketing_send_telephone', SwitchType::class, [
                'label' => $this->trans('Send Telephone', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_REMARKETING_SEND_TELEPHONE,
            ])
            ->add('log_severity', ChoiceType::class, [
                'label' => $this->trans('Log Severity', 'Modules.Newsman.Admin'),
                'required' => false,
                'choices' => [
                    $this->trans('None', 'Modules.Newsman.Admin') => Config::LOG_NONE,
                    'ERROR' => Config::LOG_ERROR,
                    'WARNING' => Config::LOG_WARNING,
                    'NOTICE' => Config::LOG_NOTICE,
                    'INFO' => Config::LOG_INFO,
                    'DEBUG' => Config::LOG_DEBUG,
                ],
                'placeholder' => false,
                'multistore_configuration_key' => Config::KEY_LOG_SEVERITY,
            ])
            ->add('log_clean_days', IntegerType::class, [
                'label' => $this->trans('Log Clean Days', 'Modules.Newsman.Admin'),
                'required' => false,
                'attr' => ['min' => 1],
                'multistore_configuration_key' => Config::KEY_LOG_CLEAN_DAYS,
            ])
            ->add('api_timeout', IntegerType::class, [
                'label' => $this->trans('API Timeout (seconds)', 'Modules.Newsman.Admin'),
                'required' => false,
                'attr' => ['min' => 5],
                'multistore_configuration_key' => Config::KEY_API_TIMEOUT,
            ])
            ->add('dev_active_user_ip', SwitchType::class, [
                'label' => $this->trans('Enable IP Restriction', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_DEV_ACTIVE_USER_IP,
            ])
            ->add('dev_user_ip', TextType::class, [
                'label' => $this->trans('Developer IP', 'Modules.Newsman.Admin'),
                'required' => false,
                'multistore_configuration_key' => Config::KEY_DEV_USER_IP,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'form_theme' => '@PrestaShop/Admin/TwigTemplateForm/prestashop_ui_kit.html.twig',
        ]);
    }

    public function getParent(): string
    {
        return MultistoreConfigurationType::class;
    }

    /**
     * @return array<array<string, mixed>>
     */
    protected function fetchEmailLists(): array
    {
        if (!$this->config->hasApiAccess()) {
            return [];
        }

        $lists = [];

        try {
            $userContext = (new UserContext())
                ->setUserId($this->config->getUserId())
                ->setApiKey($this->config->getApiKey());

            $allLists = $this->getListAll->execute($userContext);
            if (is_array($allLists)) {
                foreach ($allLists as $list) {
                    if (isset($list['list_type']) && 'sms' === $list['list_type']) {
                        continue;
                    }
                    $lists[] = $list;
                }
            }
        } catch (\Throwable $e) {
            $this->logger->logException($e);
        }

        return $lists;
    }

    /**
     * @return array<array<string, mixed>>
     */
    protected function fetchSegments(): array
    {
        $listId = $this->config->getListId();
        if (empty($listId)) {
            return [];
        }

        try {
            $listContext = (new EmailListContext())
                ->setUserId($this->config->getUserId())
                ->setApiKey($this->config->getApiKey())
                ->setListId($listId);

            $allSegments = $this->getSegmentAll->execute($listContext);
            if (is_array($allSegments)) {
                return $allSegments;
            }
        } catch (\Throwable $e) {
            $this->logger->logException($e);
        }

        return [];
    }
}
