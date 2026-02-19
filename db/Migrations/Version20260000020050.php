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
 * Icd10 pcs order code table
 */
final class Version20260000020050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create icd10_pcs_order_code table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('icd10_pcs_order_code');
        $table->addColumn('pcs_id', Types::STRING);
        $table->addColumn('pcs_code', Types::STRING, ['length' => 7]);
        $table->addColumn('valid_for_coding', Types::STRING);
        $table->addColumn('short_desc', Types::STRING, ['length' => 60]);
        $table->addColumn('long_desc', Types::TEXT);
        $table->addColumn('active', Types::SMALLINT, ['default' => 0]);
        $table->addColumn('revision', Types::INTEGER, ['default' => 0]);

        $table->addIndex(['pcs_code'], 'pcs_code');
        $table->addIndex(['active'], 'active');
        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('icd10_pcs_order_code');
    }
}
