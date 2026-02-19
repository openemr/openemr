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
 * Phone numbers table
 */
final class Version20260000010058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create phone_numbers table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('phone_numbers');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('country_code', Types::STRING, [
            'length' => 5,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('area_code', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('prefix', Types::STRING, [
            'length' => 3,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('number', Types::STRING, [
            'length' => 4,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('type', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('foreign_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['foreign_id'], 'foreign_id');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('phone_numbers');
    }
}
