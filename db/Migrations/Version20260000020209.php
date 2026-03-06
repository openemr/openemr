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
 * Form history sdoh table
 */
final class Version20260000020209 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_history_sdoh table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_history_sdoh');
        $table->addColumn('id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true,
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pid', Types::INTEGER, ['unsigned' => true]);
        $table->addColumn('encounter', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'comment' => 'Pregnancy Intent Over Next Year (codes from PregnancyIntent list)']);
        $table->addColumn('created_by', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('updated_by', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('assessment_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('screening_tool', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('assessor', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('food_insecurity', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('food_insecurity_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('housing_instability', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('housing_instability_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('transportation_insecurity', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('transportation_insecurity_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('utilities_insecurity', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('utilities_insecurity_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('interpersonal_safety', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('interpersonal_safety_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('financial_strain', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('financial_strain_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('social_isolation', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('social_isolation_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('childcare_needs', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('childcare_needs_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('digital_access', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('digital_access_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('employment_status', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('education_level', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('caregiver_status', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('veteran_status', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pregnancy_status', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pregnancy_edd', Types::DATE_MUTABLE, [
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pregnancy_intent', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
            'comment' => 'Pregnancy Intent Over Next Year (codes from PregnancyIntent list)',
        ]);
        $table->addColumn('postpartum_status', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('postpartum_end', Types::DATE_MUTABLE, [
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('goals', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('interventions', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('instrument_score', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('positive_domain_count', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('declined_flag', Types::BOOLEAN, ['notnull' => false, 'default' => null]);
        $table->addColumn('disability_status', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('disability_status_notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('disability_scale', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('hunger_q1', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
            'comment' => 'LOINC 88122-7 response',
        ]);
        $table->addColumn('hunger_q2', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
            'comment' => 'LOINC 88123-5 response',
        ]);
        $table->addColumn('hunger_score', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Calculated HVS score',
        ]);
        $this->addPrimaryKey($table, 'id');
        $table->addIndex(['uuid'], 'uuid_idx');
        $table->addIndex(['pid'], 'pid_idx');
        $table->addIndex(['assessment_date'], 'assessment_idx');
        $table->addIndex(['encounter'], 'encounter_idx');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_history_sdoh');
    }
}
