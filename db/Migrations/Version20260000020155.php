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
 * Calendar external table
 */
final class Version20260000020155 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create calendar_external table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('calendar_external');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATE_MUTABLE);
        $table->addColumn('description', Types::STRING, ['length' => 45]);
        $table->addColumn('source', Types::STRING, ['length' => 45, 'notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE calendar_external');
    }
}
