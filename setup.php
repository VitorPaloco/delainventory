<?php

define('PLUGIN_DELAINVENTORY_VERSION', '1.0.0');
define('PLUGIN_DELAINVENTORY_MIN_GLPI_VERSION', '11.0.0');
define('PLUGIN_DELAINVENTORY_MAX_GLPI_VERSION', '11.0.99');

use GlpiPlugin\Delainventory\Config;
use GlpiPlugin\Delainventory\Log;

function plugin_init_delainventory(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['delainventory'] = true;
    $PLUGIN_HOOKS['config_page']['delainventory'] = 'front/config.php';
    $PLUGIN_HOOKS['menu_toadd']['delainventory'] = ['config' => Config::class];

    Plugin::registerClass(Log::class, [
        'addtabon' => [
            Computer::class,
            Monitor::class,
            Printer::class,
            Phone::class
        ]
    ]);
}

function plugin_version_delainventory(): array
{
    return [
        'name'      => 'DelaInventory',
        'version'   => PLUGIN_DELAINVENTORY_VERSION,
        'author'    => 'Vitor Paloco',
        'license'   => 'MIT',
        'homepage'  => 'https://github.com/VitorPaloco/Delainventory-GLPI',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_DELAINVENTORY_MIN_GLPI_VERSION,
                'max' => PLUGIN_DELAINVENTORY_MAX_GLPI_VERSION,
            ],
        ],
    ];
}

function plugin_delainventory_check_prerequisites(): bool
{
    return true;
}

function plugin_delainventory_check_config(bool $verbose = false): bool
{
    return true;
}