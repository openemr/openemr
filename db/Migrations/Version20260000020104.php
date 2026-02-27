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
 * Users secure table
 */
final class Version20260000020104 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create users_secure table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('users_secure');
        $table->addColumn('id', Types::BIGINT);
        $table->addColumn('username', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('password', Types::STRING, ['length' => 255]);
        $table->addColumn('last_update_password', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('last_update', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('password_history1', Types::STRING, ['length' => 255]);
        $table->addColumn('password_history2', Types::STRING, ['length' => 255]);
        $table->addColumn('password_history3', Types::STRING, ['length' => 255]);
        $table->addColumn('password_history4', Types::STRING, ['length' => 255]);
        $table->addColumn('last_challenge_response', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('login_work_area', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('total_login_fail_counter', Types::BIGINT, ['notnull' => false, 'default' => 0]);
        $table->addColumn('login_fail_counter', Types::INTEGER, ['notnull' => false, 'default' => 0]);
        $table->addColumn('last_login_fail', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('auto_block_emailed', Types::SMALLINT, ['notnull' => false, 'default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['id', 'username'], 'USERNAME_ID');
        $table->addOption('engine', 'InnoDb');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users_secure');
    }
}
