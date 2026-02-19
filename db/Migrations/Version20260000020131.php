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
 * Globals table
 */
final class Version20260000020131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create globals table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('globals');
        $table->addColumn('gl_name', Types::STRING, ['length' => 63]);
        $table->addColumn('gl_index', Types::INTEGER, ['default' => 0]);
        $table->addColumn('gl_value', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('gl_name', 'gl_index')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('globals');
    }
}
