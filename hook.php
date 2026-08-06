<?php

use GlpiPlugin\Delainventory\Config;
use GlpiPlugin\Delainventory\Log;
use GlpiPlugin\Delainventory\Printer;

function plugin_delainventory_install(): bool
{
    Config::install();
    Log::install();
    Printer::install();
    return true;
}

function plugin_delainventory_uninstall(): bool
{
    global $DB;

    $tables = [Config::getTable(), Log::getTable(), Printer::getTable()];

    foreach ($tables as $table) {
        $DB->doQuery("DROP TABLE IF EXISTS `$table`");
    }

    return true;
}