<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
set_time_limit(0);

// Stage 1, grab the first part of the title for empty diagnosis
$fres = sqlQuery('SELECT * FROM list_options WHERE option_id = "base" AND list_id = "Poverty_Level_Amounts"');
if(!isset($fres{'codes'})) $fres{'codes'} = '';
$base_amt = $fres{'codes'};
$fres = sqlQuery('SELECT * FROM list_options WHERE option_id = "addl" AND list_id = "Poverty_Level_Amounts"');
if(!isset($fres{'codes'})) $fres{'codes'} = '';
$addl_amt = $fres{'codes'};

$sql = 'SELECT id, pid, family_size, monthly_income FROM patient_data WHERE 1';
$mres = sqlStatement($sql);
while($mrow = sqlFetchArray($mres)) {
	$pid = $mrow{'pid'};
	$id = $mrow{'id'};
	$fs = $mrow{'family_size'};
	$in = $mrow{'monthly_income'};
	echo "Processing: PID [$pid] Family Size ($fs) Income <$in><br>\n";
	if(!is_int($family_size)) $family_size = '';
	if(!is_numeric($in)) $in = '';
	if(!$in || !$fs) continue;
	$poverty = $base_amt;
	if($fs > 1) $poverty += (($fs - 1) * $addl_amt);
	$lvl = ($in / $poverty) * 100;
	$lvl = round($lvl, 2);
	echo "   Updated To ($lvl)<br>\n";
	sqlStatement('UPDATE patient_data SET poverty = ? WHERE id = ?',array($lvl, $id));
}
?>
