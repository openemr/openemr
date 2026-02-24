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
 * Ar session table
 */
final class Version20260000020115 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create ar_session table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('ar_session');
        $table->addColumn('session_id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('payer_id', Types::INTEGER, ['comment' => '0=pt else references insurance_companies.id']);
        $table->addColumn('user_id', Types::INTEGER, ['comment' => 'references users.id for session owner']);
        $table->addColumn('closed', Types::BOOLEAN, ['default' => 0, 'comment' => '0=no, 1=yes']);
        $table->addColumn('reference', Types::STRING, [
            'length' => 255,
            'default' => '',
            'comment' => 'check or EOB number',
        ]);
        $table->addColumn('check_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('deposit_date', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('pay_total', Types::DECIMAL, [
            'precision' => 12,
            'scale' => 2,
            'default' => 0,
        ]);
        $table->addColumn('created_time', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('modified_time', Types::DATETIME_MUTABLE);
        $table->addColumn('global_amount', Types::DECIMAL, ['precision' => 12, 'scale' => 2]);
        $table->addColumn('payment_type', Types::STRING, ['length' => 50]);
        $table->addColumn('description', Types::TEXT, ['notnull' => false, 'length' => 65535]);
        $table->addColumn('adjustment_code', Types::STRING, ['length' => 50]);
        $table->addColumn('post_to_date', Types::DATE_MUTABLE);
        $table->addColumn('patient_id', Types::BIGINT);
        $table->addColumn('payment_method', Types::STRING, ['length' => 25]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('session_id')
                ->create()
        );
        $table->addIndex(['user_id', 'closed'], 'user_closed');
        $table->addIndex(['deposit_date'], 'deposit_date');

        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ar_session');
    }
}
