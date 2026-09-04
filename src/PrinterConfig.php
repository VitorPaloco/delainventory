<?php
namespace GlpiPlugin\Delainventory;

use DBConnection;
use CommonDBTM;

class PrinterConfig extends CommonDBTM
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

        if (countElementsInTable($table) === 0) {
            $item->add(['ip' => null, 'port' => null, 'zpl' => null]);
        }
    }

    public static function get(): array
    {
        global $DB;

        foreach ($DB->request(['FROM' => self::getTable(), 'LIMIT' => 1]) as $row) {
            return $row;
        }

        return ['id' => 0, 'ip' => '', 'port' => '', 'zpl' => ''];
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

    public static function testConnection(string $ip, int $port): array
    {
        $connection = @fsockopen($ip, $port, $errno, $errstr, 5);

        if (!$connection) {
            return [
                'success' => false,
                'message' => sprintf(__('Could not connect to printer %s:%s. %s (%s)', 'delainventory'), $ip, $port, $errstr, $errno)
            ];
        }

        fclose($connection);

        return [
            'success' => true,
            'message' => __('Connection successful.', 'delainventory'),
        ];
    }

    public static function printLabel(string $ip, int $port, string $zpl): array
    {
        $connection = @fsockopen($ip, $port, $errno, $errstr, 5);

        if (!$connection) {
            return [
                'success' => false,
                'message' => sprintf(__('Could not connect to printer %s:%s. %s (%s)', 'delainventory'), $ip, $port, $errstr, $errno)
            ];
        }

        stream_set_timeout($connection, 5);
        fwrite($connection, $zpl);
        fclose($connection);

        return [
            'success' => true,
            'message' => __('Label sent to print.', 'delainventory')
        ];
    }
}