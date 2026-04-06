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

namespace PrestaShop\Module\Newsman\Service\Context\Configuration;

use PrestaShop\Module\Newsman\Service\Context\Store;

class User extends Store
{
    protected string $userId = '';
    protected string $apiKey = '';

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
