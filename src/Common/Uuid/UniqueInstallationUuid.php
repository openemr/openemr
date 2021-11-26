<?php

/**
 * uniqueInstallationUuid.php class
 *
 *    Support for unique installation UUID. Will create it if it doesn't yet exists.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Uuid;

use Ramsey\Uuid\Uuid;

class UniqueInstallationUuid
{
    public static function getUniqueInstallationUuid()
    {
        // Return $GLOBALS if it exists
        if (!empty($GLOBALS['unique_installation_id'])) {
            return $GLOBALS['unique_installation_id'];
        }

        // If $GLOBALS does not exists, then try to get it from globals table and return if it exists
        $sqlArray = sqlQuery("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'unique_installation_id'");
        if (!empty($sqlArray['gl_value'])) {
            return $sqlArray['gl_value'];
        }

        // Need to create it and store it and return it
        $uuid4 = Uuid::uuid4();
        $uuid = $uuid4->toString();
        sqlStatement("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'unique_installation_id'", [$uuid]);
        return $uuid;
    }
}
