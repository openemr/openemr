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
 * Codes table
 */
final class Version20260000010005 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create codes table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('codes');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('code_text', Types::TEXT);
        $table->addColumn('code_text_short', Types::TEXT);
        $table->addColumn('code', Types::STRING, ['length' => 25, 'default' => '']);
        $table->addColumn('code_type', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('modifier', Types::STRING, ['length' => 12, 'default' => '']);
        $table->addColumn('units', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('fee', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('superbill', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('related_code', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('taxrates', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('cyp_factor', Types::FLOAT, ['default' => 0, 'comment' => 'quantity representing a years supply']);
        $table->addColumn('active', Types::SMALLINT, ['default' => 1, 'comment' => '0 = inactive, 1 = active']);
        $table->addColumn('reportable', Types::SMALLINT, ['default' => 0, 'comment' => '0 = non-reportable, 1 = reportable']);
        $table->addColumn('financial_reporting', Types::SMALLINT, ['default' => 0, 'comment' => '0 = negative, 1 = considered important code in financial reporting']);
        $table->addColumn('revenue_code', Types::STRING, [
            'length' => 6,
            'default' => '',
            'comment' => 'Item revenue code',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['code'], 'code');
        $table->addIndex(['code_type'], 'code_type');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE codes');
    }
}
