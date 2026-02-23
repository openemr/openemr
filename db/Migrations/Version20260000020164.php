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
 * Multiple db table
 */
final class Version20260000020164 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create multiple_db table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('multiple_db');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('namespace', Types::STRING, ['length' => 255]);
        $table->addColumn('username', Types::STRING, ['length' => 255]);
        $table->addColumn('password', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('dbname', Types::STRING, ['length' => 255]);
        $table->addColumn('host', Types::STRING, ['length' => 255, 'default' => 'localhost']);
        $table->addColumn('port', Types::SMALLINT, ['default' => 3306]);
        $table->addColumn('date', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['namespace'], 'namespace');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE multiple_db');
    }
}
