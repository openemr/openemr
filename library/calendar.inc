<?php

//Require once the holidays controller for the is_holiday() function
require_once($GLOBALS['incdir'] . "/main/holidays/Holidays_Controller.php");

function getIDfromUser($name)
{
    $query = "select id from users where username=? limit 1";
    $rez = sqlStatement($query, array($name));
    $row = sqlFetchArray($rez);
    if (!is_numeric($row['id'])) {
        return -1;
    } else {
        return $row['id'];
    }
}

//  returns an array of facility id and names
function getUserFacilities($uID)
{
    if (!$GLOBALS['restrict_user_facility']) {
        $rez = sqlStatement("
		select id, name, color
		from facility
		where service_location != 0
	");
    } else {
        $rez = sqlStatement("
		  select uf.facility_id as id, f.name, f.color
		  from users_facility uf
		  left join facility f on (uf.facility_id = f.id)
		  where uf.tablename='users' 
		  and uf.table_id = ? 
	    ", array($uID));
    }

    $returnVal = array();
    while ($row = sqlFetchArray($rez)) {
        $returnVal[] = $row;
    }

    return $returnVal;
}

//retrieve the name based on the username
function getNameFromUsername($username)
{
    $query = "select * from users where username like ? and username != ''";
    $res = sqlQuery($query, [$username]);
    return $res;
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
