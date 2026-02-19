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
 * Dated reminders table
 */
final class Version20260000020024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create dated_reminders table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('dated_reminders');
        $table->addColumn('dr_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('dr_from_ID', Types::INTEGER);
        $table->addColumn('dr_message_text', Types::STRING, ['length' => 160]);
        $table->addColumn('dr_message_sent_date', Types::DATETIME_MUTABLE);
        $table->addColumn('dr_message_due_date', Types::DATE_MUTABLE);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('message_priority', Types::SMALLINT);
        $table->addColumn('message_processed', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('processed_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('dr_processed_by', Types::INTEGER);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('dr_id')
                ->create()
        );
        $table->addIndex(['dr_from_ID', 'dr_message_due_date'], 'dr_from_ID');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('dated_reminders');
    }
}
