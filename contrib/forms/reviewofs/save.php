<?php

//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit("form_reviewofs", $_POST, $_GET["id"], $userauthorized);
    addForm($encounter, "Review of Systems Checks", $newid, "reviewofs", $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    sqlStatement("update form_reviewofs set pid = ?, groupname= ?, user= ?, authorized= ?, activity=1, date = NOW(), fever=?,
    chills=?,  night_sweats=?,  weight_loss=?,
    poor_appetite=?,  insomnia=?,
    fatigued=?,  depressed=?,  hyperactive=?,  exposure_to_foreign_countries=?,
    cataracts=?,  cataract_surgery=?,  glaucoma=?,  double_vision=?,
    blurred_vision=?,  poor_hearing=?,  headaches=?,  ringing_in_ears=?,
    bloody_nose=?,  sinusitis=?,  sinus_surgery=?,
    dry_mouth=?,  strep_throat=?,  tonsillectomy=?,  swollen_lymph_nodes=?,
    throat_cancer=?,  throat_cancer_surgery=?,  heart_attack=?,  irregular_heart_beat=?,
    chest_pains=?,  shortness_of_breath=?,  high_blood_pressure=?,
    heart_failure=?,  poor_circulation=?,  vascular_surgery=?,
    cardiac_catheterization=?,  coronary_artery_bypass=?,  heart_transplant=?,
    stress_test=?,  emphysema=?,  chronic_bronchitis=?,  interstitial_lung_disease=?,
    shortness_of_breath_2=?,  lung_cancer=?,  lung_cancer_surgery=?,  pheumothorax=?,
    stomach_pains=?,  peptic_ulcer_disease=?,  gastritis=?,  endoscopy=?,
    polyps=?,  colonoscopy=?,  colon_cancer=?,  colon_cancer_surgery=?,
    ulcerative_colitis=?,  crohns_disease=?,  appendectomy=?,  divirticulitis=?,
    divirticulitis_surgery=?,  gall_stones=?,  cholecystectomy=?,  hepatitis=?,
    cirrhosis_of_the_liver=?,  splenectomy=?,  kidney_failure=?,  kidney_stones=?,
    kidney_cancer=?,  kidney_infections=?,  bladder_infections=?,  bladder_cancer=?,
    prostate_problems=?,  prostate_cancer=?,  kidney_transplant=?,  sexually_transmitted_disease=?,
    burning_with_urination=?,  discharge_from_urethra=?,  rashes=?,
    infections=?,  ulcerations=?,  pemphigus=?,  herpes=?,
    osetoarthritis=?,  rheumotoid_arthritis=?,  lupus=?,  ankylosing_sondlilitis=?,
    swollen_joints=?,  stiff_joints=?,  broken_bones=?,  neck_problems=?,
    back_problems=?,  back_surgery=?,  scoliosis=?,  herniated_disc=?,
    shoulder_problems=?,  elbow_problems=?,  wrist_problems=?,  hand_problems=?,
    hip_problems=?,  knee_problems=?,  ankle_problems=?,  foot_problems=?,
    insulin_dependent_diabetes=?,  noninsulin_dependent_diabetes=?,  hypothyroidism=?,
    hyperthyroidism=?,  cushing_syndrom=?,  addison_syndrom=?,  additional_notes=?  where id=?", array($_SESSION["pid"], $_SESSION["authProvider"], $_SESSION["authUser"], $userauthorized, $_POST['weight_loss'], $_POST['poor_appetite'], $_POST['insomnia'], $_POST['fatigued'],
    $_POST['depressed'], $_POST['hyperactive'], $_POST['exposure_to_foreign_countries'], $_POST['cataracts'],
    $_POST['cataract_surgery'], $_POST['glaucoma'], $_POST['double_vision'], $_POST['blurred_vision'],
    $_POST['poor_hearing'], $_POST['headaches'], $_POST['ringing_in_ears'], $_POST['bloody_nose'],
    $_POST['sinusitis'], $_POST['sinus_surgery'], $_POST['dry_mouth'], $_POST['strep_throat'],
    $_POST['tonsillectomy'], $_POST['swollen_lymph_nodes'], $_POST['throat_cancer'], $_POST['throat_cancer_surgery'],
    $_POST['heart_attack'], $_POST['irregular_heart_beat'], $_POST['chest_pains'], $_POST['shortness_of_breath'],
    $_POST['high_blood_pressure'], $_POST['heart_failure'], $_POST['poor_circulation'], $_POST['vascular_surgery'],
    $_POST['cardiac_catheterization'], $_POST['coronary_artery_bypass'], $_POST['heart_transplant'], $_POST['stress_test'],
    $_POST['emphysema'], $_POST['chronic_bronchitis'], $_POST['interstitial_lung_disease'], $_POST['shortness_of_breath_2'],
    $_POST['lung_cancer'], $_POST['lung_cancer_surgery'], $_POST['pheumothorax'], $_POST['stomach_pains'],
    $_POST['peptic_ulcer_disease'], $_POST['gastritis'], $_POST['endoscopy'], $_POST['polyps'],
    $_POST['colonoscopy'], $_POST['colon_cancer'], $_POST['colon_cancer_surgery'], $_POST['ulcerative_colitis'],
    $_POST['crohns_disease'], $_POST['appendectomy'], $_POST['divirticulitis'], $_POST['divirticulitis_surgery'],
    $_POST['gall_stones'], $_POST['cholecystectomy'], $_POST['hepatitis'], $_POST['cirrhosis_of_the_liver'],
    $_POST['splenectomy'], $_POST['kidney_failure'], $_POST['kidney_stones'], $_POST['kidney_cancer'],
    $_POST['kidney_infections'], $_POST['bladder_infections'], $_POST['bladder_cancer'], $_POST['prostate_problems'],
    $_POST['prostate_cancer'], $_POST['kidney_transplant'], $_POST['sexually_transmitted_disease'], $_POST['burning_with_urination'],
    $_POST['discharge_from_urethra'], $_POST['rashes'], $_POST['infections'], $_POST['ulcerations'],
    $_POST['pemphigus'], $_POST['herpes'], $_POST['osetoarthritis'], $_POST['rheumotoid_arthritis'],
    $_POST['lupus'], $_POST['ankylosing_sondlilitis'], $_POST['swollen_joints'], $_POST['stiff_joints'],
    $_POST['broken_bones'], $_POST['neck_problems'], $_POST['back_problems'], $_POST['back_surgery'],
    $_POST['scoliosis'], $_POST['herniated_disc'], $_POST['shoulder_problems'], $_POST['elbow_problems'],
    $_POST['wrist_problems'], $_POST['hand_problems'], $_POST['hip_problems'], $_POST['knee_problems'],
    $_POST['ankle_problems'], $_POST['foot_problems'], $_POST['insulin_dependent_diabetes'], $_POST['noninsulin_dependent_diabetes'],
    $_POST['hypothyroidism'], $_POST['hyperthyroidism'], $_POST['cushing_syndrom'], $_POST['addison_syndrom'],
    $_POST['additional_notes'], $_GET["id"]));
}

formHeader("Redirecting....");
formJump();
formFooter();
