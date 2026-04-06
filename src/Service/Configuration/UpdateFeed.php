<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @author Newsman by Dazoot <support@newsman.com>
 * @copyright Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Service\Configuration;

use PrestaShop\Module\Newsman\Service\AbstractService;
use PrestaShop\Module\Newsman\Service\Context\AbstractContext;
use PrestaShop\Module\Newsman\Service\Context\Configuration\UpdateFeed as UpdateFeedContext;

class UpdateFeed extends AbstractService
{
    public const ENDPOINT = 'feeds.updateFeed';

    /**
     * @param UpdateFeedContext $context
     *
     * @return array<mixed>|string
     *
     * @throws \RuntimeException
     */
    public function execute(AbstractContext $context): array|string
    {
        if (empty($context->getListId())) {
            $e = new \RuntimeException('List ID is required.');
            $this->logger->logException($e);

            throw $e;
        }

        $apiContext = $this->createApiContext()
            ->setListId($context->getListId())
            ->setEndpoint(self::ENDPOINT);

        $this->logger->info(sprintf('Try to update feed %s', $context->getListId()));

        $this->dispatchServiceHookBefore($context);

        $client = $this->createApiClient();
        $result = $client->post(
            $apiContext,
            [],
            [
                'list_id' => $apiContext->getListId(),
                'feed_id' => $context->getFeedId(),
                'props' => $context->getProperties(),
            ]
        );

        if ($client->hasError()) {
            throw new \RuntimeException($client->getErrorMessage(), $client->getErrorCode());
        }

        $this->logger->info(sprintf('Updated the feed %s', $context->getListId()));

        return $result;
    }
}
