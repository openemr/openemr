<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

function getPnoteById ($id, $cols = "*")
{
	return sqlQuery("select $cols from pnotes where id='$id' order by date DESC limit 0,1");
}

function getPnotesByDate ($date, $activity = "1", $cols = "*", $pid = "%", $limit="all", $start=0)
{

	if($limit == "all")
	{

		$sql = "select $cols from pnotes where date like '%$date%' and pid like '$pid' ";
		if ($activity != "all")
			$sql .= "and activity = '$activity' ";
		$sql .= "order by date DESC";
	}
	else
	{
		$sql = "select $cols from pnotes where date like '%$date%' and pid like '$pid' ";
                if ($activity != "all")
                        $sql .= "and activity = '$activity' ";
                $sql .= "order by date DESC LIMIT $start, $limit";
	}

	$res = sqlStatement($sql);
	
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$all[$iter] = $row;
	return $all;
}

function getPnotesByPid ($pid, $activity = "1", $cols = "*", $limit=10, $start=0)
{
	$res = sqlStatement("select $cols from pnotes where pid like '$pid' and activity = '$activity' order by date DESC LIMIT $start,$limit");
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$all[$iter] = $row;
	return $all;
}

function addPnote($pid, $body, $authorized = "0", $activity = "1")
{
	$body = mysql_escape_string($body);
	return sqlInsert("insert into pnotes (date, body, pid, user, groupname, authorized, activity) values (NOW(), '$body','$pid', '" . $_SESSION['authUser'] . "', '". $_SESSION['authProvider'] ."', '$authorized', '$activity')");
}

function authorizePnote($id, $authorized = "1")
{
	sqlQuery("update pnotes set authorized = '$authorized' where id = '$id'");
}

function disappearPnote($id)
{
	sqlStatement("update pnotes set activity = '0' where id='$id'");
	return true;
}

function reappearPnote ($id)
{
	sqlStatement("update pnotes set activity = '1' where id='$id'");
	return true;
}
?>
