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
 * Rule target table
 */
final class Version20260000020100 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create rule_target table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('rule_target');
        $table->addColumn('id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to the id column in the clinical_rules table',
        ]);
        $table->addColumn('group_id', Types::BIGINT, ['default' => 1, 'comment' => 'Contains group id to identify collection of targets in a rule']);
        $table->addColumn('include_flag', Types::BOOLEAN, ['default' => 0, 'comment' => '0 is exclude and 1 is include']);
        $table->addColumn('required_flag', Types::BOOLEAN, ['default' => 0, 'comment' => '0 is required and 1 is optional']);
        $table->addColumn('method', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to list_options list rule_targets',
        ]);
        $table->addColumn('value', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Data is dependent on the method',
        ]);
        $table->addColumn('interval', Types::BIGINT, ['default' => 0, 'comment' => 'Only used in interval entries']);

        $table->addIndex(['id'], 'id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rule_target');
    }
}
