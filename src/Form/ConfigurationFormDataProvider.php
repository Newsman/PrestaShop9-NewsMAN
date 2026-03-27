<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\Newsman\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

class ConfigurationFormDataProvider implements FormDataProviderInterface
{
    public function __construct(
        protected DataConfigurationInterface $dataConfiguration,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->dataConfiguration->getConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data): array
    {
        return $this->dataConfiguration->updateConfiguration($data);
    }
}
