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

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Delainventory\Log;
use GlpiPlugin\Delainventory\Profile;

Session::checkRight(Profile::$rightname, READ);

$itemtype = $item->getType();
$item_id  = $item->getID();
$logs = Log::getLogs($itemtype, $item_id);
$logData = [];

foreach ($logs as $log) {
    $user = new User();
    $username = '';

    if ($user->getFromDB($log['users_id'])) {
        $username = $user->getFriendlyName();
    }

    $logData[] = [
        'id'            => $log['id'],
        'itemtype'      => $log['itemtype'],
        'item_id'       => $log['item_id'],
        'date_creation' => $log['date_creation'],
        'comment'       => $log['comment'],
        'users_id'      => $log['users_id'],
        'username'      => $username,
    ];
}

TemplateRenderer::getInstance()->display('@delainventory/log.html.twig', [
    'itemtype' => $itemtype,
    'item_id'  => $item_id,
    'logs'     => $logData,
]);
