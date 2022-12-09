<?php
if(isset($GLOBALS['wmt::use_proc_favorites'])) $use_proc_favorites = $GLOBALS['wmt::use_proc_favorites'];
if($use_proc_favorites == '0' || $use_proc_favorites == 'false' || 
											$use_proc_favorites == '') $use_proc_favorites = false;

function getAllProcFavorites($type='CPT4', $ord=' grp_title, code ASC')
{
  $sql = "SELECT wmt_diag_fav.*, list_options.seq AS grp_seq, ".
			"list_options.title AS grp_title FROM ".
			"wmt_diag_fav LEFT JOIN list_options ON (grp = option_id AND ".
			"list_id = 'Procedure_Categories') WHERE code_type=? AND (list_user=? ".
			"OR global_list = 1) ORDER BY $ord";
	$all = array();
  $res = sqlStatement($sql, array($type, $_SESSION['authUser']));
  for($iter = 0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function AddDiagFavorite($type='CPT4',$code='',$title='',$seq='',
						$user='',$global=0,$grp='')
{
	$test = false;
	if(!empty($code)) {
		// THIS SEEMS LIKE NO ONE NEEDS - WASTE OF TIME
		/***
		if(!$seq) {
			$sql = "SELECT seq FROM wmt_diag_fav WHERE code_type=? ".
				"AND list_user=? AND grp = ? ORDER BY seq DESC LIMIT 1";
			$binds = array($type, $_SESSION['authUser'], $grp); 
			$frow = sqlQuery($sql, $binds);
			if(!isset($frow{'seq'})) $frow{'seq'} = 0;
			$seq = $frow{'seq'} + 10;
		}
		***/
		if(!$user) $user = $_SESSION['authUser'];
  	$sql = "INSERT INTO wmt_diag_fav (date, user, code_type, code, ".
			"title, seq, list_user, global_list, grp) VALUES ".
			"(NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
		$binds = array($_SESSION['authUser'], $type, $code, $title, $seq, 
				$user, $global, $grp);
  	$test = sqlInsert($sql, $binds);
	}
	return $test;
}

function DeleteDiagFavorite($item)
{
  if(!$item) return false;
  $sql = "DELETE FROM wmt_diag_fav WHERE id=?";
  sqlStatement($sql, array($item));
	return true;
}

function UpdateDiagFavorite($item,$type='ICD10',$code='',$title='',$seq='',
			$user='',$global=0,$grp='')
{
	if(!$item) return false;
	if($user != '') {
		$sql = "UPDATE wmt_diag_fav SET date=NOW(), user=?, code_type=?, code=?, ".
			"title=?, seq=?, list_user=?, global_list=?, grp=? WHERE id=?";
		$binds = array($_SESSION['authUser'], $type, $code, $title, $seq, 
				$user, $global, $grp, $item);
	} else {
		$sql = "UPDATE wmt_diag_fav SET date=NOW(), user=?, code_type=?, code=?, ".
			"title=?, seq=?, global_list=?, grp=? WHERE id=?";
		$binds = array($_SESSION['authUser'], $type, $code, $title, $seq, 
				$global, $grp, $item);
	}
  sqlStatement($sql, $binds);
	return true;
}
?>
