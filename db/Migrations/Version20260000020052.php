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
 * Icd10 gem pcs 10 9 table
 */
final class Version20260000020052 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create icd10_gem_pcs_10_9 table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('icd10_gem_pcs_10_9');
        $table->addColumn('map_id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('pcs_icd10_source', Types::STRING, [
            'length' => 7,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pcs_icd9_target', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('flags', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('active', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('revision', Types::INTEGER, ['notnull' => false, 'default' => 0]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('map_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE icd10_gem_pcs_10_9');
    }
}
