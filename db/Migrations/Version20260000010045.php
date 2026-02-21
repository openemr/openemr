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
 * Insurance numbers table
 */
final class Version20260000010045 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create insurance_numbers table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('insurance_numbers');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('provider_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('insurance_company_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('provider_number', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('rendering_provider_number', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('group_number', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('provider_number_type', Types::STRING, [
            'length' => 4,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('rendering_provider_number_type', Types::STRING, [
            'length' => 4,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE insurance_numbers');
    }
}
