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
 * Billing table
 */
final class Version20260000010004 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create billing table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('billing');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('code_type', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('code', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('provider_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('user', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('groupname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('authorized', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('code_text', Types::TEXT);
        $table->addColumn('billed', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('activity', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('payer_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('bill_process', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('bill_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('process_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('process_file', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('modifier', Types::STRING, [
            'length' => 12,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('units', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('fee', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('justify', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('target', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_partner_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('ndc_info', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('notecodes', Types::STRING, ['length' => 25, 'default' => '']);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pricelevel', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('revenue_code', Types::STRING, [
            'length' => 6,
            'default' => '',
            'comment' => 'Item revenue code',
        ]);
        $table->addColumn('chargecat', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Charge category or customer',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE billing');
    }
}
