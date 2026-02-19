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
 * Erx narcotics table
 */
final class Version20260000020039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create erx_narcotics table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('erx_narcotics');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('drug', Types::STRING, ['length' => 255]);
        $table->addColumn('dea_number', Types::STRING, ['length' => 5]);
        $table->addColumn('csa_sch', Types::STRING, ['length' => 2]);
        $table->addColumn('narc', Types::STRING, ['length' => 2]);
        $table->addColumn('other_names', Types::STRING, ['length' => 255]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('erx_narcotics');
    }
}
