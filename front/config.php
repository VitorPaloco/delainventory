<?php

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Config;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Session;

Session::checkLoginUser();

$config = new Config();
$assets = $config->find();

if (isset($_POST['update'])) {
    foreach ($assets as $item) {
        $config->update([
            'id'      => $item['id'],
            'enabled' => isset($_POST['asset'][$item['id']]) ? 1 : 0
        ]);
    }

    Session::addMessageAfterRedirect('Configurações salvas com sucesso.', true, INFO);

    Html::redirect($CFG_GLPI['root_doc'] . '/plugins/delainventory/front/config.php');
}

Html::header(Config::getMenuName(), $_SERVER['PHP_SELF'], 'config', Config::class);

TemplateRenderer::getInstance()->display('@delainventory/config.html.twig', ['assets' => $assets]);

Html::footer();