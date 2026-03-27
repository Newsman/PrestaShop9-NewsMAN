<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\Newsman\Controller\Admin;

use PrestaShop\Module\Newsman\Config;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OauthLoginController extends PrestaShopAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message: 'Access denied.')]
    public function indexAction(Request $request, Config $config): Response
    {
        $callbackUrl = $request->getSchemeAndHttpHost() . $this->generateUrl('newsman_oauth_callback');

        return $this->render('@Modules/newsman/views/templates/admin/step1_login.html.twig', [
            'oauthUrl' => Config::OAUTH_AUTHORIZE_URL
                . '?response_type=code&client_id=' . Config::OAUTH_CLIENT_ID
                . '&nzmplugin=' . Config::PLATFORM_NAME
                . '&scope=api&redirect_uri=' . urlencode($callbackUrl),
            'hasCredentials' => $config->hasApiAccess(),
            'enableSidebar' => true,
            'help_link' => false,
        ]);
    }
}
