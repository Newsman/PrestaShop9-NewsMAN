<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Export\Retriever;

class ServerCloudflare extends AbstractRetriever implements RetrieverInterface
{
    public function process(array $data = [], array $shopIds = []): array
    {
        return ['cloudflare' => !empty($_SERVER['HTTP_CF_RAY'])];
    }
}
