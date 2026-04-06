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

namespace PrestaShop\Module\Newsman\Util;

class Version
{
    public static function getModuleVersion(): string
    {
        $module = \Module::getInstanceByName('newsman');

        return $module ? $module->version : '';
    }

    public static function getIntegrationName(): string
    {
        return 'Newsman for PrestaShop';
    }
}
