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
 * Addresses table - stores address information linked to other entities via foreign_id
 */
final class Version20260000010001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create addresses table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('addresses');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('line1', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('line2', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('city', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('state', Types::STRING, ['length' => 35, 'notnull' => false]);
        $table->addColumn('zip', Types::STRING, ['length' => 10, 'notnull' => false]);
        $table->addColumn('plus_four', Types::STRING, ['length' => 4, 'notnull' => false]);
        $table->addColumn('country', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('foreign_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('district', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'comment' => 'The county or district of the address',
        ]);
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
        $schema->dropTable('addresses');
    }
}
