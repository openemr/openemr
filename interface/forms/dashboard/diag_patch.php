<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt/wmtstandard.inc');
require_once('../../../custom/code_types.inc.php');

// Stage 1, grab the first part of the title for empty diagnosis
$sql = "SELECT * FROM lists WHERE type = 'medical_problem' AND diagnosis ".
		"IS NULL OR diagnosis = ''";
$mres = sqlStatement($sql);
echo "Phase 1 start<br>\n";
while($mrow = sqlFetchArray($mres)) {
	$pid= $mrow{'pid'};
	$id= $mrow{'id'};
	$diag = $mrow{'diagnosis'};
	$title = trim($mrow{'title'});
	$parts = explode('-', $title);
	echo "Processing: PID [$pid] Item ($id) Old Diag <$diag> '$title'<br>\n";
	if(count($parts) > 1) {
		echo "Got Parts [".$parts[0]."]  (".$parts[1].")<br>\n";
		$parts[0] = trim($parts[0]);
		$desc = lookup_code_descriptions('ICD9:'.$parts[0]);	
		if($desc != '') {
			$sql = "UPDATE lists SET diagnosis=? WHERE id=?";
			$binds = array('ICD9:'.$parts[0], $id);
			echo "  -> Updating ID ($id) to Diag [ICD9:".$parts[0]."]<br>\n";
			sqlStatement($sql, $binds);
			continue;
		}
		$desc = lookup_code_descriptions('ICD10:'.$parts[0]);	
		if($desc != '') {
			$sql = "UPDATE lists SET diagnosis=? WHERE id=?";
			$binds = array('ICD10:'.$parts[0], $id);
			echo "  -> Updating ID ($id) to Diag [ICD10:".$parts[0]."]<br>\n";
			sqlStatement($sql, $binds);
			continue;
		}
	}
	echo "We did not continue<br>\n";
	echo "  -> ID ($id) Diag [$parts[0]] *WARNING*  could NOT be referenced<br>\n";
}

echo "Phase 1 complete<br>\n";
echo "Phase 2 start<br>\n";

// Stage 2, add the ICD prefix to any codes without it
$sql = "SELECT * FROM lists WHERE type = 'medical_problem' AND diagnosis ".
		"IS NOT NULL AND diagnosis != '' AND diagnosis NOT LIKE 'ICD%'";
$mres = sqlStatement($sql);
while($mrow = sqlFetchArray($mres)) {
	$pid= $mrow{'pid'};
	$id= $mrow{'id'};
	$diag = $mrow{'diagnosis'};
	$title = trim($mrow{'title'});
	echo "Processing: PID [$pid] Item ($id) Old Diag <$diag> '$title'<br>\n";
	$desc = lookup_code_descriptions('ICD9:'.$diag);	
	if($desc != '') {
		if($title == '') $title = $diag.' - '.$desc;
		$sql = "UPDATE lists SET diagnosis=?, title=? WHERE id=?";
		$binds = array('ICD9:'.$diag, $title, $id);
		echo "  -> Updating ID ($id) to Diag [ICD9:$diag]<br>\n";
		sqlStatement($sql, $binds);
		continue;
	}
	$desc = lookup_code_descriptions('ICD10:'.$diag);	
	if($desc != '') {
		if($title == '') $title = $diag.' - '.$desc;
		$sql = "UPDATE lists SET diagnosis=?, title=? WHERE id=?";
		$binds = array('ICD10:'.$diag, $title, $id);
		echo "  -> Updating ID ($id) to Diag [ICD10:$diag]<br>\n";
		sqlStatement($sql, $binds);
		continue;
	}
	echo "We did not continue<br>\n";
	echo "  -> ID ($id) Diag [$diag] *WARNING*  could NOT be referenced<br>\n";
}

echo "Phase 2 complete<br>\n";
echo "Phase 3 start<br>\n";

// Stage 3, verify each ICD prefix 
$sql = "SELECT * FROM lists WHERE type = 'medical_problem' AND diagnosis ".
		"IS NOT NULL AND diagnosis != '' AND diagnosis LIKE 'ICD%'";
$mres = sqlStatement($sql);
while($mrow = sqlFetchArray($mres)) {
	$pid= $mrow{'pid'};
	$id= $mrow{'id'};
	$diag = $mrow{'diagnosis'};
	$title = trim($mrow{'title'});
	echo "Processing: PID [$pid] Item ($id) Old Diag <$diag> '$title'<br>\n";
	$diags = explode(';',$diag);
	$new_diag = '';
	foreach($diags as $item) {
		$iter = explode(':',$item);
		$type = $iter[0];
		$code = $iter[1];	
		echo "Working With Code [$code] and Type ($type)<br>\n";
		$desc = lookup_code_descriptions($type.':'.$code);	
		if($desc != '') {
			if($new_diag) $new_diag .= ';';
			$new_diag .= $type.':'.$code;
			continue;
		}
		$type = ($type == 'ICD9') ? 'ICD10' : 'ICD9';
		$desc = lookup_code_descriptions($type.':'.$code);	
		if($desc != '') {
			if($new_diag) $new_diag .= ';';
			$new_diag .= $type.':'.$code;
			continue;
		}
		echo "We did not continue<br>\n";
		echo "  -> ID ($id) Diag [$diag] *WARNING*  could NOT be referenced<br>\n";
	}
	if($new_diag != $diag && $new_diag != '') {
		echo "  -> ID ($id) New Diag [$new_diag]<br>\n";
		$sql = "UPDATE lists SET diagnosis=? WHERE id=?";
		sqlStatement($sql,array($new_diag, $id));
	}
}

?>
