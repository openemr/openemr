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
 * Procedure result table
 */
final class Version20260000020128 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create procedure_result table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('procedure_result');
        $table->addColumn('procedure_result_id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('procedure_report_id', Types::BIGINT, ['comment' => 'references procedure_report.procedure_report_id']);
        $table->addColumn('result_data_type', Types::STRING, [
            'length' => 1,
            'default' => 'S',
            'comment' => 'N=Numeric, S=String, F=Formatted, E=External, L=Long text as first line of comments',
        ]);
        $table->addColumn('result_code', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'LOINC code, might match a procedure_type.procedure_code',
        ]);
        $table->addColumn('result_text', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Description of result_code',
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'lab-provided date specific to this result',
        ]);
        $table->addColumn('facility', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'lab-provided testing facility ID',
        ]);
        $table->addColumn('units', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('result', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('range', Types::STRING, ['length' => 255, 'default' => '']);
        $table->addColumn('abnormal', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'no,yes,high,low',
        ]);
        $table->addColumn('comments', Types::TEXT, ['length' => 65535, 'comment' => 'comments from the lab']);
        $table->addColumn('document_id', Types::BIGINT, ['default' => 0, 'comment' => 'references documents.id if this result is a document']);
        $table->addColumn('result_status', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'preliminary, cannot be done, final, corrected, incomplete...etc.',
        ]);
        $table->addColumn('date_end', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'lab-provided end date specific to this result',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('procedure_result_id')
                ->create()
        );
        $table->addIndex(['procedure_report_id'], 'procedure_report_id');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procedure_result');
    }
}
