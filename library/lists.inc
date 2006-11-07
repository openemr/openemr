<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

$ISSUE_TYPES = array(
 // table type               plural              singular    abbrev style
 'medical_problem' => array('Medical Problems', 'Problem'   , 'P',  0),
 'allergy'         => array('Allergies'       , 'Allergy'   , 'A',  0),
 'medication'      => array('Medications'     , 'Medication', 'M',  0),
 'surgery'         => array('Surgeries'       , 'Surgery'   , 'S',  0),
 'dental'          => array('Dental Issues'   , 'Dental'    , 'D',  0)
 //
 // Styles are:
 // 0 - Normal, as in 2.8.1.
 // 1 - Simplified: only title, start date, comments and an Active checkbox;
 //     no diagnosis, occurrence, end date, referred-by or sports fields.
 //     Internally we'll still use a null end date to indicate active.
 //
);

function getListById ($id, $cols = "*")
{
	return sqlQuery("select $cols from lists where id='$id' order by date DESC limit 0,1");
}

function getListByType ($pid, $type, $cols = "*", $active = "all", $limit = "all", $offset="0")
{
	if($active == "all")
		$sql = "select $cols from lists where pid='$pid' and type='$type' order by date DESC";
	else
		$sql = "select $cols from lists where pid='$pid' and type='$type' and activity='$active' order by date DESC";
	if ($limit != "all")
		$sql .= " limit $offset,$limit";
	

	$res = sqlStatement($sql);
	for($iter =0;$row = sqlFetchArray($res);$iter++)
		$all[$iter] = $row;
	return $all;

}

function addList ($pid, $type, $title, $comments, $activity = "1")
{
	return sqlInsert("insert into lists (date, pid, type, title, activity, comments, user, groupname) values (NOW(), '$pid', '$type', '$title', '$activity', '$comments', '".$_SESSION['authUser']."', '".$_SESSION['authProvider']."')");
}

function disappearList ($id)
{
	sqlStatement("update lists set activity = '0' where id='$id'");
	return true;
}

function reappearList ($id)
{
	sqlStatement("update lists set activity = '1' where id='$id'");
	return true;
}
?>
