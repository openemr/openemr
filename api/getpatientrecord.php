<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once('classes.php');

//$p_id = add_escape_custom($_REQUEST['patientID']);
//$token = $_REQUEST['token'];

$p_id = add_escape_custom(30);
$token = 'e85e54d56c48027eddd7150b8ea2eab3';

$xml_array = array();

if ($userId = validateToken($token)) {
    $user_data = getUserData($userId);
    $user = $user_data['user'];
    $emr = $user_data['emr'];
    $username = $user_data['username'];
    $password = $user_data['password'];

    $acl_allow = acl_check('patientportal', 'portal', $username);
    if ($acl_allow) {
        $patient = getPatientData($p_id);
        $xml_array['status'] = 0;
        $xml_array['reason'] = "Success patient processing record";
        if ($patient) {
            $xml_array['Patient']['demographics'] = $patient;

            $ethencity_query = "SELECT option_id, title FROM list_options WHERE list_id  = 'ethnicity' AND `option_id` = '" . $patient["ethnicity"] . "'";
            $ethencity_result = $db->get_row($ethencity_query);
            if ($ethencity_result) {
                $xml_array['Patient']['demographics']['ethnicityvalue'] = $ethencity_result->title;
            } else {
                $xml_array['Patient']['demographics']['ethnicityvalue'] = '';
            }

            $p_insurance = getInsuranceData($p_id);
            $s_insurance = getInsuranceData($p_id, 'secondary');
            $o_insurance = getInsuranceData($p_id, 'tertiary');

            if ($p_insurance || $s_insurance) {
                $xml_array['Patient']['insurancelist']['status'] = 0;
                $xml_array['Patient']['insurancelist']['insuranceitem-1'] = $p_insurance;
                $xml_array['Patient']['insurancelist']['insuranceitem-2'] = $s_insurance;
                $xml_array['Patient']['insurancelist']['insuranceitem-3'] = $o_insurance;
            } else {
                $xml_array['Patient']['insurancelist']['status'] = 1;
                $xml_array['Patient']['insurancelist']['reason'] = 'No insurance data found';
            }

            $patient_hisory = getHistoryData($p_id);
            if ($patient_hisory) {
                $xml_array['Patient']['history']['status'] = 0;
                $xml_array['Patient']['history'] = $patient_hisory;
            } else {
                $xml_array['Patient']['history']['status'] = 1;
                $xml_array['Patient']['history']['reason'] = 'No history data found';
            }

            $list_data_mp = getListByType($p_id, 'medical_problem');

            if ($list_data_mp) {
                $xml_array['Patient']['problemlist']['status'] = 0;
                foreach ($list_data_mp as $key => $list_data1) {
                    $xml_array['Patient']['problemlist']['problem-' . $key] = $list_data1;
                    $diagnosis_title = getDrugTitle($list_data1['diagnosis'], $db);
                    $xml_array['Patient']['problemlist']['problem-' . $key]['diagnosis_title'] = $diagnosis_title;
                }
            } else {
                $xml_array['Patient']['problemlist']['status'] = 1;
                $xml_array['Patient']['problemlist']['reason'] = 'No Medical Problem data found';
            }

            $list_data_m = getListByType($p_id, 'medication');

            if ($list_data_m) {
                $xml_array['Patient']['medicationlist']['status'] = 0;
                foreach ($list_data_m as $key => $list_data1_m) {
                    $xml_array['Patient']['medicationlist']['medication-' . $key] = $list_data1_m;
                    $diagnosis_title = getDrugTitle($list_data1_m['diagnosis'], $db);
                    $xml_array['Patient']['medicationlist']['medication-' . $key]['diagnosis_title'] = $diagnosis_title;
                }
            } else {
                $xml_array['Patient']['medicationlist']['status'] = 1;
                $xml_array['Patient']['medicationlist']['reason'] = 'No Medication data found';
            }

            $list_data_a = getListByType($p_id, 'allergy');
            if ($list_data_a) {
                $xml_array['Patient']['allergylist']['status'] = 0;
                foreach ($list_data_a as $key => $list_data1_a) {
                    $xml_array['Patient']['allergylist']['allergy-' . $key] = $list_data1_a;
                    $diagnosis_title = getDrugTitle($list_data1_a['diagnosis'], $db);
                    $xml_array['Patient']['allergylist']['allergy-' . $key]['diagnosis_title'] = $diagnosis_title;
                }
            } else {
                $xml_array['Patient']['allergylist']['status'] = 1;
                $xml_array['Patient']['allergylist']['reason'] = 'No Allergy data found';
            }

            $list_data_d = getListByType($p_id, 'dental');
            if ($list_data_d) {
                $xml_array['Patient']['dentallist']['status'] = 0;
                foreach ($list_data_d as $key => $list_data1_d) {
                    $xml_array['Patient']['dentallist']['dental-' . $key] = $list_data1_d;
                    $diagnosis_title = getDrugTitle($list_data1_d['diagnosis'], $db);
                    $xml_array['Patient']['dentallist']['dental-' . $key]['diagnosis_title'] = $diagnosis_title;
                }
            } else {
                $xml_array['Patient']['dentallist']['status'] = 1;
                $xml_array['Patient']['dentallist']['reason'] = 'No Dental data found';
            }

            $list_data_s = getListByType($p_id, 'surgery');
            if ($list_data_s) {
                $xml_array['Patient']['surgerylist']['status'] = 0;
                foreach ($list_data_s as $key => $list_data1_s) {
                    $xml_array['Patient']['surgerylist']['surgery-' . $key] = $list_data1_s;
                    $diagnosis_title = getDrugTitle($list_data1_s['diagnosis'], $db);
                    $xml_array['Patient']['surgerylist']['surgery-' . $key]['diagnosis_title'] = $diagnosis_title;
                }
            } else {
                $xml_array['Patient']['surgerylist']['status'] = 1;
                $xml_array['Patient']['surgerylist']['reason'] = 'No surgery data found';
            }

            $patient_data = getPatientNotes($p_id);
            if ($patient_data) {
                $xml_array['Patient']['notelist']['status'] = 0;
                foreach ($patient_data as $key => $patient_data_a) {
                    $xml_array['Patient']['notelist']['note-' . $key] = $patient_data_a;
                }
            } else {
                $xml_array['Patient']['notelist']['status'] = 1;
                $xml_array['Patient']['notelist']['reason'] = 'No Patient Data found';
            }


            $strQuery8 = "select date as vitalsdate, bps, bpd, weight, height, temperature, temp_method,
				pulse, respiration, note as vitalnote, bmi, bmi_status, waist_circ, head_circ,
				oxygen_saturation 
				FROM form_vitals
				WHERE pid = ?
				ORDER BY DATE DESC";

            $dbresult8 = sqlStatement($strQuery8,array($p_id));
            if ($dbresult8) {
                $counter8 = 0;
                $xml_array['Patient']['vitalslist']['status'] = 0;
                while ($row8 = sqlFetchArray($dbresult8)) {
                    foreach ($row8 as $fieldname => $fieldvalue8) {
                        $rowvalue = xmlsafestring($fieldvalue8);
                        $xml_array['Patient']['vitalslist']['vitals-' . $counter8][$fieldname] = $rowvalue;
                    } // foreach
                    $counter8++;
                }
            } else {
                $xml_array['Patient']['vitalslist']['status'] = 1;
                $xml_array['Patient']['vitalslist']['reason'] = 'No Patient Vital Data found';
            }


            $strQuery1 = "SELECT d.date,d.size,d.url,d.docdate,d.mimetype,c2d.category_id
                                FROM `documents` AS d
                                INNER JOIN `categories_to_documents` AS c2d ON d.id = c2d.document_id
                                WHERE foreign_id = {$p_id}
                                AND category_id = 13
                                ORDER BY category_id, d.date DESC 
                                LIMIT 1";

            $result1 = $db->get_row($strQuery1);

            if ($result1) {
                $xml_array['Patient']['demographics']['profile_image'] = getUrl($result1->url);
            } else {
                $xml_array['Patient']['demographics']['profile_image'] = '';
            }
        } else {
            $xml_array['Patient']['patientdata']['status'] = 1;
            $xml_array['Patient']['patientdata']['reason'] = 'Error processing patient records';
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_array['status'] = -2;
    $xml_array['reason'] = 'Invalid Token';
}

echo ArrayToXML::toXml($xml_array, 'PatientList');
?>
