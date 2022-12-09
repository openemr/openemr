<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once("../interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\OrderLbfForm;

$form_field = "Notes";

$rtoData = OrderLbfForm::getRtoData($form_field);

$totalCases = 0;
$outputStr = '';

foreach ($rtoData as $rk => $rto) {
 	$rto_note = $rto['rto_notes'];
 	$form_id = $rto['form_id'];
 	$grp_rto_action = $rto['grp_rto_action'];

 	if(!empty($form_id)) {
		if(!empty($rto_note) && !empty($form_id)) {	
			$lbf_form_data = OrderLbfForm::getLbfFromData($form_id);

			if(!empty($lbf_form_data)) {
				$field_exists = false;
				foreach ($lbf_form_data as $key => $field) {
					if(isset($field['field_id']) && $field['field_id'] == $form_field) {
						$field_exists = true;
					}
				}

				OrderLbfForm::saveLbfNoteValue($form_id, $form_field, $rto_note, $field_exists);
				$totalCases++;

				$outputStr .= "OrderId: ".$rto['id']. ", FormId: ". $form_id .", Note: ".$rto_note." \n";
			}
		}
	} else if(!empty($grp_rto_action) && !empty($rto['id'])) {
		$grp_title = $rto['grp_title'];
		$grp_form_id = $rto['grp_form_id'];

		// Creating a new form. Get the new form_id by inserting and deleting a dummy row.
        // This is necessary to create the form instance even if it has no native data.
        $newid = sqlInsert("INSERT INTO lbf_data " .
            "( field_id, field_value ) VALUES ( '', '' )");
        sqlStatement("DELETE FROM lbf_data WHERE form_id = ? AND " .
            "field_id = ''", array($newid));
        @OrderLbfForm::addRtoForm($rto['id'], $grp_title, $newid, $grp_form_id, $rto['pid'], "1");

        if(!empty($newid)) {
        	OrderLbfForm::saveLbfNoteValue($newid, $form_field, $rto_note, false);
			$totalCases++;

			$outputStr .= "OrderId: ".$rto['id']. ", FormId: ". $newid .", Note: ".$rto_note." \n";
		}
	}
}

echo "\nTotal Order Records Found: ".$totalCases."\n";
echo "--------------------------------------------\n\n";
echo $outputStr;