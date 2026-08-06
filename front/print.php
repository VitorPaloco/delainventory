<?php

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Printer;
use GlpiPlugin\Delainventory\ZplVars;

global $CFG_GLPI;
global $DB;

$itemtype = $_GET['itemtype'] ?? '';
$input_id = (int)($_GET['id'] ?? 0);

$allowed = [
    Computer::class,
    Monitor::class,
    Printer::class,
    Phone::class
];

if (!in_array($itemtype, $allowed, true)) {
    http_response_code(400);
    die('Tipo inválido');
}

$item = new $itemtype();

if (!$item->getFromDB($input_id)) {
    http_response_code(404);
    die('Equipamento não encontrado');
}

$formPages = [
    Computer::class => 'computer.form.php',
    Monitor::class  => 'monitor.form.php',
    Printer::class  => 'printer.form.php',
    Phone::class    => 'phone.form.php'
];

$id = $item->fields['id'];
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? null) == 443) ? 'https' : 'http';
$base_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . rtrim($CFG_GLPI['root_doc'], '/');
$asset_url = $base_url . '/front/' . $formPages[$itemtype] . '?id=' . $id;

$printer = Printer::get();
$ip = $printer['ip'];
$port = $printer['port'];
$zpl_template = $printer['zpl'] ?? '';

if (empty($ip) || empty($port)) {
    http_response_code(500);
    die('IP ou porta da impressora não configurados.');
}

if (empty($zpl_template)) {
    http_response_code(500);
    die('Modelo ZPL não configurado.');
}

$vars = ZplVars::resolve(
    $item,
    $itemtype,
    $asset_url
);

$zpl = ZplVars::replace(
    $zpl_template,
    $vars
);

$socket = fsockopen($ip, $port, $errno, $errstr, 5);

if (!$socket) {
    http_response_code(500);
    die("Erro ao conectar na impressora: {$errstr} ({$errno})");
}

stream_set_timeout($socket, 5);

fwrite($socket, $zpl);
fclose($socket);

echo "Etiqueta enviada para impressão";