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
 * Medex icons table
 */
final class Version20260000020172 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create medex_icons table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('medex_icons');
        $table->addColumn('i_UID', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('msg_type', Types::STRING, ['length' => 50]);
        $table->addColumn('msg_status', Types::STRING, ['length' => 10]);
        $table->addColumn('i_description', Types::STRING, ['length' => 255]);
        $table->addColumn('i_html', Types::TEXT);
        $table->addColumn('i_blob', Types::TEXT);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('i_UID')
                ->create()
        );

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('medex_icons');
    }
}
