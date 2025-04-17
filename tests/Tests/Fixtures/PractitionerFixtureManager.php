<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Uuid;

/**
 * Provides OpenEMR Fixtures/Sample Records to test cases as Objects or Database Records.
 *
 * The PractitionerFixtureManager generates sample records from JSON files located within the Fixture namespace.
 * To provide support for additional record types:
 * - Add a JSON datafile to the Fixture namespace containing the sample records.
 * - Add public methods to get, install, and remove fixture records.
 * - The "practitioner" related methods provide clear working examples.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PractitionerFixtureManager
{
    // use a prefix so we can easily remove fixtures
    const FIXTURE_PREFIX = "test-fixture";

    private $practitionerFixtures;
    private $fhirPractitionerFixtures;

    public function __construct()
    {
        $this->practitionerFixtures = $this->loadJsonFile("practitioners.json");
        $this->fhirPractitionerFixtures = $this->loadJsonFile("FHIR/practitioners.json");
    }

    /**
     * Loads a JSON fixture from a file within the Fixture namespace, returning the data as an array of records.
     * @param $fileName The file name to load.
     * @return array of records.
     */
    private function loadJsonFile($fileName)
    {
        $filePath = dirname(__FILE__) . "/" . $fileName;
        $jsonData = file_get_contents($filePath);
        $parsedRecords = json_decode($jsonData, true);
        return $parsedRecords;
    }

    /**
     *
     * This will return a recorded uuid (recorded in uuid_registry)
     *
     * @param $tableName The target OpenEMR DB table name.
     * @return uuid.
     */
    private function getUuid($tableName)
    {
        return (new UuidRegistry(['table_name' => $tableName]))->createUuid();
    }

    /**
     * @return the next available practitioner id/identifier.
     */
    private function getNextId()
    {
        $idQuery = "SELECT IFNULL(MAX(id), 0) + 1 FROM users";
        $idResult = sqlQueryNoLog($idQuery);
        $idValue = intval(array_values($idResult)[0]);
        return $idValue;
    }

    /**
     * Installs fixtures into the OpenEMR DB.
     *
     * @param $tableName The target OpenEMR DB table name.
     * @param $fixtures Array of fixture objects to install.
     * @return the number of fixtures installed.
     */
    private function installFixtures($tableName, $fixtures)
    {
        $insertCount = 0;
        $sqlInsert = "INSERT INTO " . escape_table_name($tableName) . " SET ";

        foreach ($fixtures as $index => $fixture) {
            $sqlColumnValues = "";
            $sqlBinds = array();

            foreach ($fixture as $field => $fieldValue) {
                $sqlColumnValues .= $field . " = ?, ";
                array_push($sqlBinds, $fieldValue);
            }

            // add uuid
            $sqlColumnValues .= '`uuid` = ?';
            $uuidPractitioner = $this->getUuid("users");
            array_push($sqlBinds, $uuidPractitioner);

            $sqlColumnValues = rtrim($sqlColumnValues, " ,");
            $isInserted = sqlInsert($sqlInsert . $sqlColumnValues, $sqlBinds);
            if ($isInserted) {
                $insertCount += 1;
            }
        }
        return $insertCount;
    }

    /**
     * @return array of fhir practitioner fixtures.
     */
    public function getFhirPractitionerFixtures()
    {
        return $this->fhirPractitionerFixtures;
    }

    /**
     * @return single/random fhir practitioner fixture
     */
    public function getSingleFhirPractitionerFixture()
    {
        return $this->getSingleEntry($this->fhirPractitionerFixtures);
    }

    /**
     * @return array of practitioner fixtures.
     */
    public function getPractitionerFixtures()
    {
        return $this->practitionerFixtures;
    }

    /**
     * @return random single entry from an array.
     */
    private function getSingleEntry($array)
    {
        $randomIndex = array_rand($array, 1);
        return $array[$randomIndex];
    }

    /**
     * @return a random practitioner fixture.
     */
    public function getSinglePractitionerFixture()
    {
        return $this->getSingleEntry($this->practitionerFixtures);
    }

    /**
     * Installs Practitioner Fixtures into the OpenEMR DB.
     */
    public function installPractitionerFixtures()
    {
        return $this->installFixtures("users", $this->getPractitionerFixtures());
    }

    /**
     * Installs a single Practitioner Fixtures into the OpenEMR DB.
     * @param $practitionerFixture - The fixture to install.
     * @return count of records inserted.
     */
    public function installSinglePractitionerFixture($practitionerFixture)
    {
        return $this->installFixtures("users", array($practitionerFixture));
    }

    /**
     * Removes Practitioner Fixtures from the OpenEMR DB.
     */
    public function removePractitionerFixtures()
    {
        $bindVariable = self::FIXTURE_PREFIX . "%";

        // remove the related uuids from uuid_registry
        $select = "SELECT `uuid` FROM `users` WHERE `fname` LIKE ?";
        $sel = sqlStatement($select, [$bindVariable]);
        while ($row = sqlFetchArray($sel)) {
            sqlQuery("DELETE FROM `uuid_registry` WHERE `table_name` = 'users' AND `uuid` = ?", [$row['uuid']]);
        }

        // remove the practitioners
        $delete = "DELETE FROM users WHERE fname LIKE ?";
        sqlStatement($delete, array($bindVariable));
    }

    /**
     * Returns an unregistered/unlogged UUID for use in testing fixtures
     * @return uuid4 string value
     */
    public function getUnregisteredUuid()
    {
        $uuid4 = Uuid::uuid4();
        return $uuid4->toString();
    }
}
