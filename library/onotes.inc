<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

function getOnoteById ($id, $cols = "*")
{
	return sqlQuery("select $cols from onotes where id='$id' order by date DESC limit 0,1");
}

function getOnoteByDate ($date, $activity = "1", $cols = "*", $limit="all", $offset="0")
{

	if($limit == "all")
	{

		$sql = "select $cols from onotes where date like '%$date%' ";
		if ($activity != "all")
			$sql .= "and activity='$activity' ";
		$sql .= "order by date DESC";
	}
	else
	{
		$sql = "select $cols from onotes where date like '%$date%' ";
		if ($activity != "all")
			$sql .= "and activity ='$activity' ";
		$sql .= "order by date DESC LIMIT $offset,$limit";
	}
	
	$res = sqlStatement($sql);
//	if ($res == FALSE) { return FALSE; }
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$all[$iter] = $row;
	return $all;
}

function addOnote($body, $activity = "1")
{
	$body = mysql_escape_string($body);
	return sqlInsert("insert into onotes (date, body, user, groupname, activity) values (NOW(), '$body', '". $_SESSION['authUser'] ."', '". $_SESSION['authProvider'] ."', '$activity')");
}

function disappearOnote($id)
{
	sqlStatement("update onotes set activity = '0' where id='$id'");
	return true;
}

function reappearOnote ($id)
{
	sqlStatement("update onotes set activity = '1' where id='$id'");
	return true;
}
?>
