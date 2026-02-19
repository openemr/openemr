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
 * Dsi source attributes table
 */
final class Version20260000020205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create dsi_source_attributes table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('dsi_source_attributes');
        $table->addColumn('id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('client_id', Types::STRING, ['length' => 80]);
        $table->addColumn('list_id', Types::STRING, ['length' => 100]);
        $table->addColumn('option_id', Types::STRING, ['length' => 100]);
        $table->addColumn('clinical_rule_id', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('source_value', Types::TEXT);
        $table->addColumn('created_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('last_updated_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated_at', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('dsi_source_attributes');
    }
}
