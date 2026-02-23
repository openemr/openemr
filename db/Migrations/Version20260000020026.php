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
 * Documents table
 */
final class Version20260000020026 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create documents table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('documents');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('type', Types::ENUM, [
            'notnull' => false,
            'default' => null,
            'values' => ['file_url', 'blob', 'web_url'],
        ]);
        $table->addColumn('size', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_expires', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('url', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('thumb_url', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('mimetype', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pages', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('owner', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('revision', Types::DATETIME_MUTABLE);
        $table->addColumn('foreign_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('docdate', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('hash', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('list_id', Types::BIGINT, ['default' => 0]);
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('drive_uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('couch_docid', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('couch_revid', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('storagemethod', Types::BOOLEAN, ['default' => 0, 'comment' => '0->Harddisk,1->CouchDB']);
        $table->addColumn('path_depth', Types::SMALLINT, ['notnull' => false, 'default' => 1, 'comment' => 'Depth of path to use in url to find document. Not applicable for CouchDB.']);
        $table->addColumn('imported', Types::SMALLINT, [
            'notnull' => false,
            'default' => 0,
            'comment' => 'Parsing status for CCR/CCD/CCDA importing',
        ]);
        $table->addColumn('encounter_id', Types::BIGINT, ['default' => 0, 'comment' => 'Encounter id if tagged']);
        $table->addColumn('encounter_check', Types::BOOLEAN, ['default' => 0, 'comment' => 'If encounter is created while tagging']);
        $table->addColumn('audit_master_approval_status', Types::SMALLINT, ['default' => 1, 'comment' => 'approval_status from audit_master table']);
        $table->addColumn('audit_master_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('documentationOf', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('encrypted', Types::BOOLEAN, ['default' => 0, 'comment' => '0->No,1->Yes']);
        $table->addColumn('document_data', Types::TEXT, ['notnull' => false, 'length' => 16777215]);
        $table->addColumn('deleted', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('foreign_reference_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('foreign_reference_table', Types::STRING, [
            'length' => 40,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['revision'], 'revision');
        $table->addIndex(['foreign_id'], 'foreign_id');
        $table->addIndex(['foreign_reference_id', 'foreign_reference_table'], 'foreign_reference');
        $table->addIndex(['owner'], 'owner');
        $table->addUniqueIndex(['drive_uuid'], 'drive_uuid');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE documents');
    }
}
