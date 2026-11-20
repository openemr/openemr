<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Config;

use Firehed\Container\TypedContainerInterface;
use OpenEMR\BC\SiteInstallState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SiteInstallStateDefinitionTest extends TestCase
{
    use LoadsDefinitionsTrait;

    private string $installRoot;
    private string $siteDir;

    protected function setUp(): void
    {
        $this->installRoot = sys_get_temp_dir() . '/site-install-state-def-' . uniqid();
        $this->siteDir = $this->installRoot . '/sites/default';
        mkdir($this->siteDir, recursive: true);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->siteDir . '/sqlconf.php')) {
            unlink($this->siteDir . '/sqlconf.php');
        }
        rmdir($this->siteDir);
        rmdir($this->installRoot . '/sites');
        rmdir($this->installRoot);
    }

    #[DataProvider('sqlconfProvider')]
    public function testReadsInstallFlagFromSqlconf(string $sqlconf, bool $expected): void
    {
        file_put_contents($this->siteDir . '/sqlconf.php', $sqlconf);

        self::assertSame(
            $expected,
            $this->resolve(dbName: null)->isInstalled,
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
            $this->resolve(dbName: null)->isInstalled,
            'a site without sqlconf.php has not been installed',
        );
    }

    public function testEnvDatabaseConfigIsAlwaysInstalled(): void
    {
        file_put_contents($this->siteDir . '/sqlconf.php', "<?php\n\$config = 0;\n");

        self::assertTrue(
            $this->resolve(dbName: '')->isInstalled,
            'setting OPENEMR_DB_NAME, even to empty, overrides sqlconf.php',
        );
    }

    private function resolve(?string $dbName): SiteInstallState
    {
        $container = self::createStub(TypedContainerInterface::class);
        $container->method('get')->willReturnMap([
            ['OPENEMR_DB_NAME', $dbName],
        ]);
        $container->method('getString')->willReturnMap([
            ['installRoot', $this->installRoot],
            ['OPENEMR_SITE', 'default'],
        ]);

        $state = self::definition('database.php', SiteInstallState::class)($container);
        self::assertInstanceOf(SiteInstallState::class, $state);
        return $state;
    }
}
