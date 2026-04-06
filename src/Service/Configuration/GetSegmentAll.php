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
use PrestaShop\Module\Newsman\Service\Context\Configuration\EmailList;

class GetSegmentAll extends AbstractService
{
    public const ENDPOINT = 'segment.all';

    /**
     * @param EmailList $context
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
            ->setUserId($context->getUserId())
            ->setApiKey($context->getApiKey())
            ->setEndpoint(self::ENDPOINT);

        $this->dispatchServiceHookBefore($context);

        $client = $this->createApiClient();
        $result = $client->get($apiContext, ['list_id' => $context->getListId()]);

        if ($client->hasError()) {
            throw new \RuntimeException($client->getErrorMessage(), $client->getErrorCode());
        }

        return $result;
    }
}
