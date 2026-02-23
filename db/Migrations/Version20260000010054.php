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
 * Openemr postcalendar categories table
 */
final class Version20260000010054 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create openemr_postcalendar_categories table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('openemr_postcalendar_categories');
        $table->addColumn('pc_catid', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('pc_constant_id', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_catname', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_catcolor', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pc_catdesc', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('pc_recurrtype', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_enddate', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pc_recurrspec', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('pc_recurrfreq', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_duration', Types::BIGINT, ['default' => 0]);
        $table->addColumn('pc_end_date_flag', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('pc_end_date_type', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('pc_end_date_freq', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_end_all_day', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('pc_dailylimit', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pc_cattype', Types::INTEGER, ['comment' => 'Used in grouping categories']);
        $table->addColumn('pc_active', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('pc_seq', Types::INTEGER, ['default' => 0]);
        $table->addColumn('aco_spec', Types::STRING, ['length' => 63, 'default' => 'encounters|notes']);
        $table->addColumn('pc_last_updated', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pc_catid')
                ->create()
        );
        $table->addIndex(['pc_catname', 'pc_catcolor'], 'basic_cat');
        $table->addUniqueIndex(['pc_constant_id'], 'pc_constant_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE openemr_postcalendar_categories');
    }
}
