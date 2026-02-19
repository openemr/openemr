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
 * Form vitals calculation components table
 */
final class Version20260000020214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_vitals_calculation_components table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_vitals_calculation_components');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('fvc_uuid', Types::BINARY, [
            'length' => 16,
            'comment' => 'fk to form_vitals_calculation.uuid',
        ]);
        $table->addColumn('vitals_column', Types::STRING, [
            'length' => 64,
            'comment' => 'Component type: bps, bpd, pulse, etc.',
        ]);
        $table->addColumn('value', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'notnull' => false,
            'comment' => 'Calculated numeric component value',
        ]);
        $table->addColumn('value_string', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'comment' => 'Calculated non-numeric component value',
        ]);
        $table->addColumn('value_unit', Types::STRING, [
            'length' => 16,
            'notnull' => false,
            'comment' => 'Unit for this component value',
        ]);
        $table->addColumn('component_order', Types::INTEGER, [
            'default' => 0,
            'comment' => 'Display order for components',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['fvc_uuid', 'vitals_column'], 'unq_fvc_component');
        $table->addIndex(['vitals_column'], 'idx_vitals_column');
        $table->addIndex(['fvc_uuid', 'component_order'], 'idx_component_order');
        $table->addOption('engine', 'InnoDB');
        $table->addOption('comment', 'Component values for calculations (e.g., systolic=120, diastolic=80)');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_vitals_calculation_components');
    }
}
