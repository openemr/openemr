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
 * Form questionnaire assessments table
 */
final class Version20260000020201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_questionnaire_assessments table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_questionnaire_assessments');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE);
        $table->addColumn('response_id', Types::TEXT, ['comment' => 'The foreign id to the questionnaire_response repository']);
        $table->addColumn('pid', Types::BIGINT, ['default' => 0]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('groupname', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('authorized', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('activity', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('copyright', Types::TEXT);
        $table->addColumn('form_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('response_meta', Types::TEXT, ['comment' => 'json meta data for the response resource']);
        $table->addColumn('questionnaire_id', Types::TEXT, ['comment' => 'The foreign id to the questionnaire_repository']);
        $table->addColumn('questionnaire', Types::TEXT);
        $table->addColumn('questionnaire_response', Types::TEXT);
        $table->addColumn('lform', Types::TEXT);
        $table->addColumn('lform_response', Types::TEXT);
        $table->addColumn('category', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_questionnaire_assessments');
    }
}
