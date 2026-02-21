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
 * Openemr modules table
 */
final class Version20260000010053 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create openemr_modules table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('openemr_modules');
        $table->addColumn('pn_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('pn_name', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pn_type', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pn_displayname', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pn_description', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pn_regid', Types::INTEGER, ['unsigned' => true, 'default' => 0]);
        $table->addColumn('pn_directory', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pn_version', Types::STRING, [
            'length' => 10,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pn_admin_capable', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('pn_user_capable', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('pn_state', Types::SMALLINT, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pn_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE openemr_modules');
    }
}
