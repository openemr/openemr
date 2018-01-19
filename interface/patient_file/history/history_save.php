<?php



 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("history.inc.php");
 include_once("$srcdir/acl.inc");
 include_once("$srcdir/options.inc.php");

 // Check authorization.
if (acl_check('patients', 'med')) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
        die(htmlspecialchars(xlt("Not authorized for this squad."), ENT_NOQUOTES));
    }
}

if (!acl_check('patients', 'med', '', array('write','addonly'))) {
    die(htmlspecialchars(xlt("Not authorized"), ENT_NOQUOTES));
}

foreach ($_POST as $key => $val) {
    if ($val == "YYYY-MM-DD") {
        $_POST[$key] = "";
    }
}

// Update history_data:
//
$newdata = array();
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'HIS' AND uor > 0 AND field_id != '' " .
  "ORDER BY group_id, seq");
while ($frow = sqlFetchArray($fres)) {
    $field_id  = $frow['field_id'];
  //get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
    if (isset($_POST["form_$field_id"])) {
        $newdata[$field_id] = get_layout_form_value($frow);
    }
}

updateHistoryData($pid, $newdata);

 include_once("history.php");
