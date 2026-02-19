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
 * Sequences table
 */
final class Version20260000010062 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sequences table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('sequences');
        $table->addColumn('id', Types::INTEGER, ['unsigned' => true, 'default' => 0]);

        $table->addOption('engine', 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('sequences');
    }
}
