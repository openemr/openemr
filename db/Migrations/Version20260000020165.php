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
 * Therapy groups table
 */
final class Version20260000020165 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create therapy_groups table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('therapy_groups');
        $table->addColumn('group_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('group_name', Types::STRING, ['length' => 255]);
        $table->addColumn('group_start_date', Types::DATE_MUTABLE);
        $table->addColumn('group_end_date', Types::DATE_MUTABLE);
        $table->addColumn('group_type', Types::SMALLINT);
        $table->addColumn('group_participation', Types::SMALLINT);
        $table->addColumn('group_status', Types::INTEGER);
        $table->addColumn('group_notes', Types::TEXT);
        $table->addColumn('group_guest_counselors', Types::STRING, ['length' => 255]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('group_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('therapy_groups');
    }
}
