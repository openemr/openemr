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
 * Employer data table
 */
final class Version20260000010007 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create employer_data table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('employer_data');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
            'comment' => 'UUID for this employer record, for data exchange purposes',
        ]);
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('street', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('street_line_2', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('city', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('state', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('country', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('start_date', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Employment start date for patient',
        ]);
        $table->addColumn('end_date', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Employment end date for patient',
        ]);
        $table->addColumn('occupation', Types::TEXT, ['notnull' => false, 'comment' => 'Employment Occupation fk to list_options.option_id where list_id=OccupationODH']);
        $table->addColumn('industry', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'Employment Industry fk to list_options.option_id where list_id=IndustryODH']);
        $table->addColumn('created_by', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to users.id for the user that entered in the employer data',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'pid');
        $table->addUniqueIndex(['uuid'], 'uuid_unique');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE employer_data');
    }
}
