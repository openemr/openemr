<?php
// 2005-03-14
// High risk screening storage and update module
//

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$flds = sqlListFields("form_high_risk_screening_record");
$flds = array_slice($flds,7);
foreach ($flds as $key => $fld){
  $data[$fld] = '';
}

foreach ($_POST as $k => $var) {
  $_POST[$k] = mysql_escape_string($var);
  //echo "$var\n";
}

$ii = 0;
while ($ii<13){
  $ii++;
  $data["record_".$ii] = 
$_POST["hemoglobin_date_".$ii].';'.$_POST["hemoglobin_res_".$ii].'|~'.
$_POST["bone_density_date_".$ii].';'.$_POST["bone_density_res_".$ii].'|~'.
$_POST["bacteriuria_date_".$ii]	.';'.$_POST["bacteriuria_res_".$ii].'|~'.
$_POST["std_date_".$ii]	.';'.$_POST["std_res_".$ii].'|~'.
$_POST["hiv_date_".$ii]	.';'.$_POST["hiv_res_".$ii].'|~'.
$_POST["genetic_date_".$ii].';'.$_POST["genetic_res_".$ii].'|~'.
$_POST["rubella_date_".$ii].';'.$_POST["rubella_res_".$ii].'|~'.
$_POST["tb_skin_date_".$ii].';'.$_POST["tb_skin_res_".$ii].'|~'.
$_POST["lipid_date_".$ii].';'.$_POST["lipid_res_".$ii].'|~'.
$_POST["mammography_date_".$ii].';'.$_POST["mammography_res_".$ii].'|~'.
$_POST["fasting_glucose_date_".$ii].';'.$_POST["fasting_glucose_res_".$ii]	.'|~'.
$_POST["tsh_date_".$ii].';'.  $_POST["tsh_res_".$ii].'|~'.
$_POST["cancer_date_".$ii].';'. $_POST["cancer_res_".$ii].'|~'.
$_POST["hepatitis_c_date_".$ii].';'.$_POST["hepatitis_c_res_".$ii];
}

if ($_GET["mode"] == "new"){
  if ($encounter == "") { $encounter = date("Ymd"); }
  $newid = formSubmit("form_high_risk_screening_record", $data, $_GET["id"], $userauthorized);
  addForm($encounter, "High risk screening record", $newid, "high_risk_screening_record", $pid, $userauthorized);
  $_SESSION["encounter"] = $encounter;
} elseif ($_GET["mode"] == "update") {
  $q1 = '';
  foreach ($data as $key => $val){
    $q1 .= "$key='$val', ";
  }
  sqlInsert("update form_high_risk_screening_record set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, $q1  date = NOW() where id=$id");
}
sqlInsert("update patient_data set DOB='".$_POST['pbdate']."' where  id=$pid");

//$_SESSION["pid"] = $pid;
formHeader("Redirecting....");
formJump();
formFooter();
?>
