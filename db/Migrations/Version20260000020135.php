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
 * Customlists table
 */
final class Version20260000020135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create customlists table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('customlists');
        $table->addColumn('cl_list_slno', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('cl_list_id', Types::INTEGER, ['unsigned' => true, 'comment' => 'ID OF THE lIST FOR NEW TAKE SELECT MAX(cl_list_id)+1']);
        $table->addColumn('cl_list_item_id', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
            'comment' => 'ID OF THE lIST FOR NEW TAKE SELECT MAX(cl_list_item_id)+1',
        ]);
        $table->addColumn('cl_list_type', Types::INTEGER, ['unsigned' => true, 'comment' => '0=>List Name 1=>list items 2=>Context 3=>Template 4=>Sentence 5=> SavedTemplate 6=>CustomButton']);
        $table->addColumn('cl_list_item_short', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('cl_list_item_long', Types::TEXT);
        $table->addColumn('cl_list_item_level', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Flow level for List Designation',
        ]);
        $table->addColumn('cl_order', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('cl_deleted', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('cl_creator', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('cl_list_slno')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('customlists');
    }
}
