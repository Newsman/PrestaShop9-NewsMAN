<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Service\Sms;

use PrestaShop\Module\Newsman\Service\AbstractService;
use PrestaShop\Module\Newsman\Service\Context\AbstractContext;
use PrestaShop\Module\Newsman\Service\Context\Sms\Unsubscribe as UnsubscribeContext;

class Unsubscribe extends AbstractService
{
    public const ENDPOINT = 'sms.saveUnsubscribe';

    /**
     * @param UnsubscribeContext $context
     *
     * @return array<mixed>|string
     *
     * @throws \RuntimeException
     */
    public function execute(AbstractContext $context): array|string
    {
        $apiContext = $this->createApiContext()
            ->setListId($context->getListId())
            ->setEndpoint(self::ENDPOINT);

        $this->logger->info(sprintf('Try to unsubscribe telephone %s', $context->getTelephone()));

        $client = $this->createApiClient();
        $result = $client->post(
            $apiContext,
            [],
            [
                'list_id' => $apiContext->getListId(),
                'telephone' => $context->getTelephone(),
                'ip' => $context->getIp(),
            ]
        );

        if ($client->hasError()) {
            throw new \RuntimeException($client->getErrorMessage(), $client->getErrorCode());
        }

        $this->logger->info(sprintf('Unsubscribed telephone %s', $context->getTelephone()));

        return $result;
    }
}
