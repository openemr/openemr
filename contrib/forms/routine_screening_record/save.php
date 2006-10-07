<?php
// 2005-03-14
// Routine screening storage and update module
//

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$flds = sqlListFields("form_routine_screening_record");
$flds = array_slice($flds,7);
foreach ($flds as $key => $fld){
  $data[$fld] = '';
}

foreach ($_POST as $k => $var) {
  $_POST[$k] = mysql_escape_string($var);
  //echo "$var\n";
}
$factors = array("cervical", "lipid", "mammo", "colorectal",
"bone", "chlamyd", "gonor", "urinal", "glucose", "thyroid");


$ii = 1;
while ($ii<9){
  $record = '';
  foreach($factors as $k=>$v){
    $record .= $_POST[$v."_date_".$ii] .'|'. $_POST[$v."_res_".$ii];
    if ($v != "thyroid") { $record .= '|~'; }
  }
  $data["record_".$ii] = $record;
  $ii++;
}

if ($_GET["mode"] == "new"){
  if ($encounter == "") { $encounter = date("Ymd"); }
  $newid = formSubmit("form_routine_screening_record", $data, $_GET["id"], $userauthorized);
  addForm($encounter, "Routine screening record", $newid, "routine_screening_record", $pid, $userauthorized);
  $_SESSION["encounter"] = $encounter;
} elseif ($_GET["mode"] == "update") {
  $q1 = '';
  foreach ($data as $key => $val){
    $q1 .= "$key='$val', ";
  }
  sqlInsert("update form_routine_screening_record set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, $q1 date = NOW() where id=$id");
}
sqlInsert("update patient_data set DOB='".$_POST['pbdate']."' where  id=$pid");

//$_SESSION["pid"] = $pid;
formHeader("Redirecting....");
formJump();
formFooter();
?>
