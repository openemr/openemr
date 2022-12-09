<?php
function checkReview($pid, $enc, $dir, $module, $user = '', $dt = '') {
	if(!$pid) return '';
	if(!$user) $user = $_SESSION['authUserID'];
	$sql = 'SELECT * FROM `wmt_mod_review` AS rv WHERE `pid` = ? AND '.
		'`module` = ? AND `formdir` = ?';
	$binds = array($pid, $module, $dir);
	if($enc) {
		$sql .= ' AND `encounter` = ?';
		$binds[] = $enc;
	} else if($dt) {
		$sql .= ' AND SUBSTRING(`date`,1,10) = ?';
		$binds[] = $dt;
	}
	if($user) {
		$sql .= ' AND `user` = ?';
		$binds[] = $user;
	}
	$sql .= ' ORDER BY `date` DESC';
	$rrow = sqlQuery($sql, $binds);
	if(!isset($rrow['id'])) $rrow['id'] = '';
	return $rrow['id'];
}

function addReview($mode, $pid, $enc, $dir, $module, $user = '', $dt = '') {
	if(!$pid) return '';
	if(!$user) $user = $_SESSION['authUserID'];
	$mode = strtolower(substr($mode,0,3)) == 'del' ? '0' : '1';
	if($exists = checkReview($pid, $enc, $dir, $module, $user, $dt)) {
		$sql = 'UPDATE wmt_mod_review SET activity = ? WHERE id = ?';
		$binds = array($mode, $exists);
		sqlStatement($sql, $binds);
	} else {
		if(!$dt) $dt = date('Y-m-d H:i:s');
		$sql = 'INSERT INTO wmt_mod_review (`pid`, `module`, `user`, `date`, '.
			'`formdir`, `encounter`, `activity`) VALUES (?, ?, ?, ?, ?, ?, ?)';
		$binds = array($pid, $module, $user, $dt, $dir, $enc, $mode);
		sqlInsert($sql, $binds);
	}
}

function getReviewStatus($pid, $enc, $dir, $module, $user = '', $not = '') {
	if(!$pid) return '';
	if(!isset($GLOBALS['wmt::user_name_display_order'])) $GLOBALS['wmt::user_name_display_order'] = 'first';
	$ret = array('id' => '', 'user_id' => '', 'user_name' => '');
	// IF WE'RE CALLING FROM THE DASHBOARD WE WILL SEND BACK THE MOST RECENT
	if($dir == 'dashboard') {
		$sql = 'SELECT rv.*, u.`lname`, u.`fname`, u.`mname`, u.`suffix` FROM '.
			'`wmt_mod_review` AS rv LEFT JOIN `users` AS u ON (rv.`user` = u.`id`) '.
			'WHERE rv.`pid` = ? AND rv.`module` = ? AND ' .
			'SUBSTRING(rv.`date`,1,10) = ? AND `activity` > 0';
		$binds = array($pid, $module, date('Y-m-d'));
		if($user) {
			$sql .= ' AND user = ?';
			$binds[] = $user;
		}
		$sql .= ' ORDER BY `date` DESC';
		$row = sqlQuery($sql, $binds);
		if(!isset($row{'id'})) return $ret;
	} else {
		$sql = 'SELECT rv.*, u.`lname`, u.`fname`, u.`mname`, u.`suffix` FROM '.
			'`wmt_mod_review` AS rv LEFT JOIN `users` AS u ON (rv.`user` = u.`id`) '.
			'WHERE rv.`pid` = ? AND rv.`module` = ? AND `activity` > 0';
		$binds = array($pid, $module);
		if($enc) {
			$sql .= ' AND rv.encounter = ?';
			$binds[] = $enc;
		}
		if($user) {
			$sql .= ' AND `user` = ?';
			$binds[] = $user;
		}
		if($not) {
			$sql .= ' AND `user` != ?';
			$binds[] = $not;
		}
		$sql .= ' ORDER BY `date` DESC';
		$row = sqlQuery($sql, $binds);
		if(!isset($row{'id'})) return $ret;
	}
	if(!isset($row{'suffix'})) $row{'suffix'} = '';
	if($GLOBALS['wmt::user_name_display_order'] == 'last') {
   	$name = $row{'lname'};
		if($row{'suffix'}) $name .= ' ' . $row{'suffix'};
		$name .= ', ' . $row{'fname'} . ' ' . $row{'mname'};
	} else {
		$name = $row{'fname'};
		if($row{'mname'}) $name .=  ' ' . $row{'mname'};
		$name .= ' ' . $row{'lname'};
		if($row{'suffix'}) $name .= ', ' . $row{'suffix'};
	}
	if(!$enc) $name .= ' on ' . $row{'date'};
	$ret['id'] = $row{'id'};
	$ret['user_id'] = $row{'user'};
	$ret['user_name'] = $name;
	return $ret;
}
?>
