<?php

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Log;
use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\PrinterConfig;
use GlpiPlugin\Delainventory\ZplVar;

Session::checkLoginUser();
Session::checkRight(Profile::$rightname, UPDATE);

$itemtype = (string) ($_POST['itemtype'] ?? '');
$item_id  = (int) ($_POST['item_id'] ?? 0);

$action = $_POST['action'] ?? '';

if ($action === 'add_log') {
    $comment = trim($_POST['comment'] ?? '');

    if ($comment === '') {
        Session::addMessageAfterRedirect(__('The comment cannot be empty.', 'delainventory'), true, ERROR);
        Html::redirect($_SERVER['HTTP_REFERER'] ?? '');
    }

    Log::addLog($itemtype, $item_id, $comment);

    Session::addMessageAfterRedirect(__('Log added successfully!', 'delainventory'), true, INFO);
    Html::redirect($_SERVER['HTTP_REFERER'] ?? '');
}

if ($action === 'print') {
    global $CFG_GLPI;

    $allowed = [
        Computer::class,
        Monitor::class,
        Printer::class,
        Phone::class
    ];

    if (!in_array($itemtype, $allowed, true)) {
        http_response_code(400);
        die(__('Invalid type', 'delainventory'));
    }

    $item = new $itemtype();

    if (!$item->getFromDB($item_id)) {
        http_response_code(404);
        die(__('Asset not found', 'delainventory'));
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

    $printerConfig = PrinterConfig::get();
    $ip = $printerConfig['ip'] ?? '';
    $port = (int) ($printerConfig['port'] ?? 0);
    $zpl_template = $printerConfig['zpl'] ?? '';

    if (empty($ip) || empty($port)) {
        http_response_code(500);
        die(__('Printer IP address or port not configured.', 'delainventory'));
    }

    if (empty($zpl_template)) {
        http_response_code(500);
        die(__('ZPL model not configured.', 'delainventory'));
    }

    $vars = ZplVar::resolve($item, $itemtype, $asset_url);
    $zpl = ZplVar::replace($zpl_template, $vars);

    $result = PrinterConfig::printLabel($ip, $port, $zpl);

    if (!$result['success']) {
        http_response_code(500);
    }

    echo $result['message'];

    exit;
}

http_response_code(400);
die(__('Invalid action', 'delainventory'));
