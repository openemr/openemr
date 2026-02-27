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
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use OpenEMR\Core\Migrations\CreateTableTrait;

/**
 * Form vitals calculation table
 */
final class Version20260000020192 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_vitals_calculation table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_vitals_calculation');
        $table->addOption('comment', 'Main calculation records - one per logical calculation (e.g., average BP)');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('encounter', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to form_encounter.id',
        ]);
        $table->addColumn('pid', Types::BIGINT, ['comment' => 'fk to patient_data.pid']);
        $table->addColumn('date_start', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_end', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('created_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('updated_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('calculation_id', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
            'comment' => 'application identifier representing calculation e.g., bp-MeanLast5, bp-Mean3Day, bp-MeanEncounter',
        ]);
        $table->addColumn('fvc_uuid', Types::BINARY, ['length' => 16, 'comment' => 'fk to form_vitals_calculation.uuid']);
        $table->addColumn('vitals_column', Types::STRING, ['length' => 64, 'comment' => 'Component type: bps, bpd, pulse, etc.']);
        $table->addColumn('value', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'notnull' => false,
            'default' => null,
            'comment' => 'Calculated numeric component value',
        ]);
        $table->addColumn('value_string', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'Calculated non-numeric component value',
        ]);
        $table->addColumn('value_unit', Types::STRING, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
            'comment' => 'Unit for this component value',
        ]);
        $table->addColumn('component_order', Types::INTEGER, ['default' => 0, 'comment' => 'Display order for components']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'idx_pid');
        $table->addIndex(['encounter'], 'idx_encounter');
        $table->addIndex(['calculation_id'], 'idx_calculation_id');
        $table->addIndex(['vitals_column'], 'idx_vitals_column');
        $table->addIndex(['fvc_uuid', 'component_order'], 'idx_component_order');
        $table->addUniqueIndex(['uuid'], 'unq_uuid');
        $table->addUniqueIndex(['fvc_uuid', 'vitals_column'], 'unq_fvc_component');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_vitals_calculation');
    }
}
