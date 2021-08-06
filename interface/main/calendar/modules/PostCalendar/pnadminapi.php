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

    list($dbconn) = pnDBGetConn();
    foreach ($updates as $update) {
        $result = $dbconn->Execute($update);
        if ($result === false) {
            return false;
        }
    }

    return true;
}
function postcalendar_adminapi_deleteCategories($args)
{
    extract($args);
    if (!isset($delete)) {
        return false;
    }

    list($dbconn) = pnDBGetConn();
    $result = $dbconn->Execute($delete);
    if ($result === false) {
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

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $name = pnVarPrepForStore($name);
    $constantid = trim(pnVarPrepForStore($constantid));
    $desc = trim(pnVarPrepForStore($desc));
    $value_cat_type = pnVarPrepForStore($value_cat_type);
    $color = pnVarPrepForStore($color);
    $recurrtype = pnVarPrepForStore($repeat);
    $recurrspec = pnVarPrepForStore($spec);
    $recurrfreq = pnVarPrepForStore($recurrfreq);
    $duration = pnVarPrepForStore($duration);
    $limitid = pnVarPrepForStore($limitid);
    $end_date_flag = pnVarPrepForStore($end_date_flag);
    $end_date_type = pnVarPrepForStore($end_date_type);
    $end_date_freq = pnVarPrepForStore($end_date_freq);
    $end_all_day = pnVarPrepForStore($end_all_day);
    $active = pnVarPrepForStore($active);
    $sequence = pnVarPrepForStore($sequence);
    $aco = pnVarPrepForStore($aco);

    $sql = "INSERT INTO $pntable[postcalendar_categories] 
                                (pc_catid,pc_catname,pc_constant_id,pc_catdesc,pc_catcolor,
                                pc_recurrtype,pc_recurrspec,pc_recurrfreq,pc_duration,
    							pc_dailylimit,pc_end_date_flag,pc_end_date_type,
    							pc_end_date_freq,pc_end_all_day,pc_cattype,pc_active,pc_seq,aco_spec)
                                VALUES ('','$name','$constantid','$desc','$color',
                                '$recurrtype','$recurrspec','$recurrfreq',
                                '$duration','$limitid','$end_date_flag','$end_date_type',
                                '$end_date_freq','$end_all_day','$value_cat_type','$active','$sequence','$aco')";


    //print "sql is $sql \n";
    $result = $dbconn->Execute($sql);
    if ($result === false) {
        print $dbconn->ErrorMsg();
        return false;
    }

    return true;
}
