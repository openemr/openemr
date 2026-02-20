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
 * Patient history table
 */
final class Version20260000020084 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create patient_history table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('patient_history');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE);
        $table->addColumn('care_team_provider', Types::TEXT);
        $table->addColumn('care_team_facility', Types::TEXT);
        $table->addColumn('pid', Types::BIGINT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('patient_history');
    }
}
