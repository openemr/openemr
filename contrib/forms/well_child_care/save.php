<?php
//

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

foreach ($_POST as $k => $var) {
	$_POST[$k] = mysql_escape_string($var);
	echo "$var\n";
}

// calculate body mass index bmi=k/(m**2)
$k=$_POST["weight"]/2.2;
$w=$_POST["height"]/39.4;
$_POST["BMI"]=round ( ($k/($w*$w)),2);
/* 
bmi for children needs to be adjusted acording a table,
this status calculation won't work as for adults
$bmi=$_POST["BMI"];
if ( $bmi > 42 ) { $_POST["BMI_status"]='Obesity III'; }
elseif ( $bmi > 34 ) { $_POST["BMI_status"]='Obesity II'; }
elseif ( $bmi > 30 ) { $_POST["BMI_status"]='Obesity I'; }
elseif ( $bmi > 27 ) { $_POST["BMI_status"]='Overweight'; }
elseif ( $bmi > 25 ) { $_POST["BMI_status"]='Normal BL'; }
elseif ( $bmi > 18.5 ) { $_POST["BMI_status"]='Normal'; }
elseif ( $bmi > 10 ) { $_POST["BMI_status"]='Underweight'; }
*/

if ($encounter == "") $encounter = date("Ymd");

if ($_GET["mode"] == "new"){
	$newid = formSubmit("form_well_child_care", $_POST, $_GET["id"], $userauthorized);
	addForm($encounter, "Well Child Care", $newid, "well_child_care", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
	$insert="update form_well_child_care set `pid` = {$_SESSION["pid"]},
	`groupname`='".$_SESSION["authProvider"]."',
	`user`='".$_SESSION["authUser"]."',
	`authorized`=$userauthorized,
	`activity`=1, 
	`date` = NOW(), ";
		
	foreach ($_POST as $k => $var) {
		$insert.="`$k`='$var',";
	}
	$insert=substr ($insert,0,-1);	
	$insert.="WHERE id=$id";
	sqlInsert($insert);
}

$_SESSION["encounter"] = $encounter;

formHeader("Redirecting....");
formJump();
formFooter();

?>
