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
 * Eligibility verification table
 */
final class Version20260000020035 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create eligibility_verification table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('eligibility_verification');
        $table->addColumn('verification_id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('response_id', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('insurance_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('eligibility_check_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('copay', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('deductible', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('deductiblemet', Types::ENUM, [
            'default' => 'Y',
            'values' => ['Y', 'N'],
        ]);
        $table->addColumn('create_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('verification_id')
                ->create()
        );
        $table->addIndex(['insurance_id'], 'insurance_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE eligibility_verification');
    }
}
