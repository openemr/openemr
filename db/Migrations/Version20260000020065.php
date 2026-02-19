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
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Layout options table
 */
final class Version20260000020065 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create layout_options table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('layout_options');
        $table->addColumn('form_id', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('field_id', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('group_id', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('title', Types::TEXT);
        $table->addColumn('seq', Types::INTEGER, ['default' => 0]);
        $table->addColumn('data_type', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('uor', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('fld_length', Types::INTEGER, ['default' => 15]);
        $table->addColumn('max_length', Types::INTEGER, ['default' => 0]);
        $table->addColumn('list_id', Types::STRING, ['length' => 100, 'default' => '']);
        $table->addColumn('titlecols', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('datacols', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('default_value', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('edit_options', Types::STRING, ['length' => 36, 'default' => '']);
        $table->addColumn('description', Types::TEXT);
        $table->addColumn('fld_rows', Types::INTEGER, ['default' => 0]);
        $table->addColumn('list_backup_id', Types::STRING, ['length' => 100, 'default' => '']);
        $table->addColumn('source', Types::STRING, [
            'length' => 1,
            'default' => 'F',
            'comment' => 'F=Form, D=Demographics, H=History, E=Encounter',
        ]);
        $table->addColumn('conditions', Types::TEXT, ['comment' => 'serialized array of skip conditions']);
        $table->addColumn('validation', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('codes', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('form_id', 'field_id', 'seq')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('layout_options');
    }
}
