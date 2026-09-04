<?php

namespace GlpiPlugin\Delainventory;

use ProfileRight;
use CommonGLPI;
use CommonDBTM;
use Profile as GLPI_Profile;

class Profile extends CommonDBTM
{
    public static $rightname = 'delainventory';

    public static function getMenuName()
    {
        return 'DelaInventory';
    }

    public static function install()
    {
        ProfileRight::addProfileRights([self::$rightname]);
        $profileRight = new ProfileRight();

        if ($profileRight->getFromDBByCrit(['profiles_id' => 4, 'name' => self::$rightname])) {
            $profileRight->update([
                'id' => $profileRight->getID(), 
                'rights' => READ | UPDATE,
            ]);
        }
    }

    public static function uninstall()
    {
        ProfileRight::deleteProfileRights([self::$rightname]);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof GLPI_Profile && $item->getField('id')) {
            return self::createTabEntry(self::getMenuName(), 0, null, 'ti ti-stack-2');
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) 
    {
        require GLPI_ROOT . '/plugins/delainventory/front/profile.php';
    }

}