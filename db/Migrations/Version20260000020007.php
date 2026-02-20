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
 * Audit master table
 */
final class Version20260000020007 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create audit_master table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('audit_master');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('user_id', Types::BIGINT, ['comment' => 'The Id of the user who approves or denies']);
        $table->addColumn('approval_status', Types::SMALLINT, ['comment' => '1-Pending,2-Approved,3-Denied,4-Appointment directly updated to calendar table,5-Cancelled appointment']);
        $table->addColumn('comments', Types::TEXT);
        $table->addColumn('created_time', Types::DATETIME_MUTABLE);
        $table->addColumn('modified_time', Types::DATETIME_MUTABLE);
        $table->addColumn('ip_address', Types::STRING, ['length' => 100]);
        $table->addColumn('type', Types::SMALLINT, ['comment' => '1-new patient,2-existing patient,3-change is only in the document,4-Patient upload,5-random key,10-Appointment']);
        $table->addColumn('is_qrda_document', Types::BOOLEAN, ['notnull' => false]);
        $table->addColumn('is_unstructured_document', Types::BOOLEAN, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audit_master');
    }
}
