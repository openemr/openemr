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
 * Therapy groups participants table
 */
final class Version20260000020166 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create therapy_groups_participants table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('therapy_groups_participants');
        $table->addColumn('group_id', Types::INTEGER);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('group_patient_status', Types::INTEGER);
        $table->addColumn('group_patient_start', Types::DATE_MUTABLE);
        $table->addColumn('group_patient_end', Types::DATE_MUTABLE);
        $table->addColumn('group_patient_comment', Types::TEXT, ['length' => 65535]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('group_id', 'pid')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE therapy_groups_participants');
    }
}
