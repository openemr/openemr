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
 * Form vitals table
 */
final class Version20260000010015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_vitals table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_vitals');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('groupname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('authorized', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('activity', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('bps', Types::STRING, [
            'length' => 40,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('bpd', Types::STRING, [
            'length' => 40,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('weight', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('height', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('temperature', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('temp_method', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pulse', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('respiration', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('note', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('BMI', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.0,
        ]);
        $table->addColumn('BMI_status', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('waist_circ', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('head_circ', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('oxygen_saturation', Types::DECIMAL, [
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('oxygen_flow_rate', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ped_weight_height', Types::DECIMAL, [
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('ped_bmi', Types::DECIMAL, [
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('ped_head_circ', Types::DECIMAL, [
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('inhaled_oxygen_concentration', Types::DECIMAL, [
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'pid');
        $table->addUniqueIndex(['uuid'], 'uuid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_vitals');
    }
}
