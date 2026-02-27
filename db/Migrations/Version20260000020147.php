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
 * External procedures table
 */
final class Version20260000020147 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create external_procedures table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('external_procedures');
        $table->addColumn('ep_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('ep_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('ep_code_type', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ep_code', Types::STRING, [
            'length' => 9,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ep_pid', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('ep_encounter', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('ep_code_text', Types::TEXT, ['notnull' => false]);
        $table->addColumn('ep_facility_id', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ep_external_id', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('ep_id')
                ->create()
        );
        $table->addIndex(['ep_pid'], 'ep_pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE external_procedures');
    }
}
