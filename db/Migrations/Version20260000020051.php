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
 * Icd10 gem pcs 9 10 table
 */
final class Version20260000020051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create icd10_gem_pcs_9_10 table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('icd10_gem_pcs_9_10');
        $table->addColumn('map_id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('pcs_icd9_source', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pcs_icd10_target', Types::STRING, [
            'length' => 7,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('flags', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('active', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('revision', Types::INTEGER, ['default' => 0]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('map_id')
                ->create()
        );
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('icd10_gem_pcs_9_10');
    }
}
