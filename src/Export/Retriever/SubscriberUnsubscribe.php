<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Export\Retriever;

use PrestaShop\Module\Newsman\Action\Reverse\NewsletterManager;
use PrestaShop\Module\Newsman\Config;
use PrestaShop\Module\Newsman\Export\V1\ApiV1Exception;
use PrestaShop\Module\Newsman\Logger;

class SubscriberUnsubscribe extends AbstractRetriever implements RetrieverInterface
{
    protected NewsletterManager $newsletterManager;

    public function __construct(Config $config, Logger $logger, NewsletterManager $newsletterManager)
    {
        parent::__construct($config, $logger);
        $this->newsletterManager = $newsletterManager;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<mixed>
     */
    /**
     * @param array<int> $shopIds
     */
    public function process(array $data = [], array $shopIds = []): array
    {
        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        if (empty($email)) {
            throw new ApiV1Exception(3200, 'Missing "email" parameter', 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ApiV1Exception(3201, 'Invalid email address: ' . $email, 400);
        }

        $this->logger->info(sprintf('subscriber.unsubscribe: %s, shop IDs [%s]', $email, implode(',', $shopIds)));

        $this->newsletterManager->unsubscribeMultiShop($email, $shopIds);

        return ['success' => true, 'email' => $email];
    }
}
