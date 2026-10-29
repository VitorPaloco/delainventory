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

use function Safe\preg_replace;

class ZplVar
{
    private static function toString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * @return array<string, string>
     */
    public static function resolve(CommonDBTM $item, string $itemtype, string $assetUrl): array
    {
        global $DB;

        $vars = [];

        foreach ($item->fields as $field => $value) {
            if (is_scalar($value) || $value === null) {
                $vars[strtoupper((string) $field)] = self::toString($value);
            }
        }

        $vars['ID'] = str_pad(self::toString($item->fields['id'] ?? ''), 5, '0', STR_PAD_LEFT);
        $vars['URL'] = $assetUrl;

        // ==========================
        // Group
        // ==========================

        $group_name = '';
        $group_item = $DB->request(['FROM' => 'glpi_groups_items', 'WHERE' => ['itemtype' => $itemtype, 'items_id' => $item->fields['id']], 'LIMIT' => 1])->current();

        if (is_array($group_item)) {
            $group_id = $group_item['groups_id'] ?? null;

            if (is_numeric($group_id)) {
                $group = new Group();

                if ($group->getFromDB((int) $group_id)) {
                    $group_name = self::toString($group->fields['name'] ?? '');
                }
            }
        }

        $vars['GROUP'] = $group_name;

        // ==========================
        // Location
        // ==========================

        $vars['LOCATION'] = '';
        $location_id = $item->fields['locations_id'] ?? null;

        if (is_numeric($location_id) && (int) $location_id > 0) {
            $location = new Location();

            if ($location->getFromDB((int) $location_id)) {
                $vars['LOCATION'] = self::toString($location->fields['completename'] ?? '');
            }
        }

        // ==========================
        // Manufacturer
        // ==========================

        $vars['MANUFACTURER'] = '';
        $manufacturer_id = $item->fields['manufacturers_id'] ?? null;

        if (is_numeric($manufacturer_id) && (int) $manufacturer_id > 0) {
            $manufacturer = new Manufacturer();

            if ($manufacturer->getFromDB((int) $manufacturer_id)) {
                $vars['MANUFACTURER'] = self::toString($manufacturer->fields['name'] ?? '');
            }
        }

        // ==========================
        // Entity
        // ==========================

        $vars['ENTITY'] = '';
        $entity_id = $item->fields['entities_id'] ?? null;

        if (is_numeric($entity_id)) {
            $entity = new Entity();

            if ($entity->getFromDB((int) $entity_id)) {
                $vars['ENTITY'] = self::toString($entity->fields['name'] ?? '');
            }
        }

        return $vars;
    }


    /**
     * @param array<string, string> $vars
     */
    public static function replace(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $value = preg_replace('/[\x00-\x1F\x7F^~]/', '', (string) $value);
            $template = str_replace('{{' . strtoupper((string) $key) . '}}', $value, $template);
        }

        return $template;
    }

    /**
     * @return list<string>
     */
    public static function available(CommonDBTM $item): array
    {
        $vars = [];

        foreach ($item->fields as $field => $value) {
            $vars[] = '{{' . strtoupper((string) $field) . '}}';
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
