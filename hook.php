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
            'glpi_plugin_delainventory_assettypes',
        );
    }

    if ($DB->tableExists('glpi_plugin_delainventory_printers') && !$DB->tableExists('glpi_plugin_delainventory_printerconfigs')) {
        $migration->renameTable(
            'glpi_plugin_delainventory_printers',
            'glpi_plugin_delainventory_printerconfigs',
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
        $DB->doQuery(sprintf('DROP TABLE IF EXISTS `%s`', $table));
    }

    return true;
}
