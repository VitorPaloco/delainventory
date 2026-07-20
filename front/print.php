<?php

include('../../../inc/includes.php');

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

$id = $item->fields['id'];
$full_id = str_pad($id, 5, '0', STR_PAD_LEFT);

$nome   = mb_substr(trim($item->fields['name'] ?? ''), 0, 35);
$serial = mb_substr(trim($item->fields['serial'] ?? ''), 0, 35);

$group_name = '';

$group_item = $DB->request([
    'FROM' => 'glpi_groups_items',
    'WHERE' => [
        'itemtype' => $itemtype,
        'items_id' => $id
    ],
    'LIMIT' => 1
])->current();

if ($group_item) {
    $group = new Group();

    if ($group->getFromDB($group_item['groups_id'])) {
        $group_name = $group->fields['name'];
    }
}

$formPages = [
    Computer::class => 'computer.form.php',
    Monitor::class => 'monitor.form.php',
    Printer::class => 'printer.form.php',
    Phone::class => 'phone.form.php'
];

// $asset_url = sprintf(
//     '%s/front/%s?id=%d',
//     rtrim($CFG_GLPI['root_doc'], '/'),
//     $formPages[$itemtype],
//     $id
// );

$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? null) == 443) ? 'https' : 'http';
$base_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . rtrim($CFG_GLPI['root_doc'], '/');
$asset_url = $base_url . '/front/' . $formPages[$itemtype] . '?id=' . $id;

$zpl = <<<ZPL
^XA
^CI28

^PW480
^LL240

^FO40,20
^A0N,15,15
^FDID DO EQUIPAMENTO^FS

^FO40,35
^A0N,20,20
^FD{$full_id}^FS

^FO40,75
^A0N,15,15
^FDDESCRICAO^FS

^FO40,90
^A0N,20,20
^FD{$nome}^FS

^FO40,120
^A0N,15,15
^FDSERIAL^FS

^FO40,135
^A0N,20,20
^FD{$serial}^FS

^FO40,165
^A0N,15,15
^FDRESPONSÁVEL^FS

^FO40,180
^A0N,16,16
^FD{$group_name}^FS

^FO300,20
^BQN,2,5
^FDLA,{$asset_url}^FS

^FO0,215
^GB480,25,25,B,0^FS

^FO40,223
^A0N,14,14
^FR
^FDDELAFOODS^FS

^FO310,223
^A0N,14,14
^FR
^FDESCANEIE O QR CODE^FS

^XZ
ZPL;

$ip = "192.168.20.71";
$porta = 9100;

$socket = fsockopen($ip, $porta, $errno, $errstr, 5);

if (!$socket) {
    http_response_code(500);
    die("Erro ao conectar na impressora: {$errstr} ({$errno})");
}

fwrite($socket, $zpl);
fclose($socket);

echo "Etiqueta enviada para impressão";