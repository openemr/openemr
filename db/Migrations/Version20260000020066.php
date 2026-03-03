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
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * List options table
 */
final class Version20260000020066 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create list_options table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('list_options');
        $table->addColumn('list_id', Types::STRING, ['length' => 100, 'default' => '']);
        $table->addColumn('option_id', Types::STRING, ['length' => 100, 'default' => '']);
        $table->addColumn('title', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('seq', Types::INTEGER, ['default' => 0]);
        $this->addBooleanColumn($table, 'is_default', default: false);
        $table->addColumn('option_value', Types::SMALLFLOAT, ['default' => 0]);
        $table->addColumn('mapping', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('codes', Types::STRING, ['length' => 255, 'default' => '']);
        $this->addBooleanColumn($table, 'toggle_setting_1', default: false);
        $this->addBooleanColumn($table, 'toggle_setting_2', default: false);
        $this->addBooleanColumn($table, 'activity', default: true);
        $table->addColumn('subtype', Types::STRING, ['length' => 31, 'default' => '']);
        $this->addBooleanColumn($table, 'edit_options', default: true);
        $table->addColumn('timestamp', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $this->addPrimaryKey($table, 'list_id', 'option_id');
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE list_options');
    }
}
