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

use Exception;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\FHIR\Observation\FhirObservationSocialHistoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;

class UuidMapping
{
    private const UUID_MAPPING_DEFINITIONS = [
        ['resource' => 'CareTeam', 'table' => 'patient_data'],
        ['resource' => 'Location', 'table' => 'patient_data'],
        ['resource' => 'Location', 'table' => 'users'],
        ['resource' => 'Location', 'table' => 'facility'],
        ['resource' => 'Observation', 'table' => 'form_vitals', 'codes' => FhirObservationVitalsService::COLUMN_MAPPINGS, 'category' => FhirObservationVitalsService::CATEGORY],
        ['resource' => 'Observation', 'table' => 'history_data', 'codes' => FhirObservationSocialHistoryService::COLUMN_MAPPINGS, 'category' => FhirObservationSocialHistoryService::CATEGORY],
        ['resource' => 'Group', 'table' => 'users']
    ];

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

    public static function createAllMissingResourceUuids()
    {
        // Update for mapped uuids
        $mappedCounter = 0;
        $mappedTables = self::UUID_MAPPING_DEFINITIONS;
        foreach ($mappedTables as $mapping) {
            if (empty($mapping['codes'])) {
                $count = self::createMissingResourceUuids($mapping['resource'], $mapping['table']);
                $mappedCounter += $count;
            } else {
                foreach ($mapping['codes'] as $code) {
                    $count = self::createMissingResourceUuids($mapping['resource'], $mapping['table'], "category=" . $mapping['category'] . "&code=" . $code['code']);
                    $mappedCounter += $count;
                }
            }
        }
        return $mappedCounter;
    }

    public static function createMappingRecordForResourcePaths($targetUuid, $resource, $table, $resourcePath = array())
    {
        $uuidRegistry = new UuidRegistry(['table_name' => 'uuid_mapping', 'mapped' => true]);


        $columns = ['`uuid`', '`resource`', '`table`', '`target_uuid`', '`created`', '`resource_path`'];
        $bind_insert_str = "VALUES (?, ?, ?, ?, NOW(), ?)";
        $insertStatement = "INSERT INTO `uuid_mapping`(" . implode(",", $columns) . ") " . $bind_insert_str;
        if (empty($resourcePath)) {
            $uuid = $uuidRegistry->createUuid();
            $bindValues = [$uuid, $resource, $table, $targetUuid, null];
            $uuids = [$uuid];
            sqlStatementNoLog($insertStatement, $bindValues, true);
        } else {
            $uuids = $uuidRegistry->getUnusedUuidBatch(count($resourcePath));
            $index = 0;
            foreach ($resourcePath as $path) {
                $bindValues = [$uuids[$index], $resource, $table, $targetUuid, $path];
                sqlStatementNoLog($insertStatement, $bindValues, true);
                $index++;
            }
            // now insert the mapped uuids into the registry
            $uuidRegistry->insertUuidsIntoRegistry($uuids);
        }
        return $uuids;
    }

    public static function createMappingRecord($targetUuid, $resource, $table, $resourcePath = null)
    {

        return self::createMappingRecordForResourcePaths($targetUuid, $resource, $table, $resourcePath ?? []);
    }

    // For now, support one to one uuid to target table, but will plan to add one uuid to many targets tables in future
    //   when presented with that use case (this is why the uuid column in uuid_mapping is not unique btw).
    public static function createMissingResourceUuids($resource, $table, $resourcePath = null)
    {
        try {
            sqlBeginTrans();
            $counter = 0;
            do {
                $count = self::createMissingResourceUuidsStep($resource, $table, $resourcePath);
                $counter += $count;
            } while ($count > 0);
            sqlCommitTrans();
            return $counter;
        } catch (Exception $exception) {
            sqlRollbackTrans();
            throw $exception;
        }
    }

    private static function createMissingResourceUuidsStep($resource, $table, $resourcePath = null)
    {
        $counter = 0;

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
                       AND (`" . $table . "`.`uuid` IS NOT NULL AND `" . $table . "`.`uuid` != '' AND `" . $table . "`.`uuid` != '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0')
                       LIMIT " . UuidRegistry::UUID_MAX_BATCH_COUNT;
        // find the missing mapped uuids
        $resultSet = sqlStatementNoLog($sqlStatement, $bindValues);
        $gen_count = min((sqlNumRows($resultSet) ?? 0), UuidRegistry::UUID_MAX_BATCH_COUNT);
        if (!empty($gen_count) && $gen_count > 0) {
            $uuidRegistry = new UuidRegistry(['table_name' => 'uuid_mapping', 'mapped' => true]);
            $uuids = $uuidRegistry->getUnusedUuidBatch($gen_count);
            $uuidRegistry->insertUuidsIntoRegistry($uuids);
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
                $bindValues = [$uuids[$counter], $resource, $table, $row['uuid']];
                if ($include_resource_path) {
                    $bindValues[] = $resourcePath;
                }
                sqlStatementNoLog($insertStatement, $bindValues);
                $counter++;
            }
        }

        return $counter;
    }
}
