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
 * Report itemized table
 */
final class Version20260000020093 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create report_itemized table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('report_itemized');
        $table->addColumn('report_id', Types::BIGINT);
        $table->addColumn('itemized_test_id', Types::SMALLINT);
        $table->addColumn('numerator_label', Types::STRING, [
            'length' => 25,
            'default' => '',
            'comment' => 'Only used in special cases',
        ]);
        $table->addColumn('pass', Types::SMALLINT, ['default' => 0, 'comment' => '0 is fail, 1 is pass, 2 is excluded']);
        $table->addColumn('pid', Types::BIGINT);
        $table->addColumn('rule_id', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to clinical_rules.rule_id',
        ]);
        $table->addColumn('item_details', Types::TEXT, ['comment' => 'JSON with specific sub item results for a clinical rule']);

        $table->addIndex(['report_id', 'itemized_test_id', 'numerator_label', 'pass'], null);
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('report_itemized');
    }
}
