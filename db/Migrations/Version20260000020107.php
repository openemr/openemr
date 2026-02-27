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
        $table->addColumn('document_drive', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('mapped', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('created', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('uuid')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE uuid_registry');
    }
}
