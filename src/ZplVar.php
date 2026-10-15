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

namespace GlpiPlugin\Delainventory;

use CommonDBTM;
use Group;
use Location;
use Manufacturer;
use Entity;

class ZplVar
{
    public static function resolve(CommonDBTM $item, string $itemtype, string $assetUrl): array
    {
        global $DB;
        $vars = [];

        foreach ($item->fields as $field => $value) {
            if (is_scalar($value) || $value === null) {
                $vars[strtoupper($field)] = (string) $value;
            }
        }

        $vars['ID'] = str_pad($item->fields['id'], 5, '0', STR_PAD_LEFT);
        $vars['URL'] = $assetUrl;

        // ==========================
        // Group
        // ==========================

        $group_name = '';

        $group_item = $DB->request([
            'FROM' => 'glpi_groups_items',
            'WHERE' => [
                'itemtype' => $itemtype,
                'items_id' => $item->fields['id'],
            ],
            'LIMIT' => 1,
        ])->current();

        if ($group_item) {
            $group = new Group();

            if ($group->getFromDB($group_item['groups_id'])) {
                $group_name = $group->fields['name'];
            }
        }

        $vars['GROUP'] = $group_name;

        // ==========================
        // Location
        // ==========================

        if (!empty($item->fields['locations_id'])) {

            $location = new Location();

            if ($location->getFromDB($item->fields['locations_id'])) {
                $vars['LOCATION'] = $location->fields['completename'];
            }
        }

        // ==========================
        // Manufacturer
        // ==========================

        if (!empty($item->fields['manufacturers_id'])) {

            $manufacturer = new Manufacturer();

            if ($manufacturer->getFromDB($item->fields['manufacturers_id'])) {
                $vars['MANUFACTURER'] = $manufacturer->fields['name'];
            }
        }

        // ==========================
        // Entity
        // ==========================

        $entity = new Entity();

        if ($entity->getFromDB($item->fields['entities_id'])) {
            $vars['ENTITY'] = $entity->fields['name'];
        }

        return $vars;
    }

    public static function replace(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace(
                '{{' . strtoupper($key) . '}}',
                (string) ($value ?? ''),
                $template,
            );
        }

        return $template;
    }

    public static function available(CommonDBTM $item): array
    {
        $vars = [];

        foreach ($item->fields as $field => $value) {
            $vars[] = '{{' . strtoupper($field) . '}}';
        }

        $vars[] = '{{GROUP}}';
        $vars[] = '{{LOCATION}}';
        $vars[] = '{{MANUFACTURER}}';
        $vars[] = '{{URL}}';
        $vars[] = '{{ENTITY}}';

        sort($vars);

        return $vars;
    }
}
