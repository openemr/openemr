<?php
//------------Form created by Nikolai Vitsyn by 2004/01/23
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
foreach ($_POST as $k => $var) {
    $_POST[$k] = add_escape_custom($var);
    echo "$var\n";
}

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_bronchitis", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Bronchitis Form", $newid, "bronchitis", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlInsert("update form_bronchitis set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(),
bronchitis_date_of_illness=?, 
bronchitis_hpi=?, 
bronchitis_ops_fever=?, 
bronchitis_ops_cough=?, 
bronchitis_ops_dizziness=?, 
bronchitis_ops_chest_pain=?, 
bronchitis_ops_dyspnea=?, 
bronchitis_ops_sweating=?, 
bronchitis_ops_wheezing=?, 
bronchitis_ops_malaise=?,  
bronchitis_ops_sputum=?, 
bronchitis_ops_appearance=?,  
bronchitis_ops_all_reviewed=?, 
bronchitis_review_of_pmh=?,  
bronchitis_review_of_medications=?, 
bronchitis_review_of_allergies=?,  
bronchitis_review_of_sh=?, 
bronchitis_review_of_fh=?,  
bronchitis_tms_normal_right=?, 
bronchitis_tms_normal_left=?,  
bronchitis_nares_normal_right=?, 
bronchitis_nares_normal_left=?,  
bronchitis_tms_thickened_right=?, 
bronchitis_tms_thickened_left=?,  
bronchitis_tms_af_level_right=?, 
bronchitis_tms_af_level_left=?,  
bronchitis_nares_swelling_right=?, 
bronchitis_nares_swelling_left=?, 
bronchitis_tms_retracted_right=?, 
bronchitis_tms_retracted_left=?,  
bronchitis_nares_discharge_right=?, 
bronchitis_nares_discharge_left=?,  
bronchitis_tms_bulging_right=?, 
bronchitis_tms_bulging_left=?, 
bronchitis_tms_perforated_right=?,  
bronchitis_tms_perforated_left=?, 
bronchitis_tms_nares_not_examined=?, 
bronchitis_no_sinus_tenderness=?,  
bronchitis_oropharynx_normal=?, 
bronchitis_sinus_tenderness_frontal_right=?, 
bronchitis_sinus_tenderness_frontal_left=?, 
bronchitis_oropharynx_erythema=?,  
bronchitis_oropharynx_exudate=?, 
bronchitis_oropharynx_abcess=?, 
bronchitis_oropharynx_ulcers=?, 
bronchitis_sinus_tenderness_maxillary_right=?, 
bronchitis_sinus_tenderness_maxillary_left=?, 
bronchitis_oropharynx_appearance=?, 
bronchitis_sinus_tenderness_not_examined=?, 
bronchitis_oropharynx_not_examined=?,  
bronchitis_heart_pmi=?, 
bronchitis_heart_s3=?, 
bronchitis_heart_s4=?,  
bronchitis_heart_click=?, 
bronchitis_heart_rub=?, 
bronchitis_heart_murmur=?, 
bronchitis_heart_grade=?,  
bronchitis_heart_location=?, 
bronchitis_heart_normal=?, 
bronchitis_heart_not_examined=?, 
bronchitis_lungs_bs_normal=?,  
bronchitis_lungs_bs_reduced=?, 
bronchitis_lungs_bs_increased=?, 
bronchitis_lungs_crackles_lll=?,  
bronchitis_lungs_crackles_rll=?, 
bronchitis_lungs_crackles_bll=?,  
bronchitis_lungs_rubs_lll=?, 
bronchitis_lungs_rubs_rll=?, 
bronchitis_lungs_rubs_bll=?, 
bronchitis_lungs_wheezes_lll=?,  
bronchitis_lungs_wheezes_rll=?, 
bronchitis_lungs_wheezes_bll=?, 
bronchitis_lungs_wheezes_dll=?, 
bronchitis_lungs_normal_exam=?,  
bronchitis_lungs_not_examined=?, 
bronchitis_diagnostic_tests=?, 
diagnosis1_bronchitis_form=?, 
diagnosis2_bronchitis_form=?, 
diagnosis3_bronchitis_form=?,  
diagnosis4_bronchitis_form=?, 
bronchitis_additional_diagnosis=?, 
bronchitis_treatment='".$_POST["bronchitis_treatment"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
