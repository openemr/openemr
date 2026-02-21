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
 * Contact table
 */
final class Version20260000020017 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create contact table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('contact');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('foreign_table_name', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('foreign_id', Types::BIGINT, ['default' => 0]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['foreign_id'], null);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact');
    }
}
