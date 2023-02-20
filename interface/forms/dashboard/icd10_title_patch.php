<?php
$ignoreAuth = true;
$SITE = 'default';
$_SESSION['site_id'] = 'default';
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt/wmtstandard.inc');
require_once('../../../custom/code_types.inc.php');

// Check and ICD10 diags and update the title
$sql = "SELECT * FROM lists WHERE type = 'medical_problem' AND diagnosis ".
		"LIKE 'ICD10%'";
$mres = sqlStatement($sql);
while($mrow = sqlFetchArray($mres)) {
	$pid= $mrow{'pid'};
	$id= $mrow{'id'};
	$diag = explode(';', $mrow{'diagnosis'});
	$title = trim($mrow{'title'});
	echo "Processing: PID [$pid] Item ($id) Old Diag -$diag[0]- '$title'<br>\n";
	$desc = lookup_code_descriptions($diag[0]);	
	if($desc != '') {
		$pos = (stripos($title, $desc));
		if($pos === false) {
			$code = explode(':', $diag[0]);
			$title = $code[1].' - '.$desc;
			$sql = "UPDATE lists SET title=? WHERE id=?";
			$binds = array($title, $id);
			sqlStatement($sql, $binds);
			echo "  -> Updating ID ($id) to Title [$title]<br>\n";
		}
	}
}

?>
