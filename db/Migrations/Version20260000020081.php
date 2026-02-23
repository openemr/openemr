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
 * Onsite portal activity table
 */
final class Version20260000020081 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create onsite_portal_activity table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('onsite_portal_activity');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('patient_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('activity', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('require_audit', Types::SMALLINT, ['notnull' => false, 'default' => 1]);
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
        $table->addColumn('narrative', Types::TEXT, ['notnull' => false]);
        $table->addColumn('table_action', Types::TEXT, ['notnull' => false]);
        $table->addColumn('table_args', Types::TEXT, ['notnull' => false]);
        $table->addColumn('action_user', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('action_taken_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('checksum', Types::TEXT, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['date'], 'date');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE onsite_portal_activity');
    }
}
