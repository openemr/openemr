<?php
	require_once("{$GLOBALS['srcdir']}/sql.inc");

	function newEvent($event,$user, $groupname, $comments="")
	{
		$adodb = $GLOBALS['adodb']['db'];
		if($event == ("login" || "logout" || "backup" || "view" || "auth" || "upload"))
		{
			$sql = "insert into log (date, event, user, groupname, comments) values (NOW(), " . $adodb->qstr($event) . "," . $adodb->qstr($user) . "," . $adodb->qstr($groupname) . "," .$adodb->qstr($comments) . ")";
			return sqlInsertClean($sql);
		}

		else
		{
			return false;
		}
	}

function	getEventByDate($date, $cols = "*")
	{
		$res = sqlStatement("select $cols from log where date like '%$date%' order by date DESC");

		for($iter=0; $row=sqlFetchArray($res); $iter++)
		{
			$all[$iter] = $row;
		}

		return $all;
	}







?>
