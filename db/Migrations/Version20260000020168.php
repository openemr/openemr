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
 * Therapy groups counselors table
 */
final class Version20260000020168 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create therapy_groups_counselors table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('therapy_groups_counselors');
        $table->addColumn('group_id', Types::INTEGER);
        $table->addColumn('user_id', Types::INTEGER);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('group_id', 'user_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('therapy_groups_counselors');
    }
}
