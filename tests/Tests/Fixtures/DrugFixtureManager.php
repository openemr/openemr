<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\BaseFixtureManager;

/**
 * Provides Drug fixtures/sample records for API tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class DrugFixtureManager extends BaseFixtureManager
{
    public function __construct()
    {
        parent::__construct("drug.php", "drugs");
    }

    public function getDrugFixtures(): array
    {
        return $this->getFixturesFromFile();
    }

    public function getSingleDrugFixture(): array
    {
        return $this->getSingleEntry($this->getFixturesFromFile());
    }

    public function removeInstalledFixtures()
    {
        $bindVariable = self::FIXTURE_PREFIX . "%";

        // remove the related uuids from uuid_registry
        $select = "SELECT `uuid` FROM `drugs` WHERE `name` LIKE ?";
        $sel = sqlStatement($select, [$bindVariable]);
        while ($row = sqlFetchArray($sel)) {
            sqlQuery("DELETE FROM `uuid_registry` WHERE `table_name` = 'drugs' AND `uuid` = ?", [$row['uuid']]);
        }

        // remove the drugs
        $delete = "DELETE FROM `drugs` WHERE `name` LIKE ?";
        sqlStatement($delete, [$bindVariable]);
    }

    public function getInstalledDrugUuid(string $name): string
    {
        $row = sqlQuery("SELECT `uuid` FROM `drugs` WHERE `name` = ?", [$name]);
        return UuidRegistry::uuidToString($row['uuid']);
    }
}
