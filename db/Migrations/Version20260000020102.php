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
 * Payment processing audit table
 */
final class Version20260000020102 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create payment_processing_audit table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('payment_processing_audit');
        $table->addColumn('uuid', Types::BINARY, ['length' => 16, 'default' => '']);
        $table->addColumn('service', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('success', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('action_name', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('amount', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ticket', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('transaction_id', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('audit_data', Types::TEXT, ['length' => 65535]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('map_uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('map_transaction_id', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('reverted', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('revert_action_name', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('revert_transaction_id', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('revert_audit_data', Types::TEXT, ['length' => 65535]);
        $table->addColumn('revert_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('uuid')
                ->create()
        );
        $table->addIndex(['pid'], null);
        $table->addIndex(['success'], null);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE payment_processing_audit');
    }
}
