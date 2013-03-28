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

        $arraySqlBind = array();
	$sql = "insert into forms (date, encounter, form_name, form_id, pid, " .
		"user, groupname, authorized, formdir) values (";
	if($date == "NOW()") {
		$sql .= "$date";
        }
	else {
		$sql .= "?";
                array_push($arraySqlBind,$date);
        }
	$sql .= ", ?, ?, ?, ?, ?, ?, ?, ?)";
        array_push($arraySqlBind,$encounter,$form_name,$form_id,$pid,$user,$group,$authorized,$formdir);
	return sqlInsert($sql,$arraySqlBind);
}

function authorizeForm($id, $authorized = "1")
{
	sqlQuery("update forms set authorized = '$authorized' where id = '$id'");
}

function getEncounters($pid,$dateStart='',$dateEnd='',$encounterRuleType='')
{

        $arraySqlBind = array();
        
        if ($encounterRuleType) {
                // Only collect certain type of encounters (list_options item from the rule_enc_types list that is mapped via enc_category_map table)
                $from = "form_encounter LEFT JOIN enc_category_map ON (form_encounter.pc_catid = enc_category_map.main_cat_id)";
                $where = "enc_category_map.rule_enc_id = ? and ";
                array_push($arraySqlBind,$encounterRuleType);
        }
        else {
                // Collect all encounters
                $from = "form_encounter";
        }

        if ($dateStart && $dateEnd) {
                $where .= "form_encounter.pid = ? and form_encounter.date >= ? and form_encounter.date <= ?";
                array_push($arraySqlBind,$pid,$dateStart,$dateEnd);
        }
        else if ($dateStart && !$dateEnd) {
                $where .= "form_encounter.pid = ? and form_encounter.date >= ?";
                array_push($arraySqlBind,$pid,$dateStart);
        }
        else if (!$dateStart && $dateEnd) {
                $where .= "form_encounter.pid = ? and form_encounter.date <= ?";
                array_push($arraySqlBind,$pid,$dateEnd);
        }
        else {
                $where .= "form_encounter.pid = ?";
                array_push($arraySqlBind,$pid);
        }

        $res = sqlStatement("SELECT distinct encounter FROM $from WHERE $where ORDER by date desc;", $arraySqlBind);

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

function getProviderIdOfEncounter($encounter)
{
        $sql = "select provider_id from form_encounter where encounter='$encounter' order by date";
        $res = sqlQuery($sql);
        return $res['provider_id'];
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
