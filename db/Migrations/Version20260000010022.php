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
 * Gacl aco sections table
 */
final class Version20260000010022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create gacl_aco_sections table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('gacl_aco_sections');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('value', Types::STRING, ['length' => 150]);
        $table->addColumn('order_value', Types::INTEGER, ['default' => 0]);
        $table->addColumn('name', Types::STRING, ['length' => 230]);
        $table->addColumn('hidden', Types::INTEGER, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['hidden'], 'gacl_hidden_aco_sections');
        $table->addUniqueIndex(['value'], 'gacl_value_aco_sections');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('gacl_aco_sections');
    }
}
