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
 * Automatic notification table
 */
final class Version20260000020111 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create automatic_notification table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('automatic_notification');
        $table->addColumn('notification_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('sms_gateway_type', Types::STRING, ['length' => 255]);
        $table->addColumn('provider_name', Types::STRING, ['length' => 100]);
        $table->addColumn('message', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('email_sender', Types::STRING, ['length' => 100]);
        $table->addColumn('email_subject', Types::STRING, ['length' => 100]);
        $table->addColumn('type', Types::ENUM, [
            'default' => 'SMS',
            'values' => ['SMS', 'Email'],
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('notification_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE automatic_notification');
    }
}
