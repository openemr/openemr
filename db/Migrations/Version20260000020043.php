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
 * Fee sheet options table
 */
final class Version20260000020043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create fee_sheet_options table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('fee_sheet_options');
        $table->addColumn('fs_category', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('fs_option', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('fs_codes', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('fee_sheet_options');
    }
}
