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
 * Direct message log table
 */
final class Version20260000010006 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create direct_message_log table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('direct_message_log');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('msg_type', Types::STRING, ['length' => 1, 'comment' => 'S=sent,R=received']);
        $table->addColumn('msg_id', Types::STRING, ['length' => 127]);
        $table->addColumn('sender', Types::STRING, ['length' => 255]);
        $table->addColumn('recipient', Types::STRING, ['length' => 255]);
        $table->addColumn('create_ts', Types::DATETIME_MUTABLE);
        $table->addColumn('status', Types::STRING, ['length' => 1, 'comment' => 'Q=queued,D=dispatched,R=received,F=failed']);
        $table->addColumn('status_info', Types::STRING, [
            'length' => 511,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('status_ts', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('patient_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('user_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['msg_id'], 'msg_id');
        $table->addIndex(['patient_id'], 'patient_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE direct_message_log');
    }
}
