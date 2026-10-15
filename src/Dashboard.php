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

use User;

use function Safe\strtotime;

class Dashboard
{
    public static function getTotalInventories(): int
    {
        global $DB;

        $iterator = $DB->request([
            'FROM'  => Log::getTable(),
            'COUNT' => 'count',
        ]);

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

    public static function getTodayInventories(): int
    {
        global $DB;

        $start = date('Y-m-d 00:00:00');
        $end   = date('Y-m-d 23:59:59');

        $iterator = $DB->request([
            'FROM'  => Log::getTable(),
            'WHERE' => [
                'date_creation' => ['>=', $start],
                'AND' => [
                    'date_creation' => ['<=', $end],
                ],
            ],
            'COUNT' => 'count',
        ]);

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

        $iterator = $DB->request([
            'SELECT'   => ['itemtype', 'item_id'],
            'DISTINCT' => true,
            'FROM'     => Log::getTable(),
        ]);

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

        $iterator = $DB->request([
            'FROM'  => Log::getTable(),
            'ORDER' => 'date_creation DESC',
            'LIMIT' => 1,
        ]);

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

        $startTimestamp = strtotime(sprintf('-%d days', $days - 1));
        $start = date('Y-m-d 00:00:00', $startTimestamp);

        $iterator = $DB->request([
            'SELECT' => ['date_creation'],
            'FROM' => Log::getTable(),
            'WHERE' => ['date_creation' => ['>=', $start]],
            'ORDER' => 'date_creation ASC',
        ]);

        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days", $startTimestamp));
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

            $timestamp = strtotime($dateCreation);

            $date = date('Y-m-d', $timestamp);

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

        $iterator = $DB->request([
            'SELECT' => ['itemtype'],
            'FROM' => Log::getTable(),
            'COUNT' => 'count',
            'GROUP' => 'itemtype',
            'ORDER' => 'count DESC',
        ]);

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

        $iterator = $DB->request([
            'SELECT' => ['users_id'],
            'FROM'   => Log::getTable(),
            'COUNT'  => 'count',
            'GROUP'  => 'users_id',
            'ORDER'  => 'count DESC',
        ]);

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
                $user = new \User();

                if ($user->getFromDB($usersId)) {
                    $name = $user->getName();
                } else {
                    $name = sprintf('#%d', $usersId);
                }
            } else {
                $name = __('System', 'delainventory');
            }

            $data[$name] = (int) $count;
        }

        return $data;
    }

    /**
     * @return list<array{
     *     date_creation: string,
     *     asset_name: string,
     *     itemtype: string,
     *     users_name: string
     * }>
     */
    public static function getLatestInventories(int $limit = 10): array
    {
        global $DB;

        $iterator = $DB->request([
            'FROM'  => Log::getTable(),
            'ORDER' => 'date_creation DESC',
            'LIMIT' => $limit,
        ]);

        $data = [];

        foreach ($iterator as $row) {
            if (!is_array($row)) {
                continue;
            }

            $itemId = $row['item_id'] ?? null;
            $itemtype = $row['itemtype'] ?? null;
            $usersId = $row['users_id'] ?? null;
            $dateCreation = $row['date_creation'] ?? null;

            if (!is_numeric($itemId) || !is_string($itemtype) || !is_numeric($usersId) || !is_string($dateCreation)) {
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
                $user = new \User();

                if ($user->getFromDB($usersId)) {
                    $userName = $user->getName();
                }
            }

            $data[] = [
                'date_creation' => $dateCreation,
                'asset_name'    => $assetName,
                'itemtype'      => $itemtype,
                'users_name'    => $userName,
            ];
        }

        return $data;
    }
}
