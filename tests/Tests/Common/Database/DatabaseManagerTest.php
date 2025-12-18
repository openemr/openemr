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

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Exception\DatabaseResultException;
use OpenEMR\Common\Database\Exception\NonUniqueDatabaseResultException;
use OpenEMR\Common\Database\Exception\NoResultDatabaseResultException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('db')]
#[CoversClass(DatabaseManager::class)]
#[CoversMethod(DatabaseManager::class, 'insert')]
#[CoversMethod(DatabaseManager::class, 'count')]
#[CoversMethod(DatabaseManager::class, 'countBy')]
#[CoversMethod(DatabaseManager::class, 'find')]
#[CoversMethod(DatabaseManager::class, 'findOneBy')]
#[CoversMethod(DatabaseManager::class, 'findAll')]
#[CoversMethod(DatabaseManager::class, 'findBy')]
#[CoversMethod(DatabaseManager::class, 'removeBy')]
#[CoversMethod(DatabaseManager::class, 'getResult')]
#[CoversMethod(DatabaseManager::class, 'getSingleColumnResult')]
#[CoversMethod(DatabaseManager::class, 'getOneOrNullResult')]
#[CoversMethod(DatabaseManager::class, 'getSingleScalarResult')]
final class DatabaseManagerTest extends TestCase
{
    private readonly DatabaseManager $database;

    protected function setUp(): void
    {
        $this->database = DatabaseManager::getInstance();
    }

    protected function tearDown(): void
    {
        foreach (['igormukhin', 'john', 'johndoe'] as $username) {
            $this->database->removeBy('users', ['username' => $username]);
        }
    }

    #[Test]
    public function insertTest(): void
    {
        $data = ['username' => 'igormukhin', 'fname' => 'Igor', 'lname' => 'Mukhin'];

        $id = $this->database->insert('users', $data);
        $user = $this->database->find('users', $id);
        foreach ($data as $fieldName => $fieldValue) {
            $this->assertEquals($fieldValue, $user[$fieldName]);
        }
    }

    #[Test]
    public function countByTest(): void
    {
        $data = ['username' => 'igormukhin', 'fname' => 'Igor', 'lname' => 'Mukhin'];

        $this->assertEquals(0, $this->database->countBy('users', $data));
        $this->database->insert('users', $data);
        $this->assertEquals(1, $this->database->countBy('users', $data));
    }

    #[Test]
    public function findTest(): void
    {
        $this->assertNull(
            $this->database->find('users', 0)
        );

        $data = ['username' => 'igormukhin', 'fname' => 'Igor', 'lname' => 'Mukhin'];

        $id = $this->database->insert('users', $data);
        $user = $this->database->find('users', $id);
        $this->assertNotNull($user);
        foreach ($data as $fieldName => $fieldValue) {
            $this->assertEquals($fieldValue, $user[$fieldName]);
        }
    }

    #[Test]
    public function findOneBySucceededTest(): void
    {
        $this->assertNull(
            $this->database->findOneBy('users', ['fname' => 'Igor'])
        );

        $this->database->insert('users', ['username' => 'johndoe', 'fname' => 'John', 'lname' => 'Doe']);
        $this->database->insert('users', ['username' => 'igormukhin', 'fname' => 'Igor', 'lname' => 'Mukhin']);
        $this->database->insert('users', ['username' => 'john', 'fname' => 'John']);

        $user = $this->database->findOneBy('users', ['fname' => 'Igor']);
        $this->assertNotNull($user);
        $this->assertEquals('Igor', $user['fname']);
    }

    #[Test]
    public function findOneByFailedAsNotUniqueTest(): void
    {
        $this->database->insert('users', ['username' => 'johndoe', 'fname' => 'John', 'lname' => 'Doe']);
        $this->database->insert('users', ['username' => 'igormukhin', 'fname' => 'Igor', 'lname' => 'Mukhin']);
        $this->database->insert('users', ['username' => 'john', 'fname' => 'John']);

        $this->expectException(NonUniqueDatabaseResultException::class);
        $this->expectExceptionMessage('Unexpected non-unique result');

        $this->database->findOneBy('users', ['fname' => 'John']);
    }

    #[Test]
    public function findByTest(): void
    {
        $this->assertEmpty(
            $this->database->findBy('users', ['fname' => 'John'])
        );

        $this->database->insert('users', ['username' => 'johndoe', 'fname' => 'John', 'lname' => 'Doe']);
        $this->database->insert('users', ['username' => 'igormukhin', 'fname' => 'Igor', 'lname' => 'Mukhin']);
        $this->database->insert('users', ['username' => 'john', 'fname' => 'John']);

        $users = $this->database->findBy('users', ['fname' => 'John']);
        $this->assertCount(2, $users);
        foreach ($users as $user) {
            $this->assertEquals('John', $user['fname']);
        }
    }

    #[Test]
    public function removeByTest(): void
    {
        $this->database->insert('users', ['username' => 'johndoe', 'fname' => 'John', 'lname' => 'Doe']);
        $this->database->insert('users', ['username' => 'igormukhin', 'fname' => 'Igor', 'lname' => 'Mukhin']);
        $this->database->insert('users', ['username' => 'john', 'fname' => 'John']);

        $this->database->removeBy('users', ['fname' => 'John']);

        $this->assertEquals(0, $this->database->countBy('users', ['fname' => 'John']));
        $this->assertEquals(1, $this->database->countBy('users', ['fname' => 'Igor']));
    }

    #[Test]
    public function removeTest(): void
    {
        $this->database->insert('users', ['username' => 'johndoe', 'fname' => 'John', 'lname' => 'Doe']);
        $id = $this->database->insert('users', ['username' => 'igormukhin', 'fname' => 'Igor', 'lname' => 'Mukhin']);
        $this->database->insert('users', ['username' => 'john', 'fname' => 'John']);

        $this->database->remove('users', $id);

        $this->assertEquals(0, $this->database->countBy('users', ['fname' => 'Igor']));
        $this->assertEquals(2, $this->database->countBy('users', ['fname' => 'John']));
    }

    #[Test]
    #[DataProvider('getResultDataProvider')]
    public function getResultTest(string $statement, array $binds, array $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            $this->database->getResult($statement, $binds)
        );
    }

    public static function getResultDataProvider(): iterable
    {
        yield 'Empty result' => [
            "SELECT NULL WHERE FALSE",
            [],
            [],
        ];

        yield 'Single result - hardcoded' => [
            "select 'value1' as `field1`, 'value2' as `field2`",
            [],
            [['field1' => 'value1', 'field2' => 'value2']],
        ];

        yield 'Single result - binded' => [
            "select ? as `field1`, ? as `field2`",
            ['value1', 'value2'],
            [['field1' => 'value1', 'field2' => 'value2']],
        ];

        yield 'Multiple results - hardcoded' => [
            "select 'value1' as `field1`, 'value2' as `field2` union select 'value3' as `field1`, 'value4' as `field2`",
            [],
            [
                ['field1' => 'value1', 'field2' => 'value2'],
                ['field1' => 'value3', 'field2' => 'value4'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('getSingleColumnResultSucceededDataProvider')]
    public function getSingleColumnResultSucceededTest(string $statement, array $binds, array $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            $this->database->getSingleColumnResult($statement, $binds)
        );
    }

    public static function getSingleColumnResultSucceededDataProvider(): iterable
    {
        yield 'Empty result' => [
            "SELECT NULL WHERE FALSE",
            [],
            [],
        ];

        yield 'Single result' => [
            "select 'value1' as `field`",
            [],
            [
                'value1',
            ],
        ];

        yield 'Multiple results' => [
            "select 'value1' as `field` union select 'value2' as `field`",
            [],
            [
                'value1',
                'value2',
            ],
        ];
    }

    #[Test]
    #[DataProvider('getSingleColumnResultFailedDataProvider')]
    public function getSingleColumnResultFailedTest(
        string $statement,
        array $binds,
        string $expectedException,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->database->getSingleColumnResult($statement, $binds);
    }

    public static function getSingleColumnResultFailedDataProvider(): iterable
    {
        yield 'More than one columns' => [
            "select 'value1' as `field1`, 'value2' as `field2`",
            [],
            DatabaseResultException::class,
            'Expected exactly 1 column at result, got 2'
        ];
    }

    #[Test]
    #[DataProvider('getOneOrNullResultSucceededDataProvider')]
    public function getOneOrNullResultSucceededTest(string $statement, array $binds, null|array $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            $this->database->getOneOrNullResult($statement, $binds)
        );
    }

    public static function getOneOrNullResultSucceededDataProvider(): iterable
    {
        yield 'Empty result - returns null' => [
            "SELECT NULL WHERE FALSE",
            [],
            null,
        ];

        yield 'Single result' => [
            "select 'value1' as `field`",
            [],
            [
                'field' => 'value1',
            ],
        ];
    }

    #[Test]
    #[DataProvider('getOneOrNullResultFailedDataProvider')]
    public function getOneOrNullResultFailedTest(
        string $statement,
        array $binds,
        string $expectedException,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->database->getOneOrNullResult($statement, $binds);
    }

    public static function getOneOrNullResultFailedDataProvider(): iterable
    {
        yield 'More than one results' => [
            "select 'value1' as `field` union select 'value2' as `field`",
            [],
            NonUniqueDatabaseResultException::class,
            'Unexpected non-unique result'
        ];
    }

    #[Test]
    #[DataProvider('getSingleScalarResultSucceededDataProvider')]
    public function getSingleScalarResultSucceededTest(string $statement, array $binds, string|int $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            $this->database->getSingleScalarResult($statement, $binds)
        );
    }

    public static function getSingleScalarResultSucceededDataProvider(): iterable
    {
        yield 'Single result with single column - string' => [
            "select 'value' as `field`",
            [],
            'value',
        ];

        yield 'Single result with single column - integer' => [
            "select 1 as `field`",
            [],
            1,
        ];
    }

    #[Test]
    #[DataProvider('getSingleScalarResultFailedDataProvider')]
    public function getSingleScalarResultFailedTest(
        string $statement,
        array $binds,
        string $expectedException,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->database->getSingleScalarResult($statement, $binds);
    }

    public static function getSingleScalarResultFailedDataProvider(): iterable
    {
        yield 'Empty result' => [
            "SELECT NULL WHERE FALSE",
            [],
            NoResultDatabaseResultException::class,
            'No result'
        ];

        yield 'More than one results' => [
            "select 'value1' as `field` union select 'value2' as `field`",
            [],
            NonUniqueDatabaseResultException::class,
            'Unexpected non-unique result'
        ];

        yield 'More than one columns' => [
            "select 'value1' as `field1`, 'value2' as `field2`",
            [],
            DatabaseResultException::class,
            'Expected exactly 1 column at result, got 2'
        ];
    }
}
