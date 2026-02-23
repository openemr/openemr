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
 * Onetime auth table
 */
final class Version20260000020202 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create onetime_auth table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('onetime_auth');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('create_user_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('context', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('access_count', Types::INTEGER, ['default' => 0]);
        $table->addColumn('remote_ip', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('onetime_pin', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
            'comment' => 'Max 10 numeric. Default 6',
        ]);
        $table->addColumn('onetime_token', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('redirect_url', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('expires', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('last_accessed', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('scope', Types::TEXT, ['notnull' => false, 'length' => 255, 'comment' => 'context scope for this token']);
        $table->addColumn('profile', Types::TEXT, ['notnull' => false, 'length' => 255, 'comment' => 'profile of scope for this token']);
        $table->addColumn('onetime_actions', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'JSON array of actions that can be performed with this token']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid', 'onetime_token'], 'pid', [], ['lengths' => [null, 255]]);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE onetime_auth');
    }
}
