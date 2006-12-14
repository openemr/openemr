<?php

require_once("{$GLOBALS['srcdir']}/sql.inc");

$GLOBALS['form_exit_url'] = $GLOBALS['concurrent_layout'] ?
	"$rootdir/patient_file/encounter/encounter_top.php" :
	"$rootdir/patient_file/encounter/patient_encounter.php";

function getFormById ($id, $cols = "*")
{
	$sql = "select $cols from forms where id='$id' order by date DESC limit 0,1";
	//echo $sql . "<br />";
	return sqlQuery($sql);
}

function getFormInfoById ($id, $cols = "*")
{
	$sql = "select $cols from forms where id='$id' order by date DESC limit 0,1";
	//echo $sql . "<br />";
	$result =  sqlQuery($sql);
	if ($result['formdir'] == "newpatient") {
		$result['formdir'] = "encounter";
	}
	$sql = "select * from form_" . $result['formdir'] . " where id='" . $result['form_id']  . "'";
	//echo $sql . "<br />";
	$result =  sqlQuery($sql);
	//print_r($result);
	return $result;
}

function getFormsByPid ($pid, $cols = "*")
{
	return sqlQuery("select $cols from forms where pid ='$pid'");
}

function getFormByEncounter($pid,$encounter, $cols="form_id, form_name", $name="")
{
	$sql = "select $cols from forms where encounter = '$encounter' and pid='$pid' ";
	if(!empty($name)){
		$sql .= "and form_name='" . $name ."' ";
	}
	$sql .= "order by date DESC";
	
	$res = sqlStatement($sql);

	for($iter=0; $row=sqlFetchArray($res); $iter++)
	{
		$all[$iter] = $row;
	}
	return $all;
}

function addForm($encounter, $form_name, $form_id, $formdir, $pid, $authorized = "0", $date="NOW()")
{
	$sql = "insert into forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, formdir) values (";
	if($date == "NOW()")
		$sql .= "$date";
	else
		$sql .= "'$date'";
		
	$sql .= ", '$encounter', '$form_name', '$form_id', '$pid', '" . $_SESSION['authUser'] . "', '" . $_SESSION['authProvider'] . "', '$authorized', '$formdir')";
	return sqlInsert( $sql );
}

function authorizeForm($id, $authorized = "1")
{
	sqlQuery("update forms set authorized = '$authorized' where id = '$id'");
}

function getEncounters($pid)
{
	// $res = sqlStatement("select distinct encounter from forms where pid='$pid' order by date desc;");

  $res = sqlStatement("select distinct encounter from form_encounter where pid = '$pid' order by date desc;");

	for($iter=0; $row=sqlFetchArray($res); $iter++)
	{
		$all[$iter] = $row;
	}
	return $all;
}

function getEncounterDateByEncounter($encounter)
{
	// $sql = "select date from forms where encounter='$encounter' order by date";
	$sql = "select date from form_encounter where encounter='$encounter' order by date";
	return sqlQuery($sql);
}

function getFormNameByFormdir ($formdir)
{
	return sqlQuery("select form_name from forms where formdir='$formdir'");
}
?>
