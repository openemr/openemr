<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Uuid\UuidRegistry;
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
class BaseFixtureManager
{
    // use a prefix so we can easily remove fixtures
    const FIXTURE_PREFIX = "test-fixture";

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
}
