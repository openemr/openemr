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
 * Issue encounter table
 */
final class Version20260000020060 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create issue_encounter table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('issue_encounter');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
            'comment' => 'UUID for this issue encounter record, for data exchange purposes',
        ]);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('list_id', Types::INTEGER);
        $table->addColumn('encounter', Types::INTEGER);
        $table->addColumn('resolved', Types::SMALLINT);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to users.id for the user that entered in the issue encounter data',
        ]);
        $table->addColumn('updated_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to users.id for the user that last updated the issue encounter data',
        ]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'timestamp when this issue encounter record was created']);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'timestamp when this issue encounter record was last updated']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['pid', 'list_id', 'encounter'], 'uniq_issue_key');
        $table->addUniqueIndex(['uuid'], 'uuid_unique');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE issue_encounter');
    }
}
