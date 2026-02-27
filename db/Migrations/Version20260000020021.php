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
 * Contact relation table
 */
final class Version20260000020021 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create contact_relation table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('contact_relation');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('contact_id', Types::BIGINT);
        $table->addColumn('target_table', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('target_id', Types::BIGINT);
        $table->addColumn('active', Types::BOOLEAN, ['notnull' => false, 'default' => 1]);
        $table->addColumn('role', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('relationship', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('contact_priority', Types::INTEGER, ['notnull' => false, 'default' => 1, 'comment' => '1=highest priority']);
        $table->addColumn('is_primary_contact', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('is_emergency_contact', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('can_make_medical_decisions', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('can_receive_medical_info', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('start_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('end_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('notes', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('created_date', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id',
        ]);
        $table->addColumn('updated_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'users.id',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['contact_id'], 'contact_id');
        $table->addIndex(['target_table', 'target_id'], 'idx_contact_target_table');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_relation');
    }
}
