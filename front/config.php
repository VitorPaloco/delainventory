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

if (isset($_POST['test_connection'])) {

    header('Content-Type: application/json');

    $ip = trim($_POST['ip'] ?? '');
    $port = (int) ($_POST['port'] ?? 0);

    if (empty($ip) || empty($port)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'IP ou porta da impressora não configurados.']);

        exit;
    }

    $socket = @fsockopen($ip, $port, $errno, $errstr, 5);

    if ($socket) {
        fclose($socket);
        echo json_encode(['success' => true, 'message' => "Conexão realizada com sucesso."]);

        exit;
    }

    http_response_code(500);
    echo json_encode(['success' => false,'message' => "Não foi possível conectar à impressora {$ip}:{$port}. {$errstr} ({$errno})"]);

    exit;
}

if (isset($_POST['preview_zpl'])) {

    $zpl = $_POST['zpl'] ?? '';

    if (trim($zpl) === '') {

        http_response_code(400);
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => 'Nenhum código ZPL informado.'
        ]);

        exit;
    }

    $url = 'https://api.labelary.com/v1/printers/8dpmm/labels/4x6/0/';
    $curl = curl_init($url);

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $zpl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: image/png'
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $result = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($result === false || $httpCode !== 200) {

        http_response_code(500);
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false,
            'message' => $curlError ?: 'Erro ao gerar a pré-visualização do ZPL.'
        ]);

        exit;
    }

    header('Content-Type: image/png');
    echo $result;

    exit;
}

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