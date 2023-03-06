<?php

require_once("../../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("../php/lbf_functions.php");
require_once("$srcdir/wmt-v2/wmtstandard.inc");

$encounter_id = isset($_REQUEST['encounter_id']) ? $_REQUEST['encounter_id'] : "";
$f_id = isset($_REQUEST['f_id']) ? $_REQUEST['f_id'] : "";
$formname = isset($_REQUEST['formname']) ? $_REQUEST['formname'] : "";
$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : "";
$formid = isset($_REQUEST['formid']) ? $_REQUEST['formid'] : "";

$fres = sqlStatement("SELECT * FROM layout_options " .
            "WHERE form_id = ? AND uor > 0 " .
            "ORDER BY group_id, seq", array($formname));

$formData = array();

while ($frow = sqlFetchArray($fres)) {
	$currvalue = lbf_current_value($frow, $f_id, $encounter_id);

	if(isset($frow['group_id'])) {
		$tfrow = $frow;
		$tfrow['currentvalue'] = isset($currvalue) ? $currvalue : '';
		$formData['lbf'.$frow['group_id']][] = $tfrow;
	}
}

//Process Before Save
preProcessData($pid);

echo json_encode(array(
	'formData' => $formData,
	'group_check_list' => $group_check_list,
));