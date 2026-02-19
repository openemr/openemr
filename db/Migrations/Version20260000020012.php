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
 * Claims table
 */
final class Version20260000020012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create claims table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('claims');
        $table->addColumn('patient_id', Types::BIGINT);
        $table->addColumn('encounter_id', Types::INTEGER);
        $table->addColumn('version', Types::INTEGER, ['unsigned' => true, 'comment' => 'Claim version, incremented in code']);
        $table->addColumn('payer_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('status', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('payer_type', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('bill_process', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('bill_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('process_time', Types::DATETIME_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('process_file', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('target', Types::STRING, [
            'length' => 30,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('x12_partner_id', Types::INTEGER, ['default' => 0]);
        $table->addColumn('submitted_claim', Types::TEXT, ['comment' => 'This claims form claim data']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('patient_id', 'encounter_id', 'version')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('claims');
    }
}
