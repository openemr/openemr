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
 * Uuid mapping table
 */
final class Version20260000020106 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create uuid_mapping table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('uuid_mapping');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['length' => 16, 'default' => '']);
        $table->addColumn('resource', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('resource_path', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('table', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('target_uuid', Types::BINARY, ['length' => 16, 'default' => '']);
        $table->addColumn('created', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['uuid'], 'uuid');
        $table->addIndex(['resource'], 'resource');
        $table->addIndex(['table'], 'table');
        $table->addIndex(['target_uuid'], 'target_uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE uuid_mapping');
    }
}
