<?php

/**
 * UuidRegistry class
 *
 *    Generic support for UUID creation and use. Goal is to support uuid for both fhir, future offsite
 *    support, and other future use cases. The construct accepts an associative array in order to allow
 *    easy addition of fields and new sql columns in the uuid_registry table.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Gerhard Brink <gjdbrink@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Gerhard Brink <gjdbrink@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Uuid;

use Ramsey\Uuid\Uuid;

class UuidRegistry
{

    // Maximum tries to create a unique uuid before failing (this should never happen)
    const MAX_TRIES = 100;

    private $table_name;

    public function __construct($associations = [])
    {
        $this->table_name = $associations['table_name'] ?? '';
    }

    /**
     * @return string
     */
    public function createUuid()
    {
        $isUnique = false;
        $i = 0;
        while (!$isUnique) {
            $i++;
            if ($i > self::MAX_TRIES) {
                // This error should never happen. If so, then the random generation of the
                //  OS is compromised and no use continuing to run OpenEMR.
                error_log("OpenEMR Error: Unable to create a unique UUID");
                exit;
            }

            // Create uuid
            $uuid4 = Uuid::uuid4();
            $uuid = $uuid4->getBytes();

            // Check to ensure uuid is unique in uuid_registry
            $checkUniqueRegistry = sqlQueryNoLog("SELECT * FROM `uuid_registry` WHERE `uuid` = ?", [$uuid]);
            if (empty($checkUniqueRegistry)) {
                // If using $this->table_name, then ensure uuid is unique in that table
                if (!empty($this->table_name)) {
                    $checkUniqueTable = sqlQueryNoLog("SELECT * FROM `" . $this->table_name . "` WHERE `uuid` = ?", [$uuid]);
                    if (empty($checkUniqueTable)) {
                        $isUnique = true;
                    }
                } else {
                    $isUnique = true;
                }
            }
        }

        // Insert the uuid into uuid_registry
        sqlQueryNoLog("INSERT INTO `uuid_registry` (`uuid`, `table_name`, `created`) VALUES (?, ?, NOW())", [$uuid, $this->table_name]);

        // Return the uuid
        return $uuid;
    }

    // Generic function to create missing uuids in a sql table (table needs an `id` column to work)
    public function createMissingUuids()
    {
        $resultSet = sqlStatementNoLog("SELECT `id` FROM `" . $this->table_name . "` WHERE `uuid` = ''");
        while ($row = sqlFetchArray($resultSet)) {
            sqlQuery("UPDATE " . $this->table_name . " SET `uuid` = ? WHERE `id` = ?", [$this->createUuid(), $row['id']]);
        }
    }

    // Generic function to see if there are missing uuids in a sql table (table needs an `id` column to work)
    public function tableNeedsUuidCreation()
    {
        $resultSet = sqlQueryNoLog("SELECT count(`id`) as `total` FROM `" . $this->table_name . "` WHERE `uuid` = ''");
        if ($resultSet['total'] > 0) {
            return true;
        }
        return false;
    }
}
