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
 * Clinical notes documents table
 */
final class Version20260000010068 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create clinical_notes_documents table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('clinical_notes_documents');
        $table->addOption('comment', 'Links clinical notes to patient documents');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('clinical_note_id', Types::BIGINT, ['comment' => 'Foreign key to form_clinical_notes.id']);
        $table->addColumn('document_id', Types::BIGINT, ['comment' => 'Foreign key to documents.id']);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['comment' => 'When the link was created', 'default' => 'CURRENT_TIMESTAMP']);
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
        $table->addIndex(['document_id'], 'idx_document_id');
        $table->addIndex(['created_at'], 'idx_created_at');
        $table->addUniqueIndex(['clinical_note_id', 'document_id'], 'unique_note_document');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE clinical_notes_documents');
    }
}
