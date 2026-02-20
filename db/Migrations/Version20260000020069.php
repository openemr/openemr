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
 * Modules table
 */
final class Version20260000020069 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create modules table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('modules');
        $table->addColumn('mod_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('mod_name', Types::STRING, ['length' => 64, 'default' => 0]);
        $table->addColumn('mod_directory', Types::STRING, ['length' => 64, 'default' => '']);
        $table->addColumn('mod_parent', Types::STRING, ['length' => 64, 'default' => '']);
        $table->addColumn('mod_type', Types::STRING, ['length' => 64, 'default' => '']);
        $table->addColumn('mod_active', Types::INTEGER, ['unsigned' => true, 'default' => 0]);
        $table->addColumn('mod_ui_name', Types::STRING, ['length' => 64, 'default' => '']);
        $table->addColumn('mod_relative_link', Types::STRING, ['length' => 64, 'default' => '']);
        $table->addColumn('mod_ui_order', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('mod_ui_active', Types::INTEGER, ['unsigned' => true, 'default' => 0]);
        $table->addColumn('mod_description', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('mod_nick_name', Types::STRING, ['length' => 25, 'default' => '']);
        $table->addColumn('mod_enc_menu', Types::STRING, ['length' => 10, 'default' => 'no']);
        $table->addColumn('permissions_item_table', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('directory', Types::STRING, ['length' => 255]);
        $table->addColumn('date', Types::DATETIME_MUTABLE);
        $table->addColumn('sql_run', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('type', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('sql_version', Types::STRING, ['length' => 150]);
        $table->addColumn('acl_version', Types::STRING, ['length' => 150]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('mod_id', 'mod_directory')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE modules');
    }
}
