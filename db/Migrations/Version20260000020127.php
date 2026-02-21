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
 * Procedure report table
 */
final class Version20260000020127 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create procedure_report table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('procedure_report');
        $table->addColumn('procedure_report_id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('procedure_order_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'references procedure_order.procedure_order_id',
        ]);
        $table->addColumn('procedure_order_seq', Types::INTEGER, ['default' => 1, 'comment' => 'references procedure_order_code.procedure_order_seq']);
        $table->addColumn('date_collected', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_collected_tz', Types::STRING, [
            'length' => 5,
            'default' => '',
            'comment' => '+-hhmm offset from UTC',
        ]);
        $table->addColumn('date_report', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_report_tz', Types::STRING, [
            'length' => 5,
            'default' => '',
            'comment' => '+-hhmm offset from UTC',
        ]);
        $table->addColumn('source', Types::BIGINT, ['default' => 0, 'comment' => 'references users.id, who entered this data']);
        $table->addColumn('specimen_num', Types::STRING, ['length' => 63, 'default' => '']);
        $table->addColumn('report_status', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'received,complete,error',
        ]);
        $table->addColumn('review_status', Types::STRING, [
            'length' => 31,
            'default' => 'received',
            'comment' => 'pending review status: received,reviewed',
        ]);
        $table->addColumn('report_notes', Types::TEXT, ['comment' => 'notes from the lab']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('procedure_report_id')
                ->create()
        );
        $table->addIndex(['procedure_order_id'], 'procedure_order_id');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procedure_report');
    }
}
