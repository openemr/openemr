<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Form misc billing options table
 */
final class Version20260000010011 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_misc_billing_options table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_misc_billing_options');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('groupname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $this->addBooleanColumn($table, 'authorized', default: null, notnull: false);
        $this->addBooleanColumn($table, 'activity', default: null, notnull: false);
        $this->addBooleanColumn($table, 'employment_related', default: null, notnull: false);
        $this->addBooleanColumn($table, 'auto_accident', default: null, notnull: false);
        $table->addColumn('accident_state', Types::STRING, [
            'length' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $this->addBooleanColumn($table, 'other_accident', default: null, notnull: false);
        $table->addColumn('medicaid_referral_code', Types::STRING, [
            'length' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $this->addBooleanColumn($table, 'epsdt_flag', default: null, notnull: false);
        $table->addColumn('provider_qualifier_code', Types::STRING, [
            'length' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('provider_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $this->addBooleanColumn($table, 'outside_lab', default: null, notnull: false);
        $table->addColumn('lab_amount', Types::DECIMAL, [
            'precision' => 5,
            'scale' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $this->addBooleanColumn($table, 'is_unable_to_work', default: null, notnull: false);
        $table->addColumn('onset_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_initial_treatment', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('off_work_from', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('off_work_to', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $this->addBooleanColumn($table, 'is_hospitalized', default: null, notnull: false);
        $table->addColumn('hospitalization_date_from', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('hospitalization_date_to', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('medicaid_resubmission_code', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('medicaid_original_reference', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('prior_auth_number', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('comments', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $this->addBooleanColumn($table, 'replacement_claim', default: false, notnull: false);
        $table->addColumn('icn_resubmission_number', Types::STRING, [
            'length' => 35,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('box_14_date_qual', Types::STRING, ['fixed' => true, 
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('box_15_date_qual', Types::STRING, ['fixed' => true, 
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('encounter', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $this->addPrimaryKey($table, 'id');
        $table->addUniqueIndex(['encounter'], 'encounter');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_misc_billing_options');
    }
}
