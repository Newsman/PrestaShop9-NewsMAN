<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Service\Context\Configuration;

class UpdateFeed extends EmailList
{
    protected string $feedId = '';

    /** @var array<string, mixed> */
    protected array $properties = [];

    public function setFeedId(string $feedId): static
    {
        $this->feedId = $feedId;

        return $this;
    }

    public function getFeedId(): string
    {
        return $this->feedId;
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function setProperties(array $properties): static
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
