<?php

include('../../../inc/includes.php');

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Delainventory\Log;
use GlpiPlugin\Delainventory\Profile;

Session::checkLoginUser();
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
    'logs'     => $logData
]);
