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

namespace PrestaShop\Module\Newsman\Service\Context;

class InitUnsubscribeEmail extends UnsubscribeEmail
{
    /** @var array<string, mixed>|null */
    protected ?array $options = null;

    /**
     * @param array<string, mixed>|null $options
     */
    public function setOptions(?array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }
}
