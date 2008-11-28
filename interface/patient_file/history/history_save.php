<?php
 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("history.inc.php");
 include_once("$srcdir/acl.inc");

 // Check authorization.
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if ($thisauth != 'write' && $thisauth != 'addonly')
  die("Not authorized.");

foreach ($_POST as $key => $val) {
	if ($val == "YYYY-MM-DD") {
		$_POST[$key] = "";
	// } elseif ($val == "on") {
	//   $_POST[$key] = "1";
	}
}

/********************************************************************/

// Update history_data:
//
$newdata = array();
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'HIS' AND uor > 0 AND field_id != '' " .
  "ORDER BY group_name, seq");
while ($frow = sqlFetchArray($fres)) {
  $field_id  = $frow['field_id'];
  /*******************************************************************
  $data_type = $frow['data_type'];
  $value  = '';
  $colname = $field_id;
  if (isset($_POST["form_$field_id"])) {
    if ($data_type == 21) {
      // $_POST["form_$field_id"] is an array of checkboxes and its keys
      // must be concatenated into a |-separated string.
      foreach ($_POST["form_$field_id"] as $key => $val) {
        if (strlen($value)) $value .= '|';
        $value .= $key;
      }
    }
    else if ($data_type == 22) {
      // $_POST["form_$field_id"] is an array of text fields to be imploded
      // into "key:value|key:value|...".
      foreach ($_POST["form_$field_id"] as $key => $val) {
        $val = str_replace('|', ' ', $val);
        if (strlen($value)) $value .= '|';
        $value .= "$key:$val";
      }
    }
    else if ($data_type == 23) {
      // $_POST["form_$field_id"] is an array of text fields with companion
      // radio buttons to be imploded into "key:n:notes|key:n:notes|...".
      foreach ($_POST["form_$field_id"] as $key => $val) {
        $restype = $_POST["radio_{$field_id}"][$key];
        if (empty($restype)) $restype = '0';
        $val = str_replace('|', ' ', $val);
        if (strlen($value)) $value .= '|';
        $value .= "$key:$restype:$val";
      }
    }
    else {
      $value = $_POST["form_$field_id"];
    }
  }
  $newdata[$colname] = $value;
  *******************************************************************/
  $newdata[$field_id] = get_layout_form_value($frow);
}
updateHistoryData($pid, $newdata);

/*********************************************************************

// Compute the string of radio button values representing
// normal/abnormal exam results.
//
$ltr = "0000000000000000";
foreach ($exams as $key => $value) {
 if ($_POST[$key]) {
  $tmp = $_POST['rb_' . $key];
  $digit = ($tmp == '1') ? '1' : (($tmp == '2') ? '2' : '0');
  $index = substr($value, 0, 2);
  $ltr = substr($ltr, 0, $index) . $digit . substr($ltr, $index + 1);
 }
}

newHistoryData($pid,
 array(
  'coffee'                         => $_POST["coffee"],
  'tobacco'                        => $_POST["tobacco"],
  'alcohol'                        => $_POST["alcohol"],
  'sleep_patterns'                 => $_POST["sleep_patterns"],
  'exercise_patterns'              => $_POST["exercise_patterns"],
  'seatbelt_use'                   => $_POST["seatbelt_use"],
  'counseling'                     => $_POST["counseling"],
  'hazardous_activities'           => $_POST["hazardous_activities"],
  'last_breast_exam'               => $_POST["last_breast_exam"],
  'last_mammogram'                 => $_POST["last_mammogram"],
  'last_gynocological_exam'        => $_POST["last_gynocological_exam"],
  'last_rectal_exam'               => $_POST["last_rectal_exam"],
  'last_prostate_exam'             => $_POST["last_prostate_exam"],
  'last_physical_exam'             => $_POST["last_physical_exam"],
  'last_sigmoidoscopy_colonoscopy' => $_POST["last_sigmoidoscopy_colonoscopy"],
  'history_mother'                 => $_POST["history_mother"],
  'history_father'                 => $_POST["history_father"],
  'history_siblings'               => $_POST["history_siblings"],
  'history_offspring'              => $_POST["history_offspring"],
  'history_spouse'                 => $_POST["history_spouse"],
  'relatives_cancer'               => $_POST["relatives_cancer"],
  'relatives_tuberculosis'         => $_POST["relatives_tuberculosis"],
  'relatives_diabetes'             => $_POST["relatives_diabetes"],
  'relatives_high_blood_pressure'  => $_POST["relatives_high_blood_pressure"],
  'relatives_heart_problems'       => $_POST["relatives_heart_problems"],
  'relatives_stroke'               => $_POST["relatives_stroke"],
  'relatives_epilepsy'             => $_POST["relatives_epilepsy"],
  'relatives_mental_illness'       => $_POST["relatives_mental_illness"],
  'relatives_suicide'              => $_POST["relatives_suicide"],
  'cataract_surgery'               => $_POST["cataract_surgery"],
  'tonsillectomy'                  => $_POST["tonsillectomy"],
  'appendectomy'                   => $_POST["appendectomy"],
  'cholecystestomy'                => $_POST["cholecystestomy"],
  'heart_surgery'                  => $_POST["heart_surgery"],
  'hysterectomy'                   => $_POST["hysterectomy"],
  'hernia_repair'                  => $_POST["hernia_repair"],
  'hip_replacement'                => $_POST["hip_replacement"],
  'knee_replacement'               => $_POST["knee_replacement"],
  'name_1'                         => $_POST["name_1"],
  'value_1'                        => $_POST["value_1"],
  'name_2'                         => $_POST["name_2"],
  'value_2'                        => $_POST["value_2"],
  'additional_history'             => $_POST["additional_history"],
  'last_ecg'                       => $_POST["last_ecg"],
  'last_cardiac_echo'              => $_POST["last_cardiac_echo"],
  'last_retinal'                   => $_POST["last_retinal"],
  'last_fluvax'                    => $_POST["last_fluvax"],
  'last_pneuvax'                   => $_POST["last_pneuvax"],
  'last_ldl'                       => $_POST["last_ldl"],
  'last_hemoglobin'                => $_POST["last_hemoglobin"],
  'last_psa'                       => $_POST["last_psa"],
  'last_exam_results'              => $ltr
 )
);

*********************************************************************/

if ($GLOBALS['concurrent_layout']) {
 include_once("history.php");
} else {
 include_once("patient_history.php");
}
?>
