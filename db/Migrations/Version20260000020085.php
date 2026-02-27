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
 * Patient portal menu table
 */
final class Version20260000020085 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create patient_portal_menu table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('patient_portal_menu');
        $table->addColumn('patient_portal_menu_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('patient_portal_menu_group_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('menu_name', Types::STRING, [
            'length' => 40,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('menu_order', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('menu_status', Types::SMALLINT, ['notnull' => false, 'default' => 1]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('patient_portal_menu_id')
                ->create()
        );

        $table->addOption('engine', 'INNODB');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE patient_portal_menu');
    }
}
