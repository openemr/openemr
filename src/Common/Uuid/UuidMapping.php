<?php

/**
 * UuidMapping class
 *
 *    Generic support for UUID mapping. Goal is to support:
 *     1. uuid for fhir that can not be supported via the standard mechanism (the standard mechanism is when there
 *        is a uuid representing the data within the sql row).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Uuid;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

class UuidMapping
{

    public static function getMappedRecordsForTableUUID($table_uuid)
    {
        $sql = "select `uuid`, `resource`, `table`, `target_uuid`, `created`, `resource_path` FROM `uuid_mapping` WHERE `target_uuid` = ?";
        $records = QueryUtils::fetchRecords($sql, [$table_uuid]);
        return $records;
    }

    public static function getMappingForUUID($uuid, $is_binary = false)
    {
        $sql = "select * from `uuid_mapping` WHERE uuid = ?";
        $uuid_as_binary = $is_binary ? $uuid : UuidRegistry::uuidToBytes($uuid);
        $result = QueryUtils::fetchRecords($sql, [$uuid_as_binary]);
        if (!empty($result)) {
            return $result[0];
        }
        return $result;
    }
    // For now, support one to one uuid to target table, but will plan to add one uuid to many targets tables in future
    //   when presented with that use case (this is why the uuid column in uuid_mapping is not unique btw).
    public static function createMissingResourceUuids($resource, $table, $resourcePath = null)
    {
        $include_resource_path = !empty($resourcePath);
        $sqlStatement = "SELECT `" . $table . "`.`uuid`
                       FROM `" . $table . "`
                       LEFT OUTER JOIN `uuid_mapping` ON `" . $table . "`.`uuid` = `uuid_mapping`.`target_uuid` AND `uuid_mapping`.`resource` = ? ";
        $bindValues = [$resource];
        if ($include_resource_path) {
            $sqlStatement .= " AND `resource_path` = ? ";
            $bindValues[] = $resourcePath;
        }
        $sqlStatement .= "WHERE (`uuid_mapping`.`uuid` IS NULL OR `uuid_mapping`.`uuid` = '' OR `uuid_mapping`.`uuid` = '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0')
                       AND (`" . $table . "`.`uuid` IS NOT NULL AND `" . $table . "`.`uuid` != '' AND `" . $table . "`.`uuid` != '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0')";
        // find the missing mapped uuids
        $resultSet = sqlStatementNoLog($sqlStatement, $bindValues);
        $columns = ['`uuid`', '`resource`', '`table`', '`target_uuid`', '`created`'];
        $bind_insert_str = "VALUES (?, ?, ?, ?, NOW()";
        if ($include_resource_path) {
            $columns[] = '`resource_path`';
            $bind_insert_str .= ", ?";
        }
        $bind_insert_str .= ")";
        $insertStatement = "INSERT INTO `uuid_mapping`(" . implode(",", $columns) . ") " . $bind_insert_str;
        while ($row = sqlFetchArray($resultSet)) {
            // populate the missing mapped uuids
            $bindValues = [(new UuidRegistry(['table_name' => 'uuid_mapping', 'mapped' => true]))->createUuid(), $resource, $table, $row['uuid']];
            if ($include_resource_path) {
                $bindValues[] = $resourcePath;
            }
            sqlQueryNoLog($insertStatement, $bindValues);
        }
    }
}
