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
 * Export job table
 */
final class Version20260000020190 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create export_job table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('export_job');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('user_id', Types::STRING, ['length' => 40]);
        $table->addColumn('client_id', Types::STRING, ['length' => 80]);
        $table->addColumn('status', Types::STRING, ['length' => 40]);
        $table->addColumn('start_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('resource_include_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('output_format', Types::STRING, ['length' => 128]);
        $table->addColumn('request_uri', Types::STRING, ['length' => 128]);
        $table->addColumn('resources', Types::TEXT);
        $table->addColumn('output', Types::TEXT);
        $table->addColumn('errors', Types::TEXT);
        $table->addColumn('access_token_id', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE export_job');
    }
}
