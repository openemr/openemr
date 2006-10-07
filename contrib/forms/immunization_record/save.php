<?php
// 2005-03-14
// Immunization storage and update module
//

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$flds = sqlListFields("form_immunization_record");
$flds = array_slice($flds,7);
foreach ($flds as $key => $fld){
  $data[$fld] = '';
}

foreach ($_POST as $k => $var) {
  $_POST[$k] = mysql_escape_string($var);
  //echo "$var\n";
}
  $vaccs = array(
    "vacc_tetanus"=>1, "vacc_influenza"=>1,
    "vacc_pneumococcal"=>1, "vacc_mmr"=>1,
    "vacc_hep_a"=>1, "vacc_hep_b"=>1,
    "vacc_varicella"=>1
  );
  $hdrimmrecord = array(
		"vacc_tetanus"=> "Tetanus-Diphteria booster",
		"vacc_influenza"=> "Influenza vaccine",
		"vacc_pneumococcal"=> "Pneumococcal vaccine",
		"vacc_mmr"=> "MMR Vaccine",
		"vacc_hep_a"=> "Hepatitis A vaccine",
		"vacc_hep_b"=> "Hepatitis B vaccine",
		"vacc_varicella"=> "Varicella vaccine"
  );



  foreach ($vaccs as $key => $val){
    $si = 0;
    $data[$key] = "";
    while ($si < 20){
      $data[$key] .= $_POST["${key}_${si}"].'|~';
      if ($_POST["${key}_${si}"] != ''){
        $data["last_${key}"] = $_POST["${key}_${si}"];
      }
      $si++;
    }
  }

if ($_GET["mode"] == "new"){
  if ($encounter == "") { $encounter = date("Ymd"); }
  $newid = formSubmit("form_immunization_record", $data, $_GET["id"], $userauthorized);
  addForm($encounter, "Immunization record", $newid, "immunization_record", $pid, $userauthorized);
  $_SESSION["encounter"] = $encounter;
} elseif ($_GET["mode"] == "update") {
  $q1 = '';
  foreach ($data as $key => $val){
    $q1 .= "$key='$val', ";
  }
  sqlInsert("update form_immunization_record set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, $q1  date = NOW() where id=$id");
}
sqlInsert("update patient_data set DOB='".$_POST['pbdate']."' where  id=$pid");

//$_SESSION["pid"] = $pid;
formHeader("Redirecting....");
formJump();
formFooter();
?>
