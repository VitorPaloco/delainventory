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

/** @phpstan-ignore theCodingMachineSafe.function (safe to assume this isn't already defined) */
define('PLUGIN_DELAINVENTORY_VERSION', '1.0.0');

/** @phpstan-ignore theCodingMachineSafe.function (safe to assume this isn't already defined) */
define('PLUGIN_DELAINVENTORY_MIN_GLPI_VERSION', '11.0.0');

/** @phpstan-ignore theCodingMachineSafe.function (safe to assume this isn't already defined) */
define('PLUGIN_DELAINVENTORY_MAX_GLPI_VERSION', '11.0.99');

use GlpiPlugin\Delainventory\Setting;
use GlpiPlugin\Delainventory\Profile;
use GlpiPlugin\Delainventory\Log;
use Profile as GLPI_Profile;

function plugin_init_delainventory(): void
{
    global $PLUGIN_HOOKS;

    /** @var array<string, array<string, mixed>> $PLUGIN_HOOKS */

    $PLUGIN_HOOKS['csrf_compliant']['delainventory'] = true;
    $PLUGIN_HOOKS['config_page']['delainventory'] = 'front/settings.php';

    if (Session::haveRight(Profile::$rightname, READ)) {
        $PLUGIN_HOOKS['menu_toadd']['delainventory'] = ['config' => Setting::class,];
    }

    Plugin::registerClass(Log::class, [
        'addtabon' => [
            'Computer',
            'Monitor',
            'Printer',
            'Phone',
        ],
    ]);

    Plugin::registerClass(Profile::class, ['addtabon' => GLPI_Profile::class]);
}

/**
 * @return array{
 *      name: string,
 *      version: string,
 *      author: string,
 *      license: string,
 *      homepage: string,
 *      requirements: array{
 *          glpi: array{
 *              min: string,
 *              max: string,
 *          }
 *      }
 * }
 */
function plugin_version_delainventory(): array
{
    return [
        'name'           => 'DelaInventory',
        'version'        => PLUGIN_DELAINVENTORY_VERSION,
        'author'         => 'Vitor Paloco',
        'license'        => 'MIT',
        'homepage'       => 'https://github.com/VitorPaloco/delainventory',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_DELAINVENTORY_MIN_GLPI_VERSION,
                'max' => PLUGIN_DELAINVENTORY_MAX_GLPI_VERSION,
            ],
        ],
    ];
}


function plugin_delainventory_check_prerequisites(): bool
{
    return true;
}

/**
 * @param bool $verbose Whether to display message on failure. Defaults to false.
 */
function plugin_delainventory_check_config(bool $verbose = false): bool
{
    return true;
}
