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
 * Icd9 sg long code table
 */
final class Version20260000020048 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create icd9_sg_long_code table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('icd9_sg_long_code');
        $table->addColumn('sq_id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('sg_code', Types::STRING, ['notnull' => false, 'length' => 5]);
        $table->addColumn('long_desc', Types::STRING, ['notnull' => false, 'length' => 300]);
        $table->addColumn('active', Types::BOOLEAN, ['notnull' => false, 'default' => 0]);
        $table->addColumn('revision', Types::INTEGER, ['notnull' => false, 'default' => 0]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('sq_id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE icd9_sg_long_code');
    }
}
