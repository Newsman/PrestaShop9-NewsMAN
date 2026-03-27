<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Remarketing;

class CustomerIdentify
{
    /**
     * Generate customer identify JS.
     */
    public function getHtml(string $email, string $firstName = '', string $lastName = ''): string
    {
        if (empty($email)) {
            return '';
        }

        $js = '_nzm.identify({email: "' . JsHelper::escapeJs($email) . '"';
        if (!empty($firstName)) {
            $js .= ', first_name: "' . JsHelper::escapeJs($firstName) . '"';
        }
        if (!empty($lastName)) {
            $js .= ', last_name: "' . JsHelper::escapeJs($lastName) . '"';
        }
        $js .= '});';

        return '<script>' . $js . '</script>' . "\n";
    }
}
