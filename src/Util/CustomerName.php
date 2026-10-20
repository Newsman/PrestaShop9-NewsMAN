<?php
/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @author Newsman by Dazoot <support@newsman.com>
 * @copyright Copyright © Dazoot Software S.R.L. All rights reserved.
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website https://www.newsman.ro/
 */
declare(strict_types=1);

namespace PrestaShop\Module\Newsmanv8\Util;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Customer name helpers.
 *
 * The native One Page Checkout (ps_onepagecheckout, PrestaShop 9.2+) creates the guest
 * customer as soon as the e-mail and the required consents are filled in, with "Guest" as
 * both first and last name. The real names are written only when the order is placed.
 * These placeholders must never reach Newsman as subscriber or customer names.
 */
class CustomerName
{
    public const CHECKOUT_PLACEHOLDER = 'Guest';

    public static function isCheckoutPlaceholder(?string $firstname, ?string $lastname): bool
    {
        return $firstname === self::CHECKOUT_PLACEHOLDER && $lastname === self::CHECKOUT_PLACEHOLDER;
    }

    /**
     * @return array{0: string, 1: string} [firstname, lastname], empty strings for checkout placeholders
     */
    public static function clean(?string $firstname, ?string $lastname): array
    {
        if (self::isCheckoutPlaceholder($firstname, $lastname)) {
            return ['', ''];
        }

        return [(string) $firstname, (string) $lastname];
    }

    /**
     * @return array{0: string, 1: string} [firstname, lastname]
     */
    public static function fromCustomer(\Customer $customer): array
    {
        if (!$customer->is_guest) {
            return [(string) $customer->firstname, (string) $customer->lastname];
        }

        return self::clean((string) $customer->firstname, (string) $customer->lastname);
    }
}
