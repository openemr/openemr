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
 * Prescriptions table
 */
final class Version20260000010060 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create prescriptions table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('prescriptions');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('patient_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('filled_by_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('pharmacy_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_added', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Datetime the prescriptions was initially created',
        ]);
        $table->addColumn('date_modified', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Datetime the prescriptions was last modified',
        ]);
        $table->addColumn('provider_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('start_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('drug', Types::STRING, [
            'length' => 150,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('drug_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('rxnorm_drugcode', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('form', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('dosage', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('quantity', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('size', Types::STRING, [
            'length' => 25,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('unit', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('route', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'Max size 100 characters is same max as immunizations',
        ]);
        $table->addColumn('interval', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('substitute', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('refills', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('per_refill', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('filled_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('medication', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('note', Types::TEXT, ['length' => 65535]);
        $table->addColumn('active', Types::INTEGER, ['default' => 1]);
        $table->addColumn('datetime', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('user', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('site', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('prescriptionguid', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('erx_source', Types::SMALLINT, ['default' => 0, 'comment' => '0-OpenEMR 1-External']);
        $table->addColumn('erx_uploaded', Types::SMALLINT, ['default' => 0, 'comment' => '0-Pending NewCrop upload 1-Uploaded to NewCrop']);
        $table->addColumn('drug_info_erx', Types::TEXT, ['length' => 65535]);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('end_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('indication', Types::TEXT, ['length' => 65535]);
        $table->addColumn('prn', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ntx', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('rtx', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('txDate', Types::DATE_MUTABLE);
        $table->addColumn('usage_category', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'comment' => 'option_id in list_options.list_id=medication-usage-category',
        ]);
        $table->addColumn('usage_category_title', Types::STRING, ['length' => 255, 'comment' => 'title in list_options.list_id=medication-usage-category']);
        $table->addColumn('request_intent', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'comment' => 'option_id in list_options.list_id=medication-request-intent',
        ]);
        $table->addColumn('request_intent_title', Types::STRING, ['length' => 255, 'comment' => 'title in list_options.list_id=medication-request-intent']);
        $table->addColumn('drug_dosage_instructions', Types::TEXT, ['comment' => 'Medication dosage instructions']);
        $table->addColumn('diagnosis', Types::TEXT, ['length' => 65535, 'comment' => 'Diagnosis or reason for the prescription']);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id the user that first created this record',
        ]);
        $table->addColumn('updated_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id the user that last modified this record',
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
        $this->addSql('DROP TABLE prescriptions');
    }
}
