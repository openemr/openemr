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

use Doctrine\DBAL\{Connection, Result};
use OpenEMR\BC\Database;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Database::class)]
#[Small]
class DatabaseTest extends TestCase
{
    public function testFetchOneRowSuccess(): void
    {
        $sql = 'SELECT * FROM foobars WHERE foo_id = ?';
        $output = ['a', 'b', 'c'];

        $result = self::createMock(Result::class);
        $result->expects(self::once())
            ->method('fetchAssociative')
            ->willReturn($output);

        $c = self::createMock(Connection::class);

        $c->expects(self::once())
            ->method('executeQuery')
            ->with($sql, ['foo'])
            ->willReturn($result);

        $db = new Database($c);
        self::assertSame($output, $db->fetchOneRow($sql, ['foo']));
    }

    public function testFetchOneRowEmpty(): void
    {
        $sql = 'SELECT * FROM foobars WHERE foo_id = ?';

        $result = self::createMock(Result::class);
        $result->expects(self::once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $c = self::createMock(Connection::class);
        $c->expects(self::once())
            ->method('executeQuery')
            ->with($sql, ['foo'])
            ->willReturn($result);

        $db = new Database($c);
        self::assertNull($db->fetchOneRow($sql, ['foo']));
    }

    public function testGenerateSequentialId(): void
    {
        // Note: this test is very brittle, but for now we just want to match
        // the previous implementation.
        $c = self::createMock(Connection::class);
        $c->expects(self::once())
            ->method('executeStatement')
            ->with('UPDATE foo_bars SET id=LAST_INSERT_ID(id+1)');

        $c->expects(self::once())
            ->method('lastInsertId')
            ->willReturn(42);

        $db = new Database($c);
        self::assertSame(42, $db->generateSequentialId('foo_bars'));
    }
}
