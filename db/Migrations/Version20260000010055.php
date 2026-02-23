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
 * Openemr postcalendar events table
 */
final class Version20260000010055 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create openemr_postcalendar_events table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('openemr_postcalendar_events');
        $table->addColumn('pc_eid', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('pc_catid', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_multiple', Types::INTEGER, ['unsigned' => true]);
        $table->addColumn('pc_aid', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_pid', Types::STRING, [
            'length' => 11,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_gid', Types::INTEGER, ['notnull' => false, 'default' => 0]);
        $table->addColumn('pc_title', Types::STRING, [
            'length' => 150,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pc_hometext', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('pc_comments', Types::INTEGER, ['notnull' => false, 'default' => 0]);
        $table->addColumn('pc_counter', Types::INTEGER, ['notnull' => false, 'unsigned' => true, 'default' => 0]);
        $table->addColumn('pc_topic', Types::INTEGER, ['default' => 1]);
        $table->addColumn('pc_informant', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_eventDate', Types::DATE_MUTABLE, ['default' => '0000-00-00']);
        $table->addColumn('pc_endDate', Types::DATE_MUTABLE, ['default' => '0000-00-00']);
        $table->addColumn('pc_duration', Types::BIGINT, ['default' => 0]);
        $table->addColumn('pc_recurrtype', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_recurrspec', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('pc_recurrfreq', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_startTime', Types::TIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pc_endTime', Types::TIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pc_alldayevent', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_location', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('pc_conttel', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_contname', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_contemail', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_website', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_fee', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_eventstatus', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_sharing', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_language', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_apptstatus', Types::STRING, ['length' => 15, 'default' => '-']);
        $table->addColumn('pc_prefcatid', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_facility', Types::INTEGER, ['default' => 0, 'comment' => 'facility id for this event']);
        $table->addColumn('pc_sendalertsms', Types::STRING, ['length' => 3, 'default' => 'NO']);
        $table->addColumn('pc_sendalertemail', Types::STRING, ['length' => 3, 'default' => 'NO']);
        $table->addColumn('pc_billing_location', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('pc_room', Types::STRING, ['length' => 20, 'default' => '']);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pc_eid')
                ->create()
        );
        $table->addIndex(['pc_catid', 'pc_aid', 'pc_eventDate', 'pc_endDate', 'pc_eventstatus', 'pc_sharing', 'pc_topic'], 'basic_event');
        $table->addIndex(['pc_eventDate'], 'pc_eventDate');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE openemr_postcalendar_events');
    }
}
