<?php
/**
 * api/updatereviewofsystems.php Update review of systems.
 *
 * API is allowed to update patient review of systems details.
 * 
 * Copyright (C) 2012 Karl Englund <karl@mastermobileproducts.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-3.0.html>;.
 *
 * @package OpenEMR
 * @author  Karl Englund <karl@mastermobileproducts.com>
 * @link    http://www.open-emr.org
 */
header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';
$xml_string = "";
$xml_string = "<reviewofsystems>";

$token = $_POST['token'];
$rosId = $_POST['id'];

$patientId = $_POST['pid'];
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
        $strQuery = 'UPDATE form_ros SET ';
        $strQuery .=' weight_change = "' . add_escape_custom($weight_change) . '",';
        $strQuery .=' weakness = "' .add_escape_custom( $weakness) . '",';
        $strQuery .=' fatigue = "' . add_escape_custom($fatigue) . '",';
        $strQuery .=' anorexia = "' . add_escape_custom($anorexia) . '",';
        $strQuery .=' fever = "' . add_escape_custom($fever) . '",';
        $strQuery .=' chills = "' . add_escape_custom($chills) . '",';
        $strQuery .=' night_sweats = "' . add_escape_custom($night_sweats) . '",';
        $strQuery .=' insomnia = "' . add_escape_custom($insomnia) . '",';
        $strQuery .=' irritability = "' . add_escape_custom($irritability) . '",';
        $strQuery .=' heat_or_cold = "' . add_escape_custom($heat_or_cold) . '",';
        $strQuery .=' intolerance = "' . add_escape_custom($intolerance) . '",';
        $strQuery .=' change_in_vision = "' . add_escape_custom($change_in_vision) . '",';
        $strQuery .=' glaucoma_history = "' . add_escape_custom($glaucoma_history) . '",';
        $strQuery .=' eye_pain = "' . add_escape_custom($eye_pain) . '",';
        $strQuery .=' irritation = "' . add_escape_custom($irritation) . '",';
        $strQuery .=' redness = "' . add_escape_custom($redness) . '",';
        $strQuery .=' excessive_tearing = "' . add_escape_custom($excessive_tearing) . '",';
        $strQuery .=' double_vision = "' . add_escape_custom($double_vision) . '",';
        $strQuery .=' blind_spots = "' . add_escape_custom($blind_spots) . '",';
        $strQuery .=' photophobia = "' . add_escape_custom($photophobia) . '",';
        $strQuery .=' hearing_loss = "' . add_escape_custom($hearing_loss) . '",';
        $strQuery .=' discharge = "' . add_escape_custom($discharge) . '",';
        $strQuery .=' pain = "' . add_escape_custom($pain) . '",';
        $strQuery .=' vertigo = "' . add_escape_custom($vertigo) . '",';
        $strQuery .=' tinnitus = "' . add_escape_custom($tinnitus) . '",';
        $strQuery .=' frequent_colds = "' . add_escape_custom($frequent_colds) . '",';
        $strQuery .=' sore_throat = "' . add_escape_custom($sore_throat) . '",';
        $strQuery .=' sinus_problems = "' . add_escape_custom($sinus_problems) . '",';
        $strQuery .=' post_nasal_drip = "' . add_escape_custom($post_nasal_drip) . '",';
        $strQuery .=' nosebleed = "' . add_escape_custom($nosebleed) . '",';
        $strQuery .=' snoring = "' . add_escape_custom($snoring) . '",';
        $strQuery .=' apnea = "' . add_escape_custom($apnea) . '",';
        $strQuery .=' breast_mass = "' . add_escape_custom($breast_mass) . '",';
        $strQuery .=' breast_discharge = "' . add_escape_custom($breast_discharge) . '",';
        $strQuery .=' biopsy = "' . add_escape_custom($biopsy) . '",';
        $strQuery .=' abnormal_mammogram = "' . add_escape_custom($abnormal_mammogram) . '",';
        $strQuery .=' cough = "' . add_escape_custom($cough) . '",';
        $strQuery .=' sputum = "' . add_escape_custom($sputum) . '",';
        $strQuery .=' shortness_of_breath = "' . add_escape_custom($shortness_of_breath) . '",';
        $strQuery .=' wheezing = "' . add_escape_custom($wheezing) . '",';
        $strQuery .=' hemoptsyis = "' . add_escape_custom($hemoptsyis) . '",';
        $strQuery .=' asthma = "' . add_escape_custom($asthma) . '",';
        $strQuery .=' copd = "' . add_escape_custom($copd) . '",';
        $strQuery .=' chest_pain = "' . add_escape_custom($chest_pain) . '",';
        $strQuery .=' palpitation = "' . add_escape_custom($palpitation) . '",';
        $strQuery .=' syncope = "' . add_escape_custom($syncope) . '",';
        $strQuery .=' pnd = "' . add_escape_custom($pnd) . '",';
        $strQuery .=' doe = "' . add_escape_custom($doe) . '",';
        $strQuery .=' orthopnea = "' . add_escape_custom($orthopnea) . '",';
        $strQuery .=' peripheal = "' . add_escape_custom($peripheal) . '",';
        $strQuery .=' edema = "' . add_escape_custom($edema) . '",';
        $strQuery .=' legpain_cramping = "' . add_escape_custom($legpain_cramping) . '",';
        $strQuery .=' history_murmur = "' . add_escape_custom($history_murmur) . '",';
        $strQuery .=' arrythmia = "' . add_escape_custom($arrythmia) . '",';
        $strQuery .=' heart_problem = "' . add_escape_custom($heart_problem) . '",';
        $strQuery .=' dysphagia = "' . add_escape_custom($dysphagia) . '",';
        $strQuery .=' heartburn = "' . add_escape_custom($heartburn) . '",';
        $strQuery .=' bloating = "' . add_escape_custom($bloating) . '",';
        $strQuery .=' belching = "' . add_escape_custom($belching) . '",';
        $strQuery .=' flatulence = "' . add_escape_custom($flatulence) . '",';
        $strQuery .=' nausea = "' . add_escape_custom($nausea) . '",';
        $strQuery .=' vomiting = "' . add_escape_custom($vomiting) . '",';
        $strQuery .=' hematemesis = "' . add_escape_custom($hematemesis) . '",';
        $strQuery .=' gastro_pain = "' . add_escape_custom($gastro_pain) . '",';
        $strQuery .=' food_intolerance = "' . add_escape_custom($food_intolerance) . '",';
        $strQuery .=' hepatitis = "' . add_escape_custom($hepatitis) . '",';
        $strQuery .=' jaundice = "' . add_escape_custom($jaundice) . '",';
        $strQuery .=' hematochezia = "' . add_escape_custom($hematochezia) . '",';
        $strQuery .=' changed_bowel = "' . add_escape_custom($changed_bowel) . '",';
        $strQuery .=' diarrhea = "' . add_escape_custom($diarrhea) . '",';
        $strQuery .=' constipation = "' . add_escape_custom($constipation) . '",';
        $strQuery .=' polyuria = "' . add_escape_custom($polyuria) . '",';
        $strQuery .=' polydypsia = "' . add_escape_custom($polydypsia) . '",';
        $strQuery .=' dysuria = "' . add_escape_custom($dysuria) . '",';
        $strQuery .=' hematuria = "' . add_escape_custom($hematuria) . '",';
        $strQuery .=' frequency = "' . add_escape_custom($frequency) . '",';
        $strQuery .=' urgency = "' . add_escape_custom($urgency) . '",';
        $strQuery .=' incontinence = "' . add_escape_custom($incontinence) . '",';
        $strQuery .=' renal_stones = "' . add_escape_custom($renal_stones) . '",';
        $strQuery .=' utis = "' . add_escape_custom($utis) . '",';
        $strQuery .=' hesitancy = "' . add_escape_custom($hesitancy) . '",';
        $strQuery .=' dribbling = "' . add_escape_custom($dribbling) . '",';
        $strQuery .=' stream = "' . add_escape_custom($stream) . '",';
        $strQuery .=' nocturia = "' . add_escape_custom($nocturia) . '",';
        $strQuery .=' erections = "' . add_escape_custom($erections) . '",';
        $strQuery .=' ejaculations = "' . add_escape_custom($ejaculations) . '",';
        $strQuery .=' g = "' . add_escape_custom($g) . '",';
        $strQuery .=' p = "' . add_escape_custom($p) . '",';
        $strQuery .=' ap = "' . add_escape_custom($ap) . '",';
        $strQuery .=' lc = "' . add_escape_custom($lc) . '",';
        $strQuery .=' mearche = "' . add_escape_custom($mearche) . '",';
        $strQuery .=' menopause = "' . add_escape_custom($menopause) . '",';
        $strQuery .=' lmp = "' . add_escape_custom($lmp) . '",';
        $strQuery .=' f_frequency = "' . add_escape_custom($f_frequency) . '",';
        $strQuery .=' f_flow = "' . add_escape_custom($f_flow) . '",';
        $strQuery .=' f_symptoms = "' . add_escape_custom($f_symptoms) . '",';
        $strQuery .=' abnormal_hair_growth = "' . add_escape_custom($abnormal_hair_growth) . '",';
        $strQuery .=' f_hirsutism = "' . add_escape_custom($f_hirsutism) . '",';
        $strQuery .=' joint_pain = "' . add_escape_custom($joint_pain) . '",';
        $strQuery .=' swelling = "' . add_escape_custom($swelling) . '",';
        $strQuery .=' m_redness = "' . add_escape_custom($m_redness) . '",';
        $strQuery .=' m_warm = "' . add_escape_custom($m_warm) . '",';
        $strQuery .=' m_stiffness = "' . add_escape_custom($m_stiffness) . '",';
        $strQuery .=' muscle = "' . add_escape_custom($muscle) . '",';
        $strQuery .=' m_aches = "' . add_escape_custom($m_aches) . '",';
        $strQuery .=' fms = "' . add_escape_custom($fms) . '",';
        $strQuery .=' arthritis = "' . add_escape_custom($arthritis) . '",';
        $strQuery .=' loc = "' . add_escape_custom($loc) . '",';
        $strQuery .=' seizures = "' . add_escape_custom($seizures) . '",';
        $strQuery .=' stroke = "' . add_escape_custom($stroke) . '",';
        $strQuery .=' tia = "' . add_escape_custom($tia) . '",';
        $strQuery .=' n_numbness = "' . add_escape_custom($n_numbness) . '",';
        $strQuery .=' n_weakness = "' . add_escape_custom($n_weakness) . '",';
        $strQuery .=' paralysis = "' . add_escape_custom($paralysis) . '",';
        $strQuery .=' intellectual_decline = "' . add_escape_custom($intellectual_decline) . '",';
        $strQuery .=' memory_problems = "' . add_escape_custom($memory_problems) . '",';
        $strQuery .=' dementia = "' . add_escape_custom($dementia) . '",';
        $strQuery .=' n_headache = "' . add_escape_custom($n_headache) . '",';
        $strQuery .=' s_cancer = "' . add_escape_custom($s_cancer) . '",';
        $strQuery .=' psoriasis = "' . add_escape_custom($psoriasis) . '",';
        $strQuery .=' s_acne = "' . add_escape_custom($s_acne) . '",';
        $strQuery .=' s_other = "' . add_escape_custom($s_other) . '",';
        $strQuery .=' s_disease = "' . add_escape_custom($s_disease) . '",';
        $strQuery .=' p_diagnosis = "' . add_escape_custom($p_diagnosis) . '",';
        $strQuery .=' p_medication = "' . add_escape_custom($p_medication) . '",';
        $strQuery .=' depression = "' . add_escape_custom($depression) . '",';
        $strQuery .=' anxiety = "' . add_escape_custom($anxiety) . '",';
        $strQuery .=' social_difficulties = "' . add_escape_custom($social_difficulties) . '",';
        $strQuery .=' thyroid_problems = "' . add_escape_custom($thyroid_problems) . '",';
        $strQuery .=' diabetes = "' . add_escape_custom($diabetes) . '",';
        $strQuery .=' abnormal_blood = "' . add_escape_custom($abnormal_blood) . '",';
        $strQuery .=' anemia = "' . add_escape_custom($anemia) . '",';
        $strQuery .=' fh_blood_problems = "' . add_escape_custom($fh_blood_problems) . '",';
        $strQuery .=' bleeding_problems = "' . add_escape_custom($bleeding_problems) . '",';
        $strQuery .=' allergies = "' . add_escape_custom($allergies) . '",';
        $strQuery .=' frequent_illness = "' . add_escape_custom($frequent_illness) . '",';
        $strQuery .=' hiv = "' . add_escape_custom($hiv) . '",';
        $strQuery .=' hai_status = "' . add_escape_custom($hai_status) . '"';
        $strQuery .= ' WHERE id = ?';

        $result = sqlStatement($strQuery, array($rosId));

        if ($result !== FALSE) {

            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Review of Systems has been updated</reason>";
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

$xml_string .= "</reviewofsystems>";
echo $xml_string;
?>