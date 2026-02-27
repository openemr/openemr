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
 * Benefit eligibility table
 */
final class Version20260000020186 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create benefit_eligibility table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('benefit_eligibility');
        $table->addColumn('response_id', Types::BIGINT);
        $table->addColumn('verification_id', Types::BIGINT);
        $table->addColumn('type', Types::STRING, [
            'length' => 4,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('benefit_type', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('start_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('end_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('coverage_level', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('coverage_type', Types::STRING, [
            'length' => 512,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('plan_type', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('plan_description', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('coverage_period', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('amount', Types::DECIMAL, [
            'precision' => 5,
            'scale' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('percent', Types::DECIMAL, [
            'precision' => 3,
            'scale' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('network_ind', Types::STRING, [
            'length' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('message', Types::STRING, [
            'length' => 512,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('response_status', Types::ENUM, [
            'notnull' => false,
            'default' => 'A',
            'values' => ['A', 'D'],
        ]);
        $table->addColumn('response_create_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('response_modify_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE benefit_eligibility');
    }
}
