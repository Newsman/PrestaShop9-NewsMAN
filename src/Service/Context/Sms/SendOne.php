<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Service\Context\Sms;

use PrestaShop\Module\Newsman\Service\Context\Store;

class SendOne extends Store
{
    protected string $to = '';
    protected string $text = '';

    public function setTo(string $to): static
    {
        $this->to = $to;

        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
