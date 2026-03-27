<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Service\Context\Remarketing;

use PrestaShop\Module\Newsman\Service\Context\Store;

class SetPurchaseStatus extends Store
{
    protected string $orderId = '';
    protected string $orderStatus = '';

    public function setOrderId(string $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderStatus(string $orderStatus): static
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    public function getOrderStatus(): string
    {
        return $this->orderStatus;
    }
}
