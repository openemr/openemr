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
 * Form reviewofs table
 */
final class Version20260000010012 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_reviewofs table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_reviewofs');
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
        $table->addColumn('authorized', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('activity', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('fever', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('chills', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('night_sweats', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('weight_loss', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('poor_appetite', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('insomnia', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('fatigued', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('depressed', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hyperactive', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('exposure_to_foreign_countries', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cataracts', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cataract_surgery', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('glaucoma', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('double_vision', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('blurred_vision', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('poor_hearing', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('headaches', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ringing_in_ears', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('bloody_nose', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('sinusitis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('sinus_surgery', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dry_mouth', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('strep_throat', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('tonsillectomy', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('swollen_lymph_nodes', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('throat_cancer', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('throat_cancer_surgery', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('heart_attack', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('irregular_heart_beat', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('chest_pains', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('shortness_of_breath', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('high_blood_pressure', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('heart_failure', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('poor_circulation', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('vascular_surgery', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cardiac_catheterization', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('coronary_artery_bypass', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('heart_transplant', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('stress_test', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('emphysema', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('chronic_bronchitis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('interstitial_lung_disease', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('shortness_of_breath_2', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lung_cancer', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lung_cancer_surgery', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pheumothorax', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('stomach_pains', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('peptic_ulcer_disease', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('gastritis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('endoscopy', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('polyps', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('colonoscopy', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('colon_cancer', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('colon_cancer_surgery', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ulcerative_colitis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('crohns_disease', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('appendectomy', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('divirticulitis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('divirticulitis_surgery', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('gall_stones', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cholecystectomy', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hepatitis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cirrhosis_of_the_liver', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('splenectomy', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('kidney_failure', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('kidney_stones', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('kidney_cancer', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('kidney_infections', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('bladder_infections', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('bladder_cancer', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('prostate_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('prostate_cancer', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('kidney_transplant', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('sexually_transmitted_disease', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('burning_with_urination', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('discharge_from_urethra', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('rashes', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('infections', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ulcerations', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pemphigus', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('herpes', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('osetoarthritis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('rheumotoid_arthritis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lupus', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ankylosing_sondlilitis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('swollen_joints', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('stiff_joints', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('broken_bones', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('neck_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('back_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('back_surgery', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('scoliosis', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('herniated_disc', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('shoulder_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('elbow_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('wrist_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hand_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hip_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('knee_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ankle_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('foot_problems', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('insulin_dependent_diabetes', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('noninsulin_dependent_diabetes', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hypothyroidism', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hyperthyroidism', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cushing_syndrom', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('addison_syndrom', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('additional_notes', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_reviewofs');
    }
}
