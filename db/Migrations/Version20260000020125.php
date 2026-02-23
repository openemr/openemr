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
 * Procedure order code table
 */
final class Version20260000020125 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create procedure_order_code table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('procedure_order_code');
        $table->addColumn('procedure_order_id', Types::BIGINT, ['comment' => 'references procedure_order.procedure_order_id']);
        $table->addColumn('procedure_order_seq', Types::INTEGER, ['comment' => 'Supports multiple tests per order. Procedure_order_seq, incremented in code']);
        $table->addColumn('procedure_code', Types::STRING, [
            'length' => 64,
            'default' => '',
            'comment' => 'like procedure_type.procedure_code',
        ]);
        $table->addColumn('procedure_name', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'descriptive name of the procedure code',
        ]);
        $table->addColumn('procedure_source', Types::STRING, [
            'length' => 1,
            'default' => 1,
            'comment' => '1=original order, 2=added after order sent',
        ]);
        $table->addColumn('diagnoses', Types::TEXT, ['length' => 65535, 'comment' => 'diagnoses and maybe other coding (e.g. ICD9:111.11)']);
        $table->addColumn('do_not_send', Types::SMALLINT, ['default' => 0, 'comment' => '0 = normal, 1 = do not transmit to lab']);
        $table->addColumn('procedure_order_title', Types::STRING, ['length' => 255, 'notnull' => false, 'default' => null]);
        $table->addColumn('procedure_type', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('transport', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date_end', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('reason_code', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('reason_description', Types::TEXT, ['length' => 65535]);
        $table->addColumn('reason_date_low', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('reason_date_high', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('reason_status', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('procedure_order_id', 'procedure_order_seq')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procedure_order_code');
    }
}
