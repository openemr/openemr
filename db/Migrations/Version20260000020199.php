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
 * Questionnaire repository table
 */
final class Version20260000020199 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create questionnaire_repository table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('questionnaire_repository');
        $table->addColumn('id', Types::BIGINT, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('questionnaire_id', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('provider', Types::INTEGER, [
            'unsigned' => true,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('version', Types::INTEGER, ['default' => 1]);
        $table->addColumn('created_date', Types::DATETIME_MUTABLE);
        $table->addColumn('modified_date', Types::DATETIME_MUTABLE);
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('type', Types::STRING, ['length' => 63, 'default' => 'Questionnaire']);
        $table->addColumn('profile', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('active', Types::SMALLINT, ['default' => 1]);
        $table->addColumn('status', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('source_url', Types::TEXT);
        $table->addColumn('code', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('code_display', Types::TEXT);
        $table->addColumn('questionnaire', Types::TEXT);
        $table->addColumn('lform', Types::TEXT);
        $table->addColumn('category', Types::STRING, [
            'length' => 64,
            'notnull' => false,
            'default' => null,
            'comment' => 'Used for grouping and organizing ',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['name', 'questionnaire_id'], 'search');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE questionnaire_repository');
    }
}
