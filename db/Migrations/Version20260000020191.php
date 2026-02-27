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
 * Form vital details table
 */
final class Version20260000020191 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_vital_details table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_vital_details');
        $table->addOption('comment', 'Detailed information of each vital_forms observation column');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('form_id', Types::BIGINT, ['comment' => 'FK to vital_forms.id']);
        $table->addColumn('vitals_column', Types::STRING, ['length' => 64, 'comment' => 'Column name from form_vitals']);
        $table->addColumn('interpretation_list_id', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'FK to list_options.list_id for observation_interpretation',
        ]);
        $table->addColumn('interpretation_option_id', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'FK to list_options.option_id for observation_interpretation',
        ]);
        $table->addColumn('interpretation_codes', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'Archived original codes value from list_options observation_interpretation',
        ]);
        $table->addColumn('interpretation_title', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'Archived original title value from list_options observation_interpretation',
        ]);
        $table->addColumn('reason_code', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
            'comment' => 'Medical code explaining reason of the vital observation value in form codesystem:codetype;...;',
        ]);
        $table->addColumn('reason_description', Types::TEXT, ['notnull' => false, 'length' => 65535, 'comment' => 'Human readable text description of the reason_code column']);
        $table->addColumn('reason_status', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
            'comment' => 'The status of the reason ie completed, in progress, etc',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['form_id'], 'fk_form_id');
        $table->addIndex(['interpretation_list_id', 'interpretation_option_id'], 'fk_list_options_id');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_vital_details');
    }
}
