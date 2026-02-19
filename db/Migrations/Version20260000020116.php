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
 * Ar activity table
 */
final class Version20260000020116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ar_activity table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('ar_activity');
        $table->addColumn('pid', Types::INTEGER);
        $table->addColumn('encounter', Types::INTEGER);
        $table->addColumn('sequence_no', Types::INTEGER, ['unsigned' => true, 'comment' => 'Ar_activity sequence_no, incremented in code']);
        $table->addColumn('code_type', Types::STRING, ['length' => 12, 'default' => '']);
        $table->addColumn('code', Types::STRING, ['length' => 20, 'comment' => 'empty means claim level']);
        $table->addColumn('modifier', Types::STRING, ['length' => 12, 'default' => '']);
        $table->addColumn('payer_type', Types::INTEGER, ['comment' => '0=pt, 1=ins1, 2=ins2, etc']);
        $table->addColumn('post_time', Types::DATETIME_MUTABLE);
        $table->addColumn('post_user', Types::INTEGER, ['comment' => 'references users.id']);
        $table->addColumn('session_id', Types::INTEGER, ['unsigned' => true, 'comment' => 'references ar_session.session_id']);
        $table->addColumn('memo', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'adjustment reasons go here',
        ]);
        $table->addColumn('pay_amount', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'default' => 0,
            'comment' => 'either pay or adj will always be 0',
        ]);
        $table->addColumn('adj_amount', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'default' => 0,
        ]);
        $table->addColumn('modified_time', Types::DATETIME_MUTABLE);
        $table->addColumn('follow_up', Types::STRING, ['length' => 1]);
        $table->addColumn('follow_up_note', Types::TEXT);
        $table->addColumn('account_code', Types::STRING, ['length' => 15]);
        $table->addColumn('reason_code', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
            'comment' => 'Use as needed to show the primary payer adjustment reason code',
        ]);
        $table->addColumn('deleted', Types::DATETIME_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'NULL if active, otherwise when voided',
        ]);
        $table->addColumn('post_date', Types::DATE_MUTABLE, [
            'notnull' => false,
            'default' => null,
            'comment' => 'Posting date if specified at payment time',
        ]);
        $table->addColumn('payer_claim_number', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('pid', 'encounter', 'sequence_no')
                ->create()
        );
        $table->addIndex(['session_id'], 'session_id');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('ar_activity');
    }
}
