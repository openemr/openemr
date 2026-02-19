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
 * Drug sales table
 */
final class Version20260000020031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create drug_sales table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('drug_sales');
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
            'comment' => 'UUID for this drug sales record, for data exchange purposes',
        ]);
        $table->addColumn('sale_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('drug_id', Types::INTEGER);
        $table->addColumn('inventory_id', Types::INTEGER);
        $table->addColumn('prescription_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('encounter', Types::INTEGER, ['default' => 0]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('sale_date', Types::DATE_MUTABLE);
        $table->addColumn('quantity', Types::INTEGER, ['default' => 0]);
        $table->addColumn('fee', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'default' => 0.00,
        ]);
        $table->addColumn('billed', Types::SMALLINT, ['default' => 0, 'comment' => 'indicates if the sale is posted to accounting']);
        $table->addColumn('xfer_inventory_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('distributor_id', Types::BIGINT, ['default' => 0, 'comment' => 'references users.id']);
        $table->addColumn('notes', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('bill_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pricelevel', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('selector', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'references drug_templates.selector',
        ]);
        $table->addColumn('trans_type', Types::SMALLINT, ['default' => 1, 'comment' => '1=sale, 2=purchase, 3=return, 4=transfer, 5=adjustment']);
        $table->addColumn('chargecat', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('pharmacy_supply_type', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to list_options.option_id where list_id=pharmacy_supply_type to indicate type of dispensing first order, refil, emergency, partial order, etc',
        ]);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('updated_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to users.id for user that last updated this entry',
        ]);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to users.id for user that created this entry',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('sale_id')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('drug_sales');
    }
}
