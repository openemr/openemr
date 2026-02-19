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
 * Openemr module vars table
 */
final class Version20260000010052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create openemr_module_vars table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('openemr_module_vars');
        $table->addColumn('pn_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('pn_modname', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pn_name', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pn_value', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pn_id')
                ->create()
        );
        $table->addIndex(['pn_modname'], 'pn_modname');
        $table->addIndex(['pn_name'], 'pn_name');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('openemr_module_vars');
    }
}
