<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<roschecks>";

$token = $_POST['token'];


$date = 'NOW()';
$pid = $_POST['pid'];
$visit_id = $_POST['visit_id'];
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
    $_SESSION['pid'] = $patientId;

    if ($acl_allow) {
        $strQuery = "INSERT INTO `form_reviewofs`(
                    `date`, `pid`, `user`, `groupname`, `authorized`, `activity`, `fever`, `chills`, 
                    `night_sweats`, `weight_loss`, `poor_appetite`, `insomnia`, `fatigued`, `depressed`, 
                    `hyperactive`, `exposure_to_foreign_countries`, `cataracts`, `cataract_surgery`, `glaucoma`, 
                    `double_vision`, `blurred_vision`, `poor_hearing`, `headaches`, `ringing_in_ears`, `bloody_nose`, 
                    `sinusitis`, `sinus_surgery`, `dry_mouth`, `strep_throat`, `tonsillectomy`, `swollen_lymph_nodes`, 
                    `throat_cancer`, `throat_cancer_surgery`, `heart_attack`, `irregular_heart_beat`, `chest_pains`, 
                    `shortness_of_breath`, `high_blood_pressure`, `heart_failure`, `poor_circulation`, `vascular_surgery`, 
                    `cardiac_catheterization`, `coronary_artery_bypass`, `heart_transplant`, `stress_test`, `emphysema`, 
                    `chronic_bronchitis`, `interstitial_lung_disease`, `shortness_of_breath_2`, `lung_cancer`, 
                    `lung_cancer_surgery`, `pheumothorax`, `stomach_pains`, `peptic_ulcer_disease`, `gastritis`, `endoscopy`, 
                    `polyps`, `colonoscopy`, `colon_cancer`, `colon_cancer_surgery`, `ulcerative_colitis`, `crohns_disease`, 
                    `appendectomy`, `divirticulitis`, `divirticulitis_surgery`, `gall_stones`, `cholecystectomy`, 
                    `hepatitis`, `cirrhosis_of_the_liver`, `splenectomy`, `kidney_failure`, `kidney_stones`, 
                    `kidney_cancer`, `kidney_infections`, `bladder_infections`, `bladder_cancer`, `prostate_problems`, 
                    `prostate_cancer`, `kidney_transplant`, `sexually_transmitted_disease`, `burning_with_urination`, 
                    `discharge_from_urethra`, `rashes`, `infections`, `ulcerations`, `pemphigus`, `herpes`, `osetoarthritis`, 
                    `rheumotoid_arthritis`, `lupus`, `ankylosing_sondlilitis`, `swollen_joints`, `stiff_joints`, `broken_bones`, 
                    `neck_problems`, `back_problems`, `back_surgery`, `scoliosis`, `herniated_disc`, `shoulder_problems`, 
                    `elbow_problems`, `wrist_problems`, `hand_problems`, `hip_problems`, `knee_problems`, `ankle_problems`, 
                    `foot_problems`, `insulin_dependent_diabetes`, `noninsulin_dependent_diabetes`, `hypothyroidism`, `hyperthyroidism`, 
                    `cushing_syndrom`, `addison_syndrom`, `additional_notes`
                    ) VALUES (
                    $date, '{$pid}', '{$user}', '{$groupname}', '{$authorized}', '{$activity}', '{$fever}', '{$chills}', 
                    '{$night_sweats}', '{$weight_loss}', '{$poor_appetite}', '{$insomnia}', '{$fatigued}', '{$depressed}', 
                    '{$hyperactive}', '{$exposure_to_foreign_countries}', '{$cataracts}', '{$cataract_surgery}', '{$glaucoma}', 
                    '{$double_vision}', '{$blurred_vision}', '{$poor_hearing}', '{$headaches}', '{$ringing_in_ears}', '{$bloody_nose}', 
                    '{$sinusitis}', '{$sinus_surgery}', '{$dry_mouth}', '{$strep_throat}', '{$tonsillectomy}', '{$swollen_lymph_nodes}', 
                    '{$throat_cancer}', '{$throat_cancer_surgery}', '{$heart_attack}', '{$irregular_heart_beat}', '{$chest_pains}', 
                    '{$shortness_of_breath}', '{$high_blood_pressure}', '{$heart_failure}', '{$poor_circulation}', '{$vascular_surgery}', 
                    '{$cardiac_catheterization}', '{$coronary_artery_bypass}', '{$heart_transplant}', '{$stress_test}', '{$emphysema}', 
                    '{$chronic_bronchitis}', '{$interstitial_lung_disease}', '{$shortness_of_breath_2}', '{$lung_cancer}', 
                    '{$lung_cancer_surgery}', '{$pheumothorax}', '{$stomach_pains}', '{$peptic_ulcer_disease}', '{$gastritis}', '{$endoscopy}', 
                    '{$polyps}', '{$colonoscopy}', '{$colon_cancer}', '{$colon_cancer_surgery}', '{$ulcerative_colitis}', '{$crohns_disease}', 
                    '{$appendectomy}', '{$divirticulitis}', '{$divirticulitis_surgery}', '{$gall_stones}', '{$cholecystectomy}', 
                    '{$hepatitis}', '{$cirrhosis_of_the_liver}', '{$splenectomy}', '{$kidney_failure}', '{$kidney_stones}', 
                    '{$kidney_cancer}', '{$kidney_infections}', '{$bladder_infections}', '{$bladder_cancer}', '{$prostate_problems}', 
                    '{$prostate_cancer}', '{$kidney_transplant}', '{$sexually_transmitted_disease}', '{$burning_with_urination}', 
                    '{$discharge_from_urethra}', '{$rashes}', '{$infections}', '{$ulcerations}', '{$pemphigus}', '{$herpes}', '{$osetoarthritis}', 
                    '{$rheumotoid_arthritis}', '{$lupus}', '{$ankylosing_sondlilitis}', '{$swollen_joints}', '{$stiff_joints}', '{$broken_bones}', 
                    '{$neck_problems}', '{$back_problems}', '{$back_surgery}', '{$scoliosis}', '{$herniated_disc}', '{$shoulder_problems}', 
                    '{$elbow_problems}', '{$wrist_problems}', '{$hand_problems}', '{$hip_problems}', '{$knee_problems}', '{$ankle_problems}', 
                    '{$foot_problems}', '{$insulin_dependent_diabetes}', '{$noninsulin_dependent_diabetes}', '{$hypothyroidism}', '{$hyperthyroidism}', 
                    '{$cushing_syndrom}', '{$addison_syndrom}', '{$additional_notes}')
";
        $result = sqlInsert($strQuery);
        $last_inserted_id = $result;

        if ($result) {
            addForm($visit_id, $form_name = 'Review of Systems Checks', $last_inserted_id, $formdir = 'reviewofs', $pid, $authorized = "1", $date = "NOW()", $user, $group = "Default");

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Review of System Checks has been added</reason>";
            $xml_string .= "<roscheckid>{$last_inserted_id}</roscheckid>";
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