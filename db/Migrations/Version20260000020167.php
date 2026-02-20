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
 * Therapy groups participant attendance table
 */
final class Version20260000020167 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create therapy_groups_participant_attendance table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('therapy_groups_participant_attendance');
        $table->addColumn('form_id', Types::INTEGER);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('meeting_patient_comment', Types::TEXT);
        $table->addColumn('meeting_patient_status', Types::STRING, ['length' => 15]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('form_id', 'pid')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE therapy_groups_participant_attendance');
    }
}
