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
 * Immunizations table
 */
final class Version20260000020057 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create immunizations table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('immunizations');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('patient_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('administered_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('immunization_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('cvx_code', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('manufacturer', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lot_number', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('administered_by_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('administered_by', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'Alternative to administered_by_id',
        ]);
        $table->addColumn('education_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('vis_date', Types::DATE_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Date of VIS Statement',
        ]);
        $table->addColumn('note', Types::TEXT);
        $table->addColumn('create_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('update_date', Types::DATETIME_MUTABLE);
        $table->addColumn('created_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('updated_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('amount_administered', Types::FLOAT, ['notnull' => false, 'default' => null]);
        $table->addColumn('amount_administered_unit', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('expiration_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('route', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('administration_site', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('added_erroneously', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('completion_status', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('information_source', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('refusal_reason', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ordering_provider', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('reason_code', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
            'comment' => 'Medical code explaining reason of the vital observation value in form codesystem:codetype;...;',
        ]);
        $table->addColumn('reason_description', Types::TEXT, ['comment' => 'Human readable text description of the reason_code column']);
        $table->addColumn('encounter_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to form_encounter.encounter to link immunization to encounter record',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['patient_id'], 'patient_id');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE immunizations');
    }
}
