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
 * Form vitals calculation form vitals table
 */
final class Version20260000020194 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_vitals_calculation_form_vitals table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_vitals_calculation_form_vitals');
        $table->addColumn('fvc_uuid', Types::BINARY, ['length' => 16, 'comment' => 'fk to form_vitals_calculation.uuid']);
        $table->addColumn('vitals_id', Types::BIGINT, ['comment' => 'fk to form_vitals.id']);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('fvc_uuid', 'vitals_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_vitals_calculation_form_vitals');
    }
}
