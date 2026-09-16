<?php

namespace GlpiPlugin\Delainventory;

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

        return (int) ($row['count'] ?? 0);
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

        return (int) ($row['count'] ?? 0);
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

    public static function getLastInventory(): ?array
    {
        global $DB;

        $iterator = $DB->request([
            'FROM'  => Log::getTable(),
            'ORDER' => 'date_creation DESC',
            'LIMIT' => 1,
        ]);

        $row = $iterator->current();

        if (!$row) {
            return null;
        }

        return [
            'date_creation' => $row['date_creation'],
            'itemtype'      => $row['itemtype'],
            'item_id'       => (int) $row['item_id'],
            'users_id'      => (int) $row['users_id'],
        ];
    }

    public static function getInventoriesByDate(int $days = 30): array
    {
        global $DB;

        $startTimestamp = strtotime("-" . ($days - 1) . " days");

        $start = date('Y-m-d 00:00:00', $startTimestamp);

        $iterator = $DB->request([
            'SELECT' => [
                'date_creation',
            ],
            'FROM' => Log::getTable(),
            'WHERE' => [
                'date_creation' => ['>=', $start],
            ],
            'ORDER' => 'date_creation ASC',
        ]);

        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = date(
                'Y-m-d',
                strtotime("+{$i} days", $startTimestamp)
            );

            $data[$date] = 0;
        }

        foreach ($iterator as $row) {
            $date = date('Y-m-d', strtotime($row['date_creation']));

            if (isset($data[$date])) {
                $data[$date]++;
            }
        }

        return $data;
    }

    public static function getInventoriesByAssetType(): array
    {
        global $DB;

        $iterator = $DB->request([
            'SELECT' => [
                'itemtype',
            ],
            'FROM' => Log::getTable(),
            'COUNT' => 'count',
            'GROUP' => 'itemtype',
            'ORDER' => 'count DESC',
        ]);

        $data = [];

        foreach ($iterator as $row) {
            $data[$row['itemtype']] = (int) $row['count'];
        }

        return $data;
    }

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
            $data[] = [
                'date_creation' => $row['date_creation'],
                'itemtype'      => $row['itemtype'],
                'item_id'       => (int) $row['item_id'],
                'users_id'      => (int) $row['users_id'],
            ];
        }

        return $data;
    }
}