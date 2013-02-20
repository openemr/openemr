<?php
	header("Content-Type:text/xml");
	$ignoreAuth = true;
	require_once 'classes.php';
	
	$xml_string = "";
	$xml_string = "<reviewofsystems>";
	
	$token = $_POST['token'];
	$visit_id = $_POST['visit_id'];
	
if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $strQuery = "SELECT fros.`id`,fros.`user`, fros.`date`,`fever`, `chills`, `night_sweats`, `weight_loss`, `poor_appetite`, `insomnia`, `fatigued`, `depressed`, `hyperactive`, `exposure_to_foreign_countries`, `cataracts`, `cataract_surgery`, `glaucoma`, `double_vision`, `blurred_vision`, `poor_hearing`, `headaches`, `ringing_in_ears`, `bloody_nose`, `sinusitis`, `sinus_surgery`, `dry_mouth`, `strep_throat`, `tonsillectomy`, `swollen_lymph_nodes`, `throat_cancer`, `throat_cancer_surgery`, `heart_attack`, `irregular_heart_beat`, `chest_pains`, `shortness_of_breath`, `high_blood_pressure`, `heart_failure`, `poor_circulation`, `vascular_surgery`, `cardiac_catheterization`, `coronary_artery_bypass`, `heart_transplant`, `stress_test`, `emphysema`, `chronic_bronchitis`, `interstitial_lung_disease`, `shortness_of_breath_2`, `lung_cancer`, `lung_cancer_surgery`, `pheumothorax`, `stomach_pains`, `peptic_ulcer_disease`, `gastritis`, `endoscopy`, `polyps`, `colonoscopy`, `colon_cancer`, `colon_cancer_surgery`, `ulcerative_colitis`, `crohns_disease`, `appendectomy`, `divirticulitis`, `divirticulitis_surgery`, `gall_stones`, `cholecystectomy`, `hepatitis`, `cirrhosis_of_the_liver`, `splenectomy`, `kidney_failure`, `kidney_stones`, `kidney_cancer`, `kidney_infections`, `bladder_infections`, `bladder_cancer`, `prostate_problems`, `prostate_cancer`, `kidney_transplant`, `sexually_transmitted_disease`, `burning_with_urination`, `discharge_from_urethra`, `rashes`, `infections`, `ulcerations`, `pemphigus`, `herpes`, `osetoarthritis`, `rheumotoid_arthritis`, `lupus`, `ankylosing_sondlilitis`, `swollen_joints`, `stiff_joints`, `broken_bones`, `neck_problems`, `back_problems`, `back_surgery`, `scoliosis`, `herniated_disc`, `shoulder_problems`, `elbow_problems`, `wrist_problems`, `hand_problems`, `hip_problems`, `knee_problems`, `ankle_problems`, `foot_problems`, `insulin_dependent_diabetes`, `noninsulin_dependent_diabetes`, `hypothyroidism`, `hyperthyroidism`, `cushing_syndrom`, `addison_syndrom`, `additional_notes`
	FROM `forms` AS f
	INNER JOIN `form_reviewofs` AS fros ON f.form_id = fros.id
	WHERE `encounter` = {$visit_id}
	AND `form_name` = 'Review of Systems Checks' ORDER BY fros.`date` DESC";

    $result = $db->get_results($strQuery);

    if ($result) {
        newEvent($event = 'reviewofsystem-record-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
        $xml_string .= "<status>0</status>";
        $xml_string .= "<reason>The Review of System has been fetched</reason>";

        for ($i = 0; $i < count($result); $i++) {
            $xml_string .= "<roschecks>\n";
            $xml_string .= "<id>{$result[$i]->id}</id>\n";
			$xml_string .= "<date>{$result[$i]->date}</date>\n";
			foreach ($result[$i] as $fieldName => $fieldValue) {
                $rowValue = xmlsafestring($fieldValue);
                if ($fieldName == 'id' || $fieldName == 'date') {
                    
                } else {
                    $xml_string .= "<disease>\n";
                    $xml_string .= "<name>$fieldName</name>\n";
                    $xml_string .= "<status>$rowValue</status>\n";
                    $xml_string .= "</disease>\n";
                }
			}

 			$user_query = "SELECT  `firstname` ,  `lastname` 
                                                    FROM  `medmasterusers` 
                                                    WHERE username LIKE  '{$result[$i]->user}'";
            $user_result = $db->get_row($user_query);
            $xml_string .= "<firstname>{$user_result->firstname}</firstname>\n";
            $xml_string .= "<lastname>{$user_result->lastname}</lastname>\n";
            $xml_string .= "</roschecks>\n";
        }
    } else {
        $xml_string .= "<status>-1</status>";
        $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</reviewofsystems>";
echo $xml_string;
?>