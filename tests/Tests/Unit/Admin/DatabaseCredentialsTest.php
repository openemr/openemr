<?php

/**
 * DatabaseCredentials Unit Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR <warp@agent.dev>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Admin;

use OpenEMR\Admin\Exceptions\SiteConfigException;
use OpenEMR\Admin\ValueObjects\DatabaseCredentials;
use PHPUnit\Framework\TestCase;

class DatabaseCredentialsTest extends TestCase
{
    public function testCreatesWithValidCredentials(): void
    {
        $credentials = new DatabaseCredentials('localhost', 'user', 'pass', 'dbname', 3306);

        $this->assertSame('localhost', $credentials->getHost());
        $this->assertSame('user', $credentials->getLogin());
        $this->assertSame('pass', $credentials->getPass());
        $this->assertSame('dbname', $credentials->getDbase());
        $this->assertSame(3306, $credentials->getPort());
    }

    public function testUsesDefaultPort(): void
    {
        $credentials = new DatabaseCredentials('localhost', 'user', 'pass', 'dbname');

        $this->assertSame(3306, $credentials->getPort());
    }

    public function testGeneratesCorrectPoolKey(): void
    {
        $credentials = new DatabaseCredentials('localhost', 'user', 'pass', 'dbname', 3307);

        $this->assertSame('localhost:3307/dbname', $credentials->getPoolKey());
    }

    public function testSanitizesCredentials(): void
    {
        $credentials = new DatabaseCredentials('localhost', 'user', 'secret', 'dbname');
        $sanitized = $credentials->toSanitizedArray();

        $this->assertSame('localhost', $sanitized['host']);
        $this->assertSame('user', $sanitized['login']);
        $this->assertSame('***', $sanitized['pass']);
        $this->assertSame('dbname', $sanitized['dbase']);
        $this->assertSame(3306, $sanitized['port']);
    }

    public function testThrowsExceptionWhenHostIsNull(): void
    {
        $this->expectException(SiteConfigException::class);
        $this->expectExceptionMessage('Database credentials are incomplete');

        new DatabaseCredentials(null, 'user', 'pass', 'dbname');
    }

    public function testThrowsExceptionWhenLoginIsNull(): void
    {
        $this->expectException(SiteConfigException::class);
        $this->expectExceptionMessage('Database credentials are incomplete');

        new DatabaseCredentials('localhost', null, 'pass', 'dbname');
    }

    public function testThrowsExceptionWhenPassIsNull(): void
    {
        $this->expectException(SiteConfigException::class);
        $this->expectExceptionMessage('Database credentials are incomplete');

        new DatabaseCredentials('localhost', 'user', null, 'dbname');
    }

    public function testThrowsExceptionWhenDbaseIsNull(): void
    {
        $this->expectException(SiteConfigException::class);
        $this->expectExceptionMessage('Database credentials are incomplete');

        new DatabaseCredentials('localhost', 'user', 'pass', null);
    }
}
