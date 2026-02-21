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
 * Icd10 reimbr pcs 9 10 table
 */
final class Version20260000020056 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create icd10_reimbr_pcs_9_10 table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('icd10_reimbr_pcs_9_10');
        $table->addColumn('map_id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('code', Types::STRING, ['length' => 8]);
        $table->addColumn('code_cnt', Types::SMALLINT);
        $table->addColumn('ICD9_01', Types::STRING, ['length' => 5]);
        $table->addColumn('ICD9_02', Types::STRING, ['length' => 5]);
        $table->addColumn('ICD9_03', Types::STRING, ['length' => 5]);
        $table->addColumn('ICD9_04', Types::STRING, ['length' => 5]);
        $table->addColumn('ICD9_05', Types::STRING, ['length' => 5]);
        $table->addColumn('ICD9_06', Types::STRING, ['length' => 5]);
        $table->addColumn('active', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('revision', Types::INTEGER, ['default' => 0]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('map_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE icd10_reimbr_pcs_9_10');
    }
}
