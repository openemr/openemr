<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Fixture\Purger;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\DatabaseTables;

/**
 * Usage:
 *  protected function setUp(): void {
 *      // Purge data
 *      $this->purger = CompositePurgerFactory::createPurgeable();
 *      $this->purger->purge();
 *
 *      // Load isolated data from fixtures
 *      ...
 *  }
 *
 *  protected function tearDown(): void {
 *      // Restore purged data
 *      $this->purger->restore();
 *  }
 */
class CompositePurgerFactory
{
    public static function createPurgeable(): CompositePurger
    {
        return self::createByTables(DatabaseTables::PURGEABLE);
    }

    protected static function createByTables(array $tables): CompositePurger
    {
        $db = DatabaseManager::getInstance();

        return new CompositePurger(array_map(
            static fn (string $table): PurgerInterface => new TruncatePurger($db, $table),
            $tables,
        ));
    }
}
