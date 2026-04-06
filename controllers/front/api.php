<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @author Newsman by Dazoot <support@newsman.com>
 * @copyright Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\Newsman\Export\Router;
use PrestaShop\Module\Newsman\Webhooks;

/**
 * @see ModuleFrontControllerCore
 */
class NewsmanApiModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        if ($this->isWebhookRequest()) {
            /** @var Webhooks $webhooks */
            $webhooks = $this->get(Webhooks::class);
            $webhooks->execute($_POST['newsman_events']);
        } else {
            /** @var Router $router */
            $router = $this->get(Router::class);
            $router->execute();
        }
    }

    protected function isWebhookRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST'
            && !empty($_POST['newsman_events']);
    }
}
