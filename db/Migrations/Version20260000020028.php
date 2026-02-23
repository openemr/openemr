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
 * Documents legal master table
 */
final class Version20260000020028 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create documents_legal_master table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('documents_legal_master');
        $table->addOption('comment', 'List of Master Docs to be signed');
        $table->addColumn('dlm_category', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dlm_subcategory', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dlm_document_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('dlm_document_name', Types::STRING, ['length' => 75]);
        $table->addColumn('dlm_filepath', Types::STRING, ['length' => 75]);
        $table->addColumn('dlm_facility', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dlm_provider', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('dlm_sign_height', Types::FLOAT);
        $table->addColumn('dlm_sign_width', Types::FLOAT);
        $table->addColumn('dlm_filename', Types::STRING, ['length' => 45]);
        $table->addColumn('dlm_effective_date', Types::DATETIME_MUTABLE);
        $table->addColumn('dlm_version', Types::INTEGER, ['unsigned' => true]);
        $table->addColumn('content', Types::STRING, ['length' => 255]);
        $table->addColumn('dlm_savedsign', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => '0-Yes 1-No',
        ]);
        $table->addColumn('dlm_review', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => '0-Yes 1-No',
        ]);
        $table->addColumn('dlm_upload_type', Types::SMALLINT, ['notnull' => false, 'default' => 0, 'comment' => '0-Provider Uploaded,1-Patient Uploaded']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('dlm_document_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE documents_legal_master');
    }
}
