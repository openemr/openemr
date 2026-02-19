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
 * Notification settings table
 */
final class Version20260000020113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification_settings table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('notification_settings');
        $table->addColumn('SettingsId', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('Send_SMS_Before_Hours', Types::INTEGER);
        $table->addColumn('Send_Email_Before_Hours', Types::INTEGER);
        $table->addColumn('SMS_gateway_username', Types::STRING, ['length' => 100]);
        $table->addColumn('SMS_gateway_password', Types::STRING, ['length' => 100]);
        $table->addColumn('SMS_gateway_apikey', Types::STRING, ['length' => 100]);
        $table->addColumn('type', Types::STRING, ['length' => 50]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('SettingsId')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('notification_settings');
    }
}
