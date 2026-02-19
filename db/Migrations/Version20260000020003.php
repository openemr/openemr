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
 * Amendments history table
 */
final class Version20260000020003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create amendments_history table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('amendments_history');
        $table->addColumn('amendment_id', Types::INTEGER, ['autoincrement' => true, 'comment' => 'Amendment ID']);
        $table->addColumn('amendment_note', Types::TEXT, ['comment' => 'Amendment requested from']);
        $table->addColumn('amendment_status', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'comment' => 'Amendment Request Status',
        ]);
        $table->addColumn('created_by', Types::INTEGER, ['comment' => 'references users.id for session owner']);
        $table->addColumn('created_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'created time']);

        $table->addIndex(['amendment_id'], 'amendment_history_id');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('amendments_history');
    }
}
