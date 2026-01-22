<?php

/**
 * SiteDiscoveryService Unit Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR <warp@agent.dev>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Admin;

use OpenEMR\Admin\Exceptions\InvalidSiteNameException;
use OpenEMR\Admin\Services\SiteDiscoveryService;
use PHPUnit\Framework\TestCase;

class SiteDiscoveryServiceTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a temporary directory for testing
        $this->tempDir = sys_get_temp_dir() . '/openemr_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        // Clean up temporary directory
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function testValidatesValidSiteNames(): void
    {
        $service = new SiteDiscoveryService($this->tempDir);

        $this->assertTrue($service->isValidSiteName('default'));
        $this->assertTrue($service->isValidSiteName('site-name'));
        $this->assertTrue($service->isValidSiteName('site_name'));
        $this->assertTrue($service->isValidSiteName('site.name'));
        $this->assertTrue($service->isValidSiteName('site123'));
        $this->assertTrue($service->isValidSiteName('123site'));
    }

    public function testRejectsInvalidSiteNames(): void
    {
        $service = new SiteDiscoveryService($this->tempDir);

        $this->assertFalse($service->isValidSiteName(''));
        $this->assertFalse($service->isValidSiteName('../parent'));
        $this->assertFalse($service->isValidSiteName('site/path'));
        $this->assertFalse($service->isValidSiteName('site name'));
        $this->assertFalse($service->isValidSiteName('site@name'));
        $this->assertFalse($service->isValidSiteName('site#name'));
    }

    public function testDiscoversValidSites(): void
    {
        // Create test site directories with sqlconf.php
        mkdir($this->tempDir . '/default');
        touch($this->tempDir . '/default/sqlconf.php');

        mkdir($this->tempDir . '/site1');
        touch($this->tempDir . '/site1/sqlconf.php');

        mkdir($this->tempDir . '/site2');
        touch($this->tempDir . '/site2/sqlconf.php');

        $service = new SiteDiscoveryService($this->tempDir);
        $sites = $service->discoverSites();

        $this->assertCount(3, $sites);
        $this->assertContains('default', $sites);
        $this->assertContains('site1', $sites);
        $this->assertContains('site2', $sites);
    }

    public function testIgnoresDirectoriesWithoutSqlconf(): void
    {
        mkdir($this->tempDir . '/valid');
        touch($this->tempDir . '/valid/sqlconf.php');

        mkdir($this->tempDir . '/invalid');
        // No sqlconf.php created

        $service = new SiteDiscoveryService($this->tempDir);
        $sites = $service->discoverSites();

        $this->assertCount(1, $sites);
        $this->assertContains('valid', $sites);
        $this->assertNotContains('invalid', $sites);
    }

    public function testIgnoresHiddenDirectories(): void
    {
        mkdir($this->tempDir . '/.hidden');
        touch($this->tempDir . '/.hidden/sqlconf.php');

        mkdir($this->tempDir . '/visible');
        touch($this->tempDir . '/visible/sqlconf.php');

        $service = new SiteDiscoveryService($this->tempDir);
        $sites = $service->discoverSites();

        $this->assertCount(1, $sites);
        $this->assertContains('visible', $sites);
        $this->assertNotContains('.hidden', $sites);
    }

    public function testIgnoresCVSDirectory(): void
    {
        mkdir($this->tempDir . '/CVS');
        touch($this->tempDir . '/CVS/sqlconf.php');

        mkdir($this->tempDir . '/valid');
        touch($this->tempDir . '/valid/sqlconf.php');

        $service = new SiteDiscoveryService($this->tempDir);
        $sites = $service->discoverSites();

        $this->assertCount(1, $sites);
        $this->assertNotContains('CVS', $sites);
    }

    public function testIgnoresFiles(): void
    {
        // Create a file (not directory) with valid name
        touch($this->tempDir . '/notadir');

        mkdir($this->tempDir . '/validsite');
        touch($this->tempDir . '/validsite/sqlconf.php');

        $service = new SiteDiscoveryService($this->tempDir);
        $sites = $service->discoverSites();

        $this->assertCount(1, $sites);
        $this->assertContains('validsite', $sites);
    }

    public function testReturnsSortedList(): void
    {
        mkdir($this->tempDir . '/zsite');
        touch($this->tempDir . '/zsite/sqlconf.php');

        mkdir($this->tempDir . '/asite');
        touch($this->tempDir . '/asite/sqlconf.php');

        mkdir($this->tempDir . '/msite');
        touch($this->tempDir . '/msite/sqlconf.php');

        $service = new SiteDiscoveryService($this->tempDir);
        $sites = $service->discoverSites();

        $this->assertSame(['asite', 'msite', 'zsite'], $sites);
    }

    public function testReturnsEmptyArrayWhenDirectoryUnreadable(): void
    {
        $service = new SiteDiscoveryService('/nonexistent/directory');
        $sites = $service->discoverSites();

        $this->assertIsArray($sites);
        $this->assertEmpty($sites);
    }

    public function testGetsSiteConfigPath(): void
    {
        $service = new SiteDiscoveryService($this->tempDir);
        $path = $service->getSiteConfigPath('default');

        $this->assertSame($this->tempDir . '/default/sqlconf.php', $path);
    }

    public function testThrowsExceptionForInvalidSiteNameInConfigPath(): void
    {
        $this->expectException(InvalidSiteNameException::class);
        $this->expectExceptionMessage('Invalid site name format');

        $service = new SiteDiscoveryService($this->tempDir);
        $service->getSiteConfigPath('../invalid');
    }
}
