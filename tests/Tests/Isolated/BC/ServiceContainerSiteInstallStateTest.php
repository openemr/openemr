<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use LogicException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
class ServiceContainerSiteInstallStateTest extends TestCase
{
    private string $siteDir;

    protected function setUp(): void
    {
        ServiceContainer::reset();
        $this->siteDir = sys_get_temp_dir() . '/site-install-state-' . uniqid();
        mkdir($this->siteDir);
        OEGlobalsBag::getInstance()->set('OE_SITE_DIR', $this->siteDir);
    }

    protected function tearDown(): void
    {
        ServiceContainer::reset();
        if (file_exists($this->siteDir . '/sqlconf.php')) {
            unlink($this->siteDir . '/sqlconf.php');
        }
        rmdir($this->siteDir);
    }

    #[DataProvider('sqlconfProvider')]
    public function testReadsInstallFlagFromSqlconf(string $sqlconf, bool $expected): void
    {
        file_put_contents($this->siteDir . '/sqlconf.php', $sqlconf);

        self::assertSame(
            $expected,
            ServiceContainer::getSiteInstallState()->isInstalled,
            'only $config === 1 means installed',
        );
    }

    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sqlconfProvider(): array
    {
        return [
            'installed' => ["<?php\n\$config = 1;\n", true],
            'setup required' => ["<?php\n\$config = 0;\n", false],
            'flag not set' => ["<?php\n", false],
        ];
    }

    public function testMissingSqlconfIsNotInstalled(): void
    {
        self::assertFalse(
            ServiceContainer::getSiteInstallState()->isInstalled,
            'a site without sqlconf.php has not been installed',
        );
    }

    public function testRequiresSiteDir(): void
    {
        OEGlobalsBag::getInstance()->set('OE_SITE_DIR', '');

        $this->expectException(LogicException::class);
        ServiceContainer::getSiteInstallState();
    }
}
