<?php

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\AssetType;
use GlpiPlugin\Delainventory\PrinterConfig;

Session::checkLoginUser();
Session::checkRight(Profile::$rightname, UPDATE);

$enabledIds = array_map('intval', array_keys($_POST['asset'] ?? []));
$printer_ip = trim($_POST['ip'] ?? '');
$printer_port = (int) ($_POST['port'] ?? 0);
$zpl_code = $_POST['zpl'] ?? '';

if (isset($_POST['test_connection'])) {
    header('Content-Type: application/json');

    if (empty($printer_ip) || empty($printer_port)) {
        http_response_code(400);

        echo json_encode([
            'success' => false,
            'message' => __('Printer IP address or port not configured.', 'delainventory')
        ]);

        exit;
    }

    echo json_encode(PrinterConfig::testConnection($printer_ip, $printer_port));

    exit;
}

AssetType::updateAll($enabledIds);
PrinterConfig::save($printer_ip, $printer_port, $zpl_code);

Session::addMessageAfterRedirect(__('Settings saved successfully.', 'delainventory'), true, INFO);
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/delainventory/front/settings.php');