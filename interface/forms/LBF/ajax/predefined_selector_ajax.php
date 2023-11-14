<?php

require_once("../../../globals.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/options.inc.php");

$action_mode = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";
$formname = isset($_REQUEST['formname']) ? $_REQUEST['formname'] : "";
$selection_name = isset($_REQUEST['selection_name']) ? $_REQUEST['selection_name'] : "";
$selectorIsGlobal = isset($_REQUEST['is_global']) ? $_REQUEST['is_global'] : 1;
$grp_level = isset($_REQUEST['group_level']) ? $_REQUEST['group_level'] : "";
$selector_id = isset($_REQUEST['selector_id']) ? $_REQUEST['selector_id'] : "";

/* OEMR - Predefined LBF Selections Save */
if (!empty($action_mode) && ($action_mode == "ADD_NEW" || $action_mode == "UPDATE")) {

    if(empty($selector_id)) {
        $selector_id = sqlInsert(
            "INSERT INTO vh_predefined_lbf_selector_details " .
            "( title, form_name, group_id, user, is_global ) VALUES ( ?, ?, ?, ?, ?)",
            array($selection_name, $formname, $grp_level, $_SESSION['authUserID'], $selectorIsGlobal)
        );
    } else if(!empty($selector_id)) {
        sqlStatement(
            "UPDATE vh_predefined_lbf_selector_details SET title = ?, user = ?, is_global = ? WHERE id = ? ",
            array($selection_name, $_SESSION['authUserID'], $selectorIsGlobal, $selector_id)
        );
        sqlStatementNoLog("DELETE FROM `vh_predefined_lbf_selector_data` WHERE form_id = ?", array($selector_id));
    }

    if(!empty($selector_id)) {
        $fres = sqlStatement("SELECT * FROM layout_options " .
            "WHERE form_id = ? AND uor > 0 AND field_id != '' AND " .
            "edit_options != 'H' AND edit_options NOT LIKE '%0%' " .
            "ORDER BY group_id, seq", array($formname));


        while ($frow = sqlFetchArray($fres)) {
            $field_id = $frow['field_id'];
            $data_type = $frow['data_type'];
            $group_id = $frow['group_id'];
            // If the field was not in the web form, skip it.
            // Except if it's checkboxes, if unchecked they are not returned.
            //
            // if ($data_type != 21 && !isset($_POST["form_$field_id"])) continue;
            //
            // The above statement commented out 2015-01-12 because a LBF plugin might conditionally
            // disable a field that is not applicable, and we need the ability to clear out the old
            // garbage in there so it does not show up in the "report" view of the data.  So we will
            // trust that it's OK to clear any field that is defined in the layout but not returned
            // by the form.
            //
            if ($data_type == 31) {
                continue; // skip static text fields
            }
            $value = get_layout_form_value($frow);

            if($group_id == $grp_level) {
                sqlStatement(
                    "INSERT INTO vh_predefined_lbf_selector_data " .
                    "( form_id, field_id, field_value ) VALUES ( ?, ?, ? )",
                    array($selector_id, $field_id, $value)
                );
            }
        }
    }

    echo json_encode(array('name' => $selection_name, 'id' => $selector_id));
} else if (!empty($action_mode) && $action_mode == "DELETE") {
    if(!empty($selector_id)) {
        sqlStatementNoLog("DELETE FROM `vh_predefined_lbf_selector_details` WHERE id = ?", array($selector_id));
        sqlStatementNoLog("DELETE FROM `vh_predefined_lbf_selector_data` WHERE form_id = ?", array($selector_id));
    }

    echo json_encode(array());
} else if (!empty($action_mode) && $action_mode == "get_selector") {
	$fres1 = sqlStatement("SELECT * FROM vh_predefined_lbf_selector_details WHERE ((user = ? AND is_global = 0) OR is_global = 1) AND form_name = ? AND group_id = ? ORDER BY created_date desc", array($_SESSION['authUserID'], $formname, $grp_level));

	$slist = array();

	while ($frow1 = sqlFetchArray($fres1)) {
		$slist[] = $frow1;
	}

	echo json_encode($slist);
	exit();
}
/* END */