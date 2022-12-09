<?php
include_once($GLOBALS['srcdir'] . '/forms.inc');
function miscBillingExists($enc, $pid) {
	$sql= 'SELECT * FROM `forms` WHERE `deleted` = 0 AND `pid` = ? AND '.
				'`encounter` = ? AND `formdir` = ?';
	$parms = array($pid, $enc, 'misc_billing_options');
	$frow = sqlQuery($sql, $parms);
	return($frow{'form_id'});
}

function addMiscBilling($enc, $pid, $cid) {
	if(!$cid) return false;
	$binds = array();
	$sql = 'SELECT * FROM form_cases WHERE `pid` = ? AND `id` = ?';
	$case = sqlQuery($sql, array($pid, $cid));
	$sql = 'INSERT INTO `form_misc_billing_options` (`date`, `pid`, `user`, ' .
		'`groupname`, `authorized`, `activity`, `employment_related`, ' .
		'`auto_accident`, `accident_state`, `other_accident`, ' .
		'`date_initial_treatment`, `prior_auth_number`, `comments`) VALUES ' . 
		'(NOW(), ?, ?, ?, 1, 1, ?, ?, ?, ?, ?, ?, ?)';
	$binds[] = $pid;
	$binds[] = $_SESSION['authUser'];
	$binds[] = $_SESSION['authProvider'];
	$binds[] = $case{'employment_related'};
	$binds[] = $case{'auto_accident'} == 1 ? 1 : 0;
	$binds[] = $case{'accident_state'};
	$binds[] = $case{'auto_accident'} == 2 ? 1 : 0;
	$binds[] = $case{'first_consult_date'};
	$binds[] = $case{'prior_auth_number'};
	$binds[] = $case{'notes'};
	$new_id = sqlInsert($sql, $binds);
	addForm($enc, 'Misc Billing Options', $new_id, 'misc_billing_options', $pid);

	$sql = 'UPDATE form_encounter SET referral_source = ? WHERE encounter = ?';
	sqlStatement($sql, array($case{'referral_source'}, $enc));
}

function mostRecentCase($pid, $force_new = FALSE) {
	if(!$pid) return '';
	$sql= 'SELECT * FROM `form_cases` WHERE `pid` = ? ORDER BY `form_dt` '.
		'DESC LIMIT 1';
	$frow = sqlQuery($sql, array($pid));
	if(!isset($frow{'id'}) || $force_new) {
		$today = date('Y-m-d');
		$sql = 'INSERT INTO `form_cases` (`date`, `pid`, `user`, `groupname`, ' .
			'`authorized`, `activity`, `form_dt`, `case_dt`, `case_description`, '.
			'`case_guarantor_pid`) VALUES (NOW(), ?, ?, ?, 1, 1, ?, ?, ?, ?)';
		$binds = array($pid, $_SESSION['authUser'], $_SESSION['authProvider'],
			$today, $today, 'Auto-Created Case for '.$today, $pid);
		$new_case_id = sqlInsert($sql, $binds);
		$sql = 'UPDATE `form_cases` SET `case_number` = ? WHERE `id` = ?';
		unset($binds);
		$binds = array($new_case_id, $new_case_id);
		sqlStatement($sql, $binds);
		$frow{'id'} = $new_case_id;
	}
	return $frow{'id'};
}
?>
