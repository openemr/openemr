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
 * Lang custom table
 */
final class Version20260000020063 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create lang_custom table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('lang_custom');
        $table->addColumn('lang_description', Types::STRING, ['length' => 100, 'default' => '']);
        $table->addColumn('lang_code', Types::STRING, ['length' => 2, 'default' => '']);
        $table->addColumn('constant_name', Types::TEXT, ['length' => 16777215]);
        $table->addColumn('definition', Types::TEXT, ['length' => 16777215]);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lang_custom');
    }
}
