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
 * History data table
 */
final class Version20260000010042 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create history_data table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('history_data');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('coffee', Types::TEXT, ['notnull' => false]);
        $table->addColumn('tobacco', Types::TEXT, ['notnull' => false]);
        $table->addColumn('alcohol', Types::TEXT, ['notnull' => false]);
        $table->addColumn('sleep_patterns', Types::TEXT, ['notnull' => false]);
        $table->addColumn('exercise_patterns', Types::TEXT, ['notnull' => false]);
        $table->addColumn('seatbelt_use', Types::TEXT, ['notnull' => false]);
        $table->addColumn('counseling', Types::TEXT, ['notnull' => false]);
        $table->addColumn('hazardous_activities', Types::TEXT, ['notnull' => false]);
        $table->addColumn('recreational_drugs', Types::TEXT, ['notnull' => false]);
        $table->addColumn('last_breast_exam', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_mammogram', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_gynocological_exam', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_rectal_exam', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_prostate_exam', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_physical_exam', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_sigmoidoscopy_colonoscopy', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_ecg', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_cardiac_echo', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_retinal', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_fluvax', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_pneuvax', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_ldl', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_hemoglobin', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_psa', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_exam_results', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('history_mother', Types::TEXT, ['notnull' => false]);
        $table->addColumn('dc_mother', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('history_father', Types::TEXT, ['notnull' => false]);
        $table->addColumn('dc_father', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('history_siblings', Types::TEXT, ['notnull' => false]);
        $table->addColumn('dc_siblings', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('history_offspring', Types::TEXT, ['notnull' => false]);
        $table->addColumn('dc_offspring', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('history_spouse', Types::TEXT, ['notnull' => false]);
        $table->addColumn('dc_spouse', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('relatives_cancer', Types::TEXT, ['notnull' => false]);
        $table->addColumn('relatives_tuberculosis', Types::TEXT, ['notnull' => false]);
        $table->addColumn('relatives_diabetes', Types::TEXT, ['notnull' => false]);
        $table->addColumn('relatives_high_blood_pressure', Types::TEXT, ['notnull' => false]);
        $table->addColumn('relatives_heart_problems', Types::TEXT, ['notnull' => false]);
        $table->addColumn('relatives_stroke', Types::TEXT, ['notnull' => false]);
        $table->addColumn('relatives_epilepsy', Types::TEXT, ['notnull' => false]);
        $table->addColumn('relatives_mental_illness', Types::TEXT, ['notnull' => false]);
        $table->addColumn('relatives_suicide', Types::TEXT, ['notnull' => false]);
        $table->addColumn('cataract_surgery', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('tonsillectomy', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('cholecystestomy', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('heart_surgery', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('hysterectomy', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('hernia_repair', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('hip_replacement', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('knee_replacement', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('appendectomy', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('name_1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('value_1', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('name_2', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('value_2', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('additional_history', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('exams', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('usertext11', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('usertext12', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext13', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext14', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext15', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext16', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext17', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext18', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext19', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext20', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext21', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext22', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext23', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext24', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext25', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext26', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext27', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext28', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext29', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('usertext30', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('userdate11', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('userdate12', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('userdate13', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('userdate14', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('userdate15', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('userarea11', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('userarea12', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id the user that first created this record',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'pid');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE history_data');
    }
}
