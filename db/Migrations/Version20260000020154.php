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
 * Immunization observation table
 */
final class Version20260000020154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create immunization_observation table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('immunization_observation');
        $table->addColumn('imo_id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('imo_im_id', Types::INTEGER);
        $table->addColumn('imo_pid', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('imo_criteria', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('imo_criteria_value', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('imo_user', Types::INTEGER, ['notnull' => false, 'default' => null]);
        $table->addColumn('imo_code', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('imo_codetext', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('imo_codetype', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('imo_vis_date_published', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('imo_vis_date_presented', Types::DATE_MUTABLE, ['notnull' => false, 'default' => null]);
        $table->addColumn('imo_date_observation', Types::DATETIME_MUTABLE);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('imo_id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('immunization_observation');
    }
}
