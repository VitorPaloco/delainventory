<?php

namespace GlpiPlugin\Delainventory;

use Plugin;

class Setting
{
    public static function getMenuName(): string
    {
        return 'DelaInventory';
    }

    public static function getMenuContent(): array
    {
        $dashboard = Plugin::getWebDir('delainventory') . '/front/dashboard.php';
        $settings  = Plugin::getWebDir('delainventory') . '/front/settings.php';

        return [
            'title' => self::getMenuName(),
            'page'  => $dashboard,
            'icon'  => 'fa-solid fa-layer-group',

            'options' => [
                'dashboard' => ['title' => 'Dashboard', 'page'  => $dashboard],
                'settings' => ['title' => __('Setup'), 'page'  => $settings]
            ]
        ];
    }
}