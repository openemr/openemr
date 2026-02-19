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
 * Preference value sets table
 */
final class Version20260000020212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create preference_value_sets table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('preference_value_sets');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('loinc_code', Types::STRING, ['length' => 50]);
        $table->addColumn('answer_code', Types::STRING, ['length' => 100]);
        $table->addColumn('answer_system', Types::STRING, ['length' => 255]);
        $table->addColumn('answer_display', Types::STRING, ['length' => 255]);
        $table->addColumn('answer_definition', Types::TEXT);
        $table->addColumn('sort_order', Types::INTEGER, ['default' => 0]);
        $table->addColumn('active', Types::SMALLINT, ['default' => 1]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['loinc_code'], 'loinc_code');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('preference_value_sets');
    }
}
