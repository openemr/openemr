<?php

require_once("{$GLOBALS['srcdir']}/sql.inc");

function getBillingById ($id, $cols = "*")
{
	return sqlQuery("select $cols from billing where id='$id' and activity=1 order by date DESC limit 0,1");
}

function getBillingByPid ($pid, $cols = "*")
{
	return  sqlQuery("select $cols from billing where pid ='$pid' and activity=1 order by date DESC limit 0,1");
}

function getBillingByEncounter ($pid,$encounter, $cols = "code_type, code, code_text")
{
	$res = sqlStatement("select $cols from billing where encounter = '$encounter' and pid='$pid' and activity=1 order by code_type, date ASC");

	for($iter=0; $row=sqlFetchArray($res); $iter++)
	{
		$all[$iter] = $row;
	}
	return $all;
}

function addBilling($encounter_id, $code_type, $code, $code_text, $pid, $authorized = "0",$provider,$modifier="",$units="",$fee="0.00")
{
	$sql = "insert into billing (date, encounter, code_type, code, code_text, pid, authorized, user, groupname,activity,billed,provider_id,modifier,units,fee) values (NOW(), '$encounter_id', '$code_type', '$code', '$code_text', '$pid', '$authorized', '" . $_SESSION['authId'] . "', '" . $_SESSION['authProvider'] . "',1,0,$provider,'$modifier','$units','$fee')";
	
	return sqlInsert($sql);
	
}

function authorizeBilling($id, $authorized = "1")
{
	sqlQuery("update billing set authorized = '$authorized' where id = '$id'");
}

function deleteBilling($id)
{
	sqlStatement("update billing set activity = 0 where id = '$id'");
}
function clearBilling($id)
{
	sqlStatement("update billing set justify = '' where id = '$id'");
}
?>
