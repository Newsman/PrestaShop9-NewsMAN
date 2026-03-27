<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Export\Retriever;

class Products extends ProductsFeed
{
    /**
     * @param array<string, mixed> $product
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<int> $shopIds
     */
    public function processProduct(array $product, int $langId, array $shopIds = []): array
    {
        $row = parent::processProduct($product, $langId, $shopIds);

        if (isset($row['price_discount']) || isset($row['price_full'])) {
            $row['price'] = $row['price_discount'] ?? $row['price_full'];
            $row['price_old'] = $row['price_full'] ?? '';
            unset($row['price_discount'], $row['price_full']);
        } else {
            $row['price_old'] = '';
        }

        return $row;
    }
}
