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
 * Procedure specimen table
 */
final class Version20260000020129 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create procedure_specimen table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('procedure_specimen');
        $table->addColumn('procedure_specimen_id', Types::BIGINT, ['autoincrement' => true, 'comment' => 'record id']);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
            'comment' => 'FHIR Specimen id',
        ]);
        $table->addColumn('procedure_order_id', Types::BIGINT, ['comment' => 'links to procedure_order.procedure_order_id']);
        $table->addColumn('procedure_order_seq', Types::INTEGER, ['comment' => 'links to procedure_order_code.procedure_order_seq (per test line)']);
        $table->addColumn('specimen_identifier', Types::STRING, [
            'length' => 128,
            'notnull' => false,
            'default' => null,
            'comment' => 'tube/barcode/internal id',
        ]);
        $table->addColumn('accession_identifier', Types::STRING, [
            'length' => 128,
            'notnull' => false,
            'default' => null,
            'comment' => 'lab accession number',
        ]);
        $table->addColumn('specimen_type_code', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
            'comment' => 'prefer SNOMED CT code',
        ]);
        $table->addColumn('specimen_type', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'display/text',
        ]);
        $table->addColumn('collection_method_code', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('collection_method', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('specimen_location_code', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('specimen_location', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('collected_date', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'single instant',
        ]);
        $table->addColumn('collection_date_low', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'period start',
        ]);
        $table->addColumn('collection_date_high', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'period end',
        ]);
        $table->addColumn('volume_value', Types::DECIMAL, [
            'precision' => 10,
            'scale' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('volume_unit', Types::STRING, ['notnull' => false, 'length' => 32, 'default' => 'mL']);
        $table->addColumn('condition_code', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
            'comment' => 'HL7 v2 0493 (e.g., ACT, HEM)',
        ]);
        $table->addColumn('specimen_condition', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('comments', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('created_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('updated_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('deleted', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('procedure_specimen_id')
                ->create()
        );
        $table->addIndex(['procedure_order_id', 'procedure_order_seq'], 'idx_order_line');
        $table->addIndex(['specimen_identifier'], 'idx_identifier');
        $table->addIndex(['accession_identifier'], 'idx_accession');
        $table->addUniqueIndex(['uuid'], 'uuid_unique');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procedure_specimen');
    }
}
