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
            charset: 'utf8mb4',
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

    public function testToDbalParamsWithDriverOptions(): void
    {
        $driverOptions = [
            PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca.pem',
            PDO::MYSQL_ATTR_SSL_CERT => '/path/to/cert.pem',
            PDO::MYSQL_ATTR_SSL_KEY => '/path/to/key.pem',
        ];

        $options = new DatabaseConnectionOptions(
            dbname: 'testdb',
            user: 'testuser',
            password: 'secret',
            host: 'localhost',
            port: 3306,
            driverOptions: $driverOptions,
        );

        $params = $options->toDbalParams();

        self::assertSame($driverOptions, $params['driverOptions'] ?? null);
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

    public function testFromSqlconfWithDriverOptions(): void
    {
        $sqlconf = [
            'dbase' => 'openemr',
            'login' => 'root',
            'pass' => 'secret',
            'host' => 'localhost',
            'port' => '3306',
        ];
        $driverOptions = [PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca.pem'];

        $options = DatabaseConnectionOptions::fromSqlconf($sqlconf, $driverOptions);

        self::assertSame($driverOptions, $options->driverOptions);
    }
}
