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
 * Module acl sections table
 */
final class Version20260000020071 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create module_acl_sections table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('module_acl_sections');
        $table->addColumn('section_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('section_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('parent_section', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('section_identifier', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('module_id', Types::INTEGER, ['notnull' => false, 'default' => null]);

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('module_acl_sections');
    }
}
