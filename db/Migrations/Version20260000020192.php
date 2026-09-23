<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

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
        $this->addPrimaryKey($table, 'id');
        $table->addUniqueIndex(['uuid'], 'unq_uuid');
        $table->addIndex(['pid'], 'idx_pid');
        $table->addIndex(['encounter'], 'idx_encounter');
        $table->addIndex(['calculation_id'], 'idx_calculation_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_vitals_calculation');
    }
}
