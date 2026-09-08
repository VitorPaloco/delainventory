<?php

use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\AssetType;
use GlpiPlugin\Delainventory\PrinterConfig;
use GlpiPlugin\Delainventory\Log;

function plugin_delainventory_install(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_DELAINVENTORY_VERSION);

    // Migration v0.3.0 to v0.4.0

    if ($DB->tableExists('glpi_plugin_delainventory_configs') && !$DB->tableExists('glpi_plugin_delainventory_assettypes')) {
        $migration->renameTable(
            'glpi_plugin_delainventory_configs',
            'glpi_plugin_delainventory_assettypes'
        );
    }

    if ($DB->tableExists('glpi_plugin_delainventory_printers') && !$DB->tableExists('glpi_plugin_delainventory_printerconfigs')) {
        $migration->renameTable(
            'glpi_plugin_delainventory_printers',
            'glpi_plugin_delainventory_printerconfigs'
        );
    }

    $migration->executeMigration();

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
