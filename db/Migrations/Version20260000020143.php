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
 * Ccda sections table
 */
final class Version20260000020143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ccda_sections table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('ccda_sections');
        $table->addColumn('ccda_sections_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('ccda_components_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('ccda_sections_field', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ccda_sections_name', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ccda_sections_req_mapping', Types::SMALLINT, ['default' => 1]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('ccda_sections_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('ccda_sections');
    }
}
