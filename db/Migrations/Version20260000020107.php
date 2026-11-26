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
 * Uuid registry table
 */
final class Version20260000020107 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create uuid_registry table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('uuid_registry');
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 'length' => 16, 'default' => '']);
        $table->addColumn('table_name', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('table_id', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('table_vertical', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('couchdb', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('document_drive', CustomTypes::TINYINT, ['default' => 0]);
        $table->addColumn('mapped', CustomTypes::TINYINT, ['default' => 0]);
        $table->addColumn('created', CustomTypes::TIMESTAMP, ['notnull' => false]);
        $this->addPrimaryKey($table, 'uuid');
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE uuid_registry');
    }
}
