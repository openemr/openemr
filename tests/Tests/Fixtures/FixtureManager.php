<?php

namespace OpenEMR\Tests\Fixtures;

/**
 * Provides OpenEMR Fixtures/Sample Records to test cases as Objects or Database Records
 *
 * The FixtureManager generates sample records from JSON files located within the Fixture namespace.
 * To provide support for additional record types:
 * - Add a JSON datafile to the Fixture namespace containing the sample records.
 * - Add public methods to get, install, and remove fixture records.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixon.whitmire@ibm.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixon.whitmire@ibm.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FixtureManager
{
    private $patientFixtures;

    public function __construct()
    {
        $this->patientFixtures = $this->loadJsonFile("patients.json");
    }

    /**
     * Loads a JSON fixture from a file within the Fixture namespace, returning the data as an array of records.
     *
     * @param $fileName The file name to load.
     * @return array of records
     */
    private function loadJsonFile($fileName)
    {
        $filePath = dirname(__FILE__) . "/" . $fileName;
        $jsonData = file_get_contents($filePath);
        $parsedRecords = json_decode($jsonData);
        return $parsedRecords;
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
     * @param $tableName The target OpenEMR DB table name
     * @param $fixtures Array of fixture objects to install
     * @return the number of fixtures installed
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

            if ($tableName == "patient_data") {
                $sqlColumnValues .= 'pid = ?';
                $nextPidValue = $this->getNextPid();
                array_push($sqlBinds, $nextPidValue);
            }

            $sqlColumnValues = rtrim($sqlColumnValues, " ,");

            $isInserted = sqlInsert($sqlInsert . $sqlColumnValues, $sqlBinds);
            if ($isInserted) {
                $insertCount += 1;
            }
        }
        return $insertCount;
    }

    /**
     * @return array of patient fixtures
     */
    public function getPatientFixtures()
    {
        return $this->patientFixtures;
    }

    /**
     * @return a random patient fixture
     */
    public function getPatientFixture()
    {
        $randomIndex = array_rand($this->patientFixtures, 1);
        return $this->patientFixtures[$randomIndex];
    }

    /**
     * Installs Patient Fixtures into the OpenEMR DB
     */
    public function installPatientFixtures()
    {
        return $this->installFixtures("patient_data", $this->getPatientFixtures());
    }

    /**
     * Installs a single Patient Fixtures into the OpenEMR DB
     * @param $patientFixture - The fixture to install
     * @return count of records inserted
     */
    public function installSinglePatientFixture($patientFixture)
    {
        return $this->installFixtures("patient_data", array($patientFixture));
    }

    /**
     * Removes Patient Fixtures from the OpenEMR DB
     */
    public function removePatientFixtures()
    {
        $delete = "DELETE FROM patient_data WHERE pubpid LIKE ?";
        sqlStatement($delete, array("test-fixture%"));
    }
}
