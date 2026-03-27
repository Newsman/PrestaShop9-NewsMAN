<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Export\Retriever;

use PrestaShop\Module\Newsman\Config;
use PrestaShop\Module\Newsman\Export\V1\ApiV1Exception;
use PrestaShop\Module\Newsman\Logger;
use PrestaShop\Module\Newsman\Service\Configuration\Remarketing\GetSettings;
use PrestaShop\Module\Newsman\Service\Context\Configuration\EmailList;
use PrestaShop\PrestaShop\Adapter\Configuration as ConfigurationAdapter;

/**
 * Handle inbound refresh.remarketing API v1 request.
 *
 * Fetches the remarketing script from the Newsman API via
 * remarketing.getSettings and stores it in configuration.
 */
class RefreshRemarketing extends AbstractRetriever
{
    protected GetSettings $getSettingsService;
    protected ConfigurationAdapter $configuration;

    public function __construct(
        Config $config,
        Logger $logger,
        GetSettings $getSettingsService,
        ConfigurationAdapter $configuration,
    ) {
        parent::__construct($config, $logger);
        $this->getSettingsService = $getSettingsService;
        $this->configuration = $configuration;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<mixed>
     *
     * @throws ApiV1Exception
     */
    public function process(array $data = [], array $shopIds = []): array
    {
        $shopId = $shopIds[0] ?? null;
        $refresh = isset($data['refresh']) ? (int) $data['refresh'] : 0;
        if (1 !== $refresh) {
            throw new ApiV1Exception(9001, 'Missing or invalid "refresh" parameter: must be 1', 400);
        }

        $shopConstraint = Config::shopConstraint($shopId);

        $userId = $this->config->getUserId($shopConstraint);
        $apiKey = $this->config->getApiKey($shopConstraint);
        $listId = $this->config->getListId($shopConstraint);

        if (empty($userId) || empty($apiKey) || empty($listId)) {
            throw new ApiV1Exception(9002, 'Plugin is not configured: missing user ID, API key, or list ID', 400);
        }

        try {
            $context = (new EmailList())
                ->setUserId($userId)
                ->setApiKey($apiKey)
                ->setListId($listId);

            $settings = $this->getSettingsService->execute($context);
        } catch (\Exception $e) {
            $this->logger->logException($e);
            throw new ApiV1Exception(9003, 'Failed to retrieve remarketing settings from Newsman API', 500);
        }

        if (empty($settings) || !is_array($settings) || empty($settings['javascript'])) {
            throw new ApiV1Exception(9004, 'Newsman API returned empty remarketing script', 500);
        }

        $oldRemarketingJs = $this->config->getRemarketingScriptJs($shopConstraint);
        $newRemarketingJs = $settings['javascript'];

        $this->configuration->set(Config::KEY_REMARKETING_SCRIPT_JS, Config::stripScriptTags($newRemarketingJs), $shopConstraint);

        $siteId = $settings['site_id'] ?? '';
        $formId = $settings['form_id'] ?? '';
        $controlListHash = $settings['control_list_hash'] ?? '';

        if (!empty($siteId) && !empty($formId)) {
            $remarketingId = $siteId . '-' . $listId . '-' . $formId . '-' . $controlListHash;
            $this->configuration->set(Config::KEY_REMARKETING_ID, $remarketingId, $shopConstraint);
            $this->configuration->set(Config::KEY_REMARKETING_STATUS, '1', $shopConstraint);
        }

        $this->logger->info('refresh.remarketing: updated remarketing settings');

        return [
            'status' => 1,
            'old_remarketing_js' => !empty($oldRemarketingJs) ? $oldRemarketingJs : '',
            'new_remarketing_js' => $newRemarketingJs,
        ];
    }
}
