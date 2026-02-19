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
 * Gacl aro seq table
 */
final class Version20260000010032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create gacl_aro_seq table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('gacl_aro_seq');
        $table->addColumn('id', Types::INTEGER);

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('gacl_aro_seq');
    }
}
