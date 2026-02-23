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
 * Lang definitions table
 */
final class Version20260000010047 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create lang_definitions table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('lang_definitions');
        $table->addColumn('def_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('cons_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('lang_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('definition', Types::TEXT, ['length' => 16777215]);

        $table->addIndex(['cons_id'], 'cons_id');
        $table->addIndex(['lang_id', 'cons_id'], 'lang_cons');
        $table->addUniqueIndex(['def_id'], 'def_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lang_definitions');
    }
}
