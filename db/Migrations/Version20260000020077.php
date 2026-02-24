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
 * Onsite documents table
 */
final class Version20260000020077 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create onsite_documents table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('onsite_documents');
        $table->addColumn('id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('facility', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('provider', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('encounter', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('create_date', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('doc_type', Types::STRING, ['length' => 255]);
        $table->addColumn('patient_signed_status', Types::SMALLINT, ['unsigned' => true]);
        $table->addColumn('patient_signed_time', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('authorize_signed_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('accept_signed_status', Types::SMALLINT);
        $table->addColumn('authorizing_signator', Types::STRING, ['length' => 50]);
        $table->addColumn('review_date', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('denial_reason', Types::STRING, ['length' => 255]);
        $table->addColumn('authorized_signature', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('patient_signature', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('full_document', Types::BLOB, ['length' => 16777215, 'notnull' => false]);
        $table->addColumn('file_name', Types::STRING, ['length' => 255]);
        $table->addColumn('file_path', Types::STRING, ['length' => 255]);
        $table->addColumn('template_data', Types::TEXT, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE onsite_documents');
    }
}
