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
 * Erx rx log table
 */
final class Version20260000020038 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create erx_rx_log table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('erx_rx_log');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('prescription_id', Types::INTEGER);
        $table->addColumn('date', Types::STRING, ['length' => 25]);
        $table->addColumn('time', Types::STRING, ['length' => 15]);
        $table->addColumn('code', Types::INTEGER);
        $table->addColumn('status', Types::TEXT, ['length' => 65535]);
        $table->addColumn('message_id', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('read', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE erx_rx_log');
    }
}
