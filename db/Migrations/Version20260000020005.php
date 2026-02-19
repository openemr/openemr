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
 * Api token table
 */
final class Version20260000020005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create api_token table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('api_token');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('user_id', Types::STRING, [
            'length' => 40,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('token', Types::STRING, [
            'length' => 128,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('expiry', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('client_id', Types::STRING, [
            'length' => 80,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('scope', Types::TEXT, ['comment' => 'json encoded']);
        $table->addColumn('revoked', Types::SMALLINT, ['default' => 0, 'comment' => '1=revoked,0=not revoked']);
        $table->addColumn('context', Types::TEXT, ['comment' => 'context values that change/govern how access token are used']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['token'], 'token');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('api_token');
    }
}
