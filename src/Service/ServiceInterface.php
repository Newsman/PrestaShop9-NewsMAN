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

namespace PrestaShop\Module\Newsman\Service;

use PrestaShop\Module\Newsman\Service\Context\AbstractContext;

interface ServiceInterface
{
    /**
     * @return array<mixed>|string
     */
    public function execute(AbstractContext $context): array|string;
}
