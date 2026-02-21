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
 * Pnotes table
 */
final class Version20260000010059 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create pnotes table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('pnotes');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('body', Types::TEXT);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
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
        $table->addColumn('activity', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('authorized', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('title', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('assigned_to', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('deleted', Types::SMALLINT, ['default' => 0, 'comment' => 'flag indicates note is deleted']);
        $table->addColumn('message_status', Types::STRING, ['length' => 20, 'default' => 'New']);
        $table->addColumn('portal_relation', Types::STRING, ['length' => 100, 'notnull' => false]);
        $table->addColumn('is_msg_encrypted', Types::SMALLINT, ['default' => 0, 'comment' => 'Whether messsage encrypted 0-Not encrypted, 1-Encrypted']);
        $table->addColumn('update_by', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('update_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pnotes');
    }
}
