<?php

use GlpiPlugin\Delainventory\Config;
use GlpiPlugin\Delainventory\Log;

function plugin_delainventory_install(): bool
{
    Config::install();
    Log::install();
    return true;
}

function plugin_delainventory_uninstall(): bool
{
    global $DB;

    $tables = [Config::getTable(), Log::getTable()];

    foreach ($tables as $table) {
        $DB->doQuery("DROP TABLE IF EXISTS `$table`");
    }

    return true;
}