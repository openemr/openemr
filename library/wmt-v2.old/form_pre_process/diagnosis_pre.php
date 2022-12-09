<?php
unset($diag);
$diag= array();
reset($_POST);
foreach($_POST as $k => $var) {
	if(substr($k,0,3) != 'dg_') continue;
	if(is_string($var)) $var = trim($var);
	$parts = explode('_', $key);
	$key = $parts[0] . '_' . $parts[1];
	$cnt = $parts[2];
	echo "We Got Key ($key) And Count [$cnt]<br>\n";
	$diag[$cnt][$key] = $var;
	unset($_POST[$k]);
}

foreach($diag as $cnt => $item) {
	if($cnt === 0) {
		if(isset($d['dg_code'])) {
 			AddDiag($pid,$encounter,$item);
 			$dt['dg_code_0'] = $dt['dg_title_0'] = $dt['dg_plan_0'] = '';
			$dt['dg_begdt_0'] = $dt['dg_enddt_0'] = $dt['dg_seq_0'] = '';
			$dt['dg_goal'] = $dt['dg_type_0'] = $dt['tmp_dg_desc_0'] = '';
		}
	} else {
	}
}
?>
