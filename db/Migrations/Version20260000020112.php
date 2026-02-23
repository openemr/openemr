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
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Notification log table
 */
final class Version20260000020112 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create notification_log table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('notification_log');
        $table->addColumn('iLogId', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('pc_eid', Types::INTEGER, ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('sms_gateway_type', Types::STRING, ['length' => 50]);
        $table->addColumn('smsgateway_info', Types::STRING, ['length' => 255]);
        $table->addColumn('message', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('email_sender', Types::STRING, ['length' => 255]);
        $table->addColumn('email_subject', Types::STRING, ['length' => 255]);
        $table->addColumn('type', Types::ENUM, ['values' => ['SMS', 'Email']]);
        $table->addColumn('patient_info', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('pc_eventDate', Types::DATE_MUTABLE);
        $table->addColumn('pc_endDate', Types::DATE_MUTABLE);
        $table->addColumn('pc_startTime', Types::TIME_MUTABLE);
        $table->addColumn('pc_endTime', Types::TIME_MUTABLE);
        $table->addColumn('dSentDateTime', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('iLogId')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification_log');
    }
}
