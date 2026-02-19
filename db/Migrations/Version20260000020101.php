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
 * Session tracker table
 */
final class Version20260000020101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create session_tracker table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('session_tracker');
        $table->addColumn('uuid', Types::BINARY, ['length' => 16, 'default' => '']);
        $table->addColumn('created', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('number_scripts', Types::BIGINT, ['default' => 1]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('uuid')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('session_tracker');
    }
}
