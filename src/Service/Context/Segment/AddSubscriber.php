<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Service\Context\Segment;

use PrestaShop\Module\Newsman\Service\Context\Store;

class AddSubscriber extends Store
{
    protected string $segmentId = '';
    protected string $subscriberId = '';

    public function setSegmentId(string $segmentId): static
    {
        $this->segmentId = $segmentId;

        return $this;
    }

    public function getSegmentId(): string
    {
        return $this->segmentId;
    }

    public function setSubscriberId(string $subscriberId): static
    {
        $this->subscriberId = $subscriberId;

        return $this;
    }

    public function getSubscriberId(): string
    {
        return $this->subscriberId;
    }
}
