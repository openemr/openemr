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
 * Issue types table
 */
final class Version20260000020061 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create issue_types table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('issue_types');
        $table->addColumn('active', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('category', Types::STRING, ['length' => 75, 'default' => '']);
        $table->addColumn('type', Types::STRING, ['length' => 75, 'default' => '']);
        $table->addColumn('plural', Types::STRING, ['length' => 75, 'default' => '']);
        $table->addColumn('singular', Types::STRING, ['length' => 75, 'default' => '']);
        $table->addColumn('abbreviation', Types::STRING, ['length' => 75, 'default' => '']);
        $table->addColumn('style', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('force_show', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('ordering', Types::INTEGER, ['default' => 0]);
        $table->addColumn('aco_spec', Types::STRING, ['length' => 63, 'default' => 'patients|med']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('category', 'type')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('issue_types');
    }
}
