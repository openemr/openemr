<?php

/**
 * Isolated tests for docker entrypoint query helpers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Docker;

use OpenEMR\Common\Docker\EntrypointQuery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[Group('isolated')]
final class EntrypointQueryIsolatedTest extends TestCase
{
    private string $tempDir = '';

    protected function setUp(): void
    {
        $dir = sys_get_temp_dir() . '/oe-entrypoint-query-' . bin2hex(random_bytes(4));
        $this->assertTrue(mkdir($dir, 0700));
        $this->tempDir = $dir;
    }

    protected function tearDown(): void
    {
        if ($this->tempDir === '' || !is_dir($this->tempDir)) {
            return;
        }
        foreach (glob($this->tempDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->tempDir);
    }

    #[Test]
    public function missingSqlconfIsNotConfigured(): void
    {
        $this->assertSame(0, EntrypointQuery::isConfigured($this->tempDir . '/missing.php'));
        $this->assertSame('', EntrypointQuery::configFlag($this->tempDir . '/missing.php'));
        $this->assertStringContainsString("EP_CONFIGURED='0'", EntrypointQuery::exportSqlconf($this->tempDir . '/missing.php'));
    }

    #[Test]
    public function configuredSqlconfExportsShellAssignments(): void
    {
        $path = $this->writeSqlconf('$host = "db.example"; $port = "3307"; $login = "oe"; $pass = "p\'ass"; $dbase = "openemr"; $config = 1;');
        $this->assertSame(1, EntrypointQuery::isConfigured($path));
        $this->assertSame('1', EntrypointQuery::configFlag($path));
        $exported = EntrypointQuery::exportSqlconf($path);
        $this->assertStringContainsString("EP_CONFIGURED='1'", $exported);
        $this->assertStringContainsString("EP_HOST='db.example'", $exported);
        $this->assertStringContainsString("EP_PORT='3307'", $exported);
        $this->assertStringContainsString("EP_LOGIN='oe'", $exported);
        $this->assertStringContainsString("EP_DBASE='openemr'", $exported);
        $this->assertStringContainsString('EP_PASS=', $exported);
        $this->assertStringNotContainsString("\x1f", $exported);
    }

    #[Test]
    public function unconfiguredSqlconfDoesNotExportDatabase(): void
    {
        $path = $this->writeSqlconf('$host = "db.example"; $config = 0;');
        $this->assertSame(0, EntrypointQuery::isConfigured($path));
        $this->assertSame('0', EntrypointQuery::configFlag($path));
        $exported = EntrypointQuery::exportSqlconf($path);
        $this->assertStringContainsString("EP_CONFIGURED='0'", $exported);
        $this->assertStringContainsString("EP_HOST=''", $exported);
    }

    #[Test]
    public function schemaVersionReadsVersionPhp(): void
    {
        $version = dirname(__DIR__, 5) . '/version.php';
        $this->assertGreaterThan(0, EntrypointQuery::schemaVersion($version));
        $this->assertSame(0, EntrypointQuery::schemaVersion($this->tempDir . '/missing-version.php'));
    }

    #[Test]
    public function iniGetReturnsEmptyForUnknownKey(): void
    {
        $this->assertSame('', EntrypointQuery::iniGet('this.ini.key.does.not.exist'));
    }

    #[Test]
    public function cliDispatchesIsConfigured(): void
    {
        $path = $this->writeSqlconf('$config = 1;');
        $cli = dirname(__DIR__, 5) . '/src/Common/Docker/entrypoint_query.php';
        $process = new Process([PHP_BINARY, $cli, 'is-configured', $path]);
        $process->run();
        $this->assertSame(0, $process->getExitCode());
        $this->assertSame('1', $process->getOutput());
    }

    #[Test]
    public function binaryCliDispatchesIsConfigured(): void
    {
        $path = $this->writeSqlconf('$config = 1;');
        $cli = dirname(__DIR__, 5) . '/docker/binary/utilities/entrypoint_query.php';
        $process = new Process([PHP_BINARY, $cli, 'is-configured', $path]);
        $process->run();
        $this->assertSame(0, $process->getExitCode());
        $this->assertSame('1', $process->getOutput());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function cliPaths(): array
    {
        return [
            'application' => ['src/Common/Docker/entrypoint_query.php'],
            'binary image' => ['docker/binary/utilities/entrypoint_query.php'],
            'flex image' => ['docker/flex/utilities/entrypoint_query.php'],
            'release image' => ['docker/release/utilities/entrypoint_query.php'],
        ];
    }

    #[Test]
    #[DataProvider('cliPaths')]
    public function packagedCliSupportsStartupQueries(string $relative): void
    {
        $cli = dirname(__DIR__, 5) . '/' . $relative;
        $path = $this->writeSqlconf('$config = 0;');
        $version = $this->tempDir . '/version.php';
        file_put_contents($version, '<?php $v_database = "42";');
        foreach ([
            ['is-configured', $path, '0'],
            ['config-flag', $path, '0'],
            ['is-configured', $this->tempDir . '/missing.php', '0'],
            ['config-flag', $this->tempDir . '/missing.php', ''],
            ['schema-version', $version, '42'],
            ['schema-version', $this->tempDir . '/missing.php', '0'],
            ['ini-get', 'this.ini.key.does.not.exist', ''],
        ] as [$command, $argument, $expected]) {
            $process = new Process([PHP_BINARY, $cli, $command, $argument]);
            $process->run();
            $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());
            $this->assertSame($expected, $process->getOutput());
        }
        $process = new Process([PHP_BINARY, '-d', 'session.save_handler=files', $cli, 'ini-get', 'session.save_handler']);
        $process->run();
        $this->assertSame(0, $process->getExitCode());
        $this->assertSame('files', $process->getOutput());

        $process = new Process([PHP_BINARY, $cli, 'unsupported-command']);
        $process->run();
        $this->assertSame(2, $process->getExitCode());
        $this->assertSame('', $process->getOutput());
        $this->assertStringContainsString('Unknown command', $process->getErrorOutput());
    }

    #[Test]
    #[DataProvider('cliPaths')]
    public function exportedCredentialsRoundTripThroughShellWithoutExpansion(string $relative): void
    {
        $password = "space ' quote \" dollar $ \n" . '$(printf INJECTED); `printf INJECTED`';
        $path = $this->writeSqlconf(
            '$config = 1; $host = "db.example"; $port = 3307; $login = "oe"; $dbase = "openemr"; $pass = '
            . var_export($password, true) . ';'
        );
        $process = new Process([PHP_BINARY, dirname(__DIR__, 5) . '/' . $relative, 'export-sqlconf', $path]);
        $process->run();
        $this->assertSame(0, $process->getExitCode());
        $shell = new Process([
            'sh', '-c',
            'eval "$1"; printf "%s\\0" "$EP_CONFIGURED" "$EP_HOST" "$EP_PORT" "$EP_LOGIN" "$EP_PASS" "$EP_DBASE"',
            'entrypoint-test', $process->getOutput(),
        ]);
        $shell->run();
        $this->assertSame(0, $shell->getExitCode(), $shell->getErrorOutput());
        $this->assertSame(
            ['1', 'db.example', '3307', 'oe', $password, 'openemr', ''],
            explode("\0", $shell->getOutput()),
        );
    }

    private function writeSqlconf(string $body): string
    {
        $path = $this->tempDir . '/sqlconf.php';
        $this->assertNotFalse(file_put_contents($path, "<?php\n" . $body . "\n"));
        return $path;
    }
}
