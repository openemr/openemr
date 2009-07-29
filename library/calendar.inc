<?php
//function returns the next most recent appointment given a pid
function getNextAppointment ($pid) {
$query = "select * from calendar where pid=$pid and time>now() order by time limit 1";
$rez = sqlStatement($query);
echo "<!-- getNextAppointment $pid -$query- -->";
	return sqlFetchArray($rez);
}


//this function returns calendar data given date and provider
function getCalendarDay($datetime,$owner,$groupname) {
	$query = "select * from calendar where time like '$datetime%' and owner like '$owner' and groupname like '$groupname' order by time";
	$rez = sqlStatement($query);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

echo "<!-- getCalendarDay -$query- $datetime $owner $groupname -->";
	return $returnval;
}

//toggle the status of a noshow calendar entry
function toggleNoShow( $cid) {
	$rez = sqlStatement("select noshow from calendar where id=$cid");
	$row=sqlFetchArray($rez);
	$oldnoshowval = $row{noshow};
	$newnoshowval = !($oldnoshowval);
	$query = "update calendar set noshow='$newnoshowval' where id=$cid";
	sqlStatement($query);
}


//return array of provider usernames
function getProviderUsernames() {
	$rez = sqlStatement("select distinct username, lname, fname from users " .
		"where authorized = 1 and username != ''");
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}


function getIDfromUser($name) {
	$query = "select id from users where username=\"" . mysql_real_escape_string($name) . "\" limit 1";
	$rez = sqlStatement($query);
	$row = sqlFetchArray($rez);
	if (!is_numeric($row['id']))
		return -1;
	else
		return $row['id'];
}
function getAuthorizedIDs() {
	$query = "select id, username from users where authorized=1 and username != ''";
	$rez = sqlStatement($query);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;
	return $returnval;
}


function getUserInfo() {
	$rez = sqlStatement("select distinct id, username, lname, fname, authorized, " .
		"info, facility from users where username != ''");
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}
function getUsername($uID) {
	$pi = getProviderInfo($uID,false);
	if (strlen($pi["username"]) > 0) {
		return $pi['username'];
	}

	return "";
}

//	returns an array of facility id and names
function getUserFacilities($uID) {
  if (!$GLOBALS['restrict_user_facility']) {
  	$rez = sqlStatement("
		select id, name
		from facility
		where service_location != 0
	");
  } else {
	  $rez = sqlStatement("
		  select uf.facility_id as id, f.name
		  from users_facility uf
		  left join facility f on (uf.facility_id = f.id)
		  where uf.tablename='users' 
		  and uf.table_id = '$uID' 
	    ");
  }
  $returnVal = array();
  while ($row = sqlFetchArray($rez)) 
	  $returnVal[] = $row;
  return $returnVal;
}

//retrieve the name based on the username
function getNamefromUsername($username) {
	$query = "select * from users where username like '$username' and username != ''";
	$res = sqlQuery($query);
	return $res;
}

//retrieve calendar information from calendar id
function getCalendarfromID ($calid) {
	
	//this table is deprecated and has been removed from new versions
	//return sqlQuery("select * from calendar where id='$calid'");
}

//get calendar dates in a range of dates
function getCalendarRanges ($fromdate, $todate, $username) {
	$rez = sqlStatement("select * from calendar where time>='$fromdate' and time<'$todate' and owner like '$username' order by time");
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
	
}


//get calendar dates in a range of dates
function getCalendarbyTimeandRange ($time, $fromdate, $todate, $username) {
	$query = "select * from calendar where time like '% $time%' and time>='$fromdate' and time<'$todate' and owner like '$username' order by time";
	$rez = sqlStatement($query);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;
echo "<!-- getcalendarbytimeandrange -$query- -->";
	return $returnval;
	
}


//add new calendar entry item
function newCalendarItem (
$pid,
$time,
$reason,
$owner,
$groupname


) {
	
	sqlQuery("insert into calendar set pid=$pid,time='$time',reason='$reason',owner='$owner',groupname='$groupname',date=now()");
}

//delete a calendar entry item
function deleteCalendarItem( $calid,$pid) {
	sqlQuery("delete from calendar where id='$calid' and pid='$pid'");
	
}


//save an edited calendar entry item
function saveCalendarUpdate (
$calid,
$pid,
$time,
$reason,
$owner,
$groupname


) {
	
	sqlQuery("update calendar set pid=$pid,time='$time',reason='$reason',owner='$owner',groupname='$groupname',date=now() where id='$calid'");
}


//replicated functionality of postcalendar_getDate
function pc_getDate($format='%Y%m%d')  {
//    list($Date,$jumpday,$jumpmonth,$jumpyear) = pnVarCleanFromInput('Date','jumpday','jumpmonth','jumpyear');
	if(!isset($Date)) {
        // if we still don't have a date then calculate it
        $time = time();
		// check the jump menu
        if(!isset($jumpday))   $jumpday = strftime('%d',$time);
        if(!isset($jumpmonth)) $jumpmonth = strftime('%m',$time);
        if(!isset($jumpyear))  $jumpyear = strftime('%Y',$time);
		// create the correct date string
        $Date = (int) "$jumpyear$jumpmonth$jumpday";
    }
	$y = substr($Date,0,4);
	$m = substr($Date,4,2);
	$d = substr($Date,6,2);
    return strftime($format,mktime(0,0,0,$m,$d,$y));
}

?>
