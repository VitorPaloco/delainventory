<?php

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Log;

Session::checkLoginUser();

if (!isset($_POST['itemtype'], $_POST['item_id'])) {
    Html::redirect($_SERVER['HTTP_REFERER'] ?? '');
}

$itemtype = (string) $_POST['itemtype'];
$item_id  = (int) $_POST['item_id'];
$comment  = trim($_POST['comment'] ?? '');

Log::addNewLog($itemtype, $item_id, $comment);
Session::addMessageAfterRedirect("Log adicionado com sucesso!", true, INFO);

Html::redirect($_SERVER['HTTP_REFERER'] ?? '');