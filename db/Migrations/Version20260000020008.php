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
 * Audit details table
 */
final class Version20260000020008 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create audit_details table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('audit_details');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('table_name', Types::STRING, ['length' => 100, 'comment' => 'openemr table name']);
        $table->addColumn('field_name', Types::STRING, ['length' => 100, 'comment' => 'openemr table']);
        $table->addColumn('field_value', Types::TEXT, ['comment' => 'openemr table']);
        $table->addColumn('audit_master_id', Types::BIGINT, ['comment' => 'Id of the audit_master table']);
        $table->addColumn('entry_identification', Types::STRING, [
            'length' => 255,
            'default' => 1,
            'comment' => 'Used when multiple entry occurs from the same table.1 means no multiple entry',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['audit_master_id'], 'audit_master_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audit_details');
    }
}
