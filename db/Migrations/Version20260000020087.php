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
 * Patient tracker table
 */
final class Version20260000020087 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create patient_tracker table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('patient_tracker');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('apptdate', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('appttime', Types::TIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('eid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('original_user', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'This is the user that created the original record',
        ]);
        $table->addColumn('encounter', Types::BIGINT, ['default' => 0]);
        $table->addColumn('lastseq', Types::STRING, [
            'length' => 4,
            'default' => '',
            'comment' => 'The element file should contain this number of elements',
        ]);
        $table->addColumn('random_drug_test', Types::SMALLINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'NULL if not randomized. If randomized, 0 is no, 1 is yes',
        ]);
        $table->addColumn('drug_screen_completed', Types::SMALLINT, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['eid'], 'eid');
        $table->addIndex(['pid'], 'pid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE patient_tracker');
    }
}
