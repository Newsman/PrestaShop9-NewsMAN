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

/**
 * Resolves the IP address to send to Newsman API.
 *
 * Resolution chain:
 * 1. Developer test IP (highest priority, if enabled)
 * 2. Server/host IP (if send_user_ip is disabled)
 * 3. Remote client IP (if valid and not localhost)
 * 4. Server/host IP (fallback)
 */
class IpAddress implements IpAddressInterface
{
    public function __construct(
        protected Config $config,
        protected HostIpAddress $hostIpAddress,
        protected RemoteAddress $remoteAddress,
    ) {
    }

    public function getIp(): string
    {
        if ($this->config->isDevActiveUserIp()) {
            $devIp = $this->config->getDevUserIp();
            if (!empty($devIp)) {
                return $devIp;
            }
        }

        if (!$this->config->isSendUserIp()) {
            return $this->hostIpAddress->getIp();
        }

        $ip = $this->remoteAddress->getRemoteAddress();

        if ('127.0.0.1' === $ip || empty($ip)) {
            return $this->hostIpAddress->getIp();
        }

        return $ip;
    }
}
