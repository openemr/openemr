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
 * Clinical notes procedure results table
 */
final class Version20260000010069 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create clinical_notes_procedure_results table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('clinical_notes_procedure_results');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('clinical_note_id', Types::BIGINT, ['comment' => 'Foreign key to form_clinical_notes.id']);
        $table->addColumn('procedure_result_id', Types::BIGINT, ['comment' => 'Foreign key to procedure_result.procedure_result_id']);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['comment' => 'When the link was created']);
        $table->addColumn('created_by', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'Username who created the link',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['clinical_note_id'], 'idx_clinical_note_id');
        $table->addIndex(['procedure_result_id'], 'idx_procedure_result_id');
        $table->addIndex(['created_at'], 'idx_created_at');
        $table->addUniqueIndex(['clinical_note_id', 'procedure_result_id'], 'unique_note_result');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('clinical_notes_procedure_results');
    }
}
