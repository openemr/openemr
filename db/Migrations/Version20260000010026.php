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
 * Gacl aro groups table
 */
final class Version20260000010026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create gacl_aro_groups table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('gacl_aro_groups');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('parent_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('lft', Types::INTEGER, ['default' => 0]);
        $table->addColumn('rgt', Types::INTEGER, ['default' => 0]);
        $table->addColumn('name', Types::STRING, ['length' => 255]);
        $table->addColumn('value', Types::STRING, ['length' => 150]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id', 'value')
                ->create()
        );
        $table->addIndex(['parent_id'], 'gacl_parent_id_aro_groups');
        $table->addIndex(['lft', 'rgt'], 'gacl_lft_rgt_aro_groups');
        $table->addUniqueIndex(['value'], 'gacl_value_aro_groups');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('gacl_aro_groups');
    }
}
