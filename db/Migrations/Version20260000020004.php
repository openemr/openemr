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
 * Api log table
 */
final class Version20260000020004 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create api_log table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('api_log');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('log_id', Types::INTEGER);
        $table->addColumn('user_id', Types::BIGINT);
        $table->addColumn('patient_id', Types::BIGINT);
        $table->addColumn('ip_address', Types::STRING, ['length' => 255]);
        $table->addColumn('method', Types::STRING, ['length' => 20]);
        $table->addColumn('request', Types::STRING, ['length' => 255]);
        $table->addColumn('request_url', Types::TEXT, ['length' => 65535]);
        $table->addColumn('request_body', Types::TEXT);
        $table->addColumn('response', Types::TEXT);
        $table->addColumn('created_time', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_log');
    }
}
