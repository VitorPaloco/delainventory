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

use Computer;
use Monitor;
use Phone;
use Printer;
use User;
use GlpiPlugin\Delainventory\Log;
use QuerySubQuery;
use function Safe\strtotime;

class Dashboard
{
    /**
     * @param list<array<mixed>> $additionalConditions
     *
     * @return array<string, mixed>
     */
    private static function getVisibleLogsQuery(array $additionalConditions = []): array {
        $logTable = Log::getTable();

        $assetTypes = [
            Computer::class => Computer::getTable(),
            Monitor::class  => Monitor::getTable(),
            Printer::class  => Printer::getTable(),
            Phone::class    => Phone::getTable(),
        ];

        $orSubQueries = [];

        foreach ($assetTypes as $itemtype => $table) {
            $entityCriteria = getEntitiesRestrictCriteria($table, '', '', 'auto');
            $subWhere = array_merge([$table . '.is_deleted' => 0], $entityCriteria);

            $orSubQueries[] = [
                'AND' => [
                    $logTable . '.itemtype' => $itemtype,
                    $logTable . '.item_id' => new QuerySubQuery([
                        'SELECT' => 'id',
                        'FROM'   => $table,
                        'WHERE'  => $subWhere,
                    ]),
                ],
            ];
        }

        $visibleLogsCondition = ['OR' => $orSubQueries];

        $where = $additionalConditions === []
            ? $visibleLogsCondition
            : [
                'AND' => [
                    $visibleLogsCondition,
                    ...$additionalConditions,
                ],
            ];

        return ['FROM'  => $logTable, 'WHERE' => $where];
    }

    public static function getTotalInventories(): int
    {
        global $DB;

        $query = self::getVisibleLogsQuery();
        $query['SELECT'] = ['COUNT' => '*'];
        $result = $DB->request($query);
        $row = $result->current();

        if (!is_array($row)) {
            return 0;
        }

        return (int) ($row['COUNT(*)'] ?? 0);
    }

    public static function getTodayInventories(): int
    {
        global $DB;

        $start = date('Y-m-d 00:00:00');
        $end   = date('Y-m-d 23:59:59');

        $query = self::getVisibleLogsQuery([
            ['date_creation' => ['>=', $start]],
            ['date_creation' => ['<=', $end]],
        ]);

        $query['COUNT'] = 'count';
        $iterator = $DB->request($query);
        $row = $iterator->current();

        if (!is_array($row)) {
            return 0;
        }

        $count = $row['count'] ?? null;

        if (!is_numeric($count)) {
            return 0;
        }

        return (int) $count;
    }

    public static function getInventoriedAssets(): int
    {
        global $DB;

        $query = self::getVisibleLogsQuery();
        $query['SELECT'] = ['itemtype', 'item_id'];
        $query['DISTINCT'] = true;
        $iterator = $DB->request($query);

        return count($iterator);
    }

    /**
     * @return array{
     *     date_creation: string,
     *     itemtype: string,
     *     item_id: int,
     *     users_id: int
     * }|null
     */
    public static function getLastInventory(): ?array
    {
        global $DB;

        $query = self::getVisibleLogsQuery();
        $query['ORDER'] = 'date_creation DESC';
        $query['LIMIT'] = 1;
        $iterator = $DB->request($query);
        $row = $iterator->current();

        if (!is_array($row)) {
            return null;
        }

        $dateCreation = $row['date_creation'] ?? null;
        $itemtype = $row['itemtype'] ?? null;
        $itemId = $row['item_id'] ?? null;
        $usersId = $row['users_id'] ?? null;

        if (!is_string($dateCreation) || !is_string($itemtype) || !is_numeric($itemId) || !is_numeric($usersId)) {
            return null;
        }

        return [
            'date_creation' => $dateCreation,
            'itemtype'      => $itemtype,
            'item_id'       => (int) $itemId,
            'users_id'      => (int) $usersId,
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function getInventoriesByDate(int $days = 30): array
    {
        global $DB;

        $days = max(1, $days);
        $startTimestamp = strtotime(sprintf('-%d days', $days - 1));
        $start = date('Y-m-d 00:00:00', $startTimestamp);

        $query = self::getVisibleLogsQuery([
            ['date_creation' => ['>=', $start]],
        ]);

        $query['SELECT'] = ['date_creation'];
        $query['ORDER'] = 'date_creation ASC';
        $iterator = $DB->request($query);
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime(sprintf('+%d days', $i), $startTimestamp));
            $data[$date] = 0;
        }

        foreach ($iterator as $row) {
            if (!is_array($row)) {
                continue;
            }

            $dateCreation = $row['date_creation'] ?? null;

            if (!is_string($dateCreation)) {
                continue;
            }

            $date = date('Y-m-d', strtotime($dateCreation));

            if (isset($data[$date])) {
                $data[$date]++;
            }
        }

        return $data;
    }

    /**
     * @return array<string, int>
     */
    public static function getInventoriesByAssetType(): array
    {
        global $DB;

        $query = self::getVisibleLogsQuery();
        $query['SELECT'] = ['itemtype'];
        $query['COUNT'] = 'count';
        $query['GROUP'] = 'itemtype';
        $query['ORDER'] = 'count DESC';
        $iterator = $DB->request($query);

        $data = [];

        foreach ($iterator as $row) {
            if (!is_array($row)) {
                continue;
            }

            $itemtype = $row['itemtype'] ?? null;
            $count = $row['count'] ?? null;

            if (!is_string($itemtype) || !is_numeric($count)) {
                continue;
            }

            $data[__($itemtype, 'delainventory')] = (int) $count;
        }

        return $data;
    }

    /**
     * @return array<string, int>
     */
    public static function getInventoriesByUser(): array
    {
        global $DB;

        $query = self::getVisibleLogsQuery();
        $query['SELECT'] = ['users_id'];
        $query['COUNT'] = 'count';
        $query['GROUP'] = 'users_id';
        $query['ORDER'] = 'count DESC';
        $iterator = $DB->request($query);

        $data = [];

        foreach ($iterator as $row) {
            if (!is_array($row)) {
                continue;
            }

            $usersId = $row['users_id'] ?? null;
            $count = $row['count'] ?? null;

            if (!is_numeric($usersId) || !is_numeric($count)) {
                continue;
            }

            $usersId = (int) $usersId;

            if ($usersId > 0) {
                $user = new User();

                if ($user->getFromDB($usersId)) {
                    $name = $user->getName();
                } else {
                    $name = sprintf('#%d', $usersId);
                }
            } else {
                $name = __('System', 'delainventory');
            }

            $data[$name] = ($data[$name] ?? 0) + (int) $count;
        }

        return $data;
    }

    /**
     * @return list<array{
     *     date_creation: string,
     *     asset_name: string,
     *     itemtype: string,
     *     users_name: string,
     *     comment: string
     * }>
     */
    public static function getLatestInventories(int $limit = 10): array
    {
        global $DB;

        $limit = max(1, $limit);
        $query = self::getVisibleLogsQuery();
        $query['ORDER'] = 'date_creation DESC';
        $query['LIMIT'] = $limit;
        $iterator = $DB->request($query);
        $data = [];

        foreach ($iterator as $row) {
            if (!is_array($row)) {
                continue;
            }

            $itemId = $row['item_id'] ?? null;
            $itemtype = $row['itemtype'] ?? null;
            $usersId = $row['users_id'] ?? null;
            $dateCreation = $row['date_creation'] ?? null;
            $comment = $row['comment'] ?? null;

            if (!is_numeric($itemId) || !is_string($itemtype) || !is_numeric($usersId) || !is_string($dateCreation) || !is_string($comment)) {
                continue;
            }

            $itemId = (int) $itemId;
            $usersId = (int) $usersId;
            $assetName = sprintf('#%d', $itemId);
            $item = getItemForItemtype($itemtype);

            if ($item && $item->getFromDB($itemId)) {
                $assetName = $item->getName();
            }

            $userName = '';

            if ($usersId > 0) {
                $user = new User();

                if ($user->getFromDB($usersId)) {
                    $userName = $user->getName();
                }
            }

            $data[] = [
                'date_creation' => $dateCreation,
                'asset_name'    => $assetName,
                'itemtype'      => $itemtype,
                'users_name'    => $userName,
                'comment'       => $comment,
            ];
        }

        return $data;
    }
}
