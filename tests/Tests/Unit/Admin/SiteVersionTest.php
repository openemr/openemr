<?php

/**
 * SiteVersion Unit Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR <warp@agent.dev>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Admin;

use OpenEMR\Admin\ValueObjects\SiteVersion;
use PHPUnit\Framework\TestCase;

class SiteVersionTest extends TestCase
{
    public function testCreatesVersionObject(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 4, 508, 12);

        $this->assertSame('7', $version->getMajor());
        $this->assertSame('0', $version->getMinor());
        $this->assertSame('3', $version->getPatch());
        $this->assertSame('', $version->getTag());
        $this->assertSame(4, $version->getRealPatch());
        $this->assertSame(508, $version->getDatabase());
        $this->assertSame(12, $version->getAcl());
    }

    public function testBuildsVersionString(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 4, 508, 12);

        $this->assertSame('7.0.3 (4)', $version->toString());
    }

    public function testBuildsVersionStringWithTag(): void
    {
        $version = new SiteVersion('7', '0', '3', '-dev', 0, 508, 12);

        $this->assertSame('7.0.3-dev', $version->toString());
    }

    public function testBuildsVersionStringWithZeroRealPatch(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 0, 508, 12);

        $this->assertSame('7.0.3', $version->toString());
    }

    public function testDetectsDatabaseUpgradeRequired(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 4, 500, 12);

        $this->assertTrue($version->requiresDatabaseUpgrade(508));
        $this->assertFalse($version->requiresDatabaseUpgrade(500));
    }

    public function testDetectsAclUpgradeRequired(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 4, 508, 10);

        $this->assertTrue($version->requiresAclUpgrade(12));
        $this->assertFalse($version->requiresAclUpgrade(10));
        $this->assertFalse($version->requiresAclUpgrade(9));
    }

    public function testDetectsPatchUpgradeRequired(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 3, 508, 12);

        $this->assertTrue($version->requiresPatchUpgrade(4));
        $this->assertFalse($version->requiresPatchUpgrade(3));
    }

    public function testDeterminesUpgradeStatusForDatabaseUpgrade(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 4, 500, 12);
        $status = $version->determineUpgradeStatus(508, 12, 4);

        $this->assertTrue($status['requiresUpgrade']);
        $this->assertSame('database', $status['upgradeType']);
        $this->assertFalse($status['isCurrent']);
    }

    public function testDeterminesUpgradeStatusForAclUpgrade(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 4, 508, 10);
        $status = $version->determineUpgradeStatus(508, 12, 4);

        $this->assertTrue($status['requiresUpgrade']);
        $this->assertSame('acl', $status['upgradeType']);
        $this->assertFalse($status['isCurrent']);
    }

    public function testDeterminesUpgradeStatusForPatchUpgrade(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 3, 508, 12);
        $status = $version->determineUpgradeStatus(508, 12, 4);

        $this->assertTrue($status['requiresUpgrade']);
        $this->assertSame('patch', $status['upgradeType']);
        $this->assertFalse($status['isCurrent']);
    }

    public function testDeterminesCurrentStatus(): void
    {
        $version = new SiteVersion('7', '0', '3', '', 4, 508, 12);
        $status = $version->determineUpgradeStatus(508, 12, 4);

        $this->assertFalse($status['requiresUpgrade']);
        $this->assertSame('', $status['upgradeType']);
        $this->assertTrue($status['isCurrent']);
    }

    public function testUpgradePriorityDatabaseFirst(): void
    {
        // When multiple upgrades needed, database should be reported first
        $version = new SiteVersion('7', '0', '3', '', 3, 500, 10);
        $status = $version->determineUpgradeStatus(508, 12, 4);

        $this->assertSame('database', $status['upgradeType']);
    }

    public function testUpgradePriorityAclBeforePatch(): void
    {
        // When ACL and patch needed, ACL should be reported
        $version = new SiteVersion('7', '0', '3', '', 3, 508, 10);
        $status = $version->determineUpgradeStatus(508, 12, 4);

        $this->assertSame('acl', $status['upgradeType']);
    }
}
