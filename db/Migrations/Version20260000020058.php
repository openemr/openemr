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
 * Insurance type codes table
 */
final class Version20260000020058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create insurance_type_codes table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('insurance_type_codes');
        $table->addColumn('id', Types::INTEGER);
        $table->addColumn('type', Types::STRING, ['length' => 60]);
        $table->addColumn('claim_type', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('insurance_type_codes');
    }
}
