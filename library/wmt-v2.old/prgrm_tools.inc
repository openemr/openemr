<?php
function convertJustifyToDB($jst = '') {
	if(!$jst) return '';
  return(str_replace(',', ':', $jst).':');
}

function convertJustifyToFee($jst = '') {
	if(!$jst) return '';
	if(substr($jst,-1) == ':') $jst = substr($jst,0,-1);
  return(str_replace(':', ',', $jst));
}

function getFee($type, $code, $level='standard', $mod = '') {
	$row = sqlQuery('SELECT id, code, modifier, code_text_short, pr_price FROM '.
		'codes LEFT JOIN prices ON (id = pr_id) WHERE code_type = ? AND '.
		'code = ? AND modifier = ? AND pr_level = ?', 
		array($type, $code, $mod, $level));
	if(!isset($row{'pr_price'})) $row{'pr_price'} = '0.00';
	return($row{'pr_price'});
}

// Generate JavaScript to build the array of diagnoses.
function genDiagJS($code_type, $code) {
  global $code_types;
  if ($code_types[$code_type]['diag']) {
    echo "diags.push('$code_type|$code');\n";
  }
}

function billingExists($type, $code, $pid, $enc, $mod = '') {
	$row = sqlQuery('SELECT id FROM billing WHERE code_type = ? AND code = ? '.
		'AND modifier = ? AND pid = ? AND encounter = ? AND activity > 0', 
		array($type, $code, $mod, $pid, $enc));
	return($row{'id'});
}

function getLastBillInstance($type, $code, $pid, $date='') {
	if(!$date) $date = date('Y-m-d');
	$binds = array($type, $code, $pid);
	$sql = 'SELECT id, code_type, code, e.date FROM billing '.
		'LEFT JOIN form_encounter AS e USING encounter WHERE code_type = ? AND '.
		'code = ? AND pid = ? ';
	if($date) {
		$sql .= 'AND encounter < ? ';
		$binds[] = $date . ' 00:00:00';
	}
	$sql .= 'ORDER BY date DESC LIMIT 1'; 
	$row = sqlQuery($sql, $binds);
	return($row);
}

function updateBillingItem($id, $provider=-1, $line=array()) {
  global $code_types;
	$sql = "UPDATE billing SET";
	$binds = array();
  $list = '';
	if($provider != -1) {
		$list = ' provider_id=?';
		$binds[] = $provider;
	}
	if($code_types[$line['type']]['fee']) {
		if(isset($line['units'])) {
			if($list) $list .= ',';
			$list .= ' units=?';
			$binds[] = $line['units'];
		}
		if(isset($line['fee'])) {
			if($list) $list .= ',';
			$list .= ', fee=?';
			$binds[] = $line['fee'];
		}
	}
	if($code_types[$line['type']]['mod']) {
		if(isset($line['mod'])) {
			if($list) $list .= ',';
			$list .= ' modifier=?';
			$binds[] = $line['mod'];
		}
	}
	if($code_types[$line['type']]['just']) {
		if(isset($line['justify'])) {
			if($list) $list .= ',';
			$list .= ' justify=?';
			$binds[] = $line['justify'];
		}
	}
	if(isset($line['slot'])) {
		if($list) $list .= ',';
		$list .= ' fee_sheet_slot=?';
		$binds[] = $line['slot'];
	}
	$sql .= $list.' WHERE id=?';
	$binds[] = $id;
	if($list != '') sqlStatement($sql, $binds);
}

function deleteMissingBilling($pid, $enc, $items = array()) {
	if(!$pid || !$enc) return false;
	$sql = 'SELECT id, code_type, code, modifier, pid FROM billing WHERE '.
		'pid=? AND encounter=?';
	$binds = array($pid, $enc);
	$bres = sqlStatement($sql, $binds);
	foreach($items as $item) {
		if(!$item['type'] || !$item['code']) continue;
	}
}

function getBillingCodes($pid, $enc, $type='') {
	if(!$pid || !$enc) return false;
	$items = array();
	$binds = array($pid, $enc);
	$order = 'billing.date';
	$flds = sqlListFields('billing');
	$where = 'AND billing.activity = 1';
  if($type) {
		if(substr($type,0,1) != ' ') $type = ' ' . $type;
		if(substr($type,0,4) != ' AND') $type = ' AND' . $type;
		if(substr($type,-1) != ' ') $type .= ' ';
	}
	$where .= $type;
	if(in_array('fee_sheet_slot',$flds)) $order = 'fee_sheet_slot, ' . $order;
 	$sql = 'SELECT billing.*, code_types.* '.
			'FROM billing RIGHT JOIN code_types ON (ct_key = billing.code_type) '.
			'WHERE billing.pid=? AND encounter=? ' . $where . ' ORDER BY ' . $order;
	$bres = sqlStatement($sql, $binds);
	while($brow = sqlFetchArray($bres)) {
		$items[] = $brow;
	}
  return $items;
}
		
?>
