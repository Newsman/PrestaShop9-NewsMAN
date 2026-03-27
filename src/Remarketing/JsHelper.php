<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Remarketing;

class JsHelper
{
    public const JS_TRACK_RUN_FUNC = '_nzm.run';

    public static function escapeHtml(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', false);
    }

    public static function escapeJs(?string $value): string
    {
        return addslashes((string) $value);
    }

    public static function getRunFunc(): string
    {
        return self::escapeHtml(self::JS_TRACK_RUN_FUNC);
    }
}
