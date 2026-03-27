<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Remarketing;

class PageView
{
    /**
     * Generate page view JS.
     */
    public function getJs(): string
    {
        return JsHelper::getRunFunc() . "('send', 'pageview');";
    }

    /**
     * Generate page view HTML.
     */
    public function getHtml(): string
    {
        return '<script>' . $this->getJs() . '</script>' . "\n";
    }
}
