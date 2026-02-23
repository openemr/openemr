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
 * Form observation table
 */
final class Version20260000020151 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_observation table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_observation');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 
            'length' => 16,
            'notnull' => false,
            'default' => null,
            'comment' => 'UUID for the observation, used as unique logical identifier',
        ]);
        $table->addColumn('form_id', Types::BIGINT, ['comment' => 'FK to forms.form_id']);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
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
        $table->addColumn('authorized', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('activity', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('code', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('observation', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ob_value', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('ob_unit', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('description', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('code_type', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('table_code', Types::STRING, ['notnull' => false, 'length' => 255]);
        $table->addColumn('ob_code', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ob_type', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ob_status', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('result_status', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ob_reason_status', Types::STRING, [
            'length' => 32,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ob_reason_code', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ob_reason_text', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('ob_documentationof_table', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('ob_documentationof_table_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('date_end', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('parent_observation_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'FK to parent observation for sub-observations',
        ]);
        $table->addColumn('category', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
            'comment' => 'FK to list_options.option_id for observation category (SDOH, Functional, Cognitive, Physical, etc)',
        ]);
        $table->addColumn('questionnaire_response_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'FK to questionnaire_response table',
        ]);
        $table->addColumn('ob_value_code_description', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['form_id'], 'idx_form_id');
        $table->addIndex(['parent_observation_id'], 'idx_parent_observation');
        $table->addIndex(['category'], 'idx_category');
        $table->addIndex(['questionnaire_response_id'], 'idx_questionnaire_response');
        $table->addIndex(['pid', 'encounter'], 'idx_pid_encounter');
        $table->addIndex(['date'], 'idx_date');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_observation');
    }
}
