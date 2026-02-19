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
 * Icd9 dx code table
 */
final class Version20260000020045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create icd9_dx_code table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('icd9_dx_code');
        $table->addColumn('dx_id', Types::STRING);
        $table->addColumn('dx_code', Types::STRING, ['length' => 5]);
        $table->addColumn('formatted_dx_code', Types::STRING, ['length' => 6]);
        $table->addColumn('short_desc', Types::STRING, ['length' => 60]);
        $table->addColumn('long_desc', Types::STRING, ['length' => 300]);
        $table->addColumn('active', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('revision', Types::INTEGER, ['default' => 0]);

        $table->addIndex(['dx_code'], 'dx_code');
        $table->addIndex(['formatted_dx_code'], 'formatted_dx_code');
        $table->addIndex(['active'], 'active');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('icd9_dx_code');
    }
}
