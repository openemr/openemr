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
 * Procedure providers table
 */
final class Version20260000020121 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create procedure_providers table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('procedure_providers');
        $table->addColumn('ppid', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('name', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('npi', Types::STRING, ['length' => 15, 'default' => '']);
        $table->addColumn('send_app_id', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Sending application ID (MSH-3.1)',
        ]);
        $table->addColumn('send_fac_id', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Sending facility ID (MSH-4.1)',
        ]);
        $table->addColumn('recv_app_id', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Receiving application ID (MSH-5.1)',
        ]);
        $table->addColumn('recv_fac_id', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Receiving facility ID (MSH-6.1)',
        ]);
        $table->addColumn('DorP', Types::STRING, ['fixed' => true, 
            'length' => 1,
            'default' => 'D',
            'comment' => 'Debugging or Production (MSH-11)',
        ]);
        $table->addColumn('direction', Types::STRING, ['fixed' => true, 
            'length' => 1,
            'default' => 'B',
            'comment' => 'Bidirectional or Results-only',
        ]);
        $table->addColumn('protocol', Types::STRING, ['length' => 15, 'default' => 'DL']);
        $table->addColumn('remote_host', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('login', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('password', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('orders_path', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('results_path', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('lab_director', Types::BIGINT, ['default' => 0]);
        $table->addColumn('active', Types::BOOLEAN, ['default' => 1]);
        $table->addColumn('type', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('ppid')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procedure_providers');
    }
}
