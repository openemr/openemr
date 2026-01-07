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
        $c = self::createMock(Connection::class);
        $d = new Database($c);

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


        $c->expects(self::once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statement);

        self::assertSame($output, $d->fetchOneRow($sql, ['foo']));
    }

    public function testFetchOneRowEmpty(): void
    {
        $c = self::createMock(Connection::class);
        $d = new Database($c);

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


        $c->expects(self::once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statement);

        self::assertNull($d->fetchOneRow($sql, ['foo']));
    }
}
