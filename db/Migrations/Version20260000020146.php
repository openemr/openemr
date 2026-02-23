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
 * Ccda table mapping table
 */
final class Version20260000020146 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create ccda_table_mapping table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('ccda_table_mapping');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('ccda_component', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ccda_component_section', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('form_dir', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('form_type', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('form_table', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('user_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('deleted', Types::BOOLEAN, ['default' => 0]);
        $table->addColumn('timestamp', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ccda_table_mapping');
    }
}
