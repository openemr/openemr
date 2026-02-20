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
 * Drugs table
 */
final class Version20260000020033 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create drugs table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('drugs');
        $table->addColumn('drug_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('name', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('ndc_number', Types::STRING, ['length' => 20, 'default' => '']);
        $table->addColumn('on_order', Types::INTEGER, ['default' => 0]);
        $table->addColumn('reorder_point', Types::FLOAT, ['default' => 0.0]);
        $table->addColumn('max_level', Types::FLOAT, ['default' => 0.0]);
        $table->addColumn('last_notify', Types::DATE_MUTABLE, ['notnull' => false]);
        $table->addColumn('reactions', Types::TEXT);
        $table->addColumn('form', Types::STRING, ['length' => 31, 'default' => 0]);
        $table->addColumn('size', Types::STRING, ['length' => 25, 'default' => '']);
        $table->addColumn('unit', Types::STRING, ['length' => 31, 'default' => 0]);
        $table->addColumn('route', Types::STRING, ['length' => 31, 'default' => 0]);
        $table->addColumn('substitute', Types::INTEGER, ['default' => 0]);
        $table->addColumn('related_code', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'may reference a related codes.code',
        ]);
        $table->addColumn('cyp_factor', Types::FLOAT, ['default' => 0, 'comment' => 'quantity representing a years supply']);
        $table->addColumn('active', Types::SMALLINT, ['default' => 1, 'comment' => '0 = inactive, 1 = active']);
        $table->addColumn('allow_combining', Types::SMALLINT, ['default' => 0, 'comment' => '1 = allow filling an order from multiple lots']);
        $table->addColumn('allow_multiple', Types::SMALLINT, ['default' => 1, 'comment' => '1 = allow multiple lots at one warehouse']);
        $table->addColumn('drug_code', Types::STRING, ['length' => 25, 'notnull' => false]);
        $table->addColumn('consumable', Types::SMALLINT, ['default' => 0, 'comment' => '1 = will not show on the fee sheet']);
        $table->addColumn('dispensable', Types::SMALLINT, ['default' => 1, 'comment' => '0 = pharmacy elsewhere, 1 = dispensed here']);
        $table->addColumn('date_created', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('drug_id')
                ->create()
        );
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE drugs');
    }
}
