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
 * Onsite portal activity table
 */
final class Version20260000020081 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create onsite_portal_activity table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('onsite_portal_activity');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('patient_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('activity', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('require_audit', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('pending_action', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('action_taken', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('status', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('narrative', Types::TEXT);
        $table->addColumn('table_action', Types::TEXT);
        $table->addColumn('table_args', Types::TEXT);
        $table->addColumn('action_user', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('action_taken_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('checksum', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['date'], 'date');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('onsite_portal_activity');
    }
}
