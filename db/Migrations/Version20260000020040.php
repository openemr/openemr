<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Standardized tables track table
 */
final class Version20260000020040 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create standardized_tables_track table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('standardized_tables_track');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('imported_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'name of standardized tables such as RXNORM',
        ]);
        $table->addColumn('revision_version', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'revision of standardized tables that were imported',
        ]);
        $table->addColumn('revision_date', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'revision of standardized tables that were imported',
        ]);
        $table->addColumn('file_checksum', Types::STRING, ['length' => 32, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE standardized_tables_track');
    }
}
