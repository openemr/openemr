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
 * Form vitals table
 */
final class Version20260000010015 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_vitals table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_vitals');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => 0]);
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
        $table->addColumn('authorized', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('activity', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
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
        $table->addColumn('weight', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('height', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('temperature', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('temp_method', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pulse', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('respiration', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('note', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('BMI', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.0,
        ]);
        $table->addColumn('BMI_status', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('waist_circ', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('head_circ', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('oxygen_saturation', Types::DECIMAL, ['notnull' => false, 
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('oxygen_flow_rate', Types::DECIMAL, ['notnull' => false, 
            'precision' => 12,
            'scale' => 6,
            'default' => 0.00,
        ]);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ped_weight_height', Types::DECIMAL, ['notnull' => false, 
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('ped_bmi', Types::DECIMAL, ['notnull' => false, 
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('ped_head_circ', Types::DECIMAL, ['notnull' => false, 
            'precision' => 6,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('inhaled_oxygen_concentration', Types::DECIMAL, ['notnull' => false, 
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

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_vitals');
    }
}
