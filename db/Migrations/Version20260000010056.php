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
 * Patient data table
 */
final class Version20260000010056 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create patient_data table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('patient_data');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('title', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('language', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('financial', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('fname', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('lname', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('mname', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('DOB', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('street', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('postal_code', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('city', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('state', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('country_code', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('drivers_license', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('ss', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('occupation', Types::TEXT, ['notnull' => false]);
        $table->addColumn('phone_home', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('phone_biz', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('phone_contact', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('phone_cell', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('pharmacy_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('status', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('contact_relationship', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('sex', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Sex at birth',
        ]);
        $table->addColumn('referrer', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('referrerID', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('providerID', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('ref_providerID', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('email', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('email_direct', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('ethnoracial', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('race', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('ethnicity', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('religion', Types::STRING, ['length' => 40, 'default' => '']);
        $table->addColumn('interpretter', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'original field used for determining if patient needs an interpreter, now used for additional notes about need for interpreter',
        ]);
        $table->addColumn('interpreter_needed', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'fk to list_options.option_id where list_id=yes_no_unknown used to determine if patient needs an interpreter']);
        $table->addColumn('migrantseasonal', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('family_size', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('monthly_income', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('billing_note', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('homeless', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('financial_review', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pubpid', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('genericname1', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('genericval1', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('genericname2', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('genericval2', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('hipaa_mail', Types::STRING, ['length' => 3, 'default' => '']);
        $table->addColumn('hipaa_voice', Types::STRING, ['length' => 3, 'default' => '']);
        $table->addColumn('hipaa_notice', Types::STRING, ['length' => 3, 'default' => '']);
        $table->addColumn('hipaa_message', Types::STRING, ['length' => 20, 'default' => '']);
        $table->addColumn('hipaa_allowsms', Types::STRING, ['length' => 3, 'default' => 'NO']);
        $table->addColumn('hipaa_allowemail', Types::STRING, ['length' => 3, 'default' => 'NO']);
        $table->addColumn('squad', Types::STRING, ['length' => 32, 'default' => '']);
        $table->addColumn('fitness', Types::INTEGER, ['default' => 0]);
        $table->addColumn('referral_source', Types::STRING, ['length' => 30, 'default' => '']);
        $table->addColumn('usertext1', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext2', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext3', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext4', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext5', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext6', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext7', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext8', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('userlist1', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('userlist2', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('userlist3', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('userlist4', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('userlist5', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('userlist6', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('userlist7', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('pricelevel', Types::STRING, ['length' => 255, 'default' => 'standard']);
        $table->addColumn('regdate', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Registration Date',
        ]);
        $table->addColumn('contrastart', Types::DATE_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Date contraceptives initially used',
        ]);
        $table->addColumn('completed_ad', Types::STRING, ['length' => 3, 'default' => 'NO']);
        $table->addColumn('ad_reviewed', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Date and time the advance care directive was reviewed and validated by the authenticator user.',
        ]);
        $table->addColumn('advance_directive_user_authenticator', Types::BIGINT, ['notnull' => false, 'comment' => 'fk to users.id of the user who authenticates that the advance care directive is valid.']);
        $table->addColumn('vfc', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('mothersname', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('guardiansname', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('allow_imm_reg_use', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('allow_imm_info_share', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('allow_health_info_ex', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('allow_patient_portal', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('deceased_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('deceased_reason', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('soap_import_status', Types::SMALLINT, [
            'notnull' => false,
            'default' => null,
            'comment' => '1-Prescription Press 2-Prescription Import 3-Allergy Press 4-Allergy Import',
        ]);
        $table->addColumn('cmsportal_login', Types::STRING, ['length' => 60, 'default' => '']);
        $table->addColumn('care_team_provider', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('care_team_facility', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('care_team_status', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('county', Types::STRING, ['length' => 40, 'default' => '']);
        $table->addColumn('industry', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('imm_reg_status', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('imm_reg_stat_effdate', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('publicity_code', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('publ_code_eff_date', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('protect_indicator', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('prot_indi_effdate', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardianrelationship', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardiansex', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardianaddress', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardiancity', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardianstate', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardianpostalcode', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardiancountry', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardianphone', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardianworkphone', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('guardianemail', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('sexual_orientation', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('gender_identity', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('birth_fname', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('birth_lname', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('birth_mname', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('dupscore', Types::INTEGER);
        $table->addColumn('name_history', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('suffix', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('street_line_2', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('patient_groups', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('prevent_portal_apps', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('provider_since_date', Types::TEXT, ['notnull' => false, 'length' => 255]);
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
        $table->addColumn('preferred_name', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('nationality_country', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addColumn('tribal_affiliations', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('sex_identified', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'Patient reported current sex']);
        $table->addColumn('pronoun', Types::TEXT);

        $table->addIndex(['lname', 'fname'], 'idx_patient_name');
        $table->addIndex(['DOB'], 'idx_patient_dob');
        $table->addIndex(['id'], 'id');
        $table->addUniqueIndex(['pid'], 'pid');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE patient_data');
    }
}
