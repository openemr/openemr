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
 * Form soap table
 */
final class Version20260000010014 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_soap table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_soap');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => 0]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('groupname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('authorized', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('activity', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('subjective', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('objective', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('assessment', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('plan', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_soap');
    }
}
