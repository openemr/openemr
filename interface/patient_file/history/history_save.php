<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("history.inc.php");
 include_once("$srcdir/acl.inc");
 include_once("$srcdir/options.inc.php");

 // Check authorization.
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if ($thisauth != 'write' && $thisauth != 'addonly')
  die(htmlspecialchars(xl("Not authorized"),ENT_NOQUOTES));

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
  "ORDER BY group_name, seq");
while ($frow = sqlFetchArray($fres)) {
  $field_id  = $frow['field_id'];
  $newdata[$field_id] = get_layout_form_value($frow);
}
updateHistoryData($pid, $newdata);

if ($GLOBALS['concurrent_layout']) {
 include_once("history.php");
} else {
 include_once("patient_history.php");
}
?>
