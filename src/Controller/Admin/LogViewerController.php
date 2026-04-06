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

namespace PrestaShop\Module\Newsman\Controller\Admin;

use PrestaShop\Module\Newsman\Config;
use PrestaShop\Module\Newsman\Util\LogFileReader;
use PrestaShop\Module\Newsman\Util\Version;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LogViewerController extends PrestaShopAdminController
{
    private const MAX_LINES = 2000;
    private const DEFAULT_LINES = 500;

    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))", message: 'Access denied.')]
    public function indexAction(
        Request $request,
        Config $config,
        LogFileReader $logFileReader,
    ): Response {
        if (!$config->hasApiAccess()) {
            return $this->redirectToRoute('newsman_oauth_step1');
        }

        $logFileReader->cleanOldLogs();
        $logFiles = $logFileReader->getFiles();

        $selectedFile = $request->query->get('file', '');
        $lines = min(
            max((int) $request->query->get('lines', self::DEFAULT_LINES), 1),
            self::MAX_LINES
        );

        if (empty($selectedFile) && !empty($logFiles)) {
            $selectedFile = $logFiles[0];
        }

        $logContent = [];
        $totalLines = 0;
        $fileSize = 0;

        if (!empty($selectedFile) && $logFileReader->isValidFilename($selectedFile)) {
            $fileSize = $logFileReader->getFileSize($selectedFile);
            [$logContent, $totalLines] = $logFileReader->readTail($selectedFile, $lines);
        }

        return $this->render('@Modules/newsman/views/templates/admin/log_viewer.html.twig', [
            'logFiles' => $logFiles,
            'selectedFile' => $selectedFile,
            'logContent' => $logContent,
            'totalLines' => $totalLines,
            'displayedLines' => count($logContent),
            'fileSize' => $fileSize,
            'linesParam' => $lines,
            'moduleVersion' => Version::getModuleVersion(),
            'enableSidebar' => true,
            'help_link' => false,
        ]);
    }

    #[AdminSecurity("is_granted('delete', request.get('_legacy_controller'))", message: 'Access denied.')]
    public function deleteAction(
        Request $request,
        LogFileReader $logFileReader,
    ): RedirectResponse {
        $file = $request->request->get('file', '');

        if (!empty($file) && $logFileReader->isValidFilename($file)) {
            if ($logFileReader->deleteFile($file)) {
                $this->addFlash('success', sprintf('Log file "%s" deleted.', $file));
            } else {
                $this->addFlash('error', sprintf('Could not delete log file "%s".', $file));
            }
        }

        return $this->redirectToRoute('newsman_log_viewer');
    }
}
