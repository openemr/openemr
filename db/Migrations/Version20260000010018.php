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
 * Gacl acl sections table
 */
final class Version20260000010018 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create gacl_acl_sections table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('gacl_acl_sections');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('value', Types::STRING, ['length' => 150]);
        $table->addColumn('order_value', Types::INTEGER, ['default' => 0]);
        $table->addColumn('name', Types::STRING, ['length' => 230]);
        $table->addColumn('hidden', Types::INTEGER, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['hidden'], 'gacl_hidden_acl_sections');
        $table->addUniqueIndex(['value'], 'gacl_value_acl_sections');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE gacl_acl_sections');
    }
}
