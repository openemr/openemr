<?php
function AddUltrasound($thisPid,$sdate='',$title='',$comm='',$rev='')
{
	$test='';
	if($sdate == 'YYYY-MM-DD') { $sdate=''; }
	// echo "Date: ",$sdate,"<br/>\n";
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($sdate) || !empty($title) || !empty($comm) || !empty($rev)) {
  	$sql="INSERT INTO lists (date, pid, type, title, activity, ".
			"user, groupname, begdate, enddate, comments, referredby) VALUES ".
			"(NOW(), ?, 'ultrasound', ?, '1', ?, ?, ?, ?, ?, ?)";
		// echo $sql,"<br/>\n";
		$parms=array($thisPid, $title, $_SESSION['authUser'], 
				$_SESSION['authProvider'], $sdate, $sdate, $comm, $rev);
  	$test=sqlInsert($sql, $parms);
	}
	return $test;
}

function UpdateUltrasound($thisPid,$item='',$sdate='',$title='',$comm='',$rev='')
{
	if($sdate == 'YYYY-MM-DD') { $sdate=''; }
  if(!VerifyPatientID($thisPid)) { return false; }
	if(!empty($sdate) || !empty($title) || !empty($comm)|| !empty($rev)) {
		$parms= array($sdate, $sdate, $title, $comm, $_SESSION['authUser'], 
				$rev, $thisPid, $item);
  	$test=sqlStatement("UPDATE lists SET date=NOW(), begdate=?, ".
			"enddate=?, title=?, comments=?, user=?, referredby=? ".
			"WHERE pid=? AND id=?", $parms);
	}
}

function DeleteUltrasound($thisPid,$item='',$links=0)
{
  if(!VerifyPatientID($thisPid)) { return false; }
 	sqlStatement("DELETE FROM lists WHERE pid=? AND id=? AND ".
				"type='ultrasound'", array($thisPid, $item));
	if($links) {
		DeleteAllListEntries($thisPid, $item, 'ultrasound');
	}
}
?>
