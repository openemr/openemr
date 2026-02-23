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
 * Keys table
 */
final class Version20260000020062 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create keys table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('keys');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 20, 'default' => '']);
        $table->addColumn('value', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['name'], 'name');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        // $this->addSql('DROP TABLE keys');
        $schema->dropTable('keys');
    }
}
