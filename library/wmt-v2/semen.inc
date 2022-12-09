<?php

function getDisposition($thisPid, $acc)
{
	$sql = "SELECT * FROM form_semen_disp WHERE pid=? AND acc=? ORDER BY date";
	$parms = array($thisPid, $acc);
	$all = array();
	$res = sqlStatementNoLog($sql,$parms);
	$iter=0;
	while($row = sqlFetchArray($res)) {
 		$all[$iter] = $row;
		$iter++;
	}
	return $all;
}

function existsDisposition($thisPid, $acc='', $cat='')
{
	if(!$acc || !$cat) { return false; }
	$sql = "SELECT * FROM form_semen_disp WHERE pid=? AND acc=? AND ".
			"disp_category=? ORDER BY date DESC LIMIT 1";
	$parms = array($thisPid, $acc, $cat);
	$all = array();
	$res = sqlStatementNoLog($sql,$parms);
	$iter=0;
	while($row = sqlFetchArray($res)) {
 		$all[$iter] = $row;
		$iter++;
	}
	return $iter;
}

function AddDisposition($thisPid, $acc='', $cat='', $nt='', $dt='', $sub='',
		$tank='', $bin='', $loc='', $cid='', $vial='', $amt='', $media='', 
		$lot='', $pat='', $name='')
{
	$test = false;
  if(!VerifyPatientID($thisPid)) { return false; }
  if(!$acc) { return false; }
	if(!empty($cat) || !empty($nt)) {
		if($dt == '' || $dt == 0) { $dt=date('Y-m-d'); }
  	$sql="INSERT INTO form_semen_disp (date, pid, activity, user, groupname, ".
			"authorized, acc, disp_date, disp_category, disp_sub, disp_nt, ".
			"disp_cryo_tank, disp_cryo_bin, disp_cryo_loc, disp_cryo_id, ".
			"disp_date_stamp, disp_cryo_vials, disp_cryo_vial_amt, disp_cryo_media, ".
			"disp_cryo_media_lot, disp_pat_id, disp_pat_name) VALUES (NOW(), ".
			"?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";
		$parms= array($thisPid, '1', $_SESSION['authUser'], 
			$_SESSION['authProvider'], $_SESSION['userauthorized'], $acc, $dt,
			$cat, $sub, $nt, $tank, $bin, $loc, $cid, $vial, $amt, $media, $lot,
			$pat, $name);
		
  	$test=sqlInsert($sql, $parms);
	}
	return $test;
}

function DeleteDisposition($thisPid, $item, $acc='')
{
	$test = false;
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!$item) { return false; }
  $sql="DELETE FROM form_semen_disp WHERE id=? AND pid=?";
  sqlStatement($sql, array($item, $thisPid));
}

function UpdateDisposition($thisPid,$acc='',$cat='',$nt='',$dt='',$sub='',
	$tank='',$bin='', $loc='', $cid='', $vial='', $amt='', $media='', 
	$lot='', $pat='', $name='')
{
	$test=false;
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!$acc) { return false; }
	if(!empty($cat) || !empty($nt)) {
		if($dt == '' || $dt == 0) { $dt=date('Y-m-d'); }
		$parms= array($dt, $cat, $sub, $nt, $tank, $bin, $loc, $cid, 
			$vial, $amt, $media, $lot, $pat, $name, $pid, $acc);
  	$test=sqlStatement("UPDATE form_semen_disp SET date=NOW(), disp_date=?, ".
			"disp_category=?, disp_sub=?, disp_nt=?, disp_cryo_tank=?, ".
			"disp_cryo_bin=?, disp_cryo_loc=?, disp_cryo_id=?, disp_cryo_vials=?, ".
			"disp_cryo_vial_amt=?, disp_cryo_media=?, disp_cryo_media_lot=?, ".
			"disp_pat_id=?, disp_pat_name=? WHERE pid=? AND acc=?", $parms);
	}
}

function AnalysisExists($thisPid, $thisAcc) {
	$test = false;
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!$thisAcc) { return false; }
	$sql = "SELECT id, pid, acc FROM form_semen_analysis WHERE pid=? AND acc=?";
	$parms = array($thisPid, $thisAcc);
	$dres = sqlStatementNoLog($sql, $parms);
	$drow = sqlFetchArray($dres);
	if($drow{'pid'} == $thisPid && $drow{'acc'} == $thisAcc) { $test = $drow{'id'}; }
	return $test;
}

function IUIExists($thisPid, $thisAcc) {
	$test = false;
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!$thisAcc) { return false; }
	$sql = "SELECT id, pid, acc FROM form_semen_iui WHERE pid=? AND acc=?";
	$parms = array($thisPid, $thisAcc);
	$dres = sqlStatementNoLog($sql, $parms);
	$drow = sqlFetchArray($dres);
	if($drow{'pid'} == $thisPid && $drow{'acc'} == $thisAcc) { $test = $drow{'id'}; }
	return $test;
}

?>
