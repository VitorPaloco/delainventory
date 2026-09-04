<?php

define('PLUGIN_DELAINVENTORY_VERSION', '0.4.0');
define("PLUGIN_DELAINVENTORY_MIN_GLPI_VERSION", "11.0.0");
define("PLUGIN_DELAINVENTORY_MAX_GLPI_VERSION", "11.0.99");

use GlpiPlugin\Delainventory\Setting;
use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\Log;
use Profile as GLPI_Profile;

function plugin_init_delainventory(): void 
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['delainventory'] = true;
    $PLUGIN_HOOKS['config_page']['delainventory'] = 'front/settings.php';
    
    if (Session::haveRight(Profile::$rightname, READ)) {
        $PLUGIN_HOOKS['menu_toadd']['delainventory'] = ['config' => Setting::class,];
    }

    Plugin::registerClass(Log::class, [
        'addtabon' => [
            'Computer',
            'Monitor',
            'Printer',
            'Phone'
        ]
    ]);

    Plugin::registerClass(Profile::class, ['addtabon' => GLPI_Profile::class]);
}

function plugin_version_delainventory(): array
{
    return [
        'name'           => 'DelaInventory',
        'version'        => PLUGIN_DELAINVENTORY_VERSION,
        'author'         => 'Vitor Paloco',
        'license'        => 'MIT',
        'homepage'       => 'https://github.com/VitorPaloco/delainventory',
        'requirements'   => [
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
