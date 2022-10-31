<?php

/**
 * Holds functions for the calendar, one is for holidays
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

//Require once the holidays controller for the is_holiday() function
require_once($GLOBALS['incdir'] . "/main/holidays/Holidays_Controller.php");

// Returns an array of the facility ids and names that the user is allowed to access.
// Access might be for inventory purposes ($inventory=true) or calendar purposes.
//
function getUserFacilities($uID, $orderby = 'id', $inventory = false)
{
    $restrict = $inventory ? $GLOBALS['gbl_fac_warehouse_restrictions'] : $GLOBALS['restrict_user_facility'];
    if ($restrict) {
        // No entries in this table means the user is not restricted.
        $countrow = sqlQuery(
            "SELECT count(*) AS count FROM users_facility WHERE " .
            "tablename = 'users' AND table_id = ?",
            array($uID)
        );
    }
    if (!$restrict || empty($countrow['count'])) {
        $rez = sqlStatement(
            "SELECT id, name, color FROM facility " .
            "ORDER BY $orderby"
        );
    } else {
        // This query gets facilities that the user is authorized to access.
        $rez = sqlStatement(
            "SELECT f.id, f.name, f.color " .
            "FROM facility AS f " .
            "JOIN users AS u ON u.id = ? " .
            "WHERE f.id = u.facility_id OR f.id IN " .
            "(SELECT DISTINCT uf.facility_id FROM users_facility AS uf WHERE uf.tablename = 'users' AND uf.table_id = u.id) " .
            "ORDER BY f.$orderby",
            array($uID)
        );
    }
    $returnVal = array();
    while ($row = sqlFetchArray($rez)) {
        $returnVal[] = $row;
    }
    return $returnVal;
}

// Returns an array of warehouse IDs for the given user and facility.
function getUserFacWH($uID, $fID)
{
    $res = sqlStatement(
        "SELECT warehouse_id FROM users_facility WHERE tablename = ? " .
        "AND table_id = ? AND facility_id = ?",
        array('users', $uID, $fID)
    );
    $returnVal = array();
    while ($row = sqlFetchArray($res)) {
        if ($row['warehouse_id'] === '') {
            continue;
        }
        $returnVal[] = $row['warehouse_id'];
    }
    return $returnVal;
}

 /**
 * Check if day is weekend day
 * @param (int) $day
 * @return boolean
 */
function is_weekend_day($day)
{

    if (in_array($day, $GLOBALS['weekend_days'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * This function checks if a certain date (YYYY/MM/DD) is a marked as a holiday/closed event in the events table
 * @param (int) $day
 * @return boolean
 */
function is_holiday($date)
{
    Holidays_Controller::is_holiday($date);
}
