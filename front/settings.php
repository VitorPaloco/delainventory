<?php

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Setting;
use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\AssetType;
use GlpiPlugin\Delainventory\PrinterConfig;
use GlpiPlugin\Delainventory\ZplVar;
use Glpi\Application\View\TemplateRenderer;

Session::checkLoginUser();
Session::checkRight(Profile::$rightname, READ);

$assets = AssetType::getAll();
$printerConfig = PrinterConfig::get();

$computer = new Computer();
$computer->getEmpty();

Html::header(Setting::getMenuName(), $_SERVER['PHP_SELF'], 'config', Setting::class);

TemplateRenderer::getInstance()->display('@delainventory/settings.html.twig', [
    'assets'    => $assets,
    'ip'        => $printerConfig['ip'] ?? '',
    'port'      => $printerConfig['port'] ?? '',
    'zpl'       => $printerConfig['zpl'] ?? '',
    'variables' => ZplVar::available($computer)
]);

Html::footer();