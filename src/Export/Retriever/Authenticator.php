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

namespace PrestaShop\Module\Newsman\Export\Retriever;

use PrestaShop\Module\Newsman\Config;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;

class Authenticator
{
    public const API_KEY_PARAM = 'nzmhash';

    protected Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws \OutOfBoundsException
     */
    public function authenticate(string $apiKey, ?ShopConstraint $shopConstraint = null): bool
    {
        if (empty($apiKey)) {
            throw new \OutOfBoundsException('Empty API key provided.');
        }

        $configApiKey = $this->config->getApiKey($shopConstraint);
        $configAuthToken = $this->config->getAuthenticateToken($shopConstraint);

        $alternateName = $this->config->getExportAuthHeaderName($shopConstraint);
        $alternateKey = $this->config->getExportAuthHeaderKey($shopConstraint);

        $isAuthenticated = false;

        if ($configApiKey === $apiKey) {
            $isAuthenticated = true;
        }
        if (!empty($configAuthToken) && $configAuthToken === $apiKey) {
            $isAuthenticated = true;
        }
        if (!empty($alternateName) && !empty($alternateKey) && $alternateKey === $apiKey) {
            $isAuthenticated = true;
        }

        if (!$isAuthenticated) {
            $shopId = $shopConstraint ? $shopConstraint->getShopId() : null;
            throw new \OutOfBoundsException(sprintf('Invalid API key for shop ID %s', $shopId ?? 'default'));
        }

        return true;
    }
}
