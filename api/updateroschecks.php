<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<roschecks>";

$token = $_POST['token'];

$id = $_POST['id'];

$groupname = isset($_POST['groupname']) ? $_POST['groupname'] : 'Default';
$authorized = isset($_POST['authorized']) ? $_POST['authorized'] : 1;
$activity = isset($_POST['activity']) ? $_POST['activity'] : 1;

$fever = $_POST['fever'];
$chills = $_POST['chills'];
$night_sweats = $_POST['night_sweats'];
$weight_loss = $_POST['weight_loss'];
$poor_appetite = $_POST['poor_appetite'];
$insomnia = $_POST['insomnia'];
$fatigued = $_POST['fatigued'];
$depressed = $_POST['depressed'];
$hyperactive = $_POST['hyperactive'];
$exposure_to_foreign_countries = $_POST['exposure_to_foreign_countries'];
$cataracts = $_POST['cataracts'];
$cataract_surgery = $_POST['cataract_surgery'];
$glaucoma = $_POST['glaucoma'];
$double_vision = $_POST['double_vision'];
$blurred_vision = $_POST['blurred_vision'];
$poor_hearing = $_POST['poor_hearing'];
$headaches = $_POST['headaches'];
$ringing_in_ears = $_POST['ringing_in_ears'];
$bloody_nose = $_POST['bloody_nose'];
$sinusitis = $_POST['sinusitis'];
$sinus_surgery = $_POST['sinus_surgery'];
$dry_mouth = $_POST['dry_mouth'];
$strep_throat = $_POST['strep_throat'];
$tonsillectomy = $_POST['tonsillectomy'];
$swollen_lymph_nodes = $_POST['swollen_lymph_nodes'];
$throat_cancer = $_POST['throat_cancer'];
$throat_cancer_surgery = $_POST['throat_cancer_surgery'];
$heart_attack = $_POST['heart_attack'];
$irregular_heart_beat = $_POST['irregular_heart_beat'];
$chest_pains = $_POST['chest_pains'];
$shortness_of_breath = $_POST['shortness_of_breath'];
$high_blood_pressure = $_POST['high_blood_pressure'];
$heart_failure = $_POST['heart_failure'];
$poor_circulation = $_POST['poor_circulation'];
$vascular_surgery = $_POST['vascular_surgery'];
$cardiac_catheterization = $_POST['cardiac_catheterization'];
$coronary_artery_bypass = $_POST['coronary_artery_bypass'];
$heart_transplant = $_POST['heart_transplant'];
$stress_test = $_POST['stress_test'];
$emphysema = $_POST['emphysema'];
$chronic_bronchitis = $_POST['chronic_bronchitis'];
$interstitial_lung_disease = $_POST['interstitial_lung_disease'];
$shortness_of_breath_2 = $_POST['shortness_of_breath_2'];
$lung_cancer = $_POST['lung_cancer'];
$lung_cancer_surgery = $_POST['lung_cancer_surgery'];
$pheumothorax = $_POST['pheumothorax'];
$stomach_pains = $_POST['stomach_pains'];
$peptic_ulcer_disease = $_POST['peptic_ulcer_disease'];
$gastritis = $_POST['gastritis'];
$endoscopy = $_POST['endoscopy'];
$polyps = $_POST['polyps'];
$colonoscopy = $_POST['colonoscopy'];
$colon_cancer = $_POST['colon_cancer'];
$colon_cancer_surgery = $_POST['colon_cancer_surgery'];
$ulcerative_colitis = $_POST['ulcerative_colitis'];
$crohns_disease = $_POST['crohns_disease'];
$appendectomy = $_POST['appendectomy'];
$divirticulitis = $_POST['divirticulitis'];
$divirticulitis_surgery = $_POST['divirticulitis_surgery'];
$gall_stones = $_POST['gall_stones'];
$cholecystectomy = $_POST['cholecystectomy'];
$hepatitis = $_POST['hepatitis'];
$cirrhosis_of_the_liver = $_POST['cirrhosis_of_the_liver'];
$splenectomy = $_POST['splenectomy'];
$kidney_failure = $_POST['kidney_failure'];
$kidney_stones = $_POST['kidney_stones'];
$kidney_cancer = $_POST['kidney_cancer'];
$kidney_infections = $_POST['kidney_infections'];
$bladder_infections = $_POST['bladder_infections'];
$bladder_cancer = $_POST['bladder_cancer'];
$prostate_problems = $_POST['prostate_problems'];
$prostate_cancer = $_POST['prostate_cancer'];
$kidney_transplant = $_POST['kidney_transplant'];
$sexually_transmitted_disease = $_POST['sexually_transmitted_disease'];
$burning_with_urination = $_POST['burning_with_urination'];
$discharge_from_urethra = $_POST['discharge_from_urethra'];
$rashes = $_POST['rashes'];
$infections = $_POST['infections'];
$ulcerations = $_POST['ulcerations'];
$pemphigus = $_POST['pemphigus'];
$herpes = $_POST['herpes'];
$osetoarthritis = $_POST['osetoarthritis'];
$rheumotoid_arthritis = $_POST['rheumotoid_arthritis'];
$lupus = $_POST['lupus'];
$ankylosing_sondlilitis = $_POST['ankylosing_sondlilitis'];
$swollen_joints = $_POST['swollen_joints'];
$stiff_joints = $_POST['stiff_joints'];
$broken_bones = $_POST['broken_bones'];
$neck_problems = $_POST['neck_problems'];
$back_problems = $_POST['back_problems'];
$back_surgery = $_POST['back_surgery'];
$scoliosis = $_POST['scoliosis'];
$herniated_disc = $_POST['herniated_disc'];
$shoulder_problems = $_POST['shoulder_problems'];
$elbow_problems = $_POST['elbow_problems'];
$wrist_problems = $_POST['wrist_problems'];
$hand_problems = $_POST['hand_problems'];
$hip_problems = $_POST['hip_problems'];
$knee_problems = $_POST['knee_problems'];
$ankle_problems = $_POST['ankle_problems'];
$foot_problems = $_POST['foot_problems'];
$insulin_dependent_diabetes = $_POST['insulin_dependent_diabetes'];
$noninsulin_dependent_diabetes = $_POST['noninsulin_dependent_diabetes'];
$hypothyroidism = $_POST['hypothyroidism'];
$hyperthyroidism = $_POST['hyperthyroidism'];
$cushing_syndrom = $_POST['cushing_syndrom'];
$addison_syndrom = $_POST['addison_syndrom'];
$additional_notes = $_POST['additional_notes'];


if ($userId = validateToken($token)) {

    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
$_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
//    $_SESSION['pid'] = $patientId;
    if ($acl_allow) {
        $strQuery = "UPDATE `form_reviewofs` SET 
                    `user`='{$user}',`groupname`='{$groupname}',
                    `authorized`='{$authorized}',`activity`='{$activity}',`fever`='{$fever}',`chills`='{$chills}',
                    `night_sweats`='{$night_sweats}',`weight_loss`='{$weight_loss}',`poor_appetite`='{$poor_appetite}',`insomnia`='{$insomnia}',
                    `fatigued`='{$fatigued}',`depressed`='{$depressed}',`hyperactive`='{$hyperactive}',
                    `exposure_to_foreign_countries`='{$exposure_to_foreign_countries}',`cataracts`='{$cataracts}',`cataract_surgery`='{$cataract_surgery}',
                    `glaucoma`='{$glaucoma}',`double_vision`='{$double_vision}',`blurred_vision`='{$blurred_vision}',`poor_hearing`='{$poor_hearing}',
                    `headaches`='{$headaches}',`ringing_in_ears`='{$ringing_in_ears}',`bloody_nose`='{$bloody_nose}',`sinusitis`='{$sinusitis}',
                    `sinus_surgery`='{$sinus_surgery}',`dry_mouth`='{$dry_mouth}',`strep_throat`='{$strep_throat}',`tonsillectomy`='{$tonsillectomy}',
                    `swollen_lymph_nodes`='{$swollen_lymph_nodes}',`throat_cancer`='{$throat_cancer}',`throat_cancer_surgery`='{$throat_cancer_surgery}',
                    `heart_attack`='{$heart_attack}',`irregular_heart_beat`='{$irregular_heart_beat}',`chest_pains`='{$chest_pains}',
                    `shortness_of_breath`='{$shortness_of_breath}',`high_blood_pressure`='{$high_blood_pressure}',`heart_failure`='{$heart_failure}',
                    `poor_circulation`='{$poor_circulation}',`vascular_surgery`='{$vascular_surgery}',`cardiac_catheterization`='{$cardiac_catheterization}',
                    `coronary_artery_bypass`='{$coronary_artery_bypass}',`heart_transplant`='{$heart_transplant}',`stress_test`='{$stress_test}',
                    `emphysema`='{$emphysema}',`chronic_bronchitis`='{$chronic_bronchitis}',`interstitial_lung_disease`='{$interstitial_lung_disease}',
                    `shortness_of_breath_2`='{$shortness_of_breath_2}',`lung_cancer`='{$lung_cancer}',`lung_cancer_surgery`='{$lung_cancer_surgery}',
                    `pheumothorax`='{$pheumothorax}',`stomach_pains`='{$stomach_pains}',`peptic_ulcer_disease`='{$peptic_ulcer_disease}',`gastritis`='{$gastritis}',
                    `endoscopy`='{$endoscopy}',`polyps`='{$polyps}',`colonoscopy`='{$colonoscopy}',`colon_cancer`='{$colon_cancer}',
                    `colon_cancer_surgery`='{$colon_cancer_surgery}',`ulcerative_colitis`='{$ulcerative_colitis}',`crohns_disease`='{$crohns_disease}',
                    `appendectomy`='{$appendectomy}',`divirticulitis`='{$divirticulitis}',`divirticulitis_surgery`='{$divirticulitis_surgery}',
                    `gall_stones`='{$gall_stones}',`cholecystectomy`='{$cholecystectomy}',`hepatitis`='{$hepatitis}',`cirrhosis_of_the_liver`='{$cirrhosis_of_the_liver}',
                    `splenectomy`='{$splenectomy}',`kidney_failure`='{$kidney_failure}',`kidney_stones`='{$kidney_stones}',`kidney_cancer`='{$kidney_cancer}',
                    `kidney_infections`='{$kidney_infections}',`bladder_infections`='{$bladder_infections}',`bladder_cancer`='{$bladder_cancer}',
                    `prostate_problems`='{$prostate_problems}',`prostate_cancer`='{$prostate_cancer}',`kidney_transplant`='{$kidney_transplant}',
                    `sexually_transmitted_disease`='{$sexually_transmitted_disease}',`burning_with_urination`='{$burning_with_urination}',`discharge_from_urethra`='{$discharge_from_urethra}',
                    `rashes`='{$rashes}',`infections`='{$infections}',`ulcerations`='{$ulcerations}',`pemphigus`='{$pemphigus}',`herpes`='{$herpes}',
                    `osetoarthritis`='{$osetoarthritis}',`rheumotoid_arthritis`='{$rheumotoid_arthritis}',`lupus`='{$lupus}',`ankylosing_sondlilitis`='{$ankylosing_sondlilitis}'
                    ,`swollen_joints`='{$swollen_joints}',`stiff_joints`='{$stiff_joints}',`broken_bones`='{$broken_bones}',`neck_problems`='{$neck_problems}',
                    `back_problems`='{$back_problems}',`back_surgery`='{$back_surgery}',`scoliosis`='{$scoliosis}',`herniated_disc`='{$herniated_disc}',
                    `shoulder_problems`='{$shoulder_problems}',`elbow_problems`='{$elbow_problems}',`wrist_problems`='{$wrist_problems}',`hand_problems`='{$hand_problems}',
                    `hip_problems`='{$hip_problems}',`knee_problems`='{$knee_problems}',`ankle_problems`='{$ankle_problems}',`foot_problems`='{$foot_problems}',
                    `insulin_dependent_diabetes`='{$insulin_dependent_diabetes}',`noninsulin_dependent_diabetes`='{$noninsulin_dependent_diabetes}',`hypothyroidism`='{$hypothyroidism}',
                    `hyperthyroidism`='{$hyperthyroidism}',`cushing_syndrom`='{$cushing_syndrom}',`addison_syndrom`='{$addison_syndrom}',
                    `additional_notes`='{$additional_notes}' 
                    WHERE id = $id";

        $result = sqlStatement($strQuery);


        if ($result) {

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Review of System Checks has been updated</reason>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</roschecks>";
echo $xml_string;
?>