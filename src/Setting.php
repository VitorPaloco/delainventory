<?php

namespace GlpiPlugin\Delainventory;

use DBConnection;
use CommonDBTM;

class Setting
{
    public static function getMenuName(): string
    {
        return 'DelaInventory';
    }

    public static function getMenuContent(): array
    {
        return [
            'title' => self::getMenuName(),
            'page'  => '/plugins/delainventory/front/settings.php',
            'icon'  => 'fa-solid fa-layer-group',
        ];
    }
}