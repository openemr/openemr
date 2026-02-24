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
 * Lists medication table
 */
final class Version20260000020067 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create lists_medication table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('lists_medication');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('list_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'FK Reference to lists.id',
        ]);
        $table->addColumn('drug_dosage_instructions', Types::TEXT, ['notnull' => false, 
            'comment' => 'Free text dosage instructions for taking the drug',
        ]);
        $table->addColumn('usage_category', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'option_id in list_options.list_id=medication-usage-category',
        ]);
        $table->addColumn('usage_category_title', Types::STRING, [
            'length' => 255,
            'comment' => 'title in list_options.list_id=medication-usage-category',
        ]);
        $table->addColumn('request_intent', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
            'comment' => 'option_id in list_options.list_id=medication-request-intent',
        ]);
        $table->addColumn('request_intent_title', Types::STRING, [
            'length' => 255,
            'comment' => 'title in list_options.list_id=medication-request-intent',
        ]);
        $table->addColumn('medication_adherence_information_source', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to list_options.option_id where list_id=medication_adherence_information_source to indicate who provided the medication adherence information',
        ]);
        $table->addColumn('medication_adherence', Types::STRING, [
            'length' => 50,
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to list_options.option_id where list_id=medication_adherence to indicate if patient is complying with medication regimen',
        ]);
        $table->addColumn('medication_adherence_date_asserted', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Date when the medication adherence information was asserted',
        ]);
        $table->addColumn('prescription_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'fk to prescriptions.prescription_id to link medication to prescription record',
        ]);
        $table->addColumn('is_primary_record', Types::BOOLEAN, [
            'default' => 1,
            'comment' => 'Indicates if this medication is a primary record(1) or a reported record(0)',
        ]);
        $table->addColumn('reporting_source_record_id', Types::BIGINT, [
            'notnull' => false,
            'default' => null,
            'comment' => 'If this is a reported record, this is the fk to the users.id column for the address book user that the medication was reported by',
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['usage_category'], 'lists_med_usage_category_idx');
        $table->addIndex(['request_intent'], 'lists_med_request_intent_idx');
        $table->addIndex(['list_id'], 'lists_medication_list_idx');
        $table->addOption('comment', 'Holds additional data about patient medications.');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lists_medication');
    }
}
