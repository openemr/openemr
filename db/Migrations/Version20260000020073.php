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
 * Module configuration table
 */
final class Version20260000020073 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create module_configuration table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('module_configuration');
        $table->addColumn('module_config_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('module_id', Types::INTEGER, ['unsigned' => true]);
        $table->addColumn('field_name', Types::STRING, ['length' => 45]);
        $table->addColumn('field_value', Types::STRING, ['length' => 255]);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id the user that first created this record',
        ]);
        $table->addColumn('date_added', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Datetime the record was initially created',
        ]);
        $table->addColumn('updated_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id the user that last modified this record',
        ]);
        $table->addColumn('date_modified', Types::DATETIME_MUTABLE, ['comment' => 'Datetime the record was last modified']);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Datetime the record was created',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('module_config_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('module_configuration');
    }
}
