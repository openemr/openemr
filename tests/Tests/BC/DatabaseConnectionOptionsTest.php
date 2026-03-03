<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\BC;

use InvalidArgumentException;
use OpenEMR\BC\DatabaseConnectionOptions;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SensitiveParameter;

#[CoversClass(DatabaseConnectionOptions::class)]
#[Small]
class DatabaseConnectionOptionsTest extends TestCase
{
    public function testToDbalParamsWithHostAndPort(): void
    {
        $options = new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
            host: 'db.example.com',
            port: 3307,
        );

        $params = $options->toDbalParams();

        self::assertSame('pdo_mysql', $params['driver'] ?? null);
        self::assertSame('testdb', $params['dbname'] ?? null);
        self::assertSame('testuser', $params['user'] ?? null);
        self::assertSame('secret', $params['password'] ?? null);
        self::assertSame('db.example.com', $params['host'] ?? null);
        self::assertSame(3307, $params['port'] ?? null);
        self::assertSame('utf8mb4', $params['charset'] ?? null);
        self::assertArrayNotHasKey('unix_socket', $params);
    }

    public function testToDbalParamsWithUnixSocket(): void
    {
        $options = new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
            unixSocket: '/var/run/mysqld/mysqld.sock',
        );

        $params = $options->toDbalParams();

        self::assertSame('/var/run/mysqld/mysqld.sock', $params['unix_socket'] ?? null);
        self::assertArrayNotHasKey('host', $params);
        self::assertArrayNotHasKey('port', $params);
    }

    public function testToDbalParamsWithSslCaOnly(): void
    {
        $options = new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
            host: 'localhost',
            port: 3306,
            sslCaPath: '/path/to/ca.pem',
        );

        $params = $options->toDbalParams();

        self::assertSame([
            PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca.pem',
        ], $params['driverOptions'] ?? null);
    }

    public function testToDbalParamsWithFullSsl(): void
    {
        $options = new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
            host: 'localhost',
            port: 3306,
            sslCaPath: '/path/to/ca.pem',
            sslClientCert: [
                'cert' => '/path/to/cert.pem',
                'key' => '/path/to/key.pem',
            ],
        );

        $params = $options->toDbalParams();

        self::assertSame([
            PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca.pem',
            PDO::MYSQL_ATTR_SSL_CERT => '/path/to/cert.pem',
            PDO::MYSQL_ATTR_SSL_KEY => '/path/to/key.pem',
        ], $params['driverOptions'] ?? null);
    }

    public function testDebugInfoRedactsPassword(): void
    {
        $options = new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'super-secret-password',
            host: 'localhost',
            port: 3306,
        );

        $debugInfo = $options->__debugInfo();

        self::assertSame('[REDACTED]', $debugInfo['password']);
        self::assertSame('testdb', $debugInfo['dbname']);
        self::assertSame('testuser', $debugInfo['user']);
        self::assertSame('localhost', $debugInfo['host']);
        self::assertSame(3306, $debugInfo['port']);
    }

    public function testPasswordParameterHasSensitiveAttribute(): void
    {
        $rc = new ReflectionClass(DatabaseConnectionOptions::class);
        $constructor = $rc->getConstructor();
        self::assertNotNull($constructor);

        $passwordParam = null;
        foreach ($constructor->getParameters() as $param) {
            if ($param->getName() === 'password') {
                $passwordParam = $param;
                break;
            }
        }

        self::assertNotNull($passwordParam, 'password parameter not found');

        $attributes = $passwordParam->getAttributes(SensitiveParameter::class);
        self::assertCount(1, $attributes, 'password parameter should have #[SensitiveParameter] attribute');
    }

    public function testRejectsNoConnectionInfo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify either host/port or unixSocket');

        new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
        );
    }

    public function testRejectsHostWithoutPort(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('host and port must both be provided together');

        new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
            host: 'localhost',
        );
    }

    public function testRejectsPortWithoutHost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('host and port must both be provided together');

        new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
            port: 3306,
        );
    }

    public function testRejectsBothHostPortAndSocket(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot specify both host/port and unixSocket');

        new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
            host: 'localhost',
            port: 3306,
            unixSocket: '/var/run/mysqld/mysqld.sock',
        );
    }

    public function testFromSqlconfWithHostAndPort(): void
    {
        $sqlconf = [
            'dbase' => 'my-database',
            'login' => 'fancy-user',
            'pass' => 'secret',
            'host' => '192.168.0.76',
            'port' => '3307',
            'db_encoding' => 'utf8mb4',
        ];

        $options = DatabaseConnectionOptions::fromSqlconf($sqlconf);

        self::assertSame('my-database', $options->dbname);
        self::assertSame('fancy-user', $options->user);
        self::assertSame('192.168.0.76', $options->host);
        self::assertSame(3307, $options->port);
        self::assertNull($options->unixSocket);
        self::assertSame('utf8mb4', $options->charset);
    }

    public function testFromSqlconfWithUnixSocket(): void
    {
        $sqlconf = [
            'dbase' => 'openemr',
            'login' => 'root',
            'pass' => 'secret',
            'socket' => '/var/run/mysqld/mysqld.sock',
            'db_encoding' => 'utf8mb4',
        ];

        $options = DatabaseConnectionOptions::fromSqlconf($sqlconf);

        self::assertSame('/var/run/mysqld/mysqld.sock', $options->unixSocket);
        self::assertNull($options->host);
        self::assertNull($options->port);
    }

    public function testFromSqlconfDefaultsCharset(): void
    {
        $sqlconf = [
            'dbase' => 'openemr',
            'login' => 'root',
            'pass' => 'secret',
            'host' => 'localhost',
            'port' => '3306',
            // db_encoding intentionally missing
        ];

        $options = DatabaseConnectionOptions::fromSqlconf($sqlconf);

        self::assertSame('utf8mb4', $options->charset);
    }

    public function testFromSqlconfWithSslConfig(): void
    {
        $sqlconf = [
            'dbase' => 'openemr',
            'login' => 'root',
            'pass' => 'secret',
            'host' => 'localhost',
            'port' => '3306',
        ];
        $ssl = [
            'ca' => '/path/to/ca.pem',
            'clientCert' => [
                'cert' => '/path/to/cert.pem',
                'key' => '/path/to/key.pem',
            ],
        ];

        $options = DatabaseConnectionOptions::fromSqlconf($sqlconf, $ssl);

        self::assertSame('/path/to/ca.pem', $options->sslCaPath);
        self::assertSame([
            'cert' => '/path/to/cert.pem',
            'key' => '/path/to/key.pem',
        ], $options->sslClientCert);
    }

    /** @var list<string> */
    private array $tempDirs = [];

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            $this->cleanupTempDir($dir);
        }
        $this->tempDirs = [];
    }

    public function testForSiteLoadsConfigFile(): void
    {
        $siteDir = $this->createTempSiteDir();
        mkdir($siteDir, 0755, true);

        // db_encoding is explicitly set to a non-utf8mb4 value to verify
        // that legacy config values are ignored and utf8mb4 is always used
        file_put_contents($siteDir . '/sqlconf.php', <<<'PHP'
<?php
$sqlconf = [
    'dbase' => 'testdb',
    'login' => 'testuser',
    'pass' => 'testpass',
    'host' => '127.0.0.1',
    'port' => '3306',
    'db_encoding' => 'latin1',
];
PHP
        );

        $options = DatabaseConnectionOptions::forSite($siteDir);

        self::assertSame('testdb', $options->dbname);
        self::assertSame('testuser', $options->user);
        self::assertSame('127.0.0.1', $options->host);
        self::assertSame(3306, $options->port);
        self::assertNull($options->unixSocket);
        self::assertSame('utf8mb4', $options->charset);
        self::assertNull($options->sslCaPath);
        self::assertNull($options->sslClientCert);
    }

    private function createTempSiteDir(): string
    {
        $dir = sys_get_temp_dir() . '/test-site-' . uniqid();
        $this->tempDirs[] = $dir;
        return $dir;
    }

    public function testForSiteDetectsSslCaOnly(): void
    {
        $siteDir = $this->createTempSiteDir();
        mkdir($siteDir, 0755, true);

        file_put_contents($siteDir . '/sqlconf.php', <<<'PHP'
<?php
$sqlconf = [
    'dbase' => 'testdb',
    'login' => 'testuser',
    'pass' => 'testpass',
    'host' => '127.0.0.1',
    'port' => '3306',
    'db_encoding' => 'utf8mb4',
];
PHP
        );

        $certDir = $siteDir . '/documents/certificates';
        mkdir($certDir, 0755, true);
        file_put_contents($certDir . '/mysql-ca', 'ca-content');

        $options = DatabaseConnectionOptions::forSite($siteDir);

        self::assertSame($certDir . '/mysql-ca', $options->sslCaPath);
        self::assertNull($options->sslClientCert);
    }

    public function testForSiteDetectsFullSslCerts(): void
    {
        $siteDir = $this->createTempSiteDir();
        mkdir($siteDir, 0755, true);

        file_put_contents($siteDir . '/sqlconf.php', <<<'PHP'
<?php
$sqlconf = [
    'dbase' => 'testdb',
    'login' => 'testuser',
    'pass' => 'testpass',
    'host' => '127.0.0.1',
    'port' => '3306',
    'db_encoding' => 'utf8mb4',
];
PHP
        );

        $certDir = $siteDir . '/documents/certificates';
        mkdir($certDir, 0755, true);
        file_put_contents($certDir . '/mysql-ca', 'ca-content');
        file_put_contents($certDir . '/mysql-cert', 'cert-content');
        file_put_contents($certDir . '/mysql-key', 'key-content');

        $options = DatabaseConnectionOptions::forSite($siteDir);

        self::assertSame($certDir . '/mysql-ca', $options->sslCaPath);
        self::assertSame([
            'cert' => $certDir . '/mysql-cert',
            'key' => $certDir . '/mysql-key',
        ], $options->sslClientCert);
    }

    public function testForSiteRejectsMismatchedCertWithoutKey(): void
    {
        $siteDir = $this->createTempSiteDir();
        mkdir($siteDir, 0755, true);

        file_put_contents($siteDir . '/sqlconf.php', <<<'PHP'
<?php
$sqlconf = [
    'dbase' => 'testdb',
    'login' => 'testuser',
    'pass' => 'testpass',
    'host' => '127.0.0.1',
    'port' => '3306',
    'db_encoding' => 'utf8mb4',
];
PHP
        );

        $certDir = $siteDir . '/documents/certificates';
        mkdir($certDir, 0755, true);
        file_put_contents($certDir . '/mysql-cert', 'cert-content');
        // mysql-key intentionally missing

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('MySQL cert or key file missing');

        DatabaseConnectionOptions::forSite($siteDir);
    }

    public function testForSiteRejectsMismatchedKeyWithoutCert(): void
    {
        $siteDir = $this->createTempSiteDir();
        mkdir($siteDir, 0755, true);

        file_put_contents($siteDir . '/sqlconf.php', <<<'PHP'
<?php
$sqlconf = [
    'dbase' => 'testdb',
    'login' => 'testuser',
    'pass' => 'testpass',
    'host' => '127.0.0.1',
    'port' => '3306',
    'db_encoding' => 'utf8mb4',
];
PHP
        );

        $certDir = $siteDir . '/documents/certificates';
        mkdir($certDir, 0755, true);
        file_put_contents($certDir . '/mysql-key', 'key-content');
        // mysql-cert intentionally missing

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('MySQL cert or key file missing');

        DatabaseConnectionOptions::forSite($siteDir);
    }

    public function testForSiteWithNoCertificates(): void
    {
        $siteDir = $this->createTempSiteDir();
        mkdir($siteDir, 0755, true);

        file_put_contents($siteDir . '/sqlconf.php', <<<'PHP'
<?php
$sqlconf = [
    'dbase' => 'testdb',
    'login' => 'testuser',
    'pass' => 'testpass',
    'host' => '127.0.0.1',
    'port' => '3306',
    'db_encoding' => 'utf8mb4',
];
PHP
        );

        // No certificates directory created

        $options = DatabaseConnectionOptions::forSite($siteDir);

        self::assertNull($options->sslCaPath);
        self::assertNull($options->sslClientCert);
    }

    private function cleanupTempDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }
}
