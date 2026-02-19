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
 * Groups table
 */
final class Version20260000010041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create groups table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('groups');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('name', Types::TEXT);
        $table->addColumn('user', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('groups');
    }
}
