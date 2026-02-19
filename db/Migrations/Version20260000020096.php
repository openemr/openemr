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
 * Rule action item table
 */
final class Version20260000020096 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create rule_action_item table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('rule_action_item');
        $table->addColumn('category', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to list_options list rule_action_category',
        ]);
        $table->addColumn('item', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to list_options list rule_action',
        ]);
        $table->addColumn('clin_rem_link', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Custom html link in clinical reminder widget',
        ]);
        $table->addColumn('reminder_message', Types::TEXT, ['comment' => 'Custom message in patient reminder']);
        $table->addColumn('custom_flag', Types::SMALLINT, ['default' => 0, 'comment' => '1 indexed to rule_patient_data, 0 indexed within main schema']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('category', 'item')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('rule_action_item');
    }
}
