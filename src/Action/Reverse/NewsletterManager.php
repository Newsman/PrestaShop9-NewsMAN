<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Action\Reverse;

class NewsletterManager
{
    public function subscribe(string $email, ?int $shopId = null): bool
    {
        $shopFilter = null !== $shopId ? ' AND id_shop = ' . (int) $shopId : '';

        $result = (bool) \Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'customer SET newsletter = 1, newsletter_date_add = NOW()'
            . " WHERE email = '" . pSQL($email) . "'" . $shopFilter
        );

        $affected = (int) \Db::getInstance()->Affected_Rows();
        if ($affected > 0) {
            return $result;
        }

        if (!$this->isEmailSubscriptionActive()) {
            return false;
        }

        $existing = \Db::getInstance()->getValue(
            'SELECT id FROM ' . _DB_PREFIX_ . 'emailsubscription'
            . " WHERE email = '" . pSQL($email) . "'" . $shopFilter
        );

        if ($existing) {
            return (bool) \Db::getInstance()->execute(
                'UPDATE ' . _DB_PREFIX_ . 'emailsubscription SET active = 1'
                . " WHERE email = '" . pSQL($email) . "'" . $shopFilter
            );
        }

        $idShop = $shopId ?? (int) \Configuration::get('PS_SHOP_DEFAULT');
        $idShopGroup = (int) \Shop::getGroupFromShop($idShop);
        $idLang = (int) \Configuration::get('PS_LANG_DEFAULT', null, $idShopGroup, $idShop);

        return (bool) \Db::getInstance()->execute(
            'INSERT INTO ' . _DB_PREFIX_ . 'emailsubscription'
            . ' (id_shop, id_shop_group, email, newsletter_date_add, ip_registration_newsletter, active, id_lang)'
            . ' VALUES ('
            . (int) $idShop . ', '
            . (int) $idShopGroup . ', '
            . "'" . pSQL($email) . "', "
            . 'NOW(), '
            . "'', "
            . '1, '
            . (int) $idLang
            . ')'
        );
    }

    public function unsubscribe(string $email, ?int $shopId = null): bool
    {
        $shopFilter = null !== $shopId ? ' AND id_shop = ' . (int) $shopId : '';

        \Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'customer SET newsletter = 0'
            . " WHERE email = '" . pSQL($email) . "'" . $shopFilter
        );

        if ($this->isEmailSubscriptionActive()) {
            \Db::getInstance()->execute(
                'UPDATE ' . _DB_PREFIX_ . 'emailsubscription SET active = 0'
                . " WHERE email = '" . pSQL($email) . "'" . $shopFilter
            );
        }

        return true;
    }

    /**
     * Subscribe an email across multiple shops.
     *
     * @param array<int> $shopIds
     */
    public function subscribeMultiShop(string $email, array $shopIds): bool
    {
        if (empty($shopIds)) {
            return $this->subscribe($email);
        }

        $result = true;
        foreach ($shopIds as $shopId) {
            $result = $this->subscribe($email, (int) $shopId) && $result;
        }

        return $result;
    }

    /**
     * Unsubscribe an email across multiple shops.
     *
     * @param array<int> $shopIds
     */
    public function unsubscribeMultiShop(string $email, array $shopIds): bool
    {
        if (empty($shopIds)) {
            return $this->unsubscribe($email);
        }

        $result = true;
        foreach ($shopIds as $shopId) {
            $result = $this->unsubscribe($email, (int) $shopId) && $result;
        }

        return $result;
    }

    protected function isEmailSubscriptionActive(): bool
    {
        return (bool) \Module::isEnabled('ps_emailsubscription');
    }
}
