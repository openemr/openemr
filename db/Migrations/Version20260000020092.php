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
 * Pro assessments table
 */
final class Version20260000020092 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create pro_assessments table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('pro_assessments');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('form_oid', Types::STRING, ['length' => 255, 'comment' => 'unique id for specific instrument, pulled from assessment center API']);
        $table->addColumn('form_name', Types::STRING, ['length' => 255, 'comment' => 'pulled from assessment center API']);
        $table->addColumn('user_id', Types::INTEGER, ['comment' => 'ID for user that orders the form']);
        $table->addColumn('deadline', Types::DATETIME_MUTABLE, ['comment' => 'deadline to complete the form, will be used when sending notification and reminders']);
        $table->addColumn('patient_id', Types::INTEGER, ['comment' => 'ID for patient to order the form for']);
        $table->addColumn('assessment_oid', Types::STRING, ['length' => 255, 'comment' => 'unique id for this specific assessment, pulled from assessment center API']);
        $table->addColumn('status', Types::STRING, ['length' => 255, 'comment' => 'ordered or completed']);
        $table->addColumn('score', Types::FLOAT, ['comment' => 'T-Score for the assessment']);
        $table->addColumn('error', Types::FLOAT, ['comment' => 'Standard error for the score']);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['comment' => 'timestamp recording the creation time of this assessment']);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['comment' => 'this field indicates the completion time when the status is completed']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pro_assessments');
    }
}
