<?php

declare(strict_types=1);

namespace OpenEMR\BC;

use Doctrine\DBAL\{Connection, Result, Statement};
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

        $statement = self::createMock(Statement::class);
        $statement->method('bindValue')
            ->with(1, 'foo');

        $statement->expects(self::once())
            ->method('executeQuery')
            ->willReturn($result);

        $c = self::createMock(Connection::class);

        $c->expects(self::once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statement);

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

        $statement = self::createMock(Statement::class);
        $statement->method('bindValue')
            ->with(1, 'foo');

        $statement->expects(self::once())
            ->method('executeQuery')
            ->willReturn($result);

        $c = self::createMock(Connection::class);
        $c->expects(self::once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statement);

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
