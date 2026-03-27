<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Export\V1;

class ApiV1Exception extends \RuntimeException
{
    protected int $errorCode;
    protected int $httpStatus;

    public function __construct(int $errorCode, string $message, int $httpStatus = 500)
    {
        $this->errorCode = $errorCode;
        $this->httpStatus = $httpStatus;
        parent::__construct($message);
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
