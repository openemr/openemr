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
 * Patient history table
 */
final class Version20260000020084 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create patient_history table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('patient_history');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE);
        $table->addColumn('care_team_provider', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('care_team_facility', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('history_type_key', Types::STRING, ['length' => 36, 'notnull' => false, 'default' => null]);
        $table->addColumn('previous_name_prefix', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('previous_name_first', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('previous_name_middle', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('previous_name_last', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('previous_name_suffix', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('previous_name_enddate', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id the user that first created this record',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');
        $table->addIndex(['pid'], 'pid_idx');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE patient_history');
    }
}
