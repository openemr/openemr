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
 * Template users table
 */
final class Version20260000020136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create template_users table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('template_users');
        $table->addColumn('tu_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('tu_user_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('tu_facility_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('tu_template_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('tu_template_order', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('tu_id')
                ->create()
        );
        $table->addUniqueIndex(['tu_user_id', 'tu_template_id'], 'templateuser');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('template_users');
    }
}
