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
 * Procedure questions table
 */
final class Version20260000020123 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create procedure_questions table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('procedure_questions');
        $table->addColumn('lab_id', Types::BIGINT, ['default' => 0, 'comment' => 'references procedure_providers.ppid to identify the lab']);
        $table->addColumn('procedure_code', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'references procedure_type.procedure_code to identify this order type',
        ]);
        $table->addColumn('question_code', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'code identifying this question',
        ]);
        $table->addColumn('seq', Types::INTEGER, ['default' => 0, 'comment' => 'sequence number for ordering']);
        $table->addColumn('question_text', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'descriptive text for question_code',
        ]);
        $table->addColumn('required', Types::SMALLINT, ['default' => 0, 'comment' => '1 = required, 0 = not']);
        $table->addColumn('maxsize', Types::INTEGER, ['default' => 0, 'comment' => 'maximum length if text input field']);
        $table->addColumn('fldtype', Types::STRING, [
            'length' => 1,
            'default' => 'T',
            'comment' => 'Text, Number, Select, Multiselect, Date, Gestational-age',
        ]);
        $table->addColumn('options', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'choices for fldtype S and T']);
        $table->addColumn('tips', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'Additional instructions for answering the question',
        ]);
        $table->addColumn('activity', Types::SMALLINT, ['default' => 1, 'comment' => '1 = active, 0 = inactive']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('lab_id', 'procedure_code', 'question_code')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procedure_questions');
    }
}
