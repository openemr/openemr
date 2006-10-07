<?php
// 2005-03-14
// Patient intake history storage and update module
//

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$flds = sqlListFields("form_patient_intake_history");
$flds = array_slice($flds,7);
foreach ($flds as $key => $fld){
  if (substr($key, 0,4)!='ros_'){ $data[$fld] = ''; }
}
$flds = sqlListFields("form_patient_intake_history_ros");
$flds = array_slice($flds,7);
foreach ($flds as $key => $fld){
  if (substr($key, 0,4)=='ros_'){ $ros[$fld] = ''; }
}

foreach ($_POST as $k => $var) {
  $_POST[$k] = mysql_escape_string($var);
  if (($k != 'pname')&&($k != 'pbdate')&&($k != 'pih_pid')&&($k != 'pih_date')){
     if (
       (substr($k, 0,4)!='ros_') &&
       (substr($k, 0,6)!='oh_ch_') &&
       (substr($k, 0,5)!='pres_') &&
       (substr($k, 0,3)!='op_') &&
       (substr($k, 0,3)!='ii_')
     )
     {
       $data[$k] = $_POST[$k];
       if ($data[$k] == "YYYY-MM-DD") {  $data[$k] = ''; }
     } else if (substr($k, 0,4)=='ros_') {
       $ros[$k] = $_POST[$k];
     }
  }
  //echo "$var\n";
}
  $ii = 0;
  while ($ii < 6){
    $data["oh_ch_rec_".$ii] = $_POST["oh_ch_date_".$ii] . '|~'.
                              $_POST["oh_ch_width_".$ii] . '|~'.
                              $_POST["oh_ch_sex_".$ii] . '|~'.
                              $_POST["oh_ch_weeks_".$ii] . '|~'.
                              $_POST["oh_ch_delivery_".$ii] . '|~'.
                              $_POST["oh_ch_notes_".$ii];
    $ii++;
  }
  $ii = 0;
  while ($ii < 10){
    $data["pres_drug_rec_".$ii] = $_POST["pres_drug_".$ii] . '|~'.
                              $_POST["pres_dosage_".$ii] . '|~'.
                              $_POST["pres_who_".$ii];
    $ii++;
  }
  $ii = 0;
  while ($ii < 6){
    $data["op_rec_".$ii] = $_POST["op_reason_".$ii] . '|~'.
                           $_POST["op_date_".$ii] . '|~'.
                           $_POST["op_hospital_".$ii];
    $ii++;
  }
  $ii=0;
  while ($ii < 12){
    $data["ii_rec_".$ii] = $_POST["ii_type_".$ii] . '|~'.
                           $_POST["ii_date_".$ii];
    $ii++;
  }

if ($_GET["mode"] == "new"){
  if ($encounter == "") { $encounter = date("Ymd"); }
  $newid = formSubmit("form_patient_intake_history_ros", $ros, $_GET["id"], $userauthorized);
  $data['linked_ros_id'] = $newid;
  $newid = formSubmit("form_patient_intake_history", $data, $_GET["id"], $userauthorized);
  addForm($encounter, "Patient intake history", $newid, "patient_intake_history", $pid, $userauthorized);
  $_SESSION["encounter"] = $encounter;
} elseif ($_GET["mode"] == "update") {
  $q1 = '';
  foreach ($data as $key => $val){
    $q1 .= "$key='$val', ";
  }
  sqlInsert("update form_patient_intake_history set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, $q1  date = NOW() where id=$id");
  $fres=sqlStatement("select linked_ros_id from form_patient_intake_history where id=$id");
  if ($fres){ $ids = sqlFetchArray($fres); }

  foreach ($ros as $key => $val){
    $q2 .= "$key='$val', ";
  }
  sqlInsert("update form_patient_intake_history_ros set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, $q2  date = NOW() where id=".$ids['linked_ros_id']);
}
sqlInsert("update patient_data set DOB='".$_POST['pbdate']."' where  id=$pid");

//$_SESSION["pid"] = $pid;
formHeader("Redirecting....");
formJump();
formFooter();
?>
