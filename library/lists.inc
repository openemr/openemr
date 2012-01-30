<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");

if (!empty($GLOBALS['ippf_specific'])) {
  $ISSUE_TYPES = array(
    // table type, plural, singular, abbrev, style, force show patient summary screen
    'medical_problem' => array(xl('Medical Problems'),xl('Problem')      ,xl('P'),0,1),
    'allergy'         => array(xl('Allergies')       ,xl('Allergy')      ,xl('Y'),0,1),
    'medication'      => array(xl('Medications')     ,xl('Medication')   ,xl('M'),0,1),
    'surgery'         => array(xl('Surgeries')       ,xl('Surgery')      ,xl('S'),0,0),
    'ippf_gcac'       => array(xl('Abortions')       ,xl('Abortion')     ,xl('A'),3,0),
    'contraceptive'   => array(xl('Contraception')   ,xl('Contraception'),xl('C'),4,0),
//  'ippf_srh'        => array(xl('SRH')             ,xl('SRH')          ,xl('R'),5),
  );
}
else if (!empty($GLOBALS['athletic_team'])) {
  $ISSUE_TYPES = array(
    'football_injury' => array(xl('Football Injuries'),xl('Injury') ,xl('I'),2,1),
    'medical_problem' => array(xl('Medical Problems') ,xl('Medical'),xl('P'),0,0),
    'allergy'         => array(xl('Allergies')        ,xl('Allergy'),xl('A'),1,0),
    'general'         => array(xl('General')          ,xl('General'),xl('G'),1,0),
  );
}
else { // default version
  $ISSUE_TYPES = array(
    // table type, plural, singular, abbrev, style, force show patient summary screen
    'medical_problem' => array(xl('Medical Problems'),xl('Problem')   ,xl('P'),0,1),
    'allergy'         => array(xl('Allergies')       ,xl('Allergy')   ,xl('A'),0,1),
    'medication'      => array(xl('Medications')     ,xl('Medication'),xl('M'),0,1),
    'surgery'         => array(xl('Surgeries')       ,xl('Surgery')   ,xl('S'),0,0),
    'dental'          => array(xl('Dental Issues')   ,xl('Dental')    ,xl('D'),0,0),
    //
    // Styles are:
    // 0 - Normal, as in 2.8.1.
    // 1 - Simplified: only title, start date, comments and an Active checkbox;
    //     no diagnosis, occurrence, end date, referred-by or sports fields.
    //     Internally we'll still use a null end date to indicate active.
    // 2 - Football Injury.
    //
    // force show patient summary screen is:
    // 0 - Do not show this category on the patient summary screen if there have
    //     been no issues entered in for this category.
    // 1 - Show this category on the patient summary screen even if no issues have
    //     been entered for this category.
    //
  );
}

//
// 06/2009 - BM Migrated the ISSUE_OCCURRENCES to list_options
//

$ISSUE_CLASSIFICATIONS = array(
  0   => xl('Unknown or N/A'),
  1   => xl('Trauma'),
  2   => xl('Overuse')
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

function getListTouch ($patient_id,$type)
{
	$ret = sqlQuery("SELECT `date` FROM `lists_touch` WHERE pid=? AND type=?", array($patient_id,$type) );

	if (!empty($ret)) {
		return $ret['date'];
	}
	else {
		return false;
	}
}

function setListTouch ($patient_id,$type)
{
	$ret = sqlQuery("SELECT `date` FROM `lists_touch` WHERE pid=? AND type=?", array($patient_id,$type) );

	if (!empty($ret)) {
                // Already touched, so can exit
		return;
	}
	else {
		sqlStatement("INSERT INTO `lists_touch` ( `pid`,`type`,`date` ) VALUES ( ?, ?, NOW() )", array($patient_id,$type) );
	}
}
?>
