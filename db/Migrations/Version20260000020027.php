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
 * Documents legal detail table
 */
final class Version20260000020027 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create documents_legal_detail table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('documents_legal_detail');
        $table->addColumn('dld_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('dld_pid', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dld_facility', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dld_provider', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dld_encounter', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dld_master_docid', Types::INTEGER, ['unsigned' => true]);
        $table->addColumn('dld_signed', Types::SMALLINT, ['unsigned' => true, 'comment' => '0-Not Signed or Cannot Sign(Layout),1-Signed,2-Ready to sign,3-Denied(Pat Regi),4-Patient Upload,10-Save(Layout)']);
        $table->addColumn('dld_signed_time', Types::DATETIME_MUTABLE);
        $table->addColumn('dld_filepath', Types::STRING, [
            'length' => 75,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dld_filename', Types::STRING, ['length' => 45]);
        $table->addColumn('dld_signing_person', Types::STRING, ['length' => 50]);
        $table->addColumn('dld_sign_level', Types::INTEGER, ['comment' => 'Sign flow level']);
        $table->addColumn('dld_content', Types::STRING, ['length' => 50, 'comment' => 'Layout sign position']);
        $table->addColumn('dld_file_for_pdf_generation', Types::BLOB, ['comment' => 'The filled details in the fdf file is stored here.Patient Registration Screen']);
        $table->addColumn('dld_denial_reason', Types::TEXT);
        $table->addColumn('dld_moved', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('dld_patient_comments', Types::TEXT, ['comment' => 'Patient comments stored here']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('dld_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE documents_legal_detail');
    }
}
