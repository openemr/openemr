<?php
if(isset($GLOBALS['wmt::use_cpt_favorites'])) $use_cpt_favorites = $GLOBALS['wmt::use_cpt_favorites'];

function AddOrUpdatePlan($thisPid, $enc, $type='', $code='', $mod='', 
		$note='', $title='') {
	// echo "Add Or Update ($type) [$code]<br>\n";
	// echo "Add Plan Title: [$title] Plan ($note)<br>\n";
	if(!$type || !$code) return false;
	$sql = 'SELECT lists.id, lists.pid FROM lists WHERE pid=? AND reinjury_id=? '.
		'AND diagnosis=? AND injury_part=? AND type="procedures"';
	$binds = array($thisPid, $enc, $type.':'.$code, $mod);
 	$row = sqlQuery($sql, $binds);
	if(!isset($row{'id'})) $row{'id'} = '';
	// echo "[$thisPid, $enc, $type, $code, $mod, $note, $title]<br>\n";
	if($row{'id'}) {
		$binds = array($title, $note, $row{'id'});
		// echo "Really Update Plan Title: [$title] ($plan)<br>\n";
	
		sqlStatement('UPDATE lists SET title=?, comments=? WHERE id=?', $binds);
	} else {
		if($title == '') $title = lookup_code_descriptions($type.':'.$code);
		// echo "Really Set New Title: [$title] ($plan)<br>\n";
		$sql = 'INSERT INTO lists (date, pid, type, title, activity, user, '.
			'groupname, comments, diagnosis, reinjury_id, injury_part) VALUES '.
			'(NOW(), ?, "procedures", ?, 1, ?, ?, ?, ?, ?, ?)';
		$binds = array($thisPid, $title, $_SESSION['authUser'], 
			$_SESSION['authProvider'], $note, $type.':'.$code, $enc, $mod);
		sqlInsert($sql, $binds);
	}
}

function GetEncounterProcedures($thisPid, $enc='', $source='') {
	$all = array();
	if(!$enc) return $all;
  $sql = 'SELECT lists.diagnosis, lists.comments, lists.reinjury_id, '.
		'lists.title, SUBSTRING_INDEX(diagnosis, ":", 1) AS stype, '.
		'SUBSTRING_INDEX(diagnosis, ":", -1) AS scode, lists.injury_part, '.
		'code_types.*, '.
		'billing.id FROM lists LEFT JOIN billing ON (billing.pid = lists.pid '.
		'AND billing.encounter = lists.reinjury_id AND '.
		'lists.injury_part = billing.modifier AND '.
		'billing.code = SUBSTRING_INDEX(diagnosis, ":", -1)) '.
		'LEFT JOIN code_types ON '.
		'(code_types.ct_key = SUBSTRING_INDEX(diagnosis, ":", 1)) '.
		'WHERE lists.reinjury_id=? AND lists.type = "procedures" AND '.
		'lists.activity = 1 AND lists.pid=? '.
		'AND (billing.id IS NULL OR billing.id="" OR billing.id=0) '.
		'ORDER BY lists.date';
	$binds = array($enc, $thisPid);
	if($source == 'billing') {
		$order = 'billing.date';
		$flds = sqlListFields('billing');
		if(in_array('fee_sheet_slot',$flds)) $order = 'fee_sheet_slot, '.$order;
  	$sql = 'SELECT billing.*, lists.*, code_types.ct_proc '.
			'FROM billing RIGHT JOIN code_types ON (ct_key = billing.code_type AND '.
			'ct_proc = 1) LEFT JOIN lists ON (billing.pid = lists.pid AND '.
			'billing.encounter = lists.reinjury_id AND '.
			'billing.modifier = lists.injury_part AND '.
			'lists.diagnosis = CONCAT(billing.code_type, ":", billing.code) AND '.
			'lists.type = "procedures" AND lists.activity = 1) WHERE '.
			'billing.pid=? AND encounter=? AND billing.activity=1 ORDER BY '.$order;
		$binds = array($thisPid, $enc);
	}

  $res = sqlStatementNoLog($sql,$binds);
	$iter=0;
  while($row = sqlFetchArray($res)) {
		$row['proc_on_fee'] = 0;
		if($source == 'billing') $row['proc_on_fee'] = 1;
  	$all[$iter] = $row;
		$iter++;
	}
  return $all;
}

function DeleteProcedure($thisPid, $enc, $type='', $code='', $mod='')
{
  if(!$type || !$code) return false;
  $sql = 'DELETE FROM billing WHERE pid=? AND encounter=? AND code_type=? '.
			'AND code=?';
	$binds = array($thisPid, $enc, $type, $code);
	if($mod != '') {
		$sql .= ' AND modifier=?';
		$binds[] = $mod;
	}
  sqlStatement($sql, $binds);
  $sql = 'DELETE FROM lists WHERE pid=? AND reinjury_id=? AND diagnosis=? ';
	$binds = array($thisPid, $enc, $type.':'.$code);
	if($mod != '') {
		$sql .= ' AND injury_part=?';
		$binds[] = $mod;
	}
  sqlStatement($sql, $binds);
	return true;
}

?>
