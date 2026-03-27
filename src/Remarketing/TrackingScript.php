<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman\Remarketing;

use PrestaShop\Module\Newsman\Config;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;

class TrackingScript
{
    protected Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Generate the main tracking script HTML.
     */
    public function getHtml(?ShopConstraint $shopConstraint = null, string $currencyCode = ''): string
    {
        $scriptJs = $this->config->getRemarketingScriptJs($shopConstraint);

        $hookResult = \Hook::exec(
            'actionNewsmanRemarketingTrackingScriptAfter',
            ['script_js' => $scriptJs],
            null,
            false,
            true,
            false,
            null,
            true
        );
        if (is_array($hookResult) && isset($hookResult['script_js'])) {
            $scriptJs = $hookResult['script_js'];
        }

        if (empty($scriptJs)) {
            return '';
        }

        $nzmConfigJs = "var _nzm_config = _nzm_config || [];\n"
            . "_nzm_config['disable_datalayer'] = 1;\n";

        $output = '<script>' . "\n" . $nzmConfigJs . '</script>' . "\n";

        $output .= '<script>' . "\n" . $scriptJs . "\n" . '</script>' . "\n";

        $run = JsHelper::getRunFunc();
        $settingsJs = '';

        if ($this->config->isRemarketingAnonymizeIp($shopConstraint)) {
            $settingsJs .= $run . "('set', 'anonymizeIp', true);\n";
        }

        if (!empty($currencyCode)) {
            $settingsJs .= $run . "('set', 'currencyCode', '" . JsHelper::escapeHtml($currencyCode) . "');\n";
        }

        $hookResult = \Hook::exec(
            'actionNewsmanRemarketingTrackingAttributesBefore',
            ['attributes' => $settingsJs],
            null,
            false,
            true,
            false,
            null,
            true
        );
        if (is_array($hookResult) && isset($hookResult['attributes'])) {
            $settingsJs = $hookResult['attributes'];
        }

        if (!empty($settingsJs)) {
            $output .= '<script>' . "\n" . $settingsJs . '</script>' . "\n";
        }

        return $output;
    }
}
