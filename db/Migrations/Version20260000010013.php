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
 * Form ros table
 */
final class Version20260000010013 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_ros table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_ros');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('activity', Types::INTEGER, ['default' => 1]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('weight_change', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('weakness', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('fatigue', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('anorexia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('fever', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('chills', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('night_sweats', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('insomnia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('irritability', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('heat_or_cold', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('intolerance', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('change_in_vision', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('glaucoma_history', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('eye_pain', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('irritation', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('redness', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('excessive_tearing', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('double_vision', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('blind_spots', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('photophobia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hearing_loss', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('discharge', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pain', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('vertigo', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('tinnitus', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('frequent_colds', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('sore_throat', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('sinus_problems', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('post_nasal_drip', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('nosebleed', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('snoring', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('apnea', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('breast_mass', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('breast_discharge', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('biopsy', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('abnormal_mammogram', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cough', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('sputum', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('shortness_of_breath', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('wheezing', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hemoptsyis', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('asthma', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('copd', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('chest_pain', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('palpitation', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('syncope', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pnd', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('doe', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('orthopnea', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('peripheal', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('edema', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('legpain_cramping', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('history_murmur', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('arrythmia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('heart_problem', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dysphagia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('heartburn', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('bloating', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('belching', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('flatulence', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('nausea', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('vomiting', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hematemesis', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('gastro_pain', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('food_intolerance', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hepatitis', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('jaundice', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hematochezia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('changed_bowel', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('diarrhea', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('constipation', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('polyuria', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('polydypsia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dysuria', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hematuria', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('frequency', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('urgency', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('incontinence', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('renal_stones', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('utis', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hesitancy', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dribbling', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('stream', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('nocturia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('erections', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ejaculations', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('g', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('p', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ap', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lc', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('mearche', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('menopause', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('lmp', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('f_frequency', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('f_flow', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('f_symptoms', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('abnormal_hair_growth', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('f_hirsutism', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('joint_pain', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('swelling', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('m_redness', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('m_warm', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('m_stiffness', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('muscle', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('m_aches', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('fms', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('arthritis', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('loc', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('seizures', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('stroke', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('tia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('n_numbness', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('n_weakness', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('paralysis', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('intellectual_decline', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('memory_problems', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dementia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('n_headache', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('s_cancer', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('psoriasis', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('s_acne', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('s_other', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('s_disease', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('p_diagnosis', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('p_medication', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('depression', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('anxiety', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('social_difficulties', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('thyroid_problems', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('diabetes', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('abnormal_blood', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('anemia', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('fh_blood_problems', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('bleeding_problems', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('allergies', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('frequent_illness', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hiv', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('hai_status', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_ros');
    }
}
