<?php
if(isset($GLOBALS['wmt::use_diag_favorites'])) $use_diag_favorites = $GLOBALS['wmt::use_diag_favorites'];
if(isset($GLOBALS['wmt::use_cpt_favorites'])) $use_cpt_favorites = $GLOBALS['wmt::use_cpt_favorites'];

if(!function_exists('getAllDiagFavorites')) {
function getAllDiagFavorites($ctype='ICD10', $ord=' grp_title, code ASC')
{
	$group_list_name = 'Diagnosis_Categories';
	if(strtoupper($ctype) == 'CPT4' || strtoupper($ctype) == 'HCPCS') 
		$group_list_name = 'Procedure_Categories';
  $sql = 'SELECT wmt_diag_fav.*, list_options.seq AS grp_seq, '.
			'list_options.title AS grp_title, pr_price FROM '.
			'wmt_diag_fav LEFT JOIN list_options ON (grp = option_id AND '.
			"list_id = '$group_list_name') LEFT JOIN codes ON ".
			'(wmt_diag_fav.code_type = codes.code_type AND '.
			'wmt_diag_fav.code = codes.code AND codes.active = 1) '.
			'LEFT JOIN prices ON (codes.id = pr_id AND pr_level = "standard") '.
			'WHERE wmt_diag_fav.code_type = ? AND '.
			'(list_user = ? OR global_list = 1) ORDER BY ' . $ord;
	$all = array();
  $res = sqlStatement($sql, array($ctype, $_SESSION['authUser']));
  for($iter = 0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function AddDiagFavorite($ctype='ICD10',$code='',$title='',$seq='',
						$user='',$global=0,$grp='',$mod='')
{
	$test = false;
	if(!empty($code)) {
		if(!$user) $user = $_SESSION['authUser'];
  	$sql = 'INSERT INTO `wmt_diag_fav` (`date`, `user`, `code_type`, `code`, '.
			'`title`, `seq`, `list_user`, `global_list`, `grp`, `modifier`) '.
			'VALUES '.
			'(NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE '.
			'`seq` = VALUES(`seq`)';
		$binds = array($_SESSION['authUser'], $ctype, $code, $title, $seq, 
				$user, $global, $grp, $mod);
  	$test = sqlInsert($sql, $binds);
	}
	return $test;
}

function DeleteDiagFavorite($item)
{
  if(!$item) return false;
  $sql = 'DELETE FROM `wmt_diag_fav` WHERE `id` = ?';
  sqlStatement($sql, array($item));
	return true;
}

function UpdateDiagFavorite($item,$ctype='ICD10',$code='',$title='',$seq='',
			$user='',$global=0,$grp='',$mod='')
{
	if(!$item) return false;
	if($user != '') {
		$sql = 'UPDATE `wmt_diag_fav` SET `date`=NOW(), `user`=?, `code_type`=?, '.
			'`code`=?, `title`=?, `seq`=?, `list_user`=?, `global_list`=?, `grp`=?, '.
			'`modifier`=? WHERE `id`=?';
		$binds = array($_SESSION['authUser'], $ctype, $code, $title, $seq, 
				$user, $global, $grp, $mod, $item);
	} else {
		$sql = 'UPDATE `wmt_diag_fav` SET `date`=NOW(), `user`=?, `code_type`=?, '.
			'`code`=?, `title`=?, `seq`=?, `global_list`=?, `grp`=?, `modifier`=? '.
			'WHERE `id`=?';
		$binds = array($_SESSION['authUser'], $ctype, $code, $title, $seq, 
				$global, $grp, $mod, $item);
	}
  sqlStatement($sql, $binds);
	return true;
}
}
?>
