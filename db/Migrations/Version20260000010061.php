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
 * Registry table
 */
final class Version20260000010061 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create registry table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('registry');
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('state', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('directory', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('sql_run', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('unpackaged', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('priority', Types::INTEGER, ['default' => 0]);
        $table->addColumn('category', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('nickname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('patient_encounter', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('therapy_group_encounter', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('aco_spec', Types::STRING, ['length' => 63, 'default' => 'encounters|notes']);
        $table->addColumn('form_foreign_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'An id to a form repository. Primarily questionnaire_repository.',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('registry');
    }
}
