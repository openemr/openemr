<?
include_once("../../globals.php");


include_once("$srcdir/patient.inc");

foreach ($_POST as $key => $val) {
	if ($val == "YYYY-MM-DD") {
		$_POST[$key] = "";
	} elseif ($val == "on") {
		$_POST[$key] = "1";
	}
}



newHistoryData($pid,
$_POST["coffee"],
$_POST["tobacco"],
$_POST["alcohol"],
$_POST["sleep_patterns"],
$_POST["exercise_patterns"],
$_POST["seatbelt_use"],
$_POST["counseling"],
$_POST["hazardous_activities"],
$_POST["last_breast_exam"],
$_POST["last_mammogram"],
$_POST["last_gynocological_exam"],
$_POST["last_rectal_exam"],
$_POST["last_prostate_exam"],
$_POST["last_physical_exam"],
$_POST["last_sigmoidoscopy_colonoscopy"],
$_POST["history_mother"],
$_POST["history_father"],
$_POST["history_siblings"],
$_POST["history_offspring"],
$_POST["history_spouse"],
$_POST["relatives_cancer"],
$_POST["relatives_tuberculosis"],
$_POST["relatives_diabetes"],
$_POST["relatives_high_blood_pressure"],
$_POST["relatives_heart_problems"],
$_POST["relatives_stroke"],
$_POST["relatives_epilepsy"],
$_POST["relatives_mental_illness"],
$_POST["relatives_suicide"],
$_POST["cataract_surgery"],
$_POST["tonsillectomy"],
$_POST["appendectomy"],
$_POST["cholecystestomy"],
$_POST["heart_surgery"],
$_POST["hysterectomy"],
$_POST["hernia_repair"],
$_POST["hip_replacement"],
$_POST["knee_replacement"],
$_POST["name_1"],
$_POST["value_1"],
$_POST["name_2"],
$_POST["value_2"],
$_POST["additional_history"]
);

include_once("patient_history.php");
?>

