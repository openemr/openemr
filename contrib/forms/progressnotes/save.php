<?php
#######################################################
# Progress Notes Form created by Kam Sharifi	      #
# kam@sharmen.com				      #
#######################################################
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
foreach ($_POST as $k => $var) {
$_POST[$k] = mysql_escape_string($var);
echo "$var\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_progressnotes", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "Progress Notes", $newid, "progressnotes", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_progressnotes set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), prog_p='".$_POST["prog_p"]."', prog_r='".$_POST["prog_r"]."', prog_bp='".$_POST["prog_bp"]."', prog_ht='".$_POST["prog_ht"]."', prog_wt='".$_POST["prog_wt"]."', prog_temp='".$_POST["prog_temp"]."', prog_lmp='".$_POST["prog_lmp"]."', prog_last_pap_smear='".$_POST["prog_last_pap_smear"]."', prog_last_td_booster='".$_POST["prog_last_td_booster"]."', prog_allergies='".$_POST["prog_allergies"]."', prog_last_mammogram='".$_POST["prog_last_mammogram"]."', prog_present_complaint='".$_POST["prog_present_complaint"]."', prog_skin_abn='".$_POST["prog_skin_abn"]."', prog_skin_ne='".$_POST["prog_skin_ne"]."', prog_head_abn='".$_POST["prog_head_abn"]."', prog_head_ne='".$_POST["prog_head_ne"]."', prog_eyes_abn='".$_POST["prog_eyes_abn"]."', prog_eyes_ne='".$_POST["prog_eyes_ne"]."', prog_ears_abn='".$_POST["prog_ears_abn"]."', prog_ears_ne='".$_POST["prog_ears_ne"]."', prog_nose_abn='".$_POST["prog_nose_abn"]."', prog_nose_ne='".$_POST["prog_nose_ne"]."', prog_throat_abn='".$_POST["prog_throat_abn"]."', prog_throat_ne='".$_POST["prog_throat_ne"]."', prog_teeth_abn='".$_POST["prog_teeth_abn"]."', prog_teeth_ne='".$_POST["prog_teeth_ne"]."', prog_neck_abn='".$_POST["prog_neck_abn"]."', prog_neck_ne='".$_POST["prog_neck_ne"]."', prog_chest_abn='".$_POST["prog_chest_abn"]."', prog_chest_ne='".$_POST["prog_chest_ne"]."', prog_breast_abn='".$_POST["prog_breast_abn"]."', prog_breast_ne='".$_POST["prog_breast_ne"]."', prog_lungs_abn='".$_POST["prog_lungs_abn"]."', prog_lungs_ne='".$_POST["prog_lungs_ne"]."', prog_heart_abn='".$_POST["prog_heart_abn"]."', prog_heart_ne='".$_POST["prog_heart_ne"]."', prog_abdomen_abn='".$_POST["prog_abdomen_abn"]."', prog_abdomen_ne='".$_POST["prog_abdomen_ne"]."', prog_spine_abn='".$_POST["prog_spine_abn"]."', prog_spine_ne='".$_POST["prog_spine_ne"]."', prog_extremeities_abn='".$_POST["prog_extremeities_abn"]."', prog_extremeities_ne='".$_POST["prog_extremeities_ne"]."', prog_lowback_abn='".$_POST["prog_lowback_abn"]."', prog_lowback_ne='".$_POST["prog_lowback_ne"]."', prog_neuro_abn='".$_POST["prog_neuro_abn"]."', prog_neuro_ne='".$_POST["prog_neuro_ne"]."', prog_rectal_abn='".$_POST["prog_rectal_abn"]."', prog_rectal_ne='".$_POST["prog_rectal_ne"]."', prog_pelvic_abn='".$_POST["prog_pelvic_abn"]."', prog_pelvic_ne='".$_POST["prog_pelvic_ne"]."', prog_assessment='".$_POST["prog_assessment"]."', prog_plan='".$_POST["prog_plan"]."', prog_breast_se='".$_POST["prog_breast_se"]."', prog_dental_h='".$_POST["prog_dental_h"]."', prog_diagnosis='".$_POST["prog_diagnosis"]."', prog_injur_p='".$_POST["prog_injur_p"]."', prog_new_treat='".$_POST["prog_new_treat"]."', prog_nutrition_e='".$_POST["prog_nutrition_e"]."', prog_sexual_p='".$_POST["prog_sexual_p"]."', prog_substance_a='".$_POST["prog_substance_a"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
