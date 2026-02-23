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
 * Procedure order table
 */
final class Version20260000020124 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create procedure_order table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('procedure_order');
        $table->addColumn('procedure_order_id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('provider_id', Types::BIGINT, ['default' => 0, 'comment' => 'references users.id, the ordering provider']);
        $table->addColumn('patient_id', Types::BIGINT, ['comment' => 'references patient_data.pid']);
        $table->addColumn('encounter_id', Types::BIGINT, ['default' => 0, 'comment' => 'references form_encounter.encounter']);
        $table->addColumn('date_collected', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'time specimen collected',
        ]);
        $table->addColumn('date_ordered', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('order_priority', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('order_status', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'pending,routed,complete,canceled',
        ]);
        $table->addColumn('patient_instructions', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('activity', Types::SMALLINT, ['default' => 1, 'comment' => '0 if deleted']);
        $table->addColumn('control_id', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'This is the CONTROL ID that is sent back from lab',
        ]);
        $table->addColumn('lab_id', Types::BIGINT, ['default' => 0, 'comment' => 'references procedure_providers.ppid']);
        $table->addColumn('specimen_type', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'from the Specimen_Type list',
        ]);
        $table->addColumn('specimen_location', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'from the Specimen_Location list',
        ]);
        $table->addColumn('specimen_volume', Types::STRING, [
            'length' => 30,
            'default' => '',
            'comment' => 'from a text input field',
        ]);
        $table->addColumn('date_transmitted', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'time of order transmission, null if unsent',
        ]);
        $table->addColumn('clinical_hx', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'clinical history text that may be relevant to the order',
        ]);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('history_order', Types::ENUM, [
            'notnull' => false,
            'default' => '0',
            'values' => ['0', '1'],
            'comment' => 'references order is added for history purpose only.',
        ]);
        $table->addColumn('order_diagnosis', Types::STRING, ['notnull' => false, 
            'length' => 255,
            'default' => '',
            'comment' => 'primary order diagnosis',
        ]);
        $table->addColumn('billing_type', Types::STRING, [
            'length' => 4,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('specimen_fasting', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('order_psc', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('order_abn', Types::STRING, ['length' => 31, 'default' => 'not_required']);
        $table->addColumn('collector_id', Types::BIGINT, ['default' => 0]);
        $table->addColumn('account', Types::STRING, [
            'length' => 60,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('account_facility', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('provider_number', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('procedure_order_type', Types::STRING, ['length' => 32, 'default' => 'laboratory_test']);
        $table->addColumn('scheduled_date', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Scheduled date for service (FHIR occurrence[x])',
        ]);
        $table->addColumn('scheduled_start', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Scheduled start time (FHIR occurrencePeriod.start)',
        ]);
        $table->addColumn('scheduled_end', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Scheduled end time (FHIR occurrencePeriod.end)',
        ]);
        $table->addColumn('performer_type', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
            'comment' => 'Type of performer: laboratory, radiology, pathology (SNOMED CT)',
        ]);
        $table->addColumn('order_intent', Types::STRING, [
            'length' => 31,
            'default' => 'order',
            'comment' => 'FHIR intent: order, plan, directive, proposal',
        ]);
        $table->addColumn('location_id', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'References facility.id for service location (FHIR locationReference)',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('procedure_order_id')
                ->create()
        );
        $table->addIndex(['date_ordered', 'patient_id'], 'datepid');
        $table->addIndex(['patient_id'], 'patient_id');
        $table->addIndex(['specimen_type'], 'idx_specimen_type');
        $table->addIndex(['scheduled_date'], 'idx_scheduled_date');
        $table->addIndex(['order_intent'], 'idx_order_intent');
        $table->addIndex(['location_id'], 'idx_location_id');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procedure_order');
    }
}
