<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

interface TestableCreateTableMigration
{
    public function runCreateTable(Table $table): void;

    /**
     * @param non-empty-string $column
     * @param non-empty-string ...$otherColumns
     */
    public function runAddPrimaryKey(Table $table, string $column, string ...$otherColumns): Table;

    /**
     * @return list<\Doctrine\Migrations\Query\Query>
     */
    public function getSql(): array;
}

#[Group('isolated')]
#[Group('core')]
#[Group('migrations')]
class CreateTableTraitTest extends TestCase
{
    public function testCreateTableSetsCharsetAndCollationAndAddsSql(): void
    {
        $table = $this->table('id');

        $expectedSql = [
            'CREATE TABLE test_table (id INT NOT NULL)',
            'ALTER TABLE test_table ADD PRIMARY KEY (id)',
        ];

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->expects(self::once())
            ->method('getCreateTableSQL')
            ->with(self::callback(function (Table $t) {
                self::assertSame('utf8mb4', $t->getOption('charset'));
                self::assertSame('utf8mb4_general_ci', $t->getOption('collation'));
                return true;
            }))
            ->willReturn($expectedSql);

        $connection = self::createStub(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);

        $migration = $this->createMigration($connection);
        $migration->runCreateTable($table);

        self::assertSame($expectedSql, array_map(
            static fn($q) => $q->getStatement(),
            $migration->getSql(),
        ));
    }

    public function testCreateTableKeepsTheTableOptionsItWasGiven(): void
    {
        $table = $this->table('id')->edit()
            ->setOptions(['engine' => 'InnoDB', 'charset' => 'latin1'])
            ->setComment('a table with options')
            ->create();

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->expects(self::once())
            ->method('getCreateTableSQL')
            ->with(self::callback(function (Table $t) {
                self::assertSame('InnoDB', $t->getOption('engine'));
                self::assertSame('a table with options', $t->getComment());
                self::assertSame('utf8mb4', $t->getOption('charset'));
                self::assertSame('utf8mb4_general_ci', $t->getOption('collation'));
                return true;
            }))
            ->willReturn([]);

        $connection = self::createStub(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);

        $this->createMigration($connection)->runCreateTable($table);

        self::assertSame('latin1', $table->getOption('charset'), 'the table given is not changed');
    }

    public function testAddPrimaryKeyAddsSingleColumnPrimaryKey(): void
    {
        $table = $this->table('id');

        $migration = $this->createMigration(self::createStub(Connection::class));
        $keyed = $migration->runAddPrimaryKey($table, 'id');

        $pk = $keyed->getPrimaryKeyConstraint();
        self::assertNotNull($pk);
        self::assertSame(['id'], $this->extractColumnNames($pk->getColumnNames()));
        self::assertNull($table->getPrimaryKeyConstraint(), 'the table given is not changed');
    }

    public function testAddPrimaryKeyAddsCompositeKey(): void
    {
        $table = $this->table('user_id', 'group_id');

        $migration = $this->createMigration(self::createStub(Connection::class));
        $keyed = $migration->runAddPrimaryKey($table, 'user_id', 'group_id');

        $pk = $keyed->getPrimaryKeyConstraint();
        self::assertNotNull($pk);
        self::assertSame(['user_id', 'group_id'], $this->extractColumnNames($pk->getColumnNames()));
    }

    /**
     * A test_table with the given integer columns.
     *
     * @param non-empty-string $column
     * @param non-empty-string ...$otherColumns
     */
    private function table(string $column, string ...$otherColumns): Table
    {
        $editor = Table::editor()->setUnquotedName('test_table');
        foreach ([$column, ...$otherColumns] as $name) {
            $editor->addColumn(
                Column::editor()
                    ->setUnquotedName($name)
                    ->setTypeName(Types::INTEGER)
                    ->create()
            );
        }
        return $editor->create();
    }

    private function createMigration(Connection $connection): TestableCreateTableMigration
    {
        return new class ($connection, new NullLogger()) extends AbstractMigration implements TestableCreateTableMigration {
            use CreateTableTrait;

            public function up(Schema $schema): void
            {
            }

            public function runCreateTable(Table $table): void
            {
                $this->createTable($table);
            }

            /**
             * @param non-empty-string $column
             * @param non-empty-string ...$otherColumns
             */
            public function runAddPrimaryKey(Table $table, string $column, string ...$otherColumns): Table
            {
                return $this->addPrimaryKey($table, $column, ...$otherColumns);
            }
        };
    }

    /**
     * @param list<UnqualifiedName> $columnNames
     * @return list<string>
     */
    private function extractColumnNames(array $columnNames): array
    {
        return array_map(
            static fn(UnqualifiedName $name): string => $name->toString(),
            $columnNames,
        );
    }
}
