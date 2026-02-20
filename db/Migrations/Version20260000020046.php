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
 * Icd9 sg code table
 */
final class Version20260000020046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create icd9_sg_code table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('icd9_sg_code');
        $table->addColumn('sg_id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('sg_code', Types::STRING, ['length' => 5]);
        $table->addColumn('formatted_sg_code', Types::STRING, ['length' => 6]);
        $table->addColumn('short_desc', Types::STRING, ['length' => 60]);
        $table->addColumn('long_desc', Types::STRING, ['length' => 300]);
        $table->addColumn('active', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('revision', Types::INTEGER, ['default' => 0]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('sg_id')
                ->create()
        );
        $table->addIndex(['sg_code'], 'sg_code');
        $table->addIndex(['formatted_sg_code'], 'formatted_sg_code');
        $table->addIndex(['active'], 'active');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('icd9_sg_code');
    }
}
