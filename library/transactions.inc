<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

function getTransById ($id, $cols = "*")
{
	return sqlQuery("select $cols from transactions where id='$id' order by date DESC limit 0,1");
}

function getTransByPid ($pid, $cols = "*")
{
	$res = sqlStatement("select $cols from transactions where pid='$pid' order by date DESC");
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$all[$iter] = $row;
	return $all;
}

function newTransaction($pid, $body, $title, $authorized = "0")
{
	$body = mysql_escape_string($body);
	return sqlInsert("insert into transactions (date, title, body, pid, user, groupname, authorized) values (NOW(), '$title', '$body', '$pid', '". $_SESSION['authUser'] ."', '". $_SESSION['authProvider'] ."', '$authorized')");
}

function authorizeTransaction($id, $authorized = "1")
{
	sqlQuery("update transactions set authorized = '$authorized' where id = '$id'");
}
?>
