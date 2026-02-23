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
 * Clinical rules log table
 */
final class Version20260000020016 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create clinical_rules_log table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('clinical_rules_log');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('uid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('category', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'An example category is clinical_reminder_widget',
        ]);
        $table->addColumn('value', Types::TEXT, ['length' => 65535]);
        $table->addColumn('new_value', Types::TEXT, ['length' => 65535]);
        $table->addColumn('facility_id', Types::INTEGER, ['default' => 0, 'comment' => 'facility where the rule was executed, 0 if unknown']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid'], 'pid');
        $table->addIndex(['uid'], 'uid');
        $table->addIndex(['category'], 'category');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE clinical_rules_log');
    }
}
