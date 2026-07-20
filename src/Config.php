<?php
namespace GlpiPlugin\Delainventory;

use DBConnection;
use CommonDBTM;

class Config extends CommonDBTM
{
    public static function getMenuName() 
    {
        return "DelaInventory";
    }

    public static function getMenuContent() 
    {
        return [
            'title' => self::getMenuName(),
            'page'  => '/plugins/delainventory/front/config.php',
            'icon'  => 'fa-solid fa-layer-group'
        ];
    }

    public static function install()
    {
        global $DB;

        $default_charset   = DBConnection::getDefaultCharset();
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
                ['itemtype' => 'Computer', 'label' => 'Computador', 'enabled' => 1],
                ['itemtype' => 'Monitor',  'label' => 'Monitor',     'enabled' => 0],
                ['itemtype' => 'Printer',  'label' => 'Impressora',  'enabled' => 0],
                ['itemtype' => 'Phone',  'label' => 'Telefone',  'enabled' => 0],
            ];

            foreach ($defaults as $row) {
                $item->add($row);
            }
        }
    }

    public static function isEnabled(string $itemtype): bool
    {
        global $DB;

        $table = self::getTable();

        $iterator = $DB->request([
            'FROM' => $table,
            'WHERE' => [
                'itemtype' => $itemtype,
                'enabled'  => 1
            ],
            'LIMIT' => 1
        ]);

        return count($iterator) > 0;
    }
}