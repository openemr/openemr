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
 * Oauth trusted user table
 */
final class Version20260000020188 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create oauth_trusted_user table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('oauth_trusted_user');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('user_id', Types::STRING, [
            'length' => 80,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('client_id', Types::STRING, [
            'length' => 80,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('scope', Types::TEXT, ['length' => 65535]);
        $table->addColumn('persist_login', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('code', Types::TEXT, ['length' => 65535]);
        $table->addColumn('session_cache', Types::TEXT, ['length' => 65535]);
        $table->addColumn('grant_type', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['user_id'], 'accounts_id');
        $table->addIndex(['client_id'], 'clients_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE oauth_trusted_user');
    }
}
