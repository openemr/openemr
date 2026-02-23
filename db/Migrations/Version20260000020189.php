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
 * X12 remote tracker table
 */
final class Version20260000020189 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create x12_remote_tracker table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('x12_remote_tracker');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('x12_partner_id', Types::INTEGER);
        $table->addColumn('x12_filename', Types::STRING, ['length' => 255]);
        $table->addColumn('status', Types::STRING, ['length' => 255]);
        $table->addColumn('claims', Types::TEXT, ['length' => 65535]);
        $table->addColumn('messages', Types::TEXT, ['length' => 65535]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE x12_remote_tracker');
    }
}
