<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<list>";

$token = $_POST['token'];
$patientId = $_POST['patientId'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);

    if ($acl_allow) {

        $strQuery = "SELECT fros.id, fros.date, weight_change, weakness, fatigue, anorexia, fever, chills, night_sweats, insomnia, irritability, heat_or_cold, intolerance, change_in_vision, glaucoma_history, eye_pain, irritation, redness, excessive_tearing, double_vision, blind_spots, photophobia, hearing_loss, discharge, pain, vertigo, tinnitus, frequent_colds, sore_throat, sinus_problems, post_nasal_drip, nosebleed, snoring, apnea, breast_mass, breast_discharge, biopsy, abnormal_mammogram, cough, sputum, shortness_of_breath, wheezing, hemoptsyis, asthma, copd, chest_pain, palpitation, syncope, pnd, doe, orthopnea, peripheal, edema, legpain_cramping, history_murmur, arrythmia, heart_problem, dysphagia, heartburn, bloating, belching, flatulence, nausea, vomiting, hematemesis, gastro_pain, food_intolerance, hepatitis, jaundice, hematochezia, changed_bowel, diarrhea, constipation, polyuria, polydypsia, dysuria, hematuria, frequency, urgency, incontinence, renal_stones, utis, hesitancy, dribbling, stream, nocturia, erections, ejaculations, g, p, ap, lc, mearche, menopause, lmp, f_frequency, f_flow, f_symptoms, abnormal_hair_growth, f_hirsutism, joint_pain, swelling, m_redness, m_warm, m_stiffness, muscle, m_aches, fms, arthritis, loc, seizures, stroke, tia, n_numbness, n_weakness, paralysis, intellectual_decline, memory_problems, dementia, n_headache, s_cancer, psoriasis, s_acne, s_other, s_disease, p_diagnosis, p_medication, depression, anxiety, social_difficulties, thyroid_problems, diabetes, abnormal_blood, anemia, fh_blood_problems, bleeding_problems, allergies, frequent_illness, hiv, hai_status
	FROM `forms` AS f
	INNER JOIN `form_ros` AS fros ON f.form_id = fros.id
	WHERE fros.pid = {$patientId}
	AND `form_name` = 'Review Of Systems'";

        $result = $db->get_results($strQuery);

        if ($result) {
            newEvent($event = 'reviewofsystem-record-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Review of Systems Record has been fetched</reason>";

            for ($i = 0; $i < count($result); $i++) {
                $xml_string .= "<reviewofsystem>\n";
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

                $xml_string .= "</reviewofsystem>\n";
            }
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

$xml_string .= "</list>";
echo $xml_string;
?>