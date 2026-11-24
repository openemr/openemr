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
use InvalidArgumentException;
use OpenEMR\BC\DatabaseConnectionOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DatabaseConnectionOptionsDefinitionTest extends TestCase
{
    use LoadsDefinitionsTrait;

    private const ENV_TCP = [
        'OPENEMR_DB_NAME' => 'envdb',
        'OPENEMR_DB_HOST' => 'db.example.com',
        'OPENEMR_DB_PORT' => '3307',
    ];

    public function testEnvReadsTcpConnection(): void
    {
        $options = $this->resolve(self::ENV_TCP);

        self::assertSame('envdb', $options->dbname, 'dbname comes from OPENEMR_DB_NAME');
        self::assertSame('envuser', $options->user, 'user comes from OPENEMR_DB_USER');
        self::assertSame('envpass', $options->password, 'password comes from OPENEMR_DB_PASSWORD');
        self::assertSame('db.example.com', $options->host, 'host comes from OPENEMR_DB_HOST');
        self::assertSame(3307, $options->port, 'port is parsed from OPENEMR_DB_PORT');
        self::assertNull($options->unixSocket, 'no socket was configured');
    }

    public function testEnvReadsSocketConnection(): void
    {
        $options = $this->resolve([
            'OPENEMR_DB_NAME' => 'envdb',
            'OPENEMR_DB_SOCKET' => '/run/mysqld/mysqld.sock',
        ]);

        self::assertSame('/run/mysqld/mysqld.sock', $options->unixSocket, 'socket comes from OPENEMR_DB_SOCKET');
        self::assertNull($options->host, 'no host was configured');
        self::assertNull($options->port, 'no port was configured');
    }

    public function testEnvReadsSsl(): void
    {
        $options = $this->resolve(self::ENV_TCP + [
            'OPENEMR_DB_SSL_CA' => '/certs/ca.pem',
            'OPENEMR_DB_SSL_CERT' => '/certs/cert.pem',
            'OPENEMR_DB_SSL_KEY' => '/certs/key.pem',
        ]);

        self::assertSame('/certs/ca.pem', $options->sslCaPath);
        self::assertSame(
            ['cert' => '/certs/cert.pem', 'key' => '/certs/key.pem'],
            $options->sslClientCert,
        );
    }

    public function testEnvWithoutSsl(): void
    {
        $options = $this->resolve(self::ENV_TCP);

        self::assertNull($options->sslCaPath, 'no CA was configured');
        self::assertNull($options->sslClientCert, 'no client cert was configured');
    }

    public function testFallsBackToSiteSqlconfWithoutEnv(): void
    {
        // The fallback reads sites/<site>/sqlconf.php relative to the working
        // directory. A unique site name avoids fromSiteSqlconf()'s cache.
        $root = sys_get_temp_dir() . '/db-options-def-' . uniqid();
        $site = 'site' . uniqid();
        $siteDir = "$root/sites/$site";
        mkdir($siteDir, recursive: true);
        file_put_contents("$siteDir/sqlconf.php", <<<'PHP'
<?php
$sqlconf = [
    'dbase' => 'filedb',
    'login' => 'fileuser',
    'pass' => 'filepass',
    'host' => '127.0.0.1',
    'port' => '3306',
];
PHP
        );
        $cwd = getcwd();
        self::assertIsString($cwd, 'working directory should be readable');
        chdir($root);
        try {
            $options = $this->resolve([], $site);
        } finally {
            chdir($cwd);
            unlink("$siteDir/sqlconf.php");
            rmdir($siteDir);
            rmdir("$root/sites");
            rmdir($root);
        }

        self::assertSame('filedb', $options->dbname, 'settings come from sqlconf.php');
        self::assertSame('fileuser', $options->user);
        self::assertSame('filepass', $options->password);
        self::assertSame('127.0.0.1', $options->host);
        self::assertSame(3306, $options->port);
    }

    #[DataProvider('unpairedClientCertProvider')]
    public function testEnvRejectsUnpairedClientCert(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resolve(self::ENV_TCP + [$key => '/certs/file.pem']);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unpairedClientCertProvider(): array
    {
        return [
            'cert only' => ['OPENEMR_DB_SSL_CERT'],
            'key only' => ['OPENEMR_DB_SSL_KEY'],
        ];
    }

    /**
     * @param array<string, string> $env Nullable container entries that are set
     */
    private function resolve(array $env, string $site = 'default'): DatabaseConnectionOptions
    {
        $container = self::createStub(TypedContainerInterface::class);
        $container->method('get')->willReturnCallback(
            static fn (string $id): ?string => $env[$id] ?? null,
        );
        $container->method('getString')->willReturnMap([
            ['OPENEMR_DB_USER', 'envuser'],
            ['OPENEMR_DB_PASSWORD', 'envpass'],
            ['OPENEMR_SITE', $site],
        ]);

        $options = self::definition('database.php', DatabaseConnectionOptions::class)($container);
        self::assertInstanceOf(DatabaseConnectionOptions::class, $options);
        return $options;
    }
}
