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
 * Ccda field mapping table
 */
final class Version20260000020144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ccda_field_mapping table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('ccda_field_mapping');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('table_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('ccda_field', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('ccda_field_mapping');
    }
}
