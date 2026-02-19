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
 * Jwt grant history table
 */
final class Version20260000020215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create jwt_grant_history table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('jwt_grant_history');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('jti', Types::STRING, [
            'length' => 100,
            'comment' => 'Unique JWT id',
        ]);
        $table->addColumn('client_id', Types::STRING, [
            'length' => 80,
            'comment' => 'FK oauth2_clients.client_id',
        ]);
        $table->addColumn('jti_exp', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'comment' => 'jwt exp claim when the jwt expires',
        ]);
        $table->addColumn('creation_date', Types::DATETIME_MUTABLE, [
            'comment' => 'datetime the grant authorization was requested',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['jti'], 'jti');
        $table->addOption('engine', 'InnoDB');
        $table->addOption('comment', 'Holds JWT authorization grant ids to prevent replay attacks');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('jwt_grant_history');
    }
}
