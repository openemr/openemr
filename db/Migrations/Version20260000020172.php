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
 * Medex icons table
 */
final class Version20260000020172 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create medex_icons table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('medex_icons');
        $table->addColumn('i_UID', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('msg_type', Types::STRING, ['length' => 50]);
        $table->addColumn('msg_status', Types::STRING, ['length' => 10]);
        $table->addColumn('i_description', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('i_html', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('i_blob', Types::TEXT, ['notnull' => false]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('i_UID')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE medex_icons');
    }
}
