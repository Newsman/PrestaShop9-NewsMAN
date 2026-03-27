<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Api;

interface ContextInterface
{
    public function getUserId(): string;

    public function setUserId(string $userId): static;

    public function getApiKey(): string;

    public function setApiKey(string $apiKey): static;

    public function getEndpoint(): string;

    public function setEndpoint(string $endpoint): static;

    public function getListId(): string;

    public function setListId(string $listId): static;

    public function getSegmentId(): string;

    public function setSegmentId(string $segmentId): static;
}
