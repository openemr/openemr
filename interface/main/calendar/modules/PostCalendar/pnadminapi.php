<?php

/**
 * Admin API for the calendar
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2002 The PostCalendar Team
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @author    The PostCalendar Team
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

//=========================================================================
//  Require utility classes
//=========================================================================

if (!defined('__POSTCALENDAR__')) {
    define('__POSTCALENDAR__', 'PostCalendar');
}

$pcModInfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
$pcDir = pnVarPrepForOS($pcModInfo['directory']);
require_once("modules/$pcDir/common.api.php");
unset($pcModInfo, $pcDir);



function postcalendar_adminapi_updateCategories($args)
{
    extract($args);
    if (!isset($updates)) {
        return false;
    }

    $conn = pnDBGetConn();
    try {
        foreach ($updates as $update) {
            $conn->executeStatement($update);
        }
    } catch (Doctrine\DBAL\Exception) {
        return false;
    }

    return true;
}
function postcalendar_adminapi_deleteCategories($args)
{
    extract($args);
    if (!isset($delete)) {
        return false;
    }

    $conn = pnDBGetConn();
    try {
        $conn->executeStatement($delete);
    } catch (Doctrine\DBAL\Exception) {
        return false;
    }

    return true;
}
function postcalendar_adminapi_addCategories($args)
{
    extract($args);
    if (!isset($name)) {
        return false;
    }

    $conn = pnDBGetConn();
    $pntable = pnDBGetTables();

    $sql = "INSERT INTO $pntable[postcalendar_categories]
                                (pc_catid,pc_catname,pc_constant_id,pc_catdesc,pc_catcolor,
                                pc_recurrtype,pc_recurrspec,pc_recurrfreq,pc_duration,
                                pc_dailylimit,pc_end_date_flag,pc_end_date_type,
                                pc_end_date_freq,pc_end_all_day,pc_cattype,pc_active,pc_seq,aco_spec)
                                VALUES ('',?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    try {
        $conn->executeStatement($sql, [
            $name,
            trim((string) $constantid),
            trim((string) $desc),
            $color,
            $repeat,
            $spec,
            $recurrfreq,
            $duration,
            $limitid,
            $end_date_flag,
            $end_date_type,
            $end_date_freq,
            $end_all_day,
            $value_cat_type,
            $active,
            $sequence,
            $aco,
        ]);
    } catch (Doctrine\DBAL\Exception) {
        return false;
    }

    return true;
}
