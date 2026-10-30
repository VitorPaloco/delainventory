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

use GlpiPlugin\Delainventory\Setting;
use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\AssetType;
use GlpiPlugin\Delainventory\PrinterConfig;
use GlpiPlugin\Delainventory\ZplVar;
use Glpi\Application\View\TemplateRenderer;

Session::checkRight(Profile::$rightname, READ);

$assets = AssetType::getAll();
$printerConfig = PrinterConfig::get();

$computer = new Computer();
$computer->getEmpty();

Html::header(Setting::getMenuName(), $_SERVER['PHP_SELF'], 'config', Setting::class, 'settings');

TemplateRenderer::getInstance()->display('@delainventory/settings.html.twig', [
    'assets'    => $assets,
    'ip'        => $printerConfig['ip'] ?? '',
    'port'      => $printerConfig['port'] ?? '',
    'zpl'       => $printerConfig['zpl'] ?? '',
    'variables' => ZplVar::available($computer),
]);

Html::footer();
