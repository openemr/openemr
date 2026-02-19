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
 * Amc misc data table
 */
final class Version20260000020001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create amc_misc_data table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('amc_misc_data');
        $table->addColumn('amc_id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Unique and maps to list_options list clinical_rules',
        ]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('map_category', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Maps to an object category (such as prescriptions etc.)',
        ]);
        $table->addColumn('map_id', Types::BIGINT, ['default' => 0, 'comment' => 'Maps to an object id (such as prescription id etc.)']);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_completed', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('soc_provided', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);

        $table->addIndex(['amc_id', 'pid', 'map_id'], null);
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('amc_misc_data');
    }
}
