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
 * Chart tracker table
 */
final class Version20260000020114 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create chart_tracker table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('chart_tracker');
        $table->addColumn('ct_pid', Types::INTEGER);
        $table->addColumn('ct_when', Types::DATETIME_MUTABLE);
        $table->addColumn('ct_userid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('ct_location', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('ct_pid', 'ct_when')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE chart_tracker');
    }
}
