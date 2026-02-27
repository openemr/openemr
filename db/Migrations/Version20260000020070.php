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
 * Module acl group settings table
 */
final class Version20260000020070 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create module_acl_group_settings table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('module_acl_group_settings');
        $table->addColumn('module_id', Types::INTEGER);
        $table->addColumn('group_id', Types::INTEGER);
        $table->addColumn('section_id', Types::INTEGER);
        $table->addColumn('allowed', Types::BOOLEAN, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('module_id', 'group_id', 'section_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE module_acl_group_settings');
    }
}
