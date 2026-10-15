<?php

/**
 * -------------------------------------------------------------------------
 * DelaInventory plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * @copyright Copyright (C) 2026 by the DelaInventory plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
 */

use PHPUnit\Framework\TestCase;

class SetupTest extends TestCase
{
    public function testPluginVersion(): void
    {
        $version = plugin_version_delainventory();

        $this->assertSame('DelaInventory', $version['name']);
        $this->assertSame('1.0.0', $version['version']);
        $this->assertSame('11.0.0', $version['requirements']['glpi']['min']);
        $this->assertSame('11.0.99', $version['requirements']['glpi']['max']);
    }

    public function testPluginPrerequisites(): void
    {
        $this->assertTrue(plugin_delainventory_check_prerequisites());
    }

    public function testPluginConfiguration(): void
    {
        $this->assertTrue(plugin_delainventory_check_config());
    }
}