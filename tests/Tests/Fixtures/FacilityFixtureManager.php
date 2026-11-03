<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;

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
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FacilityFixtureManager extends BaseFixtureManager
{
    /** @var array<string, mixed>[] */
    private $fhirFacilityFixtures;

    public function __construct()
    {
        parent::__construct("facility.php", "facility");
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
     * @return array<string, mixed> a single random fhir facility fixture
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
     * @return array<string, mixed>
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
        return $this->installFixtures();
    }

    /**
     * Removes Facility Fixtures from the OpenEMR DB.
     */
    public function removeInstalledFixtures()
    {
        $facilityIds = $this->getInstalledFixtureIds('facility');
        if ($facilityIds === []) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($facilityIds), '?'));
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'facility' AND `uuid` IN " .
            "(SELECT `uuid` FROM `facility` WHERE `id` IN ($placeholders))",
            $facilityIds
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `facility` WHERE `id` IN ($placeholders)",
            $facilityIds
        );
    }
}
