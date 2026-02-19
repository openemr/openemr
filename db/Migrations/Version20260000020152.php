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
 * Form clinical instructions table
 */
final class Version20260000020152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create form_clinical_instructions table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('form_clinical_instructions');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('pid', Types::BIGINT, ['notnull' => false, 'default' => null]);
        $table->addColumn('encounter', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('user', Types::STRING, [
            'length' => 255,
            'notnull' => false,
            'default' => null,
        ]);
        $table->addColumn('instruction', Types::TEXT);
        $table->addColumn('date', Types::DATETIME_MUTABLE);
        $table->addColumn('activity', Types::SMALLINT, ['notnull' => false, 'default' => 1]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('form_clinical_instructions');
    }
}
