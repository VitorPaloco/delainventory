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

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Log;
use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\PrinterConfig;
use GlpiPlugin\Delainventory\ZplVar;

Session::checkRight(Profile::$rightname, UPDATE);

$itemtype = (string) ($_POST['itemtype'] ?? '');
$item_id  = (int) ($_POST['item_id'] ?? 0);

$action = $_POST['action'] ?? '';

if ($action === 'add_log') {
    Log::getValidatedItem($itemtype, $item_id, UPDATE);

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

    $item = Log::getValidatedItem($itemtype, $item_id, READ);

    $formPages = [
        Computer::class => 'computer.form.php',
        Monitor::class  => 'monitor.form.php',
        Printer::class  => 'printer.form.php',
        Phone::class    => 'phone.form.php',
    ];

    $id = $item->fields['id'];
    $base_url = rtrim($CFG_GLPI['url_base'], '/');
    $asset_url = $base_url . rtrim($CFG_GLPI['root_doc'], '/') . '/front/' . $formPages[$itemtype] . '?id=' . $id;

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
