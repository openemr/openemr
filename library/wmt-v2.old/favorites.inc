<?php

$use_favorites = checkSettingMode('wmt::use_favorites');

if(!function_exists('getAllFavorites')) {
function getAllFavorites($ctype='ICD10', $type='plan')
{
  $sql = 'SELECT * FROM `wmt_plan_fav` WHERE `code_type` = ? AND ' .
		'(`list_user` = ? OR `global_list` > 0) AND `type` = ? ' .
		'ORDER BY `code`, `seq`';
	$all = array();
  $res = sqlStatement($sql, array($ctype, $_SESSION['authUser'], $type));
  for($iter = 0;$row = sqlFetchArray($res);$iter++) $all[] = $row;
  return $all;
}

function getFavoritesByCode($ctype='ICD10', $code='', $type='plan')
{
  $sql = 'SELECT * FROM `wmt_plan_fav` WHERE `code_type` = ? AND `code` = ? ' .
		'AND (`list_user` = ? OR `global_list` > 1) AND `type` = ? ORDER BY `seq`';
	$all = array();
  $res = sqlStatement($sql, array($ctype, $code, $_SESSION['authUser'], $type));
  for($iter =0;$row = sqlFetchArray($res);$iter++) $all[] = $row;
  return $all;
}

function getFavoriteById($id=0)
{
	if(!$id) return false;
  $sql = "SELECT * FROM `wmt_plan_fav` WHERE `id` = ?";
  $res = sqlQuery($sql, array($id));
  return $res;
}

function AddFavorite($ctype='ICD10',$code='',$plan='',$title='',$seq='',$nt='',
				$user='',$global=0,$type='plan')
{
	$test = false;
	if(!empty($code) && (!empty($plan) || !empty($title))) {
		// THIS HACK IS JUST TO MAKE SURE SOME IDIOT IS NOT CLICKING AND CLICKING
		$sql = 'SELECT `id`, `date` FROM `wmt_plan_fav` WHERE `code_type` = ? ' .
			'AND `code` = ? AND `plan` = ? AND `type` = ? AND `list_user` = ?';
		$binds = array($ctype, $code, $plan, $type, $_SESSION['authUser']);
		$frow = sqlQuery($sql, $binds);
		if($frow{'id'}) return false;
		if(!$seq) {
			$sql = 'SELECT `seq` FROM `wmt_plan_fav` WHERE `code_type` = ? ' .
				'AND `code` = ? AND type=? AND (`list_user` = ? OR `global_list` = 1) '.
				'ORDER BY seq DESC LIMIT 1';
			$binds = array($ctype, $code, $type, $_SESSION['authUser']); 
			$frow = sqlQuery($sql,$binds);
			if(!isset($frow{'seq'})) $frow{'seq'} = 0;
			$seq = $frow{'seq'} + 10;
		}
		if(!$user) $user = $_SESSION['authUser'];
  	$sql = 'INSERT INTO `wmt_plan_fav` (`date`, `user`, `code_type`, `code`, ' .
			'`plan`, `title`, `seq`, `notes`, `list_user`, `global_list`, `type`) ' .
			'VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$binds = array($_SESSION['authUser'], $ctype, $code, $plan, $title, 
				$seq, $nt, $user, $global, $type);
  	$test = sqlInsert($sql, $binds);
	}
	return $test;
}

function DeleteFavorite($item)
{
  if(!$item) return false;
  $sql = 'DELETE FROM `wmt_plan_fav` WHERE `id` = ?';
  sqlStatement($sql, array($item));
	return true;
}

function UpdateFavorite($item,$ctype='ICD10',$code='',$plan='',$title='',$seq='',
			$nt='',$user='',$global=0)
{
	if(!$item) return false;
	if(!$user) $user = $_SESSION['authUser'];
	$sql = 'UPDATE `wmt_plan_fav` SET `date` = NOW(), `user` = ?, ' .
		'`code_type` = ?, `code` = ?, `plan` = ?, `title` = ?, `seq` = ?, ' .
		'`notes` = ?, `list_user` = ?, `global_list` = ? WHERE `id` = ?';
	$binds = array($_SESSION['authUser'], $ctype, $code, $plan, $title, $seq, $nt,
			$user, $global, $item);
  sqlStatement($sql, $binds);
	return true;
}
}
?>
