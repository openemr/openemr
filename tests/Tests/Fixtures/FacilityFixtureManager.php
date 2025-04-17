<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Tests\Fixtures\BaseFixtureManager;
use Ramsey\Uuid\Uuid;

/**
 * Provides OpenEMR Fixtures/Sample Records to test cases as Objects or Database Records.
 *
 * The FacilityFixtureManager generates sample records from JSON files located within the Fixture namespace.
 * To provide support for additional record types:
 * - Add a JSON datafile to the Fixture namespace containing the sample records.
 * - Add public methods to get, install, and remove fixture records.
 * - The "facility" related methods provide clear working examples.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FacilityFixtureManager extends BaseFixtureManager
{
    private $fhirFacilityFixtures;

    public function __construct()
    {
        parent::__construct("facility.json", "facility");
        $this->fhirFacilityFixtures = $this->loadJsonFile("FHIR/facility.json");
    }

    /**
     * @return array of fhir facility fixtures.
     */
    public function getFhirFacilityFixtures()
    {
        return $this->fhirFacilityFixtures;
    }

    /**
     * @return single/random fhir facility fixture
     */
    public function getSingleFhirFacilityFixture()
    {
        return $this->getSingleEntry($this->fhirFacilityFixtures);
    }

    /**
     * @return array of facility fixtures.
     */
    public function getFacilityFixtures()
    {
        return $this->getFixturesFromFile();
    }


    /**
     * @return a random facility fixture.
     */
    public function getSingleFacilityFixture()
    {
        return $this->getSingleEntry($this->getFixturesFromFile());
    }

    /**
     * Installs Facility Fixtures into the OpenEMR DB.
     */
    public function installFacilityFixtures()
    {
        return $this->installFixtures("facility", $this->getFacilityFixtures());
    }

    /**
     * Installs a single Facility Fixtures into the OpenEMR DB.
     * @param $facilityFixture - The fixture to install.
     * @return count of records inserted.
     */
    public function installSingleFacilityFixture($facilityFixture)
    {
        return $this->installFixtures("facility", array($facilityFixture));
    }

    /**
     * Removes Facility Fixtures from the OpenEMR DB.
     */
    public function removeInstalledFixtures()
    {
        $bindVariable = self::FIXTURE_PREFIX . "%";

        // remove the related uuids from uuid_registry
        $select = "SELECT `uuid` FROM `facility` WHERE `name` LIKE ?";
        $sel = sqlStatement($select, [$bindVariable]);
        while ($row = sqlFetchArray($sel)) {
            sqlQuery("DELETE FROM `uuid_registry` WHERE `table_name` = 'facility' AND `uuid` = ?", [$row['uuid']]);
        }

        // remove the facilitys
        $delete = "DELETE FROM `facility` WHERE `name` LIKE ?";
        sqlStatement($delete, [$bindVariable]);
    }
}
