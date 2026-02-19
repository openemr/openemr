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
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Oauth trusted user table
 */
final class Version20260000020188 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create oauth_trusted_user table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('oauth_trusted_user');
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
        $table->addColumn('scope', Types::TEXT);
        $table->addColumn('persist_login', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('code', Types::TEXT);
        $table->addColumn('session_cache', Types::TEXT);
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
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('oauth_trusted_user');
    }
}
