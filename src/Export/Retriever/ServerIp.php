<?php
/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @author Newsman by Dazoot <support@newsman.com>
 * @copyright Copyright © Dazoot Software S.R.L. All rights reserved.
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website https://www.newsman.ro/
 */

namespace PrestaShop\Module\Newsman\Export\Retriever;

use PrestaShop\Module\Newsman\Util\ServerIpResolver;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
