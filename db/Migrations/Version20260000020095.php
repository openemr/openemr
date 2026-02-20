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
 * Rule action table
 */
final class Version20260000020095 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create rule_action table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('rule_action');
        $table->addColumn('id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to the id column in the clinical_rules table',
        ]);
        $table->addColumn('group_id', Types::BIGINT, ['default' => 1, 'comment' => 'Contains group id to identify collection of targets in a rule']);
        $table->addColumn('category', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to the category item in the rule_action_item table',
        ]);
        $table->addColumn('item', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to the item column in the rule_action_item table',
        ]);

        $table->addIndex(['id'], null);

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rule_action');
    }
}
