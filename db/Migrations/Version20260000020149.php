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
 * Form care plan table
 */
final class Version20260000020149 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_care_plan table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_care_plan');
        $table->addColumn('id', Types::BIGINT);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('groupname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('authorized', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('activity', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('code', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('codetext', Types::TEXT);
        $table->addColumn('description', Types::TEXT);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('care_plan_type', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('note_related_to', Types::TEXT);
        $table->addColumn('date_end', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('reason_code', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('reason_description', Types::TEXT);
        $table->addColumn('reason_date_low', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'The date the reason was recorded',
        ]);
        $table->addColumn('reason_date_high', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'The date the explanation reason for the care plan entry value ends',
        ]);
        $table->addColumn('reason_status', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('plan_status', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
            'comment' => 'Care Plan status (e.g., draft, active, completed, etc)',
        ]);
        $table->addColumn('proposed_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'Target or Achieve-by date for the goal']);

        $table->addIndex(['plan_status', 'date', 'date_end'], 'idx_status_date');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_care_plan');
    }
}
