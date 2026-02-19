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
 * Clinical plans rules table
 */
final class Version20260000020014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create clinical_plans_rules table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('clinical_plans_rules');
        $table->addColumn('plan_id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Unique and maps to list_options list clinical_plans',
        ]);
        $table->addColumn('rule_id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Unique and maps to list_options list clinical_rules',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('plan_id', 'rule_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('clinical_plans_rules');
    }
}
