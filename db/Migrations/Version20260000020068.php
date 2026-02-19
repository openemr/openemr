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
 * Lists touch table
 */
final class Version20260000020068 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lists_touch table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('lists_touch');
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('type', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pid', 'type')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('lists_touch');
    }
}
