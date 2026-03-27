<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Action\Order;

use PrestaShop\Module\Newsman\Config;
use PrestaShop\Module\Newsman\Export\Order\StatusMapper;
use PrestaShop\Module\Newsman\Logger;
use PrestaShop\Module\Newsman\Service\Context\Remarketing\SetPurchaseStatus as SetPurchaseStatusContext;
use PrestaShop\Module\Newsman\Service\Remarketing\SetPurchaseStatus;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;

class Status
{
    protected Config $config;
    protected Logger $logger;
    protected StatusMapper $statusMapper;
    protected SetPurchaseStatus $setPurchaseStatus;
    protected Configuration $configuration;

    public function __construct(
        Config $config,
        Logger $logger,
        StatusMapper $statusMapper,
        SetPurchaseStatus $setPurchaseStatus,
        Configuration $configuration,
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->statusMapper = $statusMapper;
        $this->setPurchaseStatus = $setPurchaseStatus;
        $this->configuration = $configuration;
    }

    /**
     * Send order status update to Newsman API.
     */
    public function execute(int $orderId, int $newOrderStateId, ?ShopConstraint $shopConstraint = null): void
    {
        if (!$this->config->isEnabled($shopConstraint)) {
            return;
        }

        try {
            $orderStateName = $this->getOrderStateName($newOrderStateId);
            $orderStatus = $this->statusMapper->map($newOrderStateId, $orderStateName);

            $context = new SetPurchaseStatusContext();
            $context->setListId($this->config->getListId($shopConstraint))
                ->setOrderId((string) $orderId)
                ->setOrderStatus($orderStatus);

            $this->setPurchaseStatus->execute($context);
        } catch (\Exception $e) {
            $this->logger->logException($e);
        }
    }

    protected function getOrderStateName(int $orderStateId): string
    {
        $langId = (int) $this->configuration->get('PS_LANG_DEFAULT');
        $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'order_state_lang'
            . ' WHERE id_order_state = ' . $orderStateId
            . ' AND id_lang = ' . $langId;

        $result = \Db::getInstance()->getValue($sql);

        return $result ? (string) $result : '';
    }
}
