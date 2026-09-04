<?php

namespace GlpiPlugin\Delainventory;

use ProfileRight;

class Profile
{
    public static $rightname = 'delainventory';

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
}