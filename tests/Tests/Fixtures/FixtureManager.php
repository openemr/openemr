<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Common\Database\QueryUtils;
use Ramsey\Uuid\Uuid;

/**
 * Provides OpenEMR Fixtures/Sample Records to test cases as Objects or Database Records.
 *
 * The FixtureManager generates sample records from JSON files located within the Fixture namespace.
 * To provide support for additional record types:
 * - Add a JSON datafile to the Fixture namespace containing the sample records.
 * - Add public methods to get, install, and remove fixture records.
 * - The "patient" related methods provide clear working examples.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FixtureManager
{
    // use a prefix so we can easily remove fixtures
    const PATIENT_FIXTURE_PUBPID_PREFIX = "test-fixture";

    private $patientFixtures;
    private $fhirPatientFixtures;
    private $addressFixtures;
    private $contactFixtures;
    private $contactAddressFixtures;

    /**
     * @var
     */
    private $fhirAllergyIntoleranceFixtures;

    public function __construct()
    {
        $this->addressFixtures = $this->loadJsonFile("addresses.json");
        $this->contactAddressFixtures = $this->loadJsonFile("contact-addresses.json");
        $this->contactFixtures = $this->loadJsonFile("contacts.json");
        $this->patientFixtures = $this->loadJsonFile("patients.json");
        $this->fhirPatientFixtures = $this->loadJsonFile("FHIR/patients.json");
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
     * @return the next available patient pid/identifier.
     */
    private function getNextPid()
    {
        $pidQuery = "SELECT IFNULL(MAX(pid), 0) + 1 FROM patient_data";
        $pidResult = sqlQueryNoLog($pidQuery);
        $pidValue = intval(array_values($pidResult)[0]);
        return $pidValue;
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
                // not sure I like table specific comparisons here...
                if ($tableName == 'lists' && $field == 'pid') {
                    $sqlColumnValues .= "pid = (SELECT pid FROM patient_data WHERE pubpid=? LIMIT 1) ,";
                } else {
                    $sqlColumnValues .= $field . " = ?, ";
                }
//                if ($field === 'uuid') {
//                    $fieldValue = UuidRegistry::uuidToBytes($fieldValue);
//                }
                array_push($sqlBinds, $fieldValue);
            }

            if ($tableName == "patient_data") {
                // add pid
                $sqlColumnValues .= 'pid = ? ,';
                $nextPidValue = $this->getNextPid();
                array_push($sqlBinds, $nextPidValue);
                // add uuid
                $sqlColumnValues .= '`uuid` = ?';
                $uuidPatient = $this->getUuid("patient_data");
                array_push($sqlBinds, $uuidPatient);
            }
            $sqlColumnValues = rtrim($sqlColumnValues, " ,");

            $isInserted = QueryUtils::sqlInsert($sqlInsert . $sqlColumnValues, $sqlBinds);
            if ($isInserted) {
                $insertCount += 1;
            }
        }
        return $insertCount;
    }

    /**
     * @return array of fhir patient fixtures.
     */
    public function getFhirPatientFixtures()
    {
        return $this->fhirPatientFixtures;
    }

    /**
     * @return single/random fhir patient fixture
     */
    public function getSingleFhirPatientFixture()
    {
        return $this->getSingleEntry($this->fhirPatientFixtures);
    }

    /**
     * @return array of patient fixtures.
     */
    public function getPatientFixtures()
    {
        return $this->patientFixtures;
    }

    public function getAllergyIntoleranceFixtures()
    {
        if (empty($this->fhirAllergyIntoleranceFixtures)) {
            $this->fhirAllergyIntoleranceFixtures = $this->loadJsonFile("allergy-intolerance.json");
        }
        return $this->fhirAllergyIntoleranceFixtures;
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
     * @return a random patient fixture.
     */
    public function getSinglePatientFixture()
    {
        return $this->getSingleEntry($this->patientFixtures);
    }

    public function getSinglePatientFixtureWithAddressInformation()
    {
        $entry = $this->getSingleEntry($this->patientFixtures);
        $address = $this->getSingleEntry($this->addressFixtures);
        $contactAddress = $this->getSingleEntry($this->contactAddressFixtures);
        $contactAddress['contact_id'] = hexdec(uniqid()); // just need a random unique id
        unset($contactAddress['address_id']);

        // now combine our contact address and address for our unique address information
        $entry['addresses'] = [
            array_merge($address, $contactAddress)
        ];
        return $entry;
    }

    /**
     * Installs Patient Fixtures into the OpenEMR DB.
     */
    public function installPatientFixtures()
    {
        return $this->installFixtures("patient_data", $this->getPatientFixtures());
    }

    /**
     * Installs a single Patient Fixtures into the OpenEMR DB.
     * @param $patientFixture - The fixture to install.
     * @return count of records inserted.
     */
    public function installSinglePatientFixture($patientFixture)
    {
        return $this->installFixtures("patient_data", array($patientFixture));
    }

    /**
     * Removes Patient Fixtures from the OpenEMR DB.
     */
    public function removePatientFixtures()
    {
        $bindVariable = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";

        // remove the related uuids from uuid_registry
        $select = "SELECT `uuid` FROM `patient_data` WHERE `pubpid` LIKE ?";
        $sel = sqlStatement($select, [$bindVariable]);
        while ($row = sqlFetchArray($sel)) {
            sqlQuery("DELETE FROM `uuid_registry` WHERE `table_name` = 'patient_data' AND `uuid` = ?", [$row['uuid']]);
        }

        // remove the patients
        $delete = "DELETE FROM patient_data WHERE pubpid LIKE ?";
        sqlStatement($delete, array($bindVariable));
    }

    public function installAllergyIntoleranceFixtures()
    {
        $this->installPatientFixtures();
        $installed = $this->installFixtures("lists", $this->getAllergyIntoleranceFixtures());
        if ($installed < 1) {
            throw new \RuntimeException("Failed to install allergy intolerance fixtures");
        }
    }

    public function removeAllergyIntoleranceFixtures()
    {
        $pubpid = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $pids = QueryUtils::fetchTableColumn(
            "SELECT `pid` FROM `patient_data` WHERE `pubpid` LIKE ?",
            'pid',
            [$pubpid]
        );

        if (!empty($pids)) {
            $count = count($pids) - 1;
            $where = "WHERE pid = ? " . str_repeat("OR pid = ? ", $count);
            $sqlStatement = "DELETE FROM `lists` " . $where;
            QueryUtils::sqlStatementThrowException($sqlStatement, $pids);
        }
        $this->removePatientFixtures();
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
