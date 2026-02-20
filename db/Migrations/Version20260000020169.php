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
 * Form groups encounter table
 */
final class Version20260000020169 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_groups_encounter table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_groups_encounter');
        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('reason', Types::TEXT);
        $table->addColumn('facility', Types::TEXT);
        $table->addColumn('facility_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('group_id', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('onset_date', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('sensitivity', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('billing_note', Types::TEXT);
        $table->addColumn('pc_catid', Types::INTEGER, ['default' => 5, 'comment' => 'event category from openemr_postcalendar_categories']);
        $table->addColumn('last_level_billed', Types::INTEGER, ['default' => 0, 'comment' => '0=none, 1=ins1, 2=ins2, etc']);
        $table->addColumn('last_level_closed', Types::INTEGER, ['default' => 0, 'comment' => '0=none, 1=ins1, 2=ins2, etc']);
        $table->addColumn('last_stmt_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('stmt_count', Types::INTEGER, ['default' => 0]);
        $table->addColumn('provider_id', Types::INTEGER, ['default' => 0, 'comment' => 'default and main provider for this visit']);
        $table->addColumn('supervisor_id', Types::INTEGER, ['default' => 0, 'comment' => 'supervising provider, if any, for this visit']);
        $table->addColumn('invoice_refno', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('referral_source', Types::STRING, ['length' => 31, 'default' => '']);
        $table->addColumn('billing_facility', Types::INTEGER, ['default' => 0]);
        $table->addColumn('external_id', Types::STRING, [
            'length' => 20,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('pos_code', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('counselors', Types::STRING, ['length' => 255]);
        $table->addColumn('appt_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $table->addIndex(['group_id', 'encounter'], 'pid_encounter');
        $table->addIndex(['date'], 'encounter_date');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_groups_encounter');
    }
}
