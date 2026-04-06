<?php

$config = new class() extends PrestaShop\CodingStandards\CsFixer\Config {
    public function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
        ]);
    }
};
$config
    ->setUsingCache(true)
    ->getFinder()
    ->in(__DIR__)
    ->exclude('vendor');

return $config;
