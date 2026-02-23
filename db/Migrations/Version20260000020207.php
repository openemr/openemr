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
 * Care teams table
 */
final class Version20260000020207 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create care_teams table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('care_teams');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pid', Types::INTEGER, ['comment' => 'fk to patient_data.pid']);
        $table->addColumn('status', Types::STRING, [
            'length' => 100,
            'default' => 'active',
            'comment' => 'fk to list_options.option_id where list_id=Care_Team_Status',
        ]);
        $table->addColumn('team_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('note', Types::TEXT, ['length' => 65535]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('date_updated', Types::DATETIME_MUTABLE);
        $table->addColumn('created_by', Types::BIGINT, ['comment' => 'fk to users.id for user who created this record']);
        $table->addColumn('updated_by', Types::BIGINT, ['comment' => 'fk to users.id for user who last updated this record']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE care_teams');
    }
}
