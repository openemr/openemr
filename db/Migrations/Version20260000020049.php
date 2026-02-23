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
 * Icd10 dx order code table
 */
final class Version20260000020049 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create icd10_dx_order_code table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('icd10_dx_order_code');
        $table->addColumn('dx_id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('dx_code', Types::STRING, ['notnull' => false, 'length' => 7]);
        $table->addColumn('formatted_dx_code', Types::STRING, ['notnull' => false, 'length' => 10]);
        $table->addColumn('valid_for_coding', Types::STRING, ['fixed' => true, 'notnull' => false, 'length' => 1]);
        $table->addColumn('short_desc', Types::STRING, ['notnull' => false, 'length' => 60]);
        $table->addColumn('long_desc', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('active', Types::SMALLINT, ['notnull' => false, 'default' => 0]);
        $table->addColumn('revision', Types::INTEGER, ['notnull' => false, 'default' => 0]);

        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('dx_id')
                ->create()
        );
        $table->addIndex(['formatted_dx_code'], 'formatted_dx_code');
        $table->addIndex(['active'], 'active');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE icd10_dx_order_code');
    }
}
