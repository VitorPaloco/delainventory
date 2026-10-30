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

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\Setting;
use GlpiPlugin\Delainventory\Dashboard;

Session::checkRight(Profile::$rightname, READ);

Html::requireJs('charts');

$lastInventory = Dashboard::getLastInventory();
$inventoriesByDate = Dashboard::getInventoriesByDate();
$inventoriesByAssetType = Dashboard::getInventoriesByAssetType();
$inventoriesByUser = Dashboard::getInventoriesByUser();
$latestInventories = Dashboard::getLatestInventories();

Html::header(Setting::getMenuName(), $_SERVER['PHP_SELF'], 'config', Setting::class, 'dashboard');

TemplateRenderer::getInstance()->display(
    '@delainventory/dashboard.html.twig',
    [
        'total_inventories' => Dashboard::getTotalInventories(),
        'today_inventories' => Dashboard::getTodayInventories(),
        'inventoried_assets' => Dashboard::getInventoriedAssets(),
        'last_inventory' => $lastInventory,
        'inventories_by_date' => $inventoriesByDate,
        'inventories_by_type' => $inventoriesByAssetType,
        'inventories_by_user' => $inventoriesByUser,
        'latest_inventories' => $latestInventories,
    ],
);

Html::footer();
