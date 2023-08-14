<?php

require_once("../../../globals.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/options.inc.php");

$encounter_id = isset($_REQUEST['encounter_id']) ? $_REQUEST['encounter_id'] : "";
$f_id = isset($_REQUEST['f_id']) ? $_REQUEST['f_id'] : "";
$formname = isset($_REQUEST['formname']) ? $_REQUEST['formname'] : "";
$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : "";
$formid = isset($_REQUEST['formid']) ? $_REQUEST['formid'] : "";
$selectorid = isset($_REQUEST['selector_id']) ? $_REQUEST['selector_id'] : "";

$fres = sqlStatement("SELECT * FROM layout_options " .
            "WHERE form_id = ? AND uor > 0 " .
            "ORDER BY group_id, seq", array($formname));

$formData = array();
if(!isset($group_check_list) ||  empty($group_check_list)) {
	$group_check_list = array();
}

while ($frow = sqlFetchArray($fres)) {
	if(!empty($selectorid)) {
		// This is a normal form field.
        $ldrow = sqlQuery("SELECT field_value FROM vh_predefined_lbf_selector_data vplsd WHERE " .
        "vplsd.form_id = ? and vplsd.field_id = ?", array($selectorid, $frow['field_id']));
        if (!empty($ldrow)) {
            $currvalue = $ldrow['field_value'];
        }
	} else {
		$currvalue = lbf_current_value($frow, $f_id, $encounter_id);
	}

	if(isset($frow['group_id'])) {
		$tfrow = $frow;
		$tfrow['currentvalue'] = isset($currvalue) ? $currvalue : '';
		$formData['lbf'.$frow['group_id']][] = $tfrow;
	}

	foreach ($formData as $gk => $group) {
		$checked = 0;
		foreach ($group as $fk => $field) {
			if(!empty($field['currentvalue'])) {
				$checked = 1;
				break;
			}
		}

		$group_check_list['form_cb_'.$gk] = $checked;
	}
}

echo json_encode(array(
	'formData' => $formData,
	'group_check_list' => $group_check_list,
));