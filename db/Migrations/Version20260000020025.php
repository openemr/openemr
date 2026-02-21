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
 * Dated reminders link table
 */
final class Version20260000020025 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create dated_reminders_link table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('dated_reminders_link');
        $table->addColumn('dr_link_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('dr_id', Types::INTEGER);
        $table->addColumn('to_id', Types::INTEGER);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('dr_link_id')
                ->create()
        );
        $table->addIndex(['to_id'], 'to_id');
        $table->addIndex(['dr_id'], 'dr_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE dated_reminders_link');
    }
}
