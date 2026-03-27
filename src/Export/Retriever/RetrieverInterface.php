<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Export\Retriever;

interface RetrieverInterface
{
    /**
     * @param array<string, mixed> $data
     * @param array<int> $shopIds
     *
     * @return array<mixed>
     */
    public function process(array $data = [], array $shopIds = []): array;
}
