<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\SearchQueryFragment;
use Ramsey\Uuid\Uuid;

/**
 * Provides OpenEMR Fixtures/Sample Records to test cases as Objects or Database Records.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
abstract class BaseFixtureManager
{
    // use a prefix so we can easily remove fixtures
    const FIXTURE_PREFIX = "test-fixture";

    private $fileName;
    private $tableName;
    private $hasInstalledFixtured;
    private $fixtures;

    public function __construct($fileName = "", $tableName = "")
    {
        $this->fileName = $fileName;
        $this->tableName = $tableName;
        $this->hasInstalledFixtured = false;
    }

    protected function getFixturesFromFile()
    {
        if (empty($this->fixtures)) {
            $this->fixtures = $this->loadJsonFile($this->fileName);
        }
        return $this->fixtures;
    }

    /**
     * Loads a JSON fixture from a file within the Fixture namespace, returning the data as an array of records.
     * @param $fileName The file name to load.
     * @return array of records.
     */
    protected function loadJsonFile($fileName)
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
    protected function getUuid($tableName)
    {
        return (new UuidRegistry(['table_name' => $tableName]))->createUuid();
    }

    /**
     * @return the next available id/identifier in the table.
     */
    protected function getNextId($tableName, $idField)
    {
        $idQuery = "SELECT IFNULL(MAX($idField), 0) + 1 FROM $tableName";
        $idResult = sqlQueryNoLog($idQuery);
        $idValue = intval(array_values($idResult)[0]);
        return $idValue;
    }

    /**
     * Returns an unregistered/unlogged UUID for use in testing fixtures
     * @return uuid4 string value
     */
    public function getUnregisteredUuid()
    {
        return UuidRegistry::uuidToString((new UuidRegistry(['disable_tracker' => true]))->createUuid());
    }

    /**
     * Installs fixtures into the OpenEMR DB.
     *
     * @param $tableName The target OpenEMR DB table name.
     * @param $fixtures Array of fixture objects to install.
     * @return the number of fixtures installed.
     */
    protected function installFixturesForTable($tableName, $fixtures)
    {
        $insertCount = 0;
        $sqlInsert = "INSERT INTO " . escape_table_name($tableName) . " SET ";

        foreach ($fixtures as $index => $fixture) {
            $sqlColumnValues = "";
            $sqlBinds = array();

            foreach ($fixture as $field => $fieldValue) {
                if (is_array($fieldValue) && $this->isForeignReference($fieldValue)) {
                    $fragment = $this->getQueryForForeignReference($fieldValue);
                    $sqlColumnValues .= $field . " = " . $fragment->getFragment() . ", ";
                    $sqlBinds = array_merge($sqlBinds, $fragment->getBoundValues());
                } else if ($this->isFunctionCall($fieldValue)) {
                    $sqlColumnValues .= $field . " = ?, ";
                    $fieldValue = $this->getValueFromFunction($fieldValue);
                    array_push($sqlBinds, $fieldValue);
                } else {
                    $sqlColumnValues .= $field . " = ?, ";
                    array_push($sqlBinds, $fieldValue);
                }
            }
            $sqlColumnValues = rtrim($sqlColumnValues, " ,");
            $isInserted = QueryUtils::sqlInsert($sqlInsert . $sqlColumnValues, $sqlBinds);
            if ($isInserted) {
                $insertCount += 1;
            }
        }
        return $insertCount;
    }

    public function installFixtures()
    {
        $fixtures = $this->getFixturesFromFile();
        $insertCount = $this->installFixturesForTable($this->tableName, $fixtures);
        $this->hasInstalledFixtured = true;
        return $insertCount;
    }

    // we don't have a good way to solve this so we will force all sub classes to implement.
    public function removeFixtures()
    {
        // we have no generic way of signifying what the primary key of the table is so we force sub classes
        // to implement the remove fixture method
        $this->removeInstalledFixtures();
        $this->hasInstalledFixtured = false;
    }

    public function hasInstalledFixtures()
    {
        return $this->hasInstalledFixtured;
    }

    /**
     * @return a random fixture.
     */
    public function getSingleFixture()
    {
        $fixtures = $this->getFixturesFromFile();
        return $this->getSingleEntry($fixtures);
    }

    abstract protected function removeInstalledFixtures();

    private function getValueFromFunction($value)
    {
        $functionName = strtok($value, "()");

        // white list our functions here
        if ($functionName === "uuid") {
            $table = strtok("(,)");
            if ($table !== false) {
                // trim spaces, and quotes
                $table = trim($table, "'\" \t\n\r\0\x0B");
                return $this->getUuid($table);
            } else {
                throw new \BadMethodCallException("uuid(table_name) function is missing table name");
            }
        } else if ($functionName === "generateId") {
            return QueryUtils::generateId();
        } else {
            throw new \BadMethodCallException("Function could not be interpreted from fixture: " . $value);
        }
        return ""; // return empty string
    }

    private function isFunctionCall($value)
    {
        return preg_match("/[a-zA-Z]+\(['a-zA-Z_0-9\-]*\)/", $value) === 1;
    }

    private function isForeignReference(array $reference)
    {
        return isset($reference['table']) && isset($reference['columnSearch']) && isset($reference['columnSearchValue']) && isset($reference['columnReference']);
    }

    private function getQueryForForeignReference(array $reference, $limit = 3): SearchQueryFragment
    {
        // make sure we break our recursion
        if ($limit <= 0) {
            throw new \RuntimeException("Maximum recursion depth reached");
        }

        try {
            $table_name = escape_table_name($reference['table']);
            $column = escape_sql_column_name($reference['columnSearch'], [$reference['table']], false, true);
            $referenceColumn = escape_sql_column_name($reference['columnReference'], [$reference['table']], false, true);
            $searchValue = $reference['columnSearchValue'];
            if (is_array($searchValue) && $this->isForeignReference($searchValue)) {
                $fragment = $this->getQueryForForeignReference($searchValue, $limit - 1);
                $sql = "( SELECT $referenceColumn FROM $table_name WHERE $column = " . $fragment->getFragment() . " ";
                return new SearchQueryFragment($sql, $fragment->getBoundValues());
            } else {
                $sql = "( SELECT $referenceColumn FROM $table_name WHERE $column = ? )";
                return new SearchQueryFragment($sql, [$searchValue]);
            }
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error("Failed to escape column for foreign key reference ", ['reference' => $reference]);
            throw $exception;
        }
    }

    /**
     * @return random single entry from an array.
     */
    protected function getSingleEntry($array)
    {
        if (empty($array)) {
            throw new \InvalidArgumentException("cannot get single entry from empty array");
        }
        $randomIndex = array_rand($array, 1);
        return $array[$randomIndex];
    }
}
