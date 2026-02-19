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
 * Lbt data table
 */
final class Version20260000020119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lbt_data table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('lbt_data');
        $table->addColumn('form_id', Types::BIGINT, ['comment' => 'references transactions.id']);
        $table->addColumn('field_id', Types::STRING, ['length' => 31, 'comment' => 'references layout_options.field_id']);
        $table->addColumn('field_value', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('form_id', 'field_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('lbt_data');
    }
}
