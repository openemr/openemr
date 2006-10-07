<?php
// 2005-03-14
// Physician history storage and update module
//

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$flds = sqlListFields("form_physician_history");
$flds = array_slice($flds,7);
foreach ($flds as $key => $fld){
  $data[$fld] = '';
}


foreach ($_POST as $k => $var) {
  $_POST[$k] = mysql_escape_string($var);
  if (($k != 'pname')&&($k != 'pbdate')&&($k != 'ph_pid')&&($k != 'ph_date')
     && (substr($k, 0,5)!='oh_ch')){
    $data[$k] = $_POST[$k];
    if ($data[$k] == "YYYY-MM-DD") {
      $data[$k] = '';
    }
  }
  //echo "$var\n";
}
  $ii = 0;
  while ($ii < 4){
    $data["oh_ch_rec_".$ii] = $_POST["oh_ch_date_".$ii] . "|~".
                              $_POST["oh_ch_width_".$ii] .  "|~".
                              $_POST["oh_ch_sex_".$ii] .  "|~".
                              $_POST["oh_ch_weeks_".$ii] .  "|~".
                              $_POST["oh_ch_delivery_".$ii] .  "|~".
                              $_POST["oh_ch_notes_".$ii];
    $ii++;
  }

if ($_GET["mode"] == "new"){
  if ($encounter == "") { $encounter = date("Ymd"); }
  $newid = formSubmit("form_physician_history", $data, $_GET["id"], $userauthorized);
  addForm($encounter, "Physician history", $newid, "physician_history", $pid, $userauthorized);
  $_SESSION["encounter"] = $encounter;
} elseif ($_GET["mode"] == "update") {
  $q1 = '';
  foreach ($data as $key => $val){
    $q1 .= "$key='$val', ";
  }
  //print $q1; exit;
  sqlInsert("update form_physician_history set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, $q1  date = NOW() where id=$id");
}
sqlInsert("update patient_data set DOB='".$_POST['pbdate']."' where  id=$pid");

//$_SESSION["pid"] = $pid;
formHeader("Redirecting....");
formJump();
formFooter();
?>
