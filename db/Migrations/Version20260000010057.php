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
 * Pharmacies table
 */
final class Version20260000010057 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create pharmacies table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('pharmacies');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('transmit_method', Types::INTEGER, ['default' => 1]);
        $table->addColumn('email', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ncpdp', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('npi', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pharmacies');
    }
}
