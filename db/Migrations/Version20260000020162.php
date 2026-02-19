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
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Product registration table
 */
final class Version20260000020162 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create product_registration table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('product_registration');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('email', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('opt_out', Types::SMALLINT, ['notnull' => false]);
        $table->addColumn('auth_by_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('telemetry_disabled', Types::SMALLINT, ['notnull' => false, 'comment' => '1 opted out, disabled. NULL ask. 0 use option scopes']);
        $table->addColumn('last_ask_date', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('options', Types::TEXT, ['comment' => 'JSON array of scope options']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('product_registration');
    }
}
