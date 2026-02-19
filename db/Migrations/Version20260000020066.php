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
 * List options table
 */
final class Version20260000020066 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create list_options table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('list_options');
        $table->addColumn('list_id', Types::STRING, ['length' => 100, 'default' => '']);
        $table->addColumn('option_id', Types::STRING, ['length' => 100, 'default' => '']);
        $table->addColumn('title', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('seq', Types::INTEGER, ['default' => 0]);
        $table->addColumn('is_default', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('option_value', Types::FLOAT, ['default' => 0]);
        $table->addColumn('mapping', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('notes', Types::TEXT);
        $table->addColumn('codes', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('toggle_setting_1', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('toggle_setting_2', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('activity', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('subtype', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('edit_options', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('timestamp', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('list_id', 'option_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('list_options');
    }
}
