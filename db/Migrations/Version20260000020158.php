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
 * Form eye mag orders table
 */
final class Version20260000020158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_eye_mag_orders table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_eye_mag_orders');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('form_id', Types::INTEGER);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('ORDER_DETAILS', Types::STRING, ['length' => 255]);
        $table->addColumn('ORDER_STATUS', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ORDER_PRIORITY', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ORDER_DATE_PLACED', Types::DATE_MUTABLE);
        $table->addColumn('ORDER_PLACED_BYWHOM', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ORDER_DATE_COMPLETED', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('ORDER_COMPLETED_BYWHOM', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['pid', 'ORDER_DETAILS', 'ORDER_DATE_PLACED'], 'VISIT_ID');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_eye_mag_orders');
    }
}
