<?php

/**
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Core\Migrations;

use Doctrine\DBAL\Schema\Table;

/**
 * Trait for migrations that create tables.
 *
 * Generates platform-specific SQL without schema introspection overhead.
 */
trait CreateTableTrait
{
    private function createTable(Table $table): void
    {
        $table->addOption('charset', 'utf8mb4');
        $table->addOption('collation', 'utf8mb4_general_ci');
        $platform = $this->connection->getDatabasePlatform();
        foreach ($platform->getCreateTableSQL($table) as $sql) {
            $this->addSql($sql);
        }
    }
}
