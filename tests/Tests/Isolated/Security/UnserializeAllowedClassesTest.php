<?php

/**
 * Tests that unserialize() calls use allowed_classes to prevent object injection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Security;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../portal/patient/fwk/libs/verysimple/Phreeze/ConnectionSetting.php';

class UnserializeAllowedClassesTest extends TestCase
{
    // ---- ConnectionSetting: round-trip serialize/unserialize (line 99) ----

    public function testConnectionSettingRoundTrip(): void
    {
        $setting = new \ConnectionSetting();
        $setting->Type = 'mysql';
        $setting->Username = 'testuser';
        $setting->Password = 'testpass';
        $setting->ConnectionString = 'localhost:3306';
        $setting->DBName = 'testdb';
        $setting->TablePrefix = 'oe_';
        $setting->Charset = 'utf8mb4';

        $serialized = $setting->Serialize();

        $restored = new \ConnectionSetting();
        $restored->Unserialize($serialized);

        $this->assertSame('mysql', $restored->Type);
        $this->assertSame('testuser', $restored->Username);
        $this->assertSame('testpass', $restored->Password);
        $this->assertSame('localhost:3306', $restored->ConnectionString);
        $this->assertSame('testdb', $restored->DBName);
        $this->assertSame('oe_', $restored->TablePrefix);
        $this->assertSame('utf8mb4', $restored->Charset);
    }

    public function testConnectionSettingBlocksForeignClass(): void
    {
        // ConnectionSetting::Unserialize validates instanceof self before
        // copying properties, so a foreign class payload is silently rejected.
        $fake = new \stdClass();
        $fake->Type = 'evil';
        $payload = base64_encode(serialize($fake));

        $setting = new \ConnectionSetting();
        $setting->Unserialize($payload);

        $this->assertSame('mysql', $setting->Type);
    }
}
