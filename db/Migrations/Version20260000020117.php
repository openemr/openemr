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
 * Users facility table
 */
final class Version20260000020117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users_facility table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('users_facility');
        $table->addColumn('tablename', Types::STRING, ['length' => 64]);
        $table->addColumn('table_id', Types::INTEGER);
        $table->addColumn('facility_id', Types::INTEGER);
        $table->addColumn('warehouse_id', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('tablename', 'table_id', 'facility_id', 'warehouse_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users_facility');
    }
}
