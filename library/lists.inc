<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

$ISSUE_TYPES = array(
 // table type               plural              singular    abbrev
 'medical_problem' => array('Medical Problems', 'Problem'   , 'P'),
 'allergy'         => array('Allergies'       , 'Allergy'   , 'A'),
 'medication'      => array('Medications'     , 'Medication', 'M'),
 'surgery'         => array('Surgeries'       , 'Surgery'   , 'S'),
 'dental'          => array('Dental Issues'   , 'Dental'    , 'D')
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
