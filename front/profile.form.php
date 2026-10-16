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
