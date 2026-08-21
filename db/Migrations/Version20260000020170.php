<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use OpenEMR\Core\Database\Types\CustomTypes;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Form group attendance table
 */
final class Version20260000020170 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_group_attendance table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_group_attendance');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATE_MUTABLE, ['notnull' => false]);
        $table->addColumn('group_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('user', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('groupname', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('authorized', CustomTypes::TINYINT, ['notnull' => false]);
        $table->addColumn('encounter_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('activity', CustomTypes::TINYINT, ['notnull' => false]);
        $this->addPrimaryKey($table, 'id');
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_group_attendance');
    }
}
