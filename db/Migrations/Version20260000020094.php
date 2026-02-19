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
 * Report results table
 */
final class Version20260000020094 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create report_results table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('report_results');
        $table->addColumn('report_id', Types::BIGINT);
        $table->addColumn('field_id', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('field_value', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('report_id', 'field_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('report_results');
    }
}
