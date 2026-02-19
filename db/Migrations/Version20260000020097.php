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
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rule filter table
 */
final class Version20260000020097 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create rule_filter table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('rule_filter');
        $table->addColumn('id', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to the id column in the clinical_rules table',
        ]);
        $table->addColumn('include_flag', Types::SMALLINT, ['default' => 0, 'comment' => '0 is exclude and 1 is include']);
        $table->addColumn('required_flag', Types::SMALLINT, ['default' => 0, 'comment' => '0 is optional and 1 is required']);
        $table->addColumn('method', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to list_options list rule_filters',
        ]);
        $table->addColumn('method_detail', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'Maps to list_options lists rule__intervals',
        ]);
        $table->addColumn('value', Types::STRING, ['length' => 255, 'default' => '']);

        $table->addIndex(['id'], null);
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('rule_filter');
    }
}
