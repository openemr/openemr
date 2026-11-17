<?php

/**
 * @package   OpeENR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Table;

/**
 * Trait for migrations that create tables.
 *
 * Generates platform-specific SQL without schema introspection overhead.
 *
 * A Table is edited through its editor, which returns a new Table rather
 * than changing the one given (DBAL 4.5 deprecates the mutators).
 */
trait CreateTableTrait
{
    private function createTable(Table $table): void
    {
        $table = $table->edit()
            ->setOptions([
                ...$table->getOptions(),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ])
            ->create();
        $platform = $this->connection->getDatabasePlatform();
        foreach ($platform->getCreateTableSQL($table) as $sql) {
            $this->addSql($sql);
        }
    }

    /**
     * The table with the primary key added; the table given is left as it was.
     *
     * @param non-empty-string $column
     * @param non-empty-string ...$otherColumns
     */
    private function addPrimaryKey(Table $table, string $column, string ...$otherColumns): Table
    {
        return $table->edit()
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()
                    ->setUnquotedColumnNames($column, ...$otherColumns)
                    ->create()
            )
            ->create();
    }
}
