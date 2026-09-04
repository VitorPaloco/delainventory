<?php

use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\AssetType;
use GlpiPlugin\Delainventory\PrinterConfig;
use GlpiPlugin\Delainventory\Log;

function plugin_delainventory_install(): bool
{
    AssetType::install();
    PrinterConfig::install();
    Profile::install();
    Log::install();

    return true;
}

function plugin_delainventory_uninstall(): bool
{
    global $DB;

    Profile::uninstall();

    $tables = [AssetType::getTable(), PrinterConfig::getTable(), Log::getTable()];

    foreach ($tables as $table) {
        $DB->doQuery("DROP TABLE IF EXISTS `$table`");
    }

    return true;
}
