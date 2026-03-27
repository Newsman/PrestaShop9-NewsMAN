<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Api;

interface ClientInterface
{
    /**
     * @param array<string, mixed> $params
     *
     * @return array<mixed>|string
     */
    public function get(ContextInterface $context, array $params = []): array|string;

    /**
     * @param array<string, mixed> $getParams
     * @param array<string, mixed> $postParams
     *
     * @return array<mixed>|string
     */
    public function post(ContextInterface $context, array $getParams = [], array $postParams = []): array|string;

    public function hasError(): bool;

    public function getErrorCode(): int;

    public function getErrorMessage(): string;
}
