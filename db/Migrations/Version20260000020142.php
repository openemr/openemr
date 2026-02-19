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
 * Ccda components table
 */
final class Version20260000020142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ccda_components table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('ccda_components');
        $table->addColumn('ccda_components_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('ccda_components_field', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ccda_components_name', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ccda_type', Types::INTEGER, ['comment' => '0=>sections,1=>components']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('ccda_components_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('ccda_components');
    }
}
