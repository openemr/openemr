<?php
if(!class_exists('wmtDashboard')) {
	include_once("dashboard.class.php");
}
if(!function_exists('getAllPaps')) {

function getAllPaps($this_pid, $enc='', $stop_dt='')
{
  $sql = 'SELECT * FROM form_pap_track WHERE pid=? ';
	if($stop_dt) $sql .= 'AND pt_date <= ? ';
	$sql .= 'ORDER BY pt_date DESC';
	$parms=array($this_pid);
	if($enc != '') {
  	$sql = 'SELECT form_wmt_ll.list_id, form_pap_track.* FROM form_wmt_ll '.
			'LEFT JOIN form_pap_track on form_wmt_ll.list_id=form_pap_track.id '.
			'WHERE form_wmt_ll.pid=? AND form_wmt_ll.list_type=? AND '.
			'form_wmt_ll.encounter_id=? ';
		if($stop_dt) $sql .= 'AND pt_date <= ? ';
		$sql .= 'ORDER BY pt_date DESC';
		$parms = array($this_pid, 'pap', $enc);
	}
	if($stop_dt) $parms[] = $stop_dt;
	$all=array();
  $res = sqlStatement($sql, $parms);
	$iter=0;
  while($row = sqlFetchArray($res)) {
		$all[$iter] = $row;

		$sql = 'SELECT COUNT(*) FROM form_wmt_ll WHERE form_wmt_ll.list_type=? '.
				'AND form_wmt_ll.list_id=?';
		$chk = sqlStatement($sql, array('pap', $all[$iter]['id']));
		$num = sqlFetchArray($chk);
		$all[$iter]['pt_num_links'] = $num['COUNT(*)'];
		$iter++;
	}
  return $all;
}

function getReviewedPaps($this_pid, $cols = "*", $review='0')
{
  $sql = 'SELECT * FROM form_pap_track WHERE pid=? '.
			'AND pt_dr_reviewed=? ORDER BY pt_date DESC';
	$all=array();
  $res = sqlStatement($sql, array($this_pid, $review));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function AddPap($thisPid,$date='',$lab='',$test='',$result='',$note='',$hpv='',$rev=0,$rev_dr='',$notified=0,$nt_by='',$nt_time='')
{
	$ret = false;
  if(!VerifyPatientID($thisPid)) return false;
	if(!empty($test) || !empty($result) || !empty($note) || !empty($hpv))  {
		if($date == 'YYYY-MM-DD') $date='';
  	$sql = 'INSERT INTO form_pap_track (date, pid, activity, user, groupname, '.
			'authorized, pt_date, pt_lab, pt_test, pt_result_text, pt_result_nt, '.
			'pt_dr_reviewed, pt_dr, pt_pat_notified, pt_pat_notified_by, '.
			'pt_pat_time, pt_last_touch, pt_last_user, pt_hpv_result) VALUES '.
			'(NOW(), ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)';
		$parms=array($thisPid, $_SESSION['authUser'], $_SESSION['authProvider'],
			$_SESSION['userauthorized'], $date, $lab, $test, $result, $note, $rev, 
			$rev_dr, $notified, $nt_by, $nt_time, $_SESSION['authUser'], $hpv);
  	$ret=sqlInsert($sql, $parms);
		$db=wmtDashboard::getPidDashboard($thisPid);
		if($date > $db->db_last_pap) {
			$db->db_last_pap = $date;
			$db->update();
		}
	}
	return $ret;
}

function UpdatePap($thisPid,$item,$date='',$lab='',$test='',$result='',$note='',$hpv='',$rev='',$rev_dr=0,$notified='',$nt_by='',$nt_time='')
{
  if(!VerifyPatientID($thisPid)) return false;
	if($date == 'YYYY-MM-DD') $date='';
	if(!empty($test) || !empty($result) || !empty($note) || !empty($hpv)) {
		$parms=array($date, $lab, $test, $result, $note, $rev, $rev_dr, $hpv, 
			$notified, $nt_by, $nt_time, $_SESSION['authUser'], $thisPid, $item);
  	$test = sqlStatement('UPDATE form_pap_track SET pt_last_touch=NOW(), '.
			'pt_date=?, pt_lab=?, pt_test=?, pt_result_text=?, pt_result_nt=?, '.
			'pt_dr_reviewed=?, pt_dr=?, pt_hpv_result=?, pt_pat_notified=?, '.
			'pt_pat_notified_by=?, pt_pat_time=?, pt_last_user=? '.
			'WHERE pid=? AND id=?', $parms);
		$db=wmtDashboard::getPidDashboard($thisPid);
		if($date > $db->db_last_pap) {
			$db->db_last_pap = $date;
			$db->update();
		}
	}
}

function DeletePap($thisPid,$item=0,$links=0)
{
  if(!VerifyPatientID($thisPid)) return false;
	if(!$item || $item=='') return false;
	sqlStatement('DELETE FROM form_pap_track WHERE pid=? AND id=?', 
			array($thisPid, $item));
	if($links) DeleteAllListEntries($thisPid, $item, 'pap');
}
}

?>