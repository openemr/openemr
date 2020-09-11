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

use OpenEMR\Common\Uuid\UuidRegistry;

class UuidMapping
{
    // For now, support one to one uuid to target table, but will plan to add one uuid to many targets tables in future
    //   when presented with that use case (this is why the uuid column in uuid_mapping is not unique btw).
    public static function createMissingResourceUuids($resource, $table)
    {
        // find the missing mapped uuids
        $resultSet = sqlStatementNoLog(
            "SELECT `" . $table . "`.`uuid`
                       FROM `" . $table . "`
                       LEFT OUTER JOIN `uuid_mapping` ON `" . $table . "`.`uuid` = `uuid_mapping`.`target_uuid` AND `uuid_mapping`.`resource` = ?
                       WHERE (`uuid_mapping`.`uuid` IS NULL OR `uuid_mapping`.`uuid` = '' OR `uuid_mapping`.`uuid` = '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0')
                       AND (`" . $table . "`.`uuid` IS NOT NULL AND `" . $table . "`.`uuid` != '' AND `" . $table . "`.`uuid` != '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0')",
            [$resource]
        );
        while ($row = sqlFetchArray($resultSet)) {
            // populate the missing mapped uuids
            sqlQueryNoLog("INSERT INTO `uuid_mapping` (`uuid`, `resource`, `table`, `target_uuid`, `created`) VALUES (?, ?, ?, ?, NOW())", [(new UuidRegistry(['table_name' => 'uuid_mapping', 'mapped' => true]))->createUuid(), $resource, $table, $row['uuid']]);
        }
    }
}
