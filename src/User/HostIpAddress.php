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

namespace PrestaShop\Module\Newsman\User;

use PrestaShop\Module\Newsman\Config;
use PrestaShop\Module\Newsman\Util\ServerIpResolver;
use PrestaShop\PrestaShop\Adapter\Configuration;

/**
 * Resolves the server (host) IP address with caching.
 *
 * On first call, auto-detects the public IP and saves it to configuration
 * so subsequent calls are instant.
 */
class HostIpAddress implements IpAddressInterface
{
    public const NOT_FOUND = 'not found';

    protected ?string $ip = null;

    public function __construct(
        protected Config $config,
        protected ServerIpResolver $serverIpResolver,
        protected Configuration $configuration,
    ) {
    }

    public function getIp(): string
    {
        if (null !== $this->ip) {
            return $this->ip;
        }

        $ip = $this->config->getServerIp();
        if (!empty($ip)) {
            if (self::NOT_FOUND === $ip) {
                $this->ip = '';
            } else {
                $this->ip = $ip;
            }

            return $this->ip;
        }

        $ip = $this->serverIpResolver->resolve();
        if (!empty($ip)) {
            $this->configuration->set(Config::KEY_SERVER_IP, $ip);
            $this->ip = $ip;

            return $this->ip;
        }

        $this->configuration->set(Config::KEY_SERVER_IP, self::NOT_FOUND);
        $this->ip = '';

        return $this->ip;
    }
}
