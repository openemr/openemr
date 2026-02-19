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
 * Therapy groups participants table
 */
final class Version20260000020166 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create therapy_groups_participants table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('therapy_groups_participants');
        $table->addColumn('group_id', Types::INTEGER);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('group_patient_status', Types::INTEGER);
        $table->addColumn('group_patient_start', Types::DATE_MUTABLE);
        $table->addColumn('group_patient_end', Types::DATE_MUTABLE);
        $table->addColumn('group_patient_comment', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('group_id', 'pid')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('therapy_groups_participants');
    }
}
