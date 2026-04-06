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

namespace PrestaShop\Module\Newsman\Service\Context\Remarketing;

use PrestaShop\Module\Newsman\Service\Context\Store;

class SaveOrder extends Store
{
    /** @var array<string, mixed> */
    protected array $orderDetails = [];

    /** @var array<int, array<string, mixed>> */
    protected array $orderProducts = [];

    /**
     * @param array<string, mixed> $orderDetails
     */
    public function setOrderDetails(array $orderDetails): static
    {
        $this->orderDetails = $orderDetails;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrderDetails(): array
    {
        return $this->orderDetails;
    }

    /**
     * @param array<int, array<string, mixed>> $orderProducts
     */
    public function setOrderProducts(array $orderProducts): static
    {
        $this->orderProducts = $orderProducts;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getOrderProducts(): array
    {
        return $this->orderProducts;
    }
}
