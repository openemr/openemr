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
 * Modules settings table
 */
final class Version20260000020075 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create modules_settings table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('modules_settings');
        $table->addColumn('mod_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('fld_type', Types::SMALLINT, [
            'notnull' => false,
            'default' => null,
            'comment' => '1=>ACL,2=>preferences,3=>hooks',
        ]);
        $table->addColumn('obj_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('menu_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('path', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('modules_settings');
    }
}
