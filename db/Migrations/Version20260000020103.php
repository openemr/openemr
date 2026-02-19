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
 * Supported external dataloads table
 */
final class Version20260000020103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create supported_external_dataloads table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('supported_external_dataloads');
        $table->addColumn('load_id', Types::STRING);
        $table->addColumn('load_type', Types::STRING, ['length' => 24, 'default' => '']);
        $table->addColumn('load_source', Types::STRING, ['length' => 24, 'default' => 'CMS']);
        $table->addColumn('load_release_date', Types::DATE_MUTABLE);
        $table->addColumn('load_filename', Types::STRING, ['length' => 256, 'default' => '']);
        $table->addColumn('load_checksum', Types::STRING, ['length' => 32, 'default' => '']);

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('supported_external_dataloads');
    }
}
