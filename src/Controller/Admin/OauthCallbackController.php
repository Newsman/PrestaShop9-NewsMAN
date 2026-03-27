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
use PrestaShop\Module\Newsman\Logger;
use PrestaShop\Module\Newsman\Service\Configuration\GetListAll;
use PrestaShop\Module\Newsman\Service\Context\Configuration\User as UserContext;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OauthCallbackController extends PrestaShopAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message: 'Access denied.')]
    public function indexAction(
        Request $request,
        Logger $logger,
        GetListAll $getListAll,
    ): Response {
        $error = $request->query->get('error', '');
        if (!empty($error)) {
            $this->addFlash('error', 'Authorization error: ' . $error);

            return $this->redirectToRoute('newsman_oauth_step1');
        }

        $code = $request->query->get('code', '');
        if (empty($code)) {
            $this->addFlash('error', 'Missing authorization code.');

            return $this->redirectToRoute('newsman_oauth_step1');
        }

        $credentials = $this->exchangeOAuthCode($logger, $code);
        if (null === $credentials) {
            return $this->redirectToRoute('newsman_oauth_step1');
        }

        $lists = $this->fetchEmailLists($logger, $getListAll, $credentials['user_id'], $credentials['api_key']);

        if (empty($lists)) {
            $this->addFlash('error', 'No email lists found in your Newsman account.');

            return $this->redirectToRoute('newsman_oauth_step1');
        }

        return $this->render('@Modules/newsman/views/templates/admin/step2_list.html.twig', [
            'lists' => $lists,
            'userId' => $credentials['user_id'],
            'apiKey' => $credentials['api_key'],
            'enableSidebar' => true,
            'help_link' => false,
        ]);
    }

    /**
     * @return array{user_id: string, api_key: string}|null
     */
    public function exchangeOAuthCode(Logger $logger, string $code): ?array
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => Config::OAUTH_TOKEN_URL,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'client_id' => Config::OAUTH_CLIENT_ID,
                    'redirect_uri' => '',
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        } catch (\Throwable $e) {
            $logger->logException($e);
            $this->addFlash('error', 'Could not complete request to Newsman: ' . $e->getMessage());

            return null;
        }

        if ($status < 200 || $status >= 300 || empty($body)) {
            $this->addFlash('error', 'Invalid response from Newsman (HTTP ' . $status . ').');

            return null;
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded) || empty($decoded['user_id']) || empty($decoded['access_token'])) {
            $this->addFlash('error', 'Unexpected response from Newsman.');

            return null;
        }

        return [
            'user_id' => (string) $decoded['user_id'],
            'api_key' => (string) $decoded['access_token'],
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function fetchEmailLists(
        Logger $logger,
        GetListAll $getListAll,
        string $userId,
        string $apiKey,
    ): array {
        $lists = [];

        try {
            $userContext = (new UserContext())
                ->setUserId($userId)
                ->setApiKey($apiKey);

            $allLists = $getListAll->execute($userContext);
            if (is_array($allLists)) {
                foreach ($allLists as $list) {
                    if (isset($list['list_type']) && 'sms' === $list['list_type']) {
                        continue;
                    }
                    $lists[] = $list;
                }
            }
        } catch (\Throwable $e) {
            $logger->logException($e);
            $this->addFlash('error', 'Could not fetch lists from Newsman: ' . $e->getMessage());
        }

        return $lists;
    }
}
