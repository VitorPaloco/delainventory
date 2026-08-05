<?php
namespace GlpiPlugin\Delainventory;

use DBConnection;
use CommonDBTM;

class Setting extends CommonDBTM
{
    public static function install()
    {
        global $DB;

        $default_charset   = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();

        $table = self::getTable();
        $item  = new self();

        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `$table` (
                `id` INT UNSIGNED AUTO_INCREMENT,
                `ip` VARCHAR(45) DEFAULT NULL,
                `port` INT UNSIGNED DEFAULT NULL,
                `zpl` TEXT DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB
            DEFAULT CHARSET={$default_charset}
            COLLATE={$default_collation}";

            $DB->doQuery($query);
        }

        if (!$DB->fieldExists($table, 'zpl')) {
            $DB->doQuery("ALTER TABLE `$table` ADD COLUMN `zpl` TEXT DEFAULT NULL");
        }

        if (countElementsInTable($table) === 0) {
            $item->add(['ip' => null, 'port' => null]);
        }
    }

    public static function get(): array
    {
        global $DB;

        foreach ($DB->request(['FROM' => self::getTable(), 'LIMIT' => 1]) as $row) {
            return $row;
        }

        return ['id' => 0, 'ip' => '', 'port' => ''];
    }

    public static function save(string $ip, int $port, string $zpl): void
    {
        $row  = self::get();
        $item = new self();

        $data = [
            'ip'   => $ip !== '' ? $ip : null,
            'port' => $port > 0 ? $port : null,
            'zpl' => $zpl !== '' ? $zpl : null,
        ];

        if (!empty($row['id'])) {
            $data['id'] = $row['id'];
            $item->update($data);
        } else {
            $item->add($data);
        }
    }
}