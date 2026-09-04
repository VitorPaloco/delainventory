<?php

include('../../../inc/includes.php');

use GlpiPlugin\Delainventory\Profile;
use ProfileRight;

Session::checkLoginUser();
Session::checkRight('profile', UPDATE);

$profile_id = (int) ($_POST['profile_id'] ?? 0);
$rights = 0;

if (isset($_POST['delainventory_read'])) {
    $rights |= READ;
}

if (isset($_POST['delainventory_update'])) {
    $rights |= UPDATE;
}

$profileRight = new ProfileRight();

if ($profileRight->getFromDBByCrit(['profiles_id' => $profile_id, 'name' => Profile::$rightname])) {
    $profileRight->update([
        'id'     => $profileRight->getID(),
        'rights' => $rights,
    ]);
} else {
    $profileRight->add([
        'profiles_id' => $profile_id,
        'name'        => Profile::$rightname,
        'rights'      => $rights,
    ]);
}

Html::redirect($CFG_GLPI['root_doc'] . '/front/profile.form.php?id=' . $profile_id);