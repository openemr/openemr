<?php

/**
 * UuidRegistry class
 *
 *    Generic support for UUID creation and use. Goal is to support:
 *     1. uuid for fhir resources
 *     2. uuid for future offsite support (using Timestamp-first COMB Codec for uuid,
 *        so can use for primary keys)
 *     3. other future use cases.
 *    The construct accepts an associative array in order to allow easy addition of
 *    fields and new sql columns in the uuid_registry table.
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

use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Uuid;

class UuidRegistry
{

    // Maximum tries to create a unique uuid before failing (this should never happen)
    const MAX_TRIES = 100;

    private $table_name;
    private $disable_tracker;

    public function __construct($associations = [])
    {
        $this->table_name = $associations['table_name'] ?? '';
        $this->disable_tracker = $associations['disable_tracker'] ?? false;
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
            if ($i > 1) {
                // There was a uuid creation collision, so need to try again.
                error_log("OpenEMR Warning: There was a collision when creating a unique UUID. This is try number " . $i . ". Will try again.");
            }
            if ($i > self::MAX_TRIES) {
                // This error should never happen. If so, then the random generation of the
                //  OS is compromised and no use continuing to run OpenEMR.
                error_log("OpenEMR Error: Unable to create a unique UUID");
                exit;
            }

            // Create uuid using the Timestamp-first COMB Codec, so can use for primary keys
            //  (since first part is timestamp, it is naturally ordered; the rest is from uuid4, so is random)
            //  reference:
            //    https://uuid.ramsey.dev/en/latest/customize/timestamp-first-comb-codec.html#customize-timestamp-first-comb-codec
            $factory = new UuidFactory();
            $codec = new TimestampFirstCombCodec($factory->getUuidBuilder());
            $factory->setCodec($codec);
            $factory->setRandomGenerator(new CombGenerator(
                $factory->getRandomGenerator(),
                $factory->getNumberConverter()
            ));
            $timestampFirstComb = $factory->uuid4();
            $uuid = $timestampFirstComb->getBytes();

            /** temp debug stuff
            error_log(bin2hex($uuid)); // log hex uuid
            error_log(bin2hex($timestampFirstComb->getBytes())); // log hex uuid
            error_log($timestampFirstComb->toString()); // log string uuid
            $test_uuid = (\Ramsey\Uuid\Uuid::fromBytes($uuid))->toString(); // convert byte uuid to string and log below
            error_log($test_uuid);
            error_log(bin2hex((\Ramsey\Uuid\Uuid::fromString($test_uuid))->getBytes())); // convert string uuid to byte and log hex
            */

            // Check to ensure uuid is unique in uuid_registry
            if (!$this->disable_tracker) {
                $checkUniqueRegistry = sqlQueryNoLog("SELECT * FROM `uuid_registry` WHERE `uuid` = ?", [$uuid]);
            }
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
        if (!$this->disable_tracker) {
            sqlQueryNoLog("INSERT INTO `uuid_registry` (`uuid`, `table_name`, `created`) VALUES (?, ?, NOW())", [$uuid, $this->table_name]);
        }

        // Return the uuid
        return $uuid;
    }

    // Generic function to create missing uuids in a sql table (table needs an `id` column to work)
    public function createMissingUuids()
    {
        // Note needed the '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0' to check for empty in binary(16) field
        $resultSet = sqlStatementNoLog("SELECT `id` FROM `" . $this->table_name . "` WHERE `uuid` = '' OR `uuid` = '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0'");
        while ($row = sqlFetchArray($resultSet)) {
            sqlQuery("UPDATE " . $this->table_name . " SET `uuid` = ? WHERE `id` = ?", [$this->createUuid(), $row['id']]);
        }
    }

    // Generic function to see if there are missing uuids in a sql table (table needs an `id` column to work)
    public function tableNeedsUuidCreation()
    {
        // Note needed the '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0' to check for empty in binary(16) field
        $resultSet = sqlQueryNoLog("SELECT count(`id`) as `total` FROM `" . $this->table_name . "` WHERE `uuid` = '' OR `uuid` = '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0'");
        if ($resultSet['total'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * Converts a UUID byte value to a string representation
     * @return the UUID string value
     */
    public static function uuidToString($uuidBytes)
    {
        return Uuid::fromBytes($uuidBytes)->toString();
    }

    /**
     * Converts a UUID string to a bytes representation
     * @return the UUID bytes value
     */
    public static function uuidToBytes($uuidString)
    {
        return Uuid::fromString($uuidString)->getBytes();
    }
}
