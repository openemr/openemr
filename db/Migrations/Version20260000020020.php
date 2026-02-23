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
 * Person table
 */
final class Version20260000020020 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create person table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('person');
        $table->addOption('comment', 'Core person demographics - contact info in contact_telecom');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('title', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
            'comment' => 'Mr., Mrs., Dr., etc.',
        ]);
        $table->addColumn('first_name', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('middle_name', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('last_name', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('preferred_name', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
            'comment' => 'Name person prefers to be called',
        ]);
        $table->addColumn('gender', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('birth_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('death_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('marital_status', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('race', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ethnicity', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('preferred_language', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
            'comment' => 'ISO 639-1 code',
        ]);
        $table->addColumn('communication', Types::STRING, [
            'length' => 254,
            'notnull' => false,
            'default' => null,
            'comment' => 'Communication preferences/needs',
        ]);
        $table->addColumn('ssn', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
            'comment' => 'Should be encrypted in application',
        ]);
        $table->addColumn('active', Types::SMALLINT, ['notnull' => false, 'default' => 1, 'comment' => '1=active, 0=inactive']);
        $table->addColumn('inactive_reason', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('inactive_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('created_date', Types::DATETIME_MUTABLE);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id',
        ]);
        $table->addColumn('updated_date', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('updated_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['last_name', 'first_name'], 'idx_person_name');
        $table->addIndex(['birth_date'], 'idx_person_dob');
        $table->addIndex(['last_name', 'first_name', 'birth_date'], 'idx_person_search');
        $table->addIndex(['active'], 'idx_person_active');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE person');
    }
}
