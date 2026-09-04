<?php

namespace GlpiPlugin\Delainventory;

use DBConnection;
use CommonDBTM;

class AssetType extends CommonDBTM
{

    public static function install()
    {
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();

        $table = self::getTable();
        $item = new self();

        if (!$DB->tableExists($table)) {

            $query = "CREATE TABLE `$table` (
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

    public static function getAll(): array
    {
        return (new self())->find();
    }

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