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
 * Gacl groups aro map table
 */
final class Version20260000010038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create gacl_groups_aro_map table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('gacl_groups_aro_map');
        $table->addColumn('group_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('aro_id', Types::INTEGER, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('group_id', 'aro_id')
                ->create()
        );
        $table->addIndex(['aro_id'], 'gacl_aro_id');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('gacl_groups_aro_map');
    }
}
