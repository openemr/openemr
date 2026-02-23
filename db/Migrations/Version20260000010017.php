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
 * Gacl acl table
 */
final class Version20260000010017 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create gacl_acl table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('gacl_acl');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('section_value', Types::STRING, ['length' => 150, 'default' => 'system']);
        $table->addColumn('allow', Types::INTEGER, ['default' => 0]);
        $table->addColumn('enabled', Types::INTEGER, ['default' => 0]);
        $table->addColumn('return_value', Types::TEXT, ['length' => 65535]);
        $table->addColumn('note', Types::TEXT, ['length' => 65535]);
        $table->addColumn('updated_date', Types::INTEGER, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['enabled'], 'gacl_enabled_acl');
        $table->addIndex(['section_value'], 'gacl_section_value_acl');
        $table->addIndex(['updated_date'], 'gacl_updated_date_acl');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE gacl_acl');
    }
}
