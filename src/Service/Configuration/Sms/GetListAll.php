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

namespace PrestaShop\Module\Newsman\Service\Configuration\Sms;

use PrestaShop\Module\Newsman\Service\AbstractService;
use PrestaShop\Module\Newsman\Service\Context\AbstractContext;
use PrestaShop\Module\Newsman\Service\Context\Configuration\User;

class GetListAll extends AbstractService
{
    public const ENDPOINT = 'sms.lists';

    /**
     * @param User $context
     *
     * @return array<mixed>|string
     *
     * @throws \RuntimeException
     */
    public function execute(AbstractContext $context): array|string
    {
        $apiContext = $this->createApiContext()
            ->setUserId($context->getUserId())
            ->setApiKey($context->getApiKey())
            ->setEndpoint(self::ENDPOINT);

        $client = $this->createApiClient();
        $result = $client->get($apiContext);

        if ($client->hasError()) {
            throw new \RuntimeException($client->getErrorMessage(), $client->getErrorCode());
        }

        return $result;
    }
}
