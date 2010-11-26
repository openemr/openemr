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
 	$arraySqlBind = array();
	$sql = "select $cols from forms where encounter = ? and pid=? ";
	array_push($arraySqlBind,$encounter,$pid);
	if(!empty($name)){
		$sql .= "and form_name=? ";
		array_push($arraySqlBind,$name);
	}
  // This puts vitals first in the list, and newpatient last:
  $sql .= "ORDER BY FIND_IN_SET(formdir,'vitals') DESC, date DESC";

	$res = sqlStatement($sql,$arraySqlBind);

	for($iter=0; $row=sqlFetchArray($res); $iter++)
	{
		$all[$iter] = $row;
	}
	return $all;
}

function addForm($encounter, $form_name, $form_id, $formdir, $pid,
 $authorized = "0", $date="NOW()", $user="", $group="")
{
	if (!$user) $user = $_SESSION['authUser'];
	if (!$group) $group = $_SESSION['authProvider'];
	$sql = "insert into forms (date, encounter, form_name, form_id, pid, " .
		"user, groupname, authorized, formdir) values (";
	if($date == "NOW()")
		$sql .= "$date";
	else
		$sql .= "'$date'";
	$sql .= ", '$encounter', '$form_name', '$form_id', '$pid', '$user',          " .
		"'$group', '$authorized', '$formdir')";
	return sqlInsert( $sql );
}

function authorizeForm($id, $authorized = "1")
{
	sqlQuery("update forms set authorized = '$authorized' where id = '$id'");
}

function getEncounters($pid,$dateStart='',$dateEnd='')
{

        if ($dateStart && $dateEnd) {
                $res = sqlStatement("select distinct encounter from form_encounter where pid = ? and date >= ? and date <= ? order by date desc;", array($pid,$dateStart,$dateEnd) );
        }
        else if ($dateStart && !$dateEnd) {
                $res = sqlStatement("select distinct encounter from form_encounter where pid = ? and date >= ? order by date desc;", array($pid,$dateStart) );
        }
        else if (!$dateStart && $dateEnd) {
                $res = sqlStatement("select distinct encounter from form_encounter where pid = ? and date <= ? order by date desc;", array($pid,$dateEnd) );
        }
        else {
                $res = sqlStatement("select distinct encounter from form_encounter where pid = ? order by date desc;", array($pid) );
        }

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

function getFormNameByFormdirAndFormid ($formdir,$form_id)
{
        return sqlQuery("select form_name from forms where formdir='$formdir' and form_id='$form_id'");
}

function getFormNameByFormdir ($formdir)
{
	return sqlQuery("select form_name from forms where formdir='$formdir'");
}
?>
