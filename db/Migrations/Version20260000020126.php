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
 * Procedure answers table
 */
final class Version20260000020126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create procedure_answers table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('procedure_answers');
        $table->addColumn('procedure_order_id', Types::BIGINT, ['default' => 0, 'comment' => 'references procedure_order.procedure_order_id']);
        $table->addColumn('procedure_order_seq', Types::INTEGER, ['default' => 0, 'comment' => 'references procedure_order_code.procedure_order_seq']);
        $table->addColumn('question_code', Types::STRING, [
            'length' => 31,
            'default' => '',
            'comment' => 'references procedure_questions.question_code',
        ]);
        $table->addColumn('answer_seq', Types::INTEGER, ['comment' => 'supports multiple-choice questions. answer_seq, incremented in code']);
        $table->addColumn('answer', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'answer data',
        ]);
        $table->addColumn('procedure_code', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('procedure_order_id', 'procedure_order_seq', 'question_code', 'answer_seq')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('procedure_answers');
    }
}
