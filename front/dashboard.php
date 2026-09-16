<?php

include('../../../inc/includes.php');

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\Setting;
use GlpiPlugin\Delainventory\Dashboard;

Session::checkLoginUser();
Session::checkRight(Profile::$rightname, READ);

Html::requireJs('charts');

$lastInventory = Dashboard::getLastInventory();
$inventoriesByDate = Dashboard::getInventoriesByDate();
$inventoriesByAssetType = Dashboard::getInventoriesByAssetType();
$latestInventories = Dashboard::getLatestInventories();

Html::header(Setting::getMenuName(), $_SERVER['PHP_SELF'], 'config', Setting::class, 'dashboard');

TemplateRenderer::getInstance()->display('@delainventory/dashboard.html.twig',
    [
        'total_inventories' => Dashboard::getTotalInventories(),
        'today_inventories' => Dashboard::getTodayInventories(),
        'inventoried_assets' => Dashboard::getInventoriedAssets(),
        'last_inventory' => $lastInventory,
        'inventories_by_date' => $inventoriesByDate,
        'inventories_by_type' => $inventoriesByAssetType,
        'latest_inventories' => $latestInventories
    ]
);

Html::footer();