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

use DBConnection;
use CommonDBTM;

class AssetType extends CommonDBTM
{

    public static function install(): void
    {
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();

        $table = self::getTable();
        $item = new self();

        if (!$DB->tableExists($table)) {

            $query = "CREATE TABLE `{$table}` (
                `id` INT UNSIGNED AUTO_INCREMENT,
                `itemtype` VARCHAR(100) NOT NULL,
                `label` VARCHAR(255) NOT NULL,
                `enabled` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                UNIQUE KEY uniq_itemtype (`itemtype`)
            ) ENGINE=InnoDB

            DEFAULT CHARSET={$default_charset}
            COLLATE={$default_collation}";

            $DB->doQuery($query);
        }

        if (countElementsInTable($table) === 0) {
            $defaults = [
                ['itemtype' => 'Computer', 'label' => 'Computer', 'enabled' => 0],
                ['itemtype' => 'Monitor', 'label' => 'Monitor', 'enabled' => 0],
                ['itemtype' => 'Printer', 'label' => 'Printer', 'enabled' => 0],
                ['itemtype' => 'Phone', 'label' => 'Phone', 'enabled' => 0],
            ];

            foreach ($defaults as $row) {
                $item->add($row);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        /** @var array<int, array<string, mixed>> $items */
        return (new self())->find();
    }

    /**
     * @param array<int, int> $enabledIds
     */
    public static function updateAll(array $enabledIds): void
    {
        $item = new self();

        foreach (self::getAll() as $asset) {
            $item->update([
                'id'      => $asset['id'],
                'enabled' => in_array($asset['id'], $enabledIds) ? 1 : 0,
            ]);
        }
    }
}