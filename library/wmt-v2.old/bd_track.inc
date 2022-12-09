<?php
function AddBoneDensity($thisPid,$sdate='',$result='',$comm='',$rev='')
{
	if($sdate == 'YYYY-MM-DD') $sdate = '';
  if(!VerifyPatientID($thisPid)) return false;
	if(!empty($sdate) || !empty($result) || !empty($comm) || !empty($rev)) {
  	$sql="INSERT INTO lists (date, pid, type, title, activity, extrainfo, ".
			"user, groupname, begdate, enddate, comments, referredby) VALUES ".
			"(NOW(), ?, 'bonedensity', 'Bone Density', '1', ?, ?, ?, ?, ?, ?, ?)";
		$binds= array($thisPid, $result, $_SESSION['authUser'], 
			$_SESSION['authProvider'], $sdate, $sdate, $comm, $rev);
  	$test = sqlInsert($sql, $binds);
		setListTouch($thisPid, 'bonedensity');

		if($sdate != '0000-00-00') {
			if(strpos($_SERVER['PHP_SELF'],'dashboard') === false) {
				if(!class_exists('wmtDashboard')) 
						include($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
				$dash = wmtDashboard::getPidDashboard($thisPid);
				if($sdate > $dash->db_last_bone) {
					$dash->db_last_bone = $sdate;
					$dash->update();
					$dash->update_rules();
				}
			}
			if(checkSettingMode('wmt::auto_create_bd')) {
				if(!function_exists('AddImageHistory')) 
								include($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
				$irow = sqlQuery("SELECT * FROM list_options WHERE list_id=? ".
					"AND codes=?", array('Image_types','bone_density_link'));
				if(!isset($irow{'option_id'})) $irow{'option_id'} = '';
				if($irow{'option_id'}) {
					$dup = sqlQuery("SELECT * FROM lists WHERE type = ? AND pid = ? ".
						"AND injury_type = ? AND begdate = ?", 
						array('wmt_img_history', $thisPid, $irow{'option_id'}, $sdate));	
					if(!isset($dup{'id'})) $dup{'id'} = '';
					$comm = 'Auto-Add From Bone Density to Image Link';
					if(!$dup{'id'}) AddImageHistory($thisPid, $irow{'option_id'}, $sdate, 
						$result, $comm);
				}
			}
		}
	}
}

function UpdateBoneDensity($thisPid,$item='',$sdate='',$result='',$comm='',$rev='')
{
	if($sdate == 'YYYY-MM-DD') $sdate = '';
  if(!VerifyPatientID($thisPid)) return false;
	if(!empty($sdate) || !empty($result) || !empty($comm)|| !empty($rev)) {
		$binds=array($sdate, $sdate, $result, $comm, $_SESSION['authUser'],
			$rev, $thisPid, $item);
  	$test=sqlStatement("UPDATE lists SET date=NOW(), begdate=?, ".
			"enddate=?, extrainfo=?, comments=?, user=?, referredby=? WHERE pid=? ".
			"AND id=? AND type='bonedensity'", $binds);
		setListTouch($thisPid, 'bonedensity');
	}
}

function DeleteBoneDensity($thisPid,$item='',$links=0)
{
  if(!VerifyPatientID($thisPid)) return false;
 	sqlStatement("DELETE FROM lists WHERE pid=? ".
						"AND id=? AND type='bonedensity'", array($thisPid, $item));
	if($links) DeleteAllListEntries($thisPid, $item, 'bonedensity');
}
?>
