<?php

include('../../../inc/includes.php');

use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Delainventory\Profile;
use ProfileRight;

Session::checkLoginUser();

$profileRight = new ProfileRight();
$profile_id = $item->getID();
$can_read = false;
$can_update = false;

if ($profileRight->getFromDBByCrit(['profiles_id' => $profile_id, 'name' => Profile::$rightname])) {
    $rights = (int) $profileRight->fields['rights'];
    $can_read = ($rights & READ) !== 0;
    $can_update = ($rights & UPDATE) !== 0;
}

TemplateRenderer::getInstance()->display(
    '@delainventory/profile.html.twig',
    [
        'profile'   => $item,
        'rightname' => Profile::$rightname,
        'can_edit'  => Session::haveRight('profile', UPDATE),
        'can_read'  => $can_read,
        'can_update' => $can_update
    ]
);