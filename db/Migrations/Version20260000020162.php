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
 * Product registration table
 */
final class Version20260000020162 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create product_registration table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('product_registration');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('email', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('opt_out', Types::SMALLINT, ['notnull' => false]);
        $table->addColumn('auth_by_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('telemetry_disabled', Types::SMALLINT, ['notnull' => false, 'comment' => '1 opted out, disabled. NULL ask. 0 use option scopes']);
        $table->addColumn('last_ask_date', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('options', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'JSON array of scope options']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product_registration');
    }
}
