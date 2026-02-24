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
 * Ccda table
 */
final class Version20260000020145 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create ccda table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('ccda');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('ccda_data', Types::TEXT, ['notnull' => false]);
        $table->addColumn('time', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('status', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('updated_date', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('user_id', Types::STRING, ['length' => 50, 'notnull' => false]);
        $table->addColumn('couch_docid', Types::STRING, ['length' => 100, 'notnull' => false]);
        $table->addColumn('couch_revid', Types::STRING, ['length' => 100, 'notnull' => false]);
        $table->addColumn('hash', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('view', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('transfer', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('emr_transfer', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('encrypted', Types::BOOLEAN, ['default' => 0, 'comment' => '0->No,1->Yes']);
        $table->addColumn('transaction_id', Types::BIGINT, ['notnull' => false, 'comment' => 'fk to transaction referral record']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');
        $table->addUniqueIndex(['pid', 'encounter', 'time'], 'unique_key');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ccda');
    }
}
