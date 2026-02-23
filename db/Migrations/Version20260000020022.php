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
 * Person patient link table
 */
final class Version20260000020022 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create person_patient_link table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('person_patient_link');
        $table->addOption('comment', 'Links person records to patient_data records when person becomes patient');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('person_id', Types::BIGINT, ['comment' => 'FK to person.id']);
        $table->addColumn('patient_id', Types::BIGINT, ['comment' => 'FK to patient_data.id']);
        $table->addColumn('linked_date', Types::DATETIME_MUTABLE, ['comment' => 'When the link was created', 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('linked_by', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'FK to users.id - who created the link',
        ]);
        $table->addColumn('link_method', Types::STRING, ['notnull' => false, 
            'length' => 50,
            'default' => 'manual',
            'comment' => 'How link was created: manual, auto_detected, migrated, import',
        ]);
        $table->addColumn('notes', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'Optional notes about why/how they were linked']);
        $table->addColumn('active', Types::BOOLEAN, ['default' => 1, 'comment' => 'Whether link is active (allows soft delete)']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['person_id'], 'idx_ppl_person');
        $table->addIndex(['patient_id'], 'idx_ppl_patient');
        $table->addIndex(['active'], 'idx_ppl_active');
        $table->addIndex(['linked_date'], 'idx_ppl_linked_date');
        $table->addIndex(['link_method'], 'idx_ppl_method');
        $table->addUniqueIndex(['person_id', 'patient_id', 'active'], 'unique_active_link');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE person_patient_link');
    }
}
