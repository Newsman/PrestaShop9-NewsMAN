<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Export\Retriever;

use PrestaShop\Module\Newsman\Util\ServerIpResolver;

class ServerIp extends AbstractRetriever implements RetrieverInterface
{
    protected ServerIpResolver $serverIpResolver;

    public function __construct(
        \PrestaShop\Module\Newsman\Config $config,
        \PrestaShop\Module\Newsman\Logger $logger,
        ServerIpResolver $serverIpResolver,
    ) {
        parent::__construct($config, $logger);
        $this->serverIpResolver = $serverIpResolver;
    }

    public function process(array $data = [], array $shopIds = []): array
    {
        return ['ip' => $this->serverIpResolver->resolve()];
    }
}
