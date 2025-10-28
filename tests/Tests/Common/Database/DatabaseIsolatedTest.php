<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Common\Database;

use OpenEMR\Common\Database\Database;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('isolated')]
#[Group('db')]
#[CoversClass(Database::class)]
#[CoversMethod(Database::class, 'insert')]
#[CoversMethod(Database::class, 'count')]
#[CoversMethod(Database::class, 'countBy')]
#[CoversMethod(Database::class, 'find')]
#[CoversMethod(Database::class, 'findOneBy')]
#[CoversMethod(Database::class, 'findAll')]
#[CoversMethod(Database::class, 'removeBy')]
#[CoversMethod(Database::class, 'escapeIdentifier')]
final class DatabaseIsolatedTest extends TestCase
{
    private function getDatabaseMockBuilder()
    {
        return $this->getMockBuilder(Database::class)
            ->setConstructorArgs([
                $this->createMock(\ADOConnection::class),
                1
            ])
        ;
    }

    #[Test]
    #[DataProvider('insertDataProvider')]
    public function insertTest(
        string $tablename,
        array $data,
        string $expectedStatement,
        array $expectedBinds,
    ): void {
        $object = $this->getDatabaseMockBuilder()
            ->onlyMethods(['getLastInsertId'])
            ->getMock();

        $object->expects($this->once())
            ->method('getLastInsertId')
            ->with($expectedStatement, $expectedBinds)
            ;

        $object->insert($tablename, $data);
    }

    public static function insertDataProvider(): iterable
    {
        yield [
            'users',
            ['username' => 'igormukhin'],
            'INSERT INTO `users` SET `username` = ?',
            ['igormukhin']
        ];

        yield [
            'users',
            ['username' => 'igormukhin', 'authorized' => 1],
            'INSERT INTO `users` SET `username` = ?, `authorized` = ?',
            ['igormukhin', 1]
        ];
    }

    #[Test]
    #[DataProvider('countDataProvider')]
    public function countTest(
        string $tablename,
        string $expectedStatement,
    ): void {
        $object = $this->getDatabaseMockBuilder()
            ->onlyMethods(['getSingleScalarResult'])
            ->getMock();

        $object->expects($this->once())
            ->method('getSingleScalarResult')
            ->with($expectedStatement)
        ;

        $object->count($tablename);
    }

    public static function countDataProvider(): iterable
    {
        yield [
            'users',
            'SELECT COUNT(*) AS cnt FROM `users`',
        ];
    }

    #[Test]
    #[DataProvider('countByDataProvider')]
    public function countByTest(
        string $tablename,
        array $data,
        string $expectedStatement,
        array $expectedBinds,
    ): void {
        $object = $this->getDatabaseMockBuilder()
            ->onlyMethods(['getSingleScalarResult'])
            ->getMock();

        $object->expects($this->once())
            ->method('getSingleScalarResult')
            ->with($expectedStatement, $expectedBinds)
        ;

        $object->countBy($tablename, $data);
    }

    public static function countByDataProvider(): iterable
    {
        yield [
            'users',
            ['username' => 'igormukhin'],
            'SELECT COUNT(*) AS cnt FROM `users` WHERE `username` = ?',
            ['igormukhin']
        ];

        yield [
            'users',
            ['username' => 'igormukhin', 'authorized' => 1],
            'SELECT COUNT(*) AS cnt FROM `users` WHERE `username` = ? AND `authorized` = ?',
            ['igormukhin', 1]
        ];
    }

    #[Test]
    #[DataProvider('findByDataProvider')]
    public function findByTest(
        string $tablename,
        array $data,
        string $expectedStatement,
        array $expectedBinds,
    ): void {
        $object = $this->getDatabaseMockBuilder()
            ->onlyMethods(['getResult'])
            ->getMock();

        $object->expects($this->once())
            ->method('getResult')
            ->with($expectedStatement, $expectedBinds)
        ;

        $object->findBy($tablename, $data);
    }

    public static function findByDataProvider(): iterable
    {
        yield [
            'users',
            ['username' => 'igormukhin'],
            'SELECT * FROM `users` WHERE `username` = ?',
            ['igormukhin']
        ];

        yield [
            'users',
            ['username' => 'igormukhin', 'authorized' => 1],
            'SELECT * FROM `users` WHERE `username` = ? AND `authorized` = ?',
            ['igormukhin', 1]
        ];
    }

    #[Test]
    #[DataProvider('findOneByDataProvider')]
    public function findOneByTest(
        string $tablename,
        array $data,
        string $expectedStatement,
        array $expectedBinds,
    ): void {
        $object = $this->getDatabaseMockBuilder()
            ->onlyMethods(['getOneOrNullResult'])
            ->getMock();

        $object->expects($this->once())
            ->method('getOneOrNullResult')
            ->with($expectedStatement, $expectedBinds)
        ;

        $object->findOneBy($tablename, $data);
    }

    public static function findOneByDataProvider(): iterable
    {
        yield [
            'users',
            ['username' => 'igormukhin'],
            'SELECT * FROM `users` WHERE `username` = ?',
            ['igormukhin']
        ];

        yield [
            'users',
            ['username' => 'igormukhin', 'authorized' => 1],
            'SELECT * FROM `users` WHERE `username` = ? AND `authorized` = ?',
            ['igormukhin', 1]
        ];
    }

    #[Test]
    #[DataProvider('findAllDataProvider')]
    public function findAll(
        string $tablename,
        string $expectedStatement,
    ): void {
        $object = $this->getDatabaseMockBuilder()
            ->onlyMethods(['getResult'])
            ->getMock();

        $object->expects($this->once())
            ->method('getResult')
            ->with($expectedStatement)
        ;

        $object->findAll($tablename);
    }

    public static function findAllDataProvider(): iterable
    {
        yield [
            'users',
            'SELECT * FROM `users`',
        ];
    }

    #[Test]
    #[DataProvider('removeByDataProvider')]
    public function removeByTest(
        string $tablename,
        array $data,
        string $expectedStatement,
        array $expectedBinds,
    ): void {
        $object = $this->getDatabaseMockBuilder()
            ->onlyMethods(['getAffectedRows'])
            ->getMock();

        $object->expects($this->once())
            ->method('getAffectedRows')
            ->with($expectedStatement, $expectedBinds)
        ;

        $object->removeBy($tablename, $data);
    }

    public static function removeByDataProvider(): iterable
    {
        yield [
            'users',
            ['username' => 'igormukhin'],
            'DELETE FROM `users` WHERE `username` = ?',
            ['igormukhin']
        ];

        yield [
            'users',
            ['username' => 'igormukhin', 'authorized' => 1],
            'DELETE FROM `users` WHERE `username` = ? AND `authorized` = ?',
            ['igormukhin', 1]
        ];
    }

    #[Test]
    #[DataProvider('escapeIdentifierDataProvider')]
    public function escapeIdentifierTest(
        string $identifier,
        ?string $expectedExceptionMessage = null
    ): void {
        if (null !== $expectedExceptionMessage) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $object = $this->createMock(Database::class);
        $class = new \ReflectionClass($object);
        $method = $class->getMethod('escapeIdentifier');

        $this->assertEquals(
            $identifier,
            $method->invokeArgs($object, [$identifier])
        );
    }

    public static function escapeIdentifierDataProvider(): iterable
    {
        // Empty
        yield 'Empty' => ['', 'Only A-Za-z0-9_ allowed for identifiers (table or column names). Got: '];

        // Valid
        yield 'Lower case' => ['table'];
        yield 'Upper and lower case' => ['Table'];
        yield 'Upper and lower case with underscore' => ['Column_Name'];
        yield 'Upper and lower case with underscore and number' => ['Column_Name_1'];

        // Invalid - expecting exceptions
        yield 'Space' => ["Column Name", 'Only A-Za-z0-9_ allowed for identifiers (table or column names). Got: Column Name'];

        yield 'Potential SQL Injection - Single quote' => ["'", "Only A-Za-z0-9_ allowed for identifiers (table or column names). Got: '"];
        yield 'Potential SQL Injection - Double quote' => ['"', 'Only A-Za-z0-9_ allowed for identifiers (table or column names). Got: "'];
        yield 'Potential SQL Injection - Backtick' => ['`', 'Only A-Za-z0-9_ allowed for identifiers (table or column names). Got: `'];
    }
}
