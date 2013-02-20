<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<reviewofsystems>";

$token = $_POST['token'];
$visit_id = $_POST['visit_id'];

$patientId = $_POST['pid'];

$activity = 1;
$weight_change = $_POST['weight_change'];
$weakness = $_POST['weakness'];
$fatigue = $_POST['fatigue'];
$anorexia = $_POST['anorexia'];
$fever = $_POST['fever'];
$chills = $_POST['chills'];
$night_sweats = $_POST['night_sweats'];
$insomnia = $_POST['insomnia'];
$irritability = $_POST['irritability'];
$heat_or_cold = $_POST['heat_or_cold'];
$intolerance = $_POST['intolerance'];
$change_in_vision = $_POST['change_in_vision'];
$glaucoma_history = $_POST['glaucoma_history'];
$eye_pain = $_POST['eye_pain'];
$irritation = $_POST['irritation'];
$redness = $_POST['redness'];
$excessive_tearing = $_POST['excessive_tearing'];
$double_vision = $_POST['double_vision'];
$blind_spots = $_POST['blind_spots'];
$photophobia = $_POST['photophobia'];
$hearing_loss = $_POST['hearing_loss'];
$discharge = $_POST['discharge'];
$pain = $_POST['pain'];
$vertigo = $_POST['vertigo'];
$tinnitus = $_POST['tinnitus'];
$frequent_colds = $_POST['frequent_colds'];
$sore_throat = $_POST['sore_throat'];
$sinus_problems = $_POST['sinus_problems'];
$post_nasal_drip = $_POST['post_nasal_drip'];
$nosebleed = $_POST['nosebleed'];
$snoring = $_POST['snoring'];
$apnea = $_POST['apnea'];
$breast_mass = $_POST['breast_mass'];
$breast_discharge = $_POST['breast_discharge'];
$biopsy = $_POST['biopsy'];
$abnormal_mammogram = $_POST['abnormal_mammogram'];
$cough = $_POST['cough'];
$sputum = $_POST['sputum'];
$shortness_of_breath = $_POST['shortness_of_breath'];
$wheezing = $_POST['wheezing'];
$hemoptsyis = $_POST['hemoptsyis'];
$asthma = $_POST['asthma'];
$copd = $_POST['copd'];
$chest_pain = $_POST['chest_pain'];
$palpitation = $_POST['palpitation'];
$syncope = $_POST['syncope'];
$pnd = $_POST['pnd'];
$doe = $_POST['doe'];
$orthopnea = $_POST['orthopnea'];
$peripheal = $_POST['peripheal'];
$edema = $_POST['edema'];
$legpain_cramping = $_POST['legpain_cramping'];
$history_murmur = $_POST['history_murmur'];
$arrythmia = $_POST['arrythmia'];
$heart_problem = $_POST['heart_problem'];
$dysphagia = $_POST['dysphagia'];
$heartburn = $_POST['heartburn'];
$bloating = $_POST['bloating'];
$belching = $_POST['belching'];
$flatulence = $_POST['flatulence'];
$nausea = $_POST['nausea'];
$vomiting = $_POST['vomiting'];
$hematemesis = $_POST['hematemesis'];
$gastro_pain = $_POST['gastro_pain'];
$food_intolerance = $_POST['food_intolerance'];
$hepatitis = $_POST['hepatitis'];
$jaundice = $_POST['jaundice'];
$hematochezia = $_POST['hematochezia'];
$changed_bowel = $_POST['changed_bowel'];
$diarrhea = $_POST['diarrhea'];
$constipation = $_POST['constipation'];
$polyuria = $_POST['polyuria'];
$polydypsia = $_POST['polydypsia'];
$dysuria = $_POST['dysuria'];
$hematuria = $_POST['hematuria'];
$frequency = $_POST['frequency'];
$urgency = $_POST['urgency'];
$incontinence = $_POST['incontinence'];
$renal_stones = $_POST['renal_stones'];
$utis = $_POST['utis'];
$hesitancy = $_POST['hesitancy'];
$dribbling = $_POST['dribbling'];
$stream = $_POST['stream'];
$nocturia = $_POST['nocturia'];
$erections = $_POST['erections'];
$ejaculations = $_POST['ejaculations'];
$g = $_POST['g'];
$p = $_POST['p'];
$ap = $_POST['ap'];
$lc = $_POST['lc'];
$mearche = $_POST['mearche'];
$menopause = $_POST['menopause'];
$lmp = $_POST['lmp'];
$f_frequency = $_POST['f_frequency'];
$f_flow = $_POST['f_flow'];
$f_symptoms = $_POST['f_symptoms'];
$abnormal_hair_growth = $_POST['abnormal_hair_growth'];
$f_hirsutism = $_POST['f_hirsutism'];
$joint_pain = $_POST['joint_pain'];
$swelling = $_POST['swelling'];
$m_redness = $_POST['m_redness'];
$m_warm = $_POST['m_warm'];
$m_stiffness = $_POST['m_stiffness'];
$muscle = $_POST['muscle'];
$m_aches = $_POST['m_aches'];
$fms = $_POST['fms'];
$arthritis = $_POST['arthritis'];
$loc = $_POST['loc'];
$seizures = $_POST['seizures'];
$stroke = $_POST['stroke'];
$tia = $_POST['tia'];
$n_numbness = $_POST['n_numbness'];
$n_weakness = $_POST['n_weakness'];
$paralysis = $_POST['paralysis'];
$intellectual_decline = $_POST['intellectual_decline'];
$memory_problems = $_POST['memory_problems'];
$dementia = $_POST['dementia'];
$n_headache = $_POST['n_headache'];
$s_cancer = $_POST['s_cancer'];
$psoriasis = $_POST['psoriasis'];
$s_acne = $_POST['s_acne'];
$s_other = $_POST['s_other'];
$s_disease = $_POST['s_disease'];
$p_diagnosis = $_POST['p_diagnosis'];
$p_medication = $_POST['p_medication'];
$depression = $_POST['depression'];
$anxiety = $_POST['anxiety'];
$social_difficulties = $_POST['social_difficulties'];
$thyroid_problems = $_POST['thyroid_problems'];
$diabetes = $_POST['diabetes'];
$abnormal_blood = $_POST['abnormal_blood'];
$anemia = $_POST['anemia'];
$fh_blood_problems = $_POST['fh_blood_problems'];
$bleeding_problems = $_POST['bleeding_problems'];
$allergies = $_POST['allergies'];
$frequent_illness = $_POST['frequent_illness'];
$hiv = $_POST['hiv'];
$hai_status = $_POST['hai_status'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
     
    $_SESSION['authUser'] = $user;
    $_SESSION['authGroup'] = $site;
    $_SESSION['pid'] = $patientId;
    
    if ($acl_allow) {
        $strQuery = "INSERT INTO form_ros (pid, activity, date, weight_change, weakness, fatigue, anorexia, fever, chills, night_sweats, insomnia, irritability, heat_or_cold, intolerance, change_in_vision, glaucoma_history, eye_pain, irritation, redness, excessive_tearing, double_vision, blind_spots, photophobia, hearing_loss, discharge, pain, vertigo, tinnitus, frequent_colds, sore_throat, sinus_problems, post_nasal_drip, nosebleed, snoring, apnea, breast_mass, breast_discharge, biopsy, abnormal_mammogram, cough, sputum, shortness_of_breath, wheezing, hemoptsyis, asthma, copd, chest_pain, palpitation, syncope, pnd, doe, orthopnea, peripheal, edema, legpain_cramping, history_murmur, arrythmia, heart_problem, dysphagia, heartburn, bloating, belching, flatulence, nausea, vomiting, hematemesis, gastro_pain, food_intolerance, hepatitis, jaundice, hematochezia, changed_bowel, diarrhea, constipation, polyuria, polydypsia, dysuria, hematuria, frequency, urgency, incontinence, renal_stones, utis, hesitancy, dribbling, stream, nocturia, erections, ejaculations, g, p, ap, lc, mearche, menopause, lmp, f_frequency, f_flow, f_symptoms, abnormal_hair_growth, f_hirsutism, joint_pain, swelling, m_redness, m_warm, m_stiffness, muscle, m_aches, fms, arthritis, loc, seizures, stroke, tia, n_numbness, n_weakness, paralysis, intellectual_decline, memory_problems, dementia, n_headache, s_cancer, psoriasis, s_acne, s_other, s_disease, p_diagnosis, p_medication, depression, anxiety, social_difficulties, thyroid_problems, diabetes, abnormal_blood, anemia, fh_blood_problems, bleeding_problems, allergies, frequent_illness, hiv, hai_status) VALUES ('" . $patientId . "', '" . $activity . "', '" . date('Y-m-d H:i:s') . "', '" . $weight_change . "', '" . $weakness . "', '" . $fatigue . "', '" . $anorexia . "', '" . $fever . "', '" . $chills . "', '" . $night_sweats . "', '" . $insomnia . "', '" . $irritability . "', '" . $heat_or_cold . "', '" . $intolerance . "', '" . $change_in_vision . "', '" . $glaucoma_history . "', '" . $eye_pain . "', '" . $irritation . "', '" . $redness . "', '" . $excessive_tearing . "', '" . $double_vision . "', '" . $blind_spots . "', '" . $photophobia . "', '" . $hearing_loss . "', '" . $discharge . "', '" . $pain . "', '" . $vertigo . "', '" . $tinnitus . "', '" . $frequent_colds . "', '" . $sore_throat . "', '" . $sinus_problems . "', '" . $post_nasal_drip . "', '" . $nosebleed . "', '" . $snoring . "', '" . $apnea . "', '" . $breast_mass . "', '" . $breast_discharge . "', '" . $biopsy . "', '" . $abnormal_mammogram . "', '" . $cough . "', '" . $sputum . "', '" . $shortness_of_breath . "', '" . $wheezing . "', '" . $hemoptsyis . "', '" . $asthma . "', '" . $copd . "', '" . $chest_pain . "', '" . $palpitation . "', '" . $syncope . "', '" . $pnd . "', '" . $doe . "', '" . $orthopnea . "', '" . $peripheal . "', '" . $edema . "', '" . $legpain_cramping . "', '" . $history_murmur . "', '" . $arrythmia . "', '" . $heart_problem . "', '" . $dysphagia . "', '" . $heartburn . "', '" . $bloating . "', '" . $belching . "', '" . $flatulence . "', '" . $nausea . "', '" . $vomiting . "', '" . $hematemesis . "', '" . $gastro_pain . "', '" . $food_intolerance . "', '" . $hepatitis . "', '" . $jaundice . "', '" . $hematochezia . "', '" . $changed_bowel . "', '" . $diarrhea . "', '" . $constipation . "', '" . $polyuria . "', '" . $polydypsia . "', '" . $dysuria . "', '" . $hematuria . "', '" . $frequency . "', '" . $urgency . "', '" . $incontinence . "', '" . $renal_stones . "', '" . $utis . "', '" . $hesitancy . "', '" . $dribbling . "', '" . $stream . "', '" . $nocturia . "', '" . $erections . "', '" . $ejaculations . "', '" . $g . "', '" . $p . "', '" . $ap . "', '" . $lc . "', '" . $mearche . "', '" . $menopause . "', '" . $lmp . "', '" . $f_frequency . "', '" . $f_flow . "', '" . $f_symptoms . "', '" . $abnormal_hair_growth . "', '" . $f_hirsutism . "', '" . $joint_pain . "', '" . $swelling . "', '" . $m_redness . "', '" . $m_warm . "', '" . $m_stiffness . "', '" . $muscle . "', '" . $m_aches . "', '" . $fms . "', '" . $arthritis . "', '" . $loc . "', '" . $seizures . "', '" . $stroke . "', '" . $tia . "', '" . $n_numbness . "', '" . $n_weakness . "', '" . $paralysis . "', '" . $intellectual_decline . "', '" . $memory_problems . "', '" . $dementia . "', '" . $n_headache . "', '" . $s_cancer . "', '" . $psoriasis . "', '" . $s_acne . "', '" . $s_other . "', '" . $s_disease . "', '" . $p_diagnosis . "', '" . $p_medication . "', '" . $depression . "', '" . $anxiety . "', '" . $social_difficulties . "', '" . $thyroid_problems . "', '" . $diabetes . "', '" . $abnormal_blood . "', '" . $anemia . "', '" . $fh_blood_problems . "', '" . $bleeding_problems . "', '" . $allergies . "', '" . $frequent_illness . "', '" . $hiv . "', '" . $hai_status . "')";

        $result = sqlInsert($strQuery);

        $last_inserted_id = $result;

        if ($result) {
            addForm($visit_id, $form_name = 'Review Of Systems', $last_inserted_id, $formdir = 'ros ', $patientId, $authorized = "1", $date = "NOW()", $user, $group = "Default");

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Review of Systems added successfully</reason>";
            $xml_string .= "<rosid>" . $last_inserted_id . "</rosid>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Could not add Review of Systems</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</reviewofsystems>";
echo $xml_string;
?>