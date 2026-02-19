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
 * Patient reminders table
 */
final class Version20260000020086 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create patient_reminders table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('patient_reminders');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('active', Types::SMALLINT, ['default' => 1, 'comment' => '1 if active and 0 if not active']);
        $table->addColumn('date_inactivated', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('reason_inactivated', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to list_options list rule_reminder_inactive_opt',
        ]);
        $table->addColumn('due_status', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to list_options list rule_reminder_due_opt',
        ]);
        $table->addColumn('pid', Types::BIGINT, ['comment' => 'id from patient_data table']);
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
        $table->addColumn('date_created', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_sent', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('voice_status', Types::SMALLINT, ['default' => 0, 'comment' => '0 if not sent and 1 if sent']);
        $table->addColumn('sms_status', Types::SMALLINT, ['default' => 0, 'comment' => '0 if not sent and 1 if sent']);
        $table->addColumn('email_status', Types::SMALLINT, ['default' => 0, 'comment' => '0 if not sent and 1 if sent']);
        $table->addColumn('mail_status', Types::SMALLINT, ['default' => 0, 'comment' => '0 if not sent and 1 if sent']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'pid');
        $table->addIndex(['category', 'item'], null);
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('patient_reminders');
    }
}
