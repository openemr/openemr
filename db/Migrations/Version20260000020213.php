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
 * Form history sdoh health concerns table
 */
final class Version20260000020213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_history_sdoh_health_concerns table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_history_sdoh_health_concerns');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('sdoh_history_id', Types::BIGINT, [
            'unsigned' => true,
            'comment' => 'FK to form_history_sdoh.id',
        ]);
        $table->addColumn('health_concern_id', Types::BIGINT, [
            'comment' => 'FK to lists.id where type=health_concern or medical_problem',
        ]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE);
        $table->addColumn('created_by', Types::BIGINT, [
            'notnull' => false,
            'comment' => 'FK to users.id',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['sdoh_history_id', 'health_concern_id'], 'unique_sdoh_concern');
        $table->addIndex(['sdoh_history_id'], 'idx_sdoh_history');
        $table->addIndex(['health_concern_id'], 'idx_health_concern');
        $table->addOption('engine', 'InnoDB');
        $table->addOption('comment', 'Links SDOH assessments to health concern conditions');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_history_sdoh_health_concerns');
    }
}
