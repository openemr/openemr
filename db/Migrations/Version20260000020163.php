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
 * Codes history table
 */
final class Version20260000020163 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create codes_history table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('codes_history');
        $table->addColumn('log_id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('code', Types::STRING, ['notnull' => false, 'length' => 25]);
        $table->addColumn('modifier', Types::STRING, ['notnull' => false, 'length' => 12]);
        $table->addColumn('active', Types::BOOLEAN, ['notnull' => false, 'default' => null]);
        $table->addColumn('diagnosis_reporting', Types::BOOLEAN, ['notnull' => false]);
        $table->addColumn('financial_reporting', Types::BOOLEAN, ['notnull' => false, 'default' => null]);
        $table->addColumn('category', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('code_type_name', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('code_text', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('code_text_short', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('prices', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('action_type', Types::STRING, ['notnull' => false, 'length' => 25]);
        $table->addColumn('update_by', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('log_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE codes_history');
    }
}
