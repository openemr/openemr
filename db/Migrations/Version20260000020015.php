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
 * Clinical rules table
 */
final class Version20260000020015 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create clinical_rules table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('clinical_rules');
        $table->addColumn('id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Unique and maps to list_options list clinical_rules',
        ]);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0, 'comment' => '0 is default for all patients, while > 0 is id from patient_data table']);
        $table->addColumn('active_alert_flag', Types::SMALLINT, ['comment' => 'Active Alert Widget Module flag - note not yet utilized']);
        $table->addColumn('passive_alert_flag', Types::SMALLINT, ['comment' => 'Passive Alert Widget Module flag']);
        $table->addColumn('cqm_flag', Types::SMALLINT, ['comment' => 'Clinical Quality Measure flag (unable to customize per patient)']);
        $table->addColumn('cqm_2011_flag', Types::SMALLINT, ['comment' => '2011 Clinical Quality Measure flag (unable to customize per patient)']);
        $table->addColumn('cqm_2014_flag', Types::SMALLINT, ['comment' => '2014 Clinical Quality Measure flag (unable to customize per patient)']);
        $table->addColumn('cqm_nqf_code', Types::STRING, [
            'length' => 10,
            'default' => '',
            'comment' => 'Clinical Quality Measure NQF identifier',
        ]);
        $table->addColumn('cqm_pqri_code', Types::STRING, [
            'length' => 10,
            'default' => '',
            'comment' => 'Clinical Quality Measure PQRI identifier',
        ]);
        $table->addColumn('amc_flag', Types::SMALLINT, ['comment' => 'Automated Measure Calculation flag (unable to customize per patient)']);
        $table->addColumn('amc_2011_flag', Types::SMALLINT, ['comment' => '2011 Automated Measure Calculation flag for (unable to customize per patient)']);
        $table->addColumn('amc_2014_flag', Types::SMALLINT, ['comment' => '2014 Automated Measure Calculation flag for (unable to customize per patient)']);
        $table->addColumn('amc_2015_flag', Types::SMALLINT, [
            'notnull' => false,
            'default' => null,
            'comment' => '2015 Automated Measure Calculation flag for (unable to customize per patient)',
        ]);
        $table->addColumn('amc_code', Types::STRING, [
            'length' => 10,
            'default' => '',
            'comment' => 'Automated Measure Calculation identifier (MU rule)',
        ]);
        $table->addColumn('amc_code_2014', Types::STRING, [
            'length' => 30,
            'default' => '',
            'comment' => 'Automated Measure Calculation 2014 identifier (MU rule)',
        ]);
        $table->addColumn('amc_code_2015', Types::STRING, [
            'length' => 30,
            'default' => '',
            'comment' => 'Automated Measure Calculation 2014 identifier (MU rule)',
        ]);
        $table->addColumn('amc_2014_stage1_flag', Types::SMALLINT, ['comment' => '2014 Stage 1 - Automated Measure Calculation flag for (unable to customize per patient)']);
        $table->addColumn('amc_2014_stage2_flag', Types::SMALLINT, ['comment' => '2014 Stage 2 - Automated Measure Calculation flag for (unable to customize per patient)']);
        $table->addColumn('patient_reminder_flag', Types::SMALLINT, ['comment' => 'Clinical Reminder Module flag']);
        $table->addColumn('bibliographic_citation', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('developer', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Clinical Rule Developer',
        ]);
        $table->addColumn('funding_source', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Clinical Rule Funding Source',
        ]);
        $table->addColumn('release_version', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Clinical Rule Release Version',
        ]);
        $table->addColumn('web_reference', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Clinical Rule Web Reference',
        ]);
        $table->addColumn('linked_referential_cds', Types::STRING, ['length' => 50, 'default' => '']);
        $table->addColumn('access_control', Types::STRING, [
            'length' => 255,
            'default' => 'patients:med',
            'comment' => 'ACO link for access control',
        ]);
        $table->addColumn('patient_dob_usage', Types::TEXT, ['comment' => 'Description of how patient DOB is used by this rule']);
        $table->addColumn('patient_ethnicity_usage', Types::TEXT, ['comment' => 'Description of how patient ethnicity is used by this rule']);
        $table->addColumn('patient_health_status_usage', Types::TEXT, ['comment' => 'Description of how patient health status assessments are used by this rule']);
        $table->addColumn('patient_gender_identity_usage', Types::TEXT, ['comment' => 'Description of how patient gender identity information is used by this rule']);
        $table->addColumn('patient_language_usage', Types::TEXT, ['comment' => 'Description of how patient language information is used by this rule']);
        $table->addColumn('patient_race_usage', Types::TEXT, ['comment' => 'Description of how patient race information is used by this rule']);
        $table->addColumn('patient_sex_usage', Types::TEXT, ['comment' => 'Description of how patient birth sex information is used by this rule']);
        $table->addColumn('patient_sexual_orientation_usage', Types::TEXT, ['comment' => 'Description of how patient sexual orientation is used by this rule']);
        $table->addColumn('patient_sodh_usage', Types::TEXT, ['comment' => 'Description of how patient social determinants of health are used by this rule']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id', 'pid')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE clinical_rules');
    }
}
