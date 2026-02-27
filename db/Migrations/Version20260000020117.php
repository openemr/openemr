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
 * Users facility table
 */
final class Version20260000020117 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create users_facility table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('users_facility');
        $table->addOption('comment', 'joins users or patient_data to facility table');
        $table->addColumn('tablename', Types::STRING, ['length' => 64]);
        $table->addColumn('table_id', Types::INTEGER);
        $table->addColumn('facility_id', Types::INTEGER);
        $table->addColumn('warehouse_id', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('tablename', 'table_id', 'facility_id', 'warehouse_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users_facility');
    }
}
