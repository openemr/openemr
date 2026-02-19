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
 * Module acl user settings table
 */
final class Version20260000020072 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create module_acl_user_settings table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('module_acl_user_settings');
        $table->addColumn('module_id', Types::INTEGER);
        $table->addColumn('user_id', Types::INTEGER);
        $table->addColumn('section_id', Types::INTEGER);
        $table->addColumn('allowed', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('module_id', 'user_id', 'section_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('module_acl_user_settings');
    }
}
