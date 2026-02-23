<?php

/**
 * DbUtils Isolated Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Database;

use InvalidArgumentException;
use OpenEMR\Common\Database\DbUtils;
use PHPUnit\Framework\TestCase;

class DbUtilsTest extends TestCase
{
    public function testBuildMysqlDsnWithAllParameters(): void
    {
        $dsn = DbUtils::buildMysqlDsn('openemr', 'localhost', '3306');
        $this->assertSame('mysql:dbname=openemr;host=localhost;port=3306', $dsn);
    }

    public function testBuildMysqlDsnWithoutPort(): void
    {
        $dsn = DbUtils::buildMysqlDsn('openemr', 'localhost');
        $this->assertSame('mysql:dbname=openemr;host=localhost', $dsn);
    }

    public function testBuildMysqlDsnWithEmptyPort(): void
    {
        $dsn = DbUtils::buildMysqlDsn('openemr', 'localhost', '');
        $this->assertSame('mysql:dbname=openemr;host=localhost', $dsn);
    }

    public function testBuildMysqlDsnWithPort1(): void
    {
        $dsn = DbUtils::buildMysqlDsn('mydb', '127.0.0.1', '1');
        $this->assertSame('mysql:dbname=mydb;host=127.0.0.1;port=1', $dsn);
    }

    public function testBuildMysqlDsnWithPort65535(): void
    {
        $dsn = DbUtils::buildMysqlDsn('mydb', '127.0.0.1', '65535');
        $this->assertSame('mysql:dbname=mydb;host=127.0.0.1;port=65535', $dsn);
    }

    public function testBuildMysqlDsnThrowsOnPort0(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DbUtils::buildMysqlDsn('openemr', 'localhost', '0');
    }

    public function testBuildMysqlDsnThrowsOnPort65536(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DbUtils::buildMysqlDsn('openemr', 'localhost', '65536');
    }

    public function testBuildMysqlDsnThrowsOnNonNumericPort(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DbUtils::buildMysqlDsn('openemr', 'localhost', 'abc');
    }

    public function testBuildMysqlDsnThrowsOnNegativePort(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DbUtils::buildMysqlDsn('openemr', 'localhost', '-1');
    }
}
