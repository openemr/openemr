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
 * Api refresh token table
 */
final class Version20260000020006 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create api_refresh_token table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('api_refresh_token');
        $table->addOption('comment', 'Holds information about api refresh tokens.');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('user_id', Types::STRING, [
            'length' => 40,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('client_id', Types::STRING, [
            'length' => 80,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('token', Types::STRING, ['length' => 128]);
        $table->addColumn('expiry', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('revoked', Types::BOOLEAN, ['default' => 0, 'comment' => '1=revoked,0=not revoked']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['client_id', 'user_id'], 'api_refresh_token_usr_client_idx');
        $table->addUniqueIndex(['token'], 'token');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_refresh_token');
    }
}
