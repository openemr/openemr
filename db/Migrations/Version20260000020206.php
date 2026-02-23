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
 * Track events table
 */
final class Version20260000020206 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create track_events table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('track_events');
        $table->addOption('comment', 'Telemetry Event Data');
        $table->addColumn('id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('event_type', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('event_label', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('event_url', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('event_target', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('first_event', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('last_event', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('label_count', Types::INTEGER, ['unsigned' => true, 'default' => 1]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['event_label', 'event_url'], 'unique_event_label_target', ['lengths' => [null, 255]]);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE track_events');
    }
}
