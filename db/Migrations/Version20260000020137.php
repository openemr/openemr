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
 * Product warehouse table
 */
final class Version20260000020137 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create product_warehouse table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('product_warehouse');
        $table->addColumn('pw_drug_id', Types::INTEGER);
        $table->addColumn('pw_warehouse', Types::STRING, ['length' => 31]);
        $table->addColumn('pw_min_level', Types::FLOAT, ['default' => 0]);
        $table->addColumn('pw_max_level', Types::FLOAT, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pw_drug_id', 'pw_warehouse')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product_warehouse');
    }
}
