<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Validator;

class Email
{
    public function isValid(string $email): bool
    {
        if ('' === $email || false === strpos($email, '@')) {
            return false;
        }

        [$local, $domain] = explode('@', $email, 2);

        if (function_exists('idn_to_ascii')) {
            $asciiDomain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if (false === $asciiDomain) {
                return false;
            }
            $email = $local . '@' . $asciiDomain;
        }

        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
