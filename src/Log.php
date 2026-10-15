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

use GlpiPlugin\Delainventory\AssetType;
use GlpiPlugin\Delainventory\Profile;
use DBConnection;
use CommonDBTM;
use CommonGLPI;
use DBmysqlIterator;
use Session;
use Computer;
use Monitor;
use Printer;
use Phone;

class Log extends CommonDBTM
{
    public static function getMenuName(): string
    {
        return 'DelaInventory';
    }

    public static function install(): void
    {
        global $DB;

        $table = self::getTable();

        if (!$DB->tableExists($table)) {

            $default_charset   = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();

            $query = "CREATE TABLE `{$table}` (
                `id` INT UNSIGNED AUTO_INCREMENT,
                `itemtype` VARCHAR(100) NOT NULL,
                `item_id` INT UNSIGNED NOT NULL,
                `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `comment` VARCHAR(255) NOT NULL,
                `users_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_item` (`itemtype`, `item_id`)
            ) ENGINE=InnoDB
            DEFAULT CHARSET={$default_charset}
            COLLATE={$default_collation}";

            $DB->doQuery($query);
        }
    }

    private static function isEnabled(string $itemtype): bool
    {
        global $DB;

        $table = AssetType::getTable();

        $iterator = $DB->request([
            'FROM' => $table,
            'WHERE' => [
                'itemtype' => $itemtype,
                'enabled' => 1,
            ],
            'LIMIT' => 1,
        ]);

        return count($iterator) > 0;
    }

    private static function isAllowed(string $itemtype): bool
    {
        return in_array($itemtype, [
            Computer::class,
            Monitor::class,
            Printer::class,
            Phone::class,
        ], true);
    }

    private static function canAccessItem(CommonDBTM $item): bool
    {
        return Session::haveAccessToEntity($item->getEntityID());
    }

    private static function validationError(string $message, int $code): never
    {
        throw new \RuntimeException($message, $code);
    }

    public static function getValidatedItem(string $itemtype, int $item_id): CommonDBTM
    {
        if (!self::isAllowed($itemtype) || !self::isEnabled($itemtype)) {
            self::validationError(__('Invalid type', 'delainventory'), 400);
        }

        $item = match ($itemtype) {
            Computer::class => new Computer(),
            Monitor::class  => new Monitor(),
            Printer::class  => new Printer(),
            Phone::class    => new Phone(),
            default => throw new \InvalidArgumentException(__('Invalid type', 'delainventory')),
        };

        if (!$item->getFromDB($item_id)) {
            self::validationError(__('Asset not found', 'delainventory'), 404);
        }

        if (!self::canAccessItem($item)) {
            self::validationError(__('You do not have access to this entity.', 'delainventory'), 403);
        }

        return $item;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!Session::haveRight(Profile::$rightname, READ) || !self::isEnabled($item->getType())) {
            return '';
        }

        $formItemtype = match ($item->getType()) {
            Computer::class => Computer::class,
            Monitor::class  => Monitor::class,
            Printer::class  => Printer::class,
            Phone::class    => Phone::class,
            default         => null,
        };

        if ($formItemtype === null) {
            return '';
        }

        return self::createTabEntry(self::getMenuName(), 0, $formItemtype, 'ti ti-stack-2');
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        require GLPI_ROOT . '/plugins/delainventory/front/log.php';

        return true;
    }


    /**
     * @return DBmysqlIterator<int, array<string, mixed>>
     */
    public static function getLogs(string $itemtype, int $item_id): DBmysqlIterator
    {
        global $DB;

        return $DB->request([
            'FROM' => self::getTable(),
            'WHERE' => [
                'itemtype' => $itemtype,
                'item_id'  => $item_id,
            ],
            'ORDER' => ['date_creation DESC'],
        ]);
    }

    public static function addLog(string $itemtype, int $item_id, string $comment): bool
    {
        global $DB;

        return $DB->insert(
            self::getTable(),
            [
                'itemtype' => $itemtype,
                'item_id'  => $item_id,
                'comment' => $comment,
                'users_id' => Session::getLoginUserID(),
            ],
        );
    }
}
