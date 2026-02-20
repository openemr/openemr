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
 * Version table
 */
final class Version20260000020134 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create version table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('version');
        $table->addColumn('v_major', Types::INTEGER, ['default' => 0]);
        $table->addColumn('v_minor', Types::INTEGER, ['default' => 0]);
        $table->addColumn('v_patch', Types::INTEGER, ['default' => 0]);
        $table->addColumn('v_realpatch', Types::INTEGER, ['default' => 0]);
        $table->addColumn('v_tag', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('v_database', Types::INTEGER, ['default' => 0]);
        $table->addColumn('v_acl', Types::INTEGER, ['default' => 0]);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE version');
    }
}
