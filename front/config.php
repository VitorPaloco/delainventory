<?php

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Config;
use GlpiPlugin\Delainventory\Printer;
use GlpiPlugin\Delainventory\ZplVars;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Session;

Session::checkLoginUser();

$config = new Config();
$assets = $config->find();

if (isset($_POST['update'])) {
    error_log('POST zpl = ' . var_export($_POST['zpl'] ?? 'NAO VEIO', true));
    foreach ($assets as $item) {
        $config->update([
            'id'      => $item['id'],
            'enabled' => isset($_POST['asset'][$item['id']]) ? 1 : 0
        ]);
    }

    Printer::save(
        trim($_POST['ip'] ?? ''),
        (int) ($_POST['port'] ?? 0),
        $_POST['zpl'] ?? ''
    );

    Session::addMessageAfterRedirect('Configurações salvas com sucesso.', true, INFO);

    Html::redirect($CFG_GLPI['root_doc'] . '/plugins/delainventory/front/config.php');
}

Html::header(Config::getMenuName(), $_SERVER['PHP_SELF'], 'config', Config::class);

$printer = Printer::get();
$computer = new Computer();
$computer->getEmpty();

TemplateRenderer::getInstance()->display('@delainventory/config.html.twig', [
    'assets'    => $assets,
    'ip'        => $printer['ip'] ?? '',
    'port'      => $printer['port'] ?? '',
    'zpl'       => $printer['zpl'] ?? '',
    'variables' => ZplVars::available($computer)
]);

Html::footer();