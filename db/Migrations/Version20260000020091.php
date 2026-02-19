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
 * Prices table
 */
final class Version20260000020091 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create prices table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('prices');
        $table->addColumn('pr_id', Types::STRING, ['length' => 11, 'default' => '']);
        $table->addColumn('pr_selector', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'template selector for drugs, empty for codes',
        ]);
        $table->addColumn('pr_level', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('pr_price', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'default' => 0.00,
            'comment' => 'price in local currency',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pr_id', 'pr_selector', 'pr_level')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('prices');
    }
}
