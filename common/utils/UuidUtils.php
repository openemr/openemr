<?php
/**
 * Utility class for Uuid creation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Gerhard Brink <gjdbrink@gmail.com>
 * @copyright Copyright (c) 2019 Gerhard Brink <gjdbrink@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

use Ramsey\Uuid\Uuid;

class UuidUtils
{

    const MAX_TRIES = 100;

    /**
     * @param string $table
     *
     * @return string
     * @throws \Exception
     */
    public static function createUuid(string $table): string
    {
        $uuid4 = Uuid::uuid4();

        $try = 1;
        while (self::findCollision($table, $uuid4)) {
            if ($try > self::MAX_TRIES) {
                throw new \Exception('Reached maximum amount of tries (max amount: '.self::MAX_TRIES.')');
            }

            $uuid4 = Uuid::uuid4();
            $try++;
        }

        return $uuid4->toString();
    }

    /**
     * @param $table
     * @param $uuid
     *
     * @return bool
     */
    public static function findCollision(string $table, string $uuid): bool
    {
        $query = "SELECT uuid FROM ".$table." WHERE uuid = ?";

        if (sqlQuery($query, $uuid)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $table
     *
     * @throws \Exception
     */
    public static function createMissingUuids(string $table)
    {
        $query = "SELECT id FROM ".$table." WHERE uuid = ''";

        $resultSet = sqlStatement($query);

        while ($row = sqlFetchArray($resultSet)) {

            $uuid = self::createUuid($table);

            $updateQuery = "UPDATE ".$table." SET uuid = ? WHERE id = ?";

            sqlQuery($updateQuery, [$uuid, $row['id']]);
        }

    }

    /**
     * @param string $table
     *
     * @return bool
     */
    public static function tableNeedsUuidCreation(string $table): bool
    {
        $query = "SELECT count(id) as total FROM ".$table." WHERE uuid = ''";

        $resultSet = sqlQuery($query);

        if ($resultSet['total'] > 0) {
           return true;
        }

        return false;
    }
}
