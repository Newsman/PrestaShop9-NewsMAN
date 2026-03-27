<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Export\Retriever;

class Subscribers extends SubscribersBase
{
    /**
     * @param array<string, mixed> $subscriber
     * @param array<int, string> $phones
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<int> $shopIds
     */
    public function processSubscriber(array $subscriber, array $phones = [], array $shopIds = []): array
    {
        $row = [
            'subscriber_id' => (string) $subscriber['subscriber_id'],
            'firstname' => $subscriber['firstname'],
            'lastname' => $subscriber['lastname'],
            'email' => $subscriber['email'],
            'date_subscribed' => $subscriber['date_added'],
            'confirmed' => 1,
            'source' => $subscriber['source_type'] === 'guest'
                ? 'PrestaShop guest subscribers'
                : 'PrestaShop subscribers',
        ];

        if (!empty($subscriber['ip'])) {
            $row['ip'] = $subscriber['ip'];
        }

        if ($this->config->isRemarketingSendTelephoneByShopIds($shopIds)) {
            $subscriberId = (int) $subscriber['subscriber_id'];
            $row['phone'] = $phones[$subscriberId] ?? '';
        }

        $shopId = $shopIds[0] ?? null;
        $hookResult = \Hook::exec(
            'actionNewsmanExportRetrieverSubscribersProcessSubscriberAfter',
            ['row' => $row, 'subscriber' => $subscriber, 'shop_id' => $shopId, 'shop_ids' => $shopIds],
            null,
            false,
            true,
            false,
            null,
            true
        );
        if (is_array($hookResult) && isset($hookResult['row'])) {
            $row = $hookResult['row'];
        }

        return $row;
    }
}
