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
 * Form encounter table
 */
final class Version20260000010010 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create form_encounter table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('form_encounter');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, [
            'length' => 16,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('reason', Types::TEXT, ['notnull' => false]);
        $table->addColumn('facility', Types::TEXT, ['notnull' => false]);
        $table->addColumn('facility_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('onset_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('sensitivity', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('billing_note', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('pc_catid', Types::INTEGER, ['default' => 5, 'comment' => 'event category from openemr_postcalendar_categories']);
        $table->addColumn('last_level_billed', Types::INTEGER, ['default' => 0, 'comment' => '0=none, 1=ins1, 2=ins2, etc']);
        $table->addColumn('last_level_closed', Types::INTEGER, ['default' => 0, 'comment' => '0=none, 1=ins1, 2=ins2, etc']);
        $table->addColumn('last_stmt_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('stmt_count', Types::INTEGER, ['default' => 0]);
        $table->addColumn('provider_id', Types::INTEGER, ['notnull' => false, 'default' => 0, 'comment' => 'default and main provider for this visit']);
        $table->addColumn('supervisor_id', Types::INTEGER, ['notnull' => false, 'default' => 0, 'comment' => 'supervising provider, if any, for this visit']);
        $table->addColumn('invoice_refno', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('referral_source', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('billing_facility', Types::INTEGER, ['default' => 0]);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pos_code', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('parent_encounter_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('class_code', Types::STRING, ['length' => 10, 'default' => 'AMB']);
        $table->addColumn('shift', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('voucher_number', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'also called referral number',
        ]);
        $table->addColumn('discharge_disposition', Types::STRING, [
            'length' => 100,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('encounter_type_code', Types::STRING, [
            'length' => 31,
            'notnull' => false,
            'default' => null,
            'comment' => 'not all types are categories',
        ]);
        $table->addColumn('encounter_type_description', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('referring_provider_id', Types::INTEGER, ['notnull' => false, 'default' => 0, 'comment' => 'referring provider, if any, for this visit']);
        $table->addColumn('date_end', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('in_collection', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('last_update', Types::DATETIME_MUTABLE);
        $table->addColumn('ordering_provider_id', Types::INTEGER, ['notnull' => false, 'default' => 0, 'comment' => 'referring provider, if any, for this visit']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['pid', 'encounter'], 'pid_encounter');
        $table->addIndex(['date'], 'encounter_date');
        $table->addUniqueIndex(['uuid'], 'uuid');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_encounter');
    }
}
