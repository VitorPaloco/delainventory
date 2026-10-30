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
use GlpiPlugin\Delainventory\Profile;
use ProfileRight;

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
        'can_update' => $can_update,
    ],
);
