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
 * Questionnaire response table
 */
final class Version20260000020200 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create questionnaire_response table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('questionnaire_response');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('response_id', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'A globally unique id for answer set. String version of UUID',
        ]);
        $table->addColumn('questionnaire_foreign_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'questionnaire_repository id for subject questionnaire',
        ]);
        $table->addColumn('questionnaire_id', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'Id for questionnaire content. String version of UUID',
        ]);
        $table->addColumn('questionnaire_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('patient_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'May or may not be associated with an encounter',
        ]);
        $table->addColumn('audit_user_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('creator_user_id', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'user id if answers are provider',
        ]);
        $table->addColumn('create_time', Types::DATETIME_MUTABLE);
        $table->addColumn('last_updated', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('version', Types::INTEGER, ['default' => 1]);
        $table->addColumn('status', Types::STRING, [
            'length' => 63,
            'notnull' => false,
            'default' => null,
            'comment' => 'form current status. completed,active,incomplete',
        ]);
        $table->addColumn('questionnaire', Types::TEXT, ['comment' => 'the subject questionnaire json']);
        $table->addColumn('questionnaire_response', Types::TEXT, ['comment' => 'questionnaire response json']);
        $table->addColumn('form_response', Types::TEXT, ['comment' => 'lform answers array json']);
        $table->addColumn('form_score', Types::INTEGER, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Arithmetic scoring of questionnaires',
        ]);
        $table->addColumn('tscore', Types::FLOAT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'T-Score',
        ]);
        $table->addColumn('error', Types::FLOAT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Standard error for the T-Score',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['response_id', 'patient_id', 'questionnaire_id', 'questionnaire_name'], 'response_index');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE questionnaire_response');
    }
}
