<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once("../interface/globals.php");
require_once("$srcdir/wmt-v2/wmtstandard.inc");

function getRtoData($field_id = '') {
	$result = sqlStatement("SELECT fr.*, fol.form_id AS form_id, fol.formdir, fol.form_name, ld.field_id, gp.grp_rto_action AS grp_rto_action, gp.grp_title AS grp_title, gp.grp_form_id AS grp_form_id FROM form_rto AS fr LEFT JOIN form_order_layout AS fol ON fol.pid = fr.pid AND fol.rto_id = fr.id LEFT JOIN lbf_data AS ld ON ld.form_id = fol.form_id AND fol.form_id IS NOT NULL AND ld.field_id = ? LEFT JOIN layout_group_properties AS gp ON gp.grp_rto_action = fr.rto_action WHERE gp.grp_rto_action IS NOT NULL AND (fr.rto_notes != '') AND (ld.field_id IS NULL OR ( ld.field_id IS NOT NULL AND (ld.field_value = '' OR ld.field_value IS NULL )))", array($field_id));
	$data = array();
	while ($row = sqlFetchArray($result)) {
		$data[] = $row;
	}
	return $data;
}

function addRtoForm(
    $rto_id,
    $form_name,
    $form_id,
    $formdir,
    $pid,
    $authorized = "0",
    $date = "NOW()",
    $user = "",
    $group = "",
    $therapy_group = 'not_given'
) {

    global $attendant_type;
    if (!$user) {
        $user = $_SESSION['authUser'];
    }

    if (!$group) {
        $group = $_SESSION['authProvider'];
    }

    if ($therapy_group == 'not_given') {
        $therapy_group = $attendant_type == 'pid' ? null : $_SESSION['therapy_group'];
    }

    //print_r($_SESSION['therapy_group']);die;
        $arraySqlBind = array();
    $sql = "insert into form_order_layout (date, rto_id, form_name, form_id, pid, " .
        "user, groupname, authorized, formdir, therapy_group_id) values (";
    if ($date == "NOW()") {
        $sql .= "$date";
    } else {
        $sql .= "?";
                array_push($arraySqlBind, $date);
    }

    $sql .= ", ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        array_push($arraySqlBind, $rto_id, $form_name, $form_id, $pid, $user, $group, $authorized, $formdir, $therapy_group);
    return sqlInsert($sql, $arraySqlBind);
}

function saveLbfNoteValue($form_id, $form_field, $note, $update = false) {
    if($update === false) {
        $sql = "INSERT INTO `lbf_data`( form_id, field_id, field_value ) VALUES (?, ?, ?) ";
        return sqlInsert($sql, array(
            $form_id,
            $form_field,
            $note
        ));
    } else {
        return sqlStatement("UPDATE lbf_data SET field_value = ? WHERE form_id = ? AND  field_id = ?", array($note, $form_id, $form_field));
    }
}

$form_field = "Notes";

$rtoData = getRtoData($form_field);

$totalCases = 0;
$outputStr = '';

foreach ($rtoData as $rk => $rto) {
 	$rto_note = $rto['rto_notes'];
 	$form_id = $rto['form_id'];
 	$grp_rto_action = $rto['grp_rto_action'];

 	if(!empty($form_id)) {
		if(!empty($rto_note) && !empty($form_id)) {	
			$lbf_form_data = getLbfFromData($form_id);

			if(!empty($lbf_form_data)) {
				$field_exists = false;
				foreach ($lbf_form_data as $key => $field) {
					if(isset($field['field_id']) && $field['field_id'] == $form_field) {
						$field_exists = true;
					}
				}

                //OEMR - Change
				saveLbfNoteValue($form_id, $form_field, $rto_note, $field_exists);
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

        //OEMR - Change
        addRtoForm($rto['id'], $grp_title, $newid, $grp_form_id, $rto['pid'], "1");

        if(!empty($newid)) {
            //OEMR - Change
        	saveLbfNoteValue($newid, $form_field, $rto_note, false);

			$totalCases++;

			$outputStr .= "OrderId: ".$rto['id']. ", FormId: ". $newid .", Note: ".$rto_note." \n";
		}
	}
}

echo "\nTotal Order Records Found: ".$totalCases."\n";
echo "--------------------------------------------\n\n";
echo $outputStr;