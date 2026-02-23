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
 * Ip tracking table
 */
final class Version20260000020059 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create ip_tracking table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('ip_tracking');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('ip_string', Types::STRING, ['notnull' => false, 'length' => 255, 'default' => '']);
        $table->addColumn('total_ip_login_fail_counter', Types::BIGINT, ['notnull' => false, 'default' => 0]);
        $table->addColumn('ip_login_fail_counter', Types::BIGINT, ['notnull' => false, 'default' => 0]);
        $table->addColumn('ip_last_login_fail', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('ip_auto_block_emailed', Types::SMALLINT, ['notnull' => false, 'default' => 0]);
        $table->addColumn('ip_force_block', Types::SMALLINT, ['notnull' => false, 'default' => 0]);
        $table->addColumn('ip_no_prevent_timing_attack', Types::SMALLINT, ['notnull' => false, 'default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['ip_string'], 'ip_string');
        $table->addOption('engine', 'InnoDb');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ip_tracking');
    }
}
