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
 * Fee schedule table
 */
final class Version20260000020042 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create fee_schedule table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('fee_schedule');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('insurance_company_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('plan', Types::STRING, ['length' => 20, 'default' => '']);
        $table->addColumn('code', Types::STRING, ['length' => 10, 'default' => '']);
        $table->addColumn('modifier', Types::STRING, ['length' => 2, 'default' => '']);
        $table->addColumn('type', Types::STRING, ['length' => 20, 'default' => '']);
        $table->addColumn('fee', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('effective_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addUniqueIndex(['insurance_company_id', 'plan', 'code', 'modifier', 'type', 'effective_date'], 'ins_plan_code_mod_type_date');
        $table->addOption('engine', 'InnoDb');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE fee_schedule');
    }
}
