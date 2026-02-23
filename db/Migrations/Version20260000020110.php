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
 * X12 partners table
 */
final class Version20260000020110 extends AbstractMigration
{
    use CreateTableTrait;

    public function getDescription(): string
    {
        return 'Create x12_partners table';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('x12_partners');
        $table->addColumn('id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('id_number', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_sender_id', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_receiver_id', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('processing_format', Types::ENUM, [
            'notnull' => false,
            'default' => null,
            'values' => ['standard', 'medi-cal', 'cms', 'proxymed', 'oa_eligibility', 'availity_eligibility'],
        ]);
        $table->addColumn('x12_isa01', Types::STRING, ['length' => 2, 'default' => '00', 'comment' => 'User logon Required Indicator']);
        $table->addColumn('x12_isa02', Types::STRING, ['length' => 10, 'default' => '          ', 'comment' => 'User Logon']);
        $table->addColumn('x12_isa03', Types::STRING, ['length' => 2, 'default' => '00', 'comment' => 'User password required Indicator']);
        $table->addColumn('x12_isa04', Types::STRING, ['length' => 10, 'default' => '          ', 'comment' => 'User Password']);
        $table->addColumn('x12_isa05', Types::STRING, ['fixed' => true, 'length' => 2, 'default' => 'ZZ']);
        $table->addColumn('x12_isa07', Types::STRING, ['fixed' => true, 'length' => 2, 'default' => 'ZZ']);
        $table->addColumn('x12_isa14', Types::STRING, ['fixed' => true, 'length' => 1, 'default' => 0]);
        $table->addColumn('x12_isa15', Types::STRING, ['fixed' => true, 'length' => 1, 'default' => 'P']);
        $table->addColumn('x12_gs02', Types::STRING, ['length' => 15, 'default' => '']);
        $table->addColumn('x12_per06', Types::STRING, ['length' => 80, 'default' => '']);
        $table->addColumn('x12_dtp03', Types::STRING, ['fixed' => true, 'length' => 1, 'default' => 'A']);
        $table->addColumn('x12_gs03', Types::STRING, [
            'length' => 15,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_submitter_id', Types::SMALLINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('x12_submitter_name', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_sftp_login', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_sftp_pass', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_sftp_host', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_sftp_port', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_sftp_local_dir', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_sftp_remote_dir', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_token_endpoint', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('x12_eligibility_endpoint', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('x12_claim_status_endpoint', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('x12_attachment_endpoint', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('x12_client_id', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addColumn('x12_client_secret', Types::TEXT, ['notnull' => false, 'length' => 255]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $this->createTable($table);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE x12_partners');
    }
}
