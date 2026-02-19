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
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Patient care experience preferences table
 */
final class Version20260000020211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create patient_care_experience_preferences table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('patient_care_experience_preferences');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('patient_id', Types::INTEGER);
        $table->addColumn('observation_code', Types::STRING, ['length' => 50, 'comment' => 'LOINC code']);
        $table->addColumn('observation_code_text', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('value_type', Types::STRING, ['default' => 'coded']);
        $table->addColumn('value_code', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to preference_value_sets.answer_code',
        ]);
        $table->addColumn('value_code_system', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to preference_value_sets.answer_system',
        ]);
        $table->addColumn('value_display', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to preference_value_sets.answer_display',
        ]);
        $table->addColumn('value_text', Types::TEXT);
        $table->addColumn('value_boolean', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('effective_datetime', Types::DATETIME_MUTABLE);
        $table->addColumn('status', Types::STRING, [
            'length' => 20,
            'default' => 'final',
            'comment' => 'valid options are final,amended,preliminary',
        ]);
        $table->addColumn('note', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['patient_id'], 'patient_id');
        $table->addIndex(['observation_code'], 'observation_code');
        $table->addIndex(['status'], 'status');
        $table->addUniqueIndex(['uuid'], 'unq_uuid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('patient_care_experience_preferences');
    }
}
