<?php
// 2005-03-14
// Problem list storage and update module
//

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$flds = sqlListFields("form_plist");
$flds = array_slice($flds,7);
foreach ($flds as $key => $fld){
  $data[$fld] = '';
}

foreach ($_POST as $k => $var) {
  $_POST[$k] = mysql_escape_string($var);
  //echo "$var\n";
}
  $data['pl_high_risk'] = $_POST['pl_high_risk'];
  $data['pl_family_history'] = $_POST['pl_family_history'];
  $data['pl_reactions'] = $_POST['pl_reactions'];
  $data['pl_medications'] = $_POST['pl_medications'];
  
  $si = 1;
  while ($si < 26){
    if ($_POST["pl_ed_${si}"] != ''){
      $data["pl_problem_${si}"] = $_POST["pl_ed_${si}"].'|~'.
                                  $_POST["pl_problem_${si}"].'|~'. 
                                  $_POST["pl_onset_${si}"].'|~'. 
                                  $_POST["pl_rd_${si}"];
    } else {
      $data["pl_problem_${si}"] = '';
    }
    $si++;
  }

//if ($pid == "") { $pid = $_SESSION["pid"]; }

if ($_GET["mode"] == "new"){
  if ($encounter == "") { $encounter = date("Ymd"); }
  $newid = formSubmit("form_plist", $data, $_GET["id"], $userauthorized);
  addForm($encounter, "Problem list", $newid, "plist", $pid, $userauthorized);
  $_SESSION["encounter"] = $encounter;
}elseif ($_GET["mode"] == "update") {
  $q1 = '';
  foreach ($data as $key => $val){
    $q1 .= "$key='$val', ";
  }
  sqlInsert("update form_plist set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, $q1  date = NOW() where id=$id");
}
sqlInsert("update patient_data set DOB='".$_POST['pbdate']."' where  id=$pid");

//$_SESSION["pid"] = $pid;
formHeader("Redirecting....");
formJump();
formFooter();
?>
