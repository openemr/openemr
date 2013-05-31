<?php
/**
 * api/getroschecksummary.php Get ROSChecks summary.
 *
 * API is allowed to get patient review of systems checks summary.
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
$patientId = $_POST['patientId'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    if ($acl_allow) {
        $strQuery = "SELECT fros.`id`, fros.date, `fever`, `chills`, `night_sweats`, `weight_loss`, `poor_appetite`, `insomnia`, `fatigued`, `depressed`, `hyperactive`, `exposure_to_foreign_countries`, `cataracts`, `cataract_surgery`, `glaucoma`, `double_vision`, `blurred_vision`, `poor_hearing`, `headaches`, `ringing_in_ears`, `bloody_nose`, `sinusitis`, `sinus_surgery`, `dry_mouth`, `strep_throat`, `tonsillectomy`, `swollen_lymph_nodes`, `throat_cancer`, `throat_cancer_surgery`, `heart_attack`, `irregular_heart_beat`, `chest_pains`, `shortness_of_breath`, `high_blood_pressure`, `heart_failure`, `poor_circulation`, `vascular_surgery`, `cardiac_catheterization`, `coronary_artery_bypass`, `heart_transplant`, `stress_test`, `emphysema`, `chronic_bronchitis`, `interstitial_lung_disease`, `shortness_of_breath_2`, `lung_cancer`, `lung_cancer_surgery`, `pheumothorax`, `stomach_pains`, `peptic_ulcer_disease`, `gastritis`, `endoscopy`, `polyps`, `colonoscopy`, `colon_cancer`, `colon_cancer_surgery`, `ulcerative_colitis`, `crohns_disease`, `appendectomy`, `divirticulitis`, `divirticulitis_surgery`, `gall_stones`, `cholecystectomy`, `hepatitis`, `cirrhosis_of_the_liver`, `splenectomy`, `kidney_failure`, `kidney_stones`, `kidney_cancer`, `kidney_infections`, `bladder_infections`, `bladder_cancer`, `prostate_problems`, `prostate_cancer`, `kidney_transplant`, `sexually_transmitted_disease`, `burning_with_urination`, `discharge_from_urethra`, `rashes`, `infections`, `ulcerations`, `pemphigus`, `herpes`, `osetoarthritis`, `rheumotoid_arthritis`, `lupus`, `ankylosing_sondlilitis`, `swollen_joints`, `stiff_joints`, `broken_bones`, `neck_problems`, `back_problems`, `back_surgery`, `scoliosis`, `herniated_disc`, `shoulder_problems`, `elbow_problems`, `wrist_problems`, `hand_problems`, `hip_problems`, `knee_problems`, `ankle_problems`, `foot_problems`, `insulin_dependent_diabetes`, `noninsulin_dependent_diabetes`, `hypothyroidism`, `hyperthyroidism`, `cushing_syndrom`, `addison_syndrom`, `additional_notes` FROM `forms` AS f
					INNER JOIN `form_reviewofs` AS fros ON f.form_id = fros.id
					WHERE fros.pid = ?
					AND `form_name` = 'Review of Systems Checks'";

        $result = sqlStatement($strQuery, array($patientId));
        if ($result->_numOfRows > 0) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Review of System Processed successfully</reason>";

            while ($res = sqlFetchArray($result)) {
                $xml_string .= "<roschecks>\n";
                $xml_string .= "<id>{$res['id']}</id>\n";
                $xml_string .= "<date>{$res['date']}</date>\n";
                $couter = 0;
                foreach ($res as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    if ($rowValue == 'on') {
                        $xml_string .= "<disease>\n";
                        $xml_string .= "<name>$fieldName</name>\n";
                        $xml_string .= "<status>$rowValue</status>\n";
                        $xml_string .= "</disease>\n";
                        $couter++;
                    }
                }
                $xml_string .="<counter>" . $couter . "</counter>";
                $xml_string .= "</roschecks>\n";
            }
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Could not find results</reason>";
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