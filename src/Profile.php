<?php

/**
 * -------------------------------------------------------------------------
 * DelaInventory plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2026 by the DelaInventory plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
 * @link      https://github.com/pluginsGLPI/delainventory
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Delainventory;

use ProfileRight;
use CommonGLPI;
use CommonDBTM;
use Profile as PluginProfile;

class Profile extends CommonDBTM
{
    public static $rightname = 'delainventory';

    public static function getMenuName(): string
    {
        return 'DelaInventory';
    }

    public static function install(): void
    {
        $exists = countElementsInTable(ProfileRight::getTable(), ['name' => self::$rightname]) > 0;

        if ($exists) {
            return;
        }

        ProfileRight::addProfileRights([self::$rightname]);
        $profileRight = new ProfileRight();

        foreach (PluginProfile::getSuperAdminProfilesId() as $profileId) {
            if ($profileRight->getFromDBByCrit(['profiles_id' => $profileId, 'name' => self::$rightname])) {
                $profileRight->update([
                    'id'     => $profileRight->getID(),
                    'rights' => READ | UPDATE,
                ]);
            }
        }
    }

    public static function uninstall(): void
    {
        ProfileRight::deleteProfileRights([self::$rightname]);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof PluginProfile && $item->getField('id')) {
            return self::createTabEntry(self::getMenuName(), 0, null, 'ti ti-stack-2');
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        require GLPI_ROOT . '/plugins/delainventory/front/profile.php';

        return true;
    }

}
