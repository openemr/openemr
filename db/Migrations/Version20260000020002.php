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
 * Amendments table
 */
final class Version20260000020002 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create amendments table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('amendments');
        $table->addColumn('amendment_id', Types::INTEGER, ['autoincrement' => true, 'comment' => 'Amendment ID']);
        $table->addColumn('amendment_date', Types::DATE_MUTABLE, ['comment' => 'Amendement request date']);
        $table->addColumn('amendment_by', Types::STRING, ['length' => 50, 'comment' => 'Amendment requested from']);
        $table->addColumn('amendment_status', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'comment' => 'Amendment status accepted/rejected/null',
        ]);
        $table->addColumn('pid', Types::BIGINT, ['comment' => 'Patient ID from patient_data']);
        $table->addColumn('amendment_desc', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'Amendment Details']);
        $table->addColumn('created_by', Types::INTEGER, ['comment' => 'references users.id for session owner']);
        $table->addColumn('modified_by', Types::INTEGER, ['notnull' => false, 'comment' => 'references users.id for session owner']);
        $table->addColumn('created_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'created time']);
        $table->addColumn('modified_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'comment' => 'modified time']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('amendment_id')
                ->create()
        );
        $table->addIndex(['pid'], 'amendment_pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE amendments');
    }
}
