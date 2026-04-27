<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
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
 * @link      https://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FixtureManager
{
    // use a prefix so we can easily remove fixtures
    const PATIENT_FIXTURE_PUBPID_PREFIX = "test-fixture";

    /** @var array<string, mixed>[] */
    private readonly array $patientFixtures;
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
        $this->addressFixtures = $this->loadPhpFile("addresses.php");
        $this->contactAddressFixtures = $this->loadPhpFile("contact-addresses.php");
        $this->contactFixtures = $this->loadPhpFile("contacts.php");
        $this->patientFixtures = $this->loadPhpFile("patients.php");
        $this->fhirPatientFixtures = $this->loadJsonFile("FHIR/patients.json");
    }

    /**
     * @return array<string, mixed>[]
     */
    private function loadPhpFile(string $fileName): array
    {
        $filePath = realpath(__DIR__ . '/' . $fileName);
        if ($filePath === false || !str_starts_with($filePath, __DIR__ . '/')) {
            throw new \RuntimeException('Fixture file not found or outside fixtures directory: ' . $fileName);
        }
        /** @var array<string, mixed>[] */
        return require $filePath;
    }

    /**
     * Load a JSON fixture file. Used for FHIR fixtures that stay in JSON format.
     *
     * @return array<string, mixed>[]
     */
    private function loadJsonFile(string $fileName): array
    {
        /** @var array<string, mixed>[] $parsedRecords */
        $parsedRecords = json_decode((string) file_get_contents(__DIR__ . '/' . $fileName), true);
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

        foreach ($fixtures as $fixture) {
            $sqlColumnValues = "";
            $sqlBinds = [];

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
            $this->fhirAllergyIntoleranceFixtures = $this->loadPhpFile("allergy-intolerance.php");
        }
        return $this->fhirAllergyIntoleranceFixtures;
    }

    /**
     * @template T
     * @param T[] $array
     * @return T
     */
    private function getSingleEntry(array $array): mixed
    {
        $randomIndex = array_rand($array, 1);
        return $array[$randomIndex];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSinglePatientFixture(): array
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
        return $this->installFixtures("patient_data", [$patientFixture]);
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
        sqlStatement($delete, [$bindVariable]);
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
