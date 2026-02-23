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
 * Lang constants table
 */
final class Version20260000010046 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create lang_constants table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('lang_constants');
        $table->addColumn('cons_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('constant_name', Types::TEXT, ['length' => 16777215]);

        $table->addIndex(['constant_name'], 'constant_name', [], ['lengths' => [100]]);
        $table->addUniqueIndex(['cons_id'], 'cons_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lang_constants');
    }
}
