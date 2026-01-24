<?php

/**
 * Site Value Object Unit Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR <warp@agent.dev>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Admin;

use OpenEMR\Admin\ValueObjects\Site;
use PHPUnit\Framework\TestCase;

class SiteTest extends TestCase
{
    public function testCreatesNeedsSetupSite(): void
    {
        $site = Site::needsSetup('testsite');

        $this->assertSame('testsite', $site->getSiteId());
        $this->assertSame('', $site->getDbName());
        $this->assertSame('', $site->getSiteName());
        $this->assertSame('Unknown', $site->getVersion());
        $this->assertTrue($site->requiresSetup());
        $this->assertSame('', $site->getError());
        $this->assertFalse($site->requiresUpgrade());
        $this->assertSame('', $site->getUpgradeType());
        $this->assertFalse($site->isCurrent());
    }

    public function testCreatesErrorSite(): void
    {
        $site = Site::withError('testsite', 'testdb', 'Connection failed');

        $this->assertSame('testsite', $site->getSiteId());
        $this->assertSame('testdb', $site->getDbName());
        $this->assertSame('', $site->getSiteName());
        $this->assertSame('Unknown', $site->getVersion());
        $this->assertFalse($site->requiresSetup());
        $this->assertSame('Connection failed', $site->getError());
        $this->assertFalse($site->requiresUpgrade());
        $this->assertSame('', $site->getUpgradeType());
        $this->assertFalse($site->isCurrent());
    }

    public function testCreatesFullyConfiguredSite(): void
    {
        $site = Site::create(
            siteId: 'default',
            dbName: 'openemr',
            siteName: 'OpenEMR Test',
            version: '7.0.3 (4)',
            requiresUpgrade: false,
            upgradeType: '',
            isCurrent: true
        );

        $this->assertSame('default', $site->getSiteId());
        $this->assertSame('openemr', $site->getDbName());
        $this->assertSame('OpenEMR Test', $site->getSiteName());
        $this->assertSame('7.0.3 (4)', $site->getVersion());
        $this->assertFalse($site->requiresSetup());
        $this->assertSame('', $site->getError());
        $this->assertFalse($site->requiresUpgrade());
        $this->assertSame('', $site->getUpgradeType());
        $this->assertTrue($site->isCurrent());
    }

    public function testCreatesSiteThatRequiresUpgrade(): void
    {
        $site = Site::create(
            siteId: 'default',
            dbName: 'openemr',
            siteName: 'OpenEMR Test',
            version: '7.0.2',
            requiresUpgrade: true,
            upgradeType: 'database',
            isCurrent: false
        );

        $this->assertTrue($site->requiresUpgrade());
        $this->assertSame('database', $site->getUpgradeType());
        $this->assertFalse($site->isCurrent());
    }

    public function testConvertsToArrayCorrectly(): void
    {
        $site = Site::create(
            siteId: 'default',
            dbName: 'openemr',
            siteName: 'OpenEMR Test',
            version: '7.0.3 (4)',
            requiresUpgrade: false,
            upgradeType: '',
            isCurrent: true
        );

        $array = $site->toArray();

        $this->assertIsArray($array);
        $this->assertSame('default', $array['site_id']);
        $this->assertSame('openemr', $array['db_name']);
        $this->assertSame('OpenEMR Test', $array['site_name']);
        $this->assertSame('7.0.3 (4)', $array['version']);
        $this->assertFalse($array['needs_setup']);
        $this->assertSame('', $array['error']);
        $this->assertFalse($array['requires_upgrade']);
        $this->assertSame('', $array['upgrade_type']);
        $this->assertTrue($array['is_current']);
    }

    public function testArrayStructureMatchesTemplate(): void
    {
        $site = Site::needsSetup('test');
        $array = $site->toArray();

        // Verify keys match template expectations
        $expectedKeys = [
            'site_id',
            'db_name',
            'site_name',
            'version',
            'needs_setup',
            'error',
            'requires_upgrade',
            'upgrade_type',
            'is_current',
        ];

        $this->assertSame($expectedKeys, array_keys($array));
    }
}
