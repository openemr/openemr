<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;

/**
 * Provides GACL Fixtures for testing breakglass user functionality
 *
 * @package   OpenEMR
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      http://www.open-emr.org
 */
class GaclFixtureManager extends BaseFixtureManager
{
    public function __construct()
    {
        parent::__construct("gacl-breakglass.json", "");
    }

    /**
     * Installs GACL breakglass fixtures into the database
     * @return int Number of fixtures installed
     */
    public function installFixtures(): int
    {
        $fixtures = $this->getFixturesFromFile();
        $insertCount = 0;

        // Install gacl_aro
        if (isset($fixtures['gacl_aro'])) {
            $insertCount += $this->installFixturesForTable('gacl_aro', $fixtures['gacl_aro']);
        }

        // Install gacl_groups_aro_map last (references gacl_aro table)
        if (isset($fixtures['gacl_groups_aro_map'])) {
            $insertCount += $this->installFixturesForTable('gacl_groups_aro_map', $fixtures['gacl_groups_aro_map']);
        }

        return $insertCount;
    }

    /**
     * Removes installed GACL fixtures from the database
     */
    protected function removeInstalledFixtures(): void
    {
        // Remove in reverse order to avoid foreign key constraints
        sqlStatement("DELETE FROM gacl_groups_aro_map WHERE group_id = 16 AND aro_id = 9001");
        sqlStatement("DELETE FROM gacl_aro WHERE id = 9001");
    }

    /**
     * Get a single GACL fixture for testing
     * @return array
     */
    public function getSingleFixture(): array
    {
        $fixtures = $this->getFixturesFromFile();
        return $fixtures;
    }
}
