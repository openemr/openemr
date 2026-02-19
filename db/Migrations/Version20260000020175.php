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
 * Medex recalls table
 */
final class Version20260000020175 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create medex_recalls table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('medex_recalls');
        $table->addColumn('r_ID', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('r_PRACTID', Types::INTEGER);
        $table->addColumn('r_pid', Types::INTEGER, ['comment' => 'PatientID from pat_data']);
        $table->addColumn('r_eventDate', Types::DATE_MUTABLE, ['comment' => 'Date of Appt or Recall']);
        $table->addColumn('r_facility', Types::INTEGER);
        $table->addColumn('r_provider', Types::INTEGER);
        $table->addColumn('r_reason', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('r_created', Types::DATETIME_MUTABLE, ['default' => '0000-00-00 00:00:00']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('r_ID')
                ->create()
        );
        $table->addUniqueIndex(['r_PRACTID', 'r_pid'], 'r_PRACTID');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('medex_recalls');
    }
}
