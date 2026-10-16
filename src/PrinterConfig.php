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
use Throwable;
use RuntimeException;

use function Safe\fsockopen;
use function Safe\fclose;
use function Safe\fwrite;
use function Safe\stream_set_timeout;

class PrinterConfig extends CommonDBTM
{
    public static function install(): void
    {
        global $DB;

        $default_charset   = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();

        $table = self::getTable();
        $item  = new self();

        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `{$table}` (
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
            $item->add([
                'ip' => null,
                'port' => null,
                'zpl' => null,
            ]);
        }
    }

    /**
     * @return array{id: int, ip: string, port: string, zpl: string}
     */
    public static function get(): array
    {
        global $DB;

        foreach ($DB->request(['FROM' => self::getTable(), 'LIMIT' => 1]) as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = $row['id'] ?? null;
            $ip = $row['ip'] ?? null;
            $port = $row['port'] ?? null;
            $zpl = $row['zpl'] ?? null;

            return [
                'id' => is_numeric($id) ? (int) $id : 0,
                'ip' => is_string($ip) ? $ip : '',
                'port' => is_numeric($port) ? (string) $port : '',
                'zpl' => is_string($zpl) ? $zpl : '',
            ];
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

    /**
     * @return array{success: bool, message: string}
     */
    public static function testConnection(string $ip, int $port): array
    {
        try {
            $connection = fsockopen($ip, $port, $errno, $errstr, 5);
            fclose($connection);

            return [
                'success' => true,
                'message' => __('Connection successful.', 'delainventory'),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => sprintf(__('Could not connect to printer %s:%s.', 'delainventory'), $ip, $port),
            ];
        }
    }

    /**
     * @return array{success: bool, message: string}
     */
    public static function printLabel(string $ip, int $port, string $zpl): array
    {
        $connection = null;

        try {
            $connection = fsockopen($ip, $port, $errno, $errstr, 5);
            stream_set_timeout($connection, 5);
            $length = strlen($zpl);
            $written = 0;

            while ($written < $length) {
                $result = fwrite($connection, substr($zpl, $written));

                if ($result === 0) {
                    throw new RuntimeException('Failed to write all label data to the printer.');
                }

                $written += $result;
            }

            fclose($connection);
            $connection = null;

            return [
                'success' => true,
                'message' => __('Label sent to print.', 'delainventory'),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => sprintf(__('Could not send label to printer %s:%s.', 'delainventory'), $ip, $port),
            ];
        } finally {
            if (is_resource($connection)) {
                try {
                    fclose($connection);
                } catch (Throwable $e) {
                    // Ignora falhas ao fechar o socket.
                }
            }
        }
    }
}
