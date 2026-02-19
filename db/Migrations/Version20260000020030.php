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
 * Drug inventory table
 */
final class Version20260000020030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create drug_inventory table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('drug_inventory');
        $table->addColumn('inventory_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('drug_id', Types::INTEGER);
        $table->addColumn('lot_number', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('expiration', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('manufacturer', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('on_hand', Types::INTEGER, ['default' => 0]);
        $table->addColumn('warehouse_id', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('vendor_id', Types::BIGINT, ['default' => 0]);
        $table->addColumn('last_notify', Types::DATE_MUTABLE, ['notnull' => false]);
        $table->addColumn('destroy_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('destroy_method', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('destroy_witness', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('destroy_notes', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('inventory_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('drug_inventory');
    }
}
