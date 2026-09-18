<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
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
    public function runAddPrimaryKey(Table $table, string $column, string ...$otherColumns): void;

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
        $table = new Table('test_table');
        $table->addColumn('id', 'integer');

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

    public function testAddPrimaryKeyAddsSingleColumnPrimaryKey(): void
    {
        $table = new Table('test_table');
        $table->addColumn('id', 'integer');

        $migration = $this->createMigration(self::createStub(Connection::class));
        $migration->runAddPrimaryKey($table, 'id');

        $pk = $table->getPrimaryKeyConstraint();
        self::assertNotNull($pk);
        self::assertSame(['id'], $this->extractColumnNames($pk->getColumnNames()));
    }

    public function testAddPrimaryKeyAddsCompositeKey(): void
    {
        $table = new Table('test_table');
        $table->addColumn('user_id', 'integer');
        $table->addColumn('group_id', 'integer');

        $migration = $this->createMigration(self::createStub(Connection::class));
        $migration->runAddPrimaryKey($table, 'user_id', 'group_id');

        $pk = $table->getPrimaryKeyConstraint();
        self::assertNotNull($pk);
        self::assertSame(['user_id', 'group_id'], $this->extractColumnNames($pk->getColumnNames()));
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
            public function runAddPrimaryKey(Table $table, string $column, string ...$otherColumns): void
            {
                $this->addPrimaryKey($table, $column, ...$otherColumns);
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
