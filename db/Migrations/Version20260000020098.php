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
 * Rule patient data table
 */
final class Version20260000020098 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create rule_patient_data table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('rule_patient_data');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('category', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to the category item in the rule_action_item table',
        ]);
        $table->addColumn('item', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to the item column in the rule_action_item table',
        ]);
        $table->addColumn('complete', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to list_options list yesno',
        ]);
        $table->addColumn('result', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], null);
        $table->addIndex(['category', 'item'], null);
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('rule_patient_data');
    }
}
