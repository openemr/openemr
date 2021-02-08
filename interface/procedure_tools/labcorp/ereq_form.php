<?php

/**
 * ereq_form.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once(__DIR__ . "/../../../library/patient.inc");
// 2d bar patched out until tcpdf gets upto PHP8
// require_once(__DIR__ . '/../libs/tcpdf/autoload/autoload.php');

use Mpdf\Mpdf;

$form_id = $_REQUEST['formid'];
//$_REQUEST['debug'] = 'yes';

function ereqForm($pid, $encounter, $form_id, $reqStr = null, $doDoc = true)
{

    $styleSheet =  <<<STYLES
img{border:none;outline:none;}.h3{margin:0px;padding:0px;font-size:18px;}h4{font-size:15px;}.span{display:block;text-align:center;}.cor-edi-main-wrap{margin:50px auto;max-width:850px;}.cor-edi-main-wrap table{width:100%;border-spacing:0px;font-size:11.5px;font-family:sans-serif;}.cor-edi-main-wrap table td{padding:1px 2px;vertical-align:top;}.cor-edi-main-table{border:0px;border-left:1px solid #000;border-top:1px solid #000;font-size:11.5px;}table.dotted-table{border:0px;border-left:1px dotted #000;border-top:1px dotted #000;font-size:9.5px;}.cor-edi-main-table tbody tr td, .cor-edi-main-table tr td{border:0px;border-right:1px solid #000;border-bottom:1px solid #000;}table.dotted-table tbody tr td, table.dotted-table tr td{border:0px;border-right:1px dotted #000;border-bottom:1px dotted #000;}.cor-edi-main-table tbody tr td table tr td, .cor-edi-main-table tr td table.no-border tr td{border:0px;border-right:0px;border-bottom:0px;padding:1px;padding-top:0px;}table.with-border tbody tr td, .cor-edi-main-table tbody tr td table.with-border tr td, .cor-edi-main-table tr td{border:0px;border-right:1px solid #000;border-bottom:1px solid #000;}table.dotted-table tbody tr td, table.dotted-table tbody tr td table tr td, table.dotted-table tr td{font-size:9.5px;}.no-border{border:0px;}.cor-edi-main-table.no-border{border:0px;}.cor-edi-main-table tbody tr td.no-border{border:0px;}.cor-edi-main-table tbody tr td table.no-border tr td{border:0px;}div.sig-underline{padding-top:75px;border-bottom:1px solid #000;width:250px;}.width50{width:50%;}.width25{width:25%;}.width30{width:30%;}.width75{width:75%;}div.height75{height:75px;}.cor-edi-main-table tr td table tr td.imaging-label{width:175px;height:150px;border-top:1px dashed #000;border-left:1px dashed #000;border-right:1px dashed #000;border-bottom:1px dashed #000;padding:10px;color:#303030;text-align:center;font-weight:bold;}.cor-edi-main-table tbody tr td table tr td.text-r{text-align:right;}.cor-edi-main-table tr td table tr td.bar-code-label {border:0px;outline:none;width:4.0in;height:1.0in;}
STYLES;
    /* To use barcode you need to install TCPDF and comment appropriate lines
    $barcodeobj = new TCPDF2DBarcode($reqStr, 'PDF417,4,4');
    $lc_2d = $barcodeobj->getBarcodePngData(4, 4, array(0,0,0));
    $lc_2d = "data:image/png;base64," . base64_encode($lc_2d);*/
    try {
        $mpdf = new mPDF();
        $pdfContent = '<!DOCTYPE html>';
        $pdfContent .= '<html lang="en">';
        $pdfContent .= '<head></head>';
        $pdfContent .= '<body>';
        $pdfContent .= '<div class="cor-edi-main-wrap">';
        $pdfContent .= '<table class="cor-edi-main-table no-border" style="margin-top:0px;">';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="border:0px;width:35%;padding-left:20px;"><table style="width:190px;"><tr><td class="imaging-label">Imaging Label</td></tr></table></td>';
        $pdfContent .= '<td style="border:0px;text-align:center;width:25%;"><h3 class="h3"><div class="span">LabCorp</div><div class="span">EREQ</div><div class="span">OpenEMR</div></h3></td>';
        $pdfContent .= "<td style='border:0px;width:40%;margin:0px 0px;padding:0px 0px'><table><tr>";
        $pdfContent .= "<td class='bar-code-label'>Barcode Placeholder</td>";
        //$pdfContent .= "<td class='bar-code-label'><img style='width:3.75in;height:0.95in;border:0px;outline:none;' src='$lc_2d'></td>";
        $pdfContent .= '</tr></table></td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '<table class="cor-edi-main-table no-border" style="margin-top:10px;">';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="border:0px;text-align:left;padding-left:20px;"><h4>LabCorp®</h4></td>';
        $pdfContent .= '<td style="border:0px;text-align:center;"><h4>OpenEMR</h4></td>';
        $pdfContent .= '<td style="border:0px;text-align:right;padding-right:20px;"><b>Page 1 of 2</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $procedure = sqlQuery("SELECT * FROM procedure_order WHERE procedure_order_id=?", [$form_id]);
        $account_facility = $procedure['account_facility'];
        $facility = sqlQuery("SELECT * FROM facility f WHERE f.id=?", [$account_facility]);
        $location = sqlQueryNoLog("SELECT f.facility_code FROM users as u " .
            "INNER JOIN facility as f ON u.facility_id = f.id WHERE u.id = ?", array($procedure['provider_id']));

        $account = $facility['facility_code'];
        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:45%;border-right: 0px;">';
        $pdfContent .= "<h4>Account #: $account</h4><br/>";
        $pdfContent .= '<h4>Req/Control #: ' . $form_id . '</h4>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td style="width:55%;">';
        $pdfContent .= '<table>';

        $collection_date = date("m/d/Y", strtotime($procedure['date_collected']));
        $collection_time = date("H:i", strtotime($procedure['date_collected']));
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="text-align:right;width:35%;">Collection Date:</td>';
        $pdfContent .= '<td style="width:65%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="text-align:right;">Collection Time:</td>';
        $pdfContent .= '<td>' . $collection_time . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="text-align:right;">Courtesy Copy:</td>';
        $pdfContent .= '<td>Acct# &nbsp; Attn: &nbsp;<br/>Fax# &nbsp; Attn: &nbsp;<br/>Patient </td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';

        $pdfContent .= '<td class="width50" style="padding-left:8px;"><b>Client / Ordering Site Information:</b></td>';
        $pdfContent .= '<td class="width50" style="padding-left:8px;">Physician Information:</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="width:36%;text-align:right;">Account Name:</td><td style="width:64%;padding-left:8px;">' . $facility['name'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Address 1:</td><td style="padding-left:8px;">' . $facility['street'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Address 2:</td><td style="padding-left:8px;">&nbsp;</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">City, State Zip:</td><td style="padding-left:8px;">' . $facility['city'] . ', ' . $facility['state'] . ' ' . $facility['postal_code'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Phone:</td><td style="padding-left:8px;">' . $facility['phone'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $provider = sqlQuery("SELECT concat(lname,', ', fname) as name, npi, upin, id FROM users WHERE id=?", [$procedure['provider_id']]);
        $pdfContent .= '<tr><td style="text-align:right;width:36%;">Ordering Physician:</td><td style="width:64%;padding-left:8px;">' . $provider['name'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Physician Degree:</td><td style="padding-left:8px;">&nbsp;</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">NPI:</td><td style="padding-left:8px;">' . $provider['npi'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">UPIN:</td><td style="padding-left:8px;">' . $provider['upin'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Physician ID:</td><td style="padding-left:8px;">' . $provider['id'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $patient = sqlQuery("SELECT * FROM patient_data WHERE pid=?", [$pid]);
        $pdfContent .= '<td style="padding-left:8px;" colspan="2"><b>Patient Information:</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50">';
        $page = getPatientAgeYMD($patient['DOB']);
        $ageformat = explode(' ', $page['ageinYMD']);
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="width:36%;text-align:right;">Patient Name:</td><td style="width:64%;padding-left:8px;">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Gender:</td><td style="padding-left:8px;">' . $patient['sex'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Date of Birth:</td><td style="padding-left:8px;">' . date("m/d/Y", strtotime($patient['DOB'])) . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Age:</td><td style="padding-left:8px;">' . str_replace('y', '', $ageformat[0]) . '/' . str_replace('m', '', $ageformat[1]) . '/' . str_replace('d', '', $ageformat[2]) . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Patient Address:</td><td style="padding-left:8px;">' . $patient['street'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">City, State Zip:</td><td style="padding-left:8px;">' . $patient['city'] . ', ' . $patient['state'] . ' ' . $patient['postal_code'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="text-align:right;width:36%;">Patient SSN:</td><td style="width:64%;padding-left:8px;">' . $patient['ss'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Patient ID:</td><td style="padding-left:8px;">' . $patient['pubpid'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Phone:</td><td style="padding-left:8px;">' . $patient['phone_home'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">&nbsp;</td><td style="padding-left:8px;">&nbsp;</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Alt Control #:</td><td style="padding-left:8px;">&nbsp;</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Alt Patient ID:</td><td style="padding-left:8px;">' . $patient['pid'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $proc_sql = sqlStatement("SELECT procedure_code, procedure_name, diagnoses, procedure_order_seq FROM procedure_order_code WHERE procedure_order_id=?", [$form_id]);
        $proc_order = sqlNumRows($proc_sql);
        $procedure_right = floor($proc_order / 2);
        $procedure_left = $proc_order - $procedure_right;
        $all_procedures = array();
        $all_diagnoses = array();
        if (!empty($procedure['order_diagnosis'])) {
            $all_diagnoses[] = $procedure['order_diagnosis'];
        }
        while ($row = sqlFetchArray($proc_sql)) {
            $all_procedures[] = $row;
        }
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width25" style="width:17%;padding-left:8px;"><b>ORDER CODE</b></td>';
        $pdfContent .= '<td class="width25" style="width:33%;padding-left:8px;"><b>TESTS ORDERED (TOTAL: ' . $proc_order . ')</b></td>';
        $pdfContent .= '<td class="width25" style="width:17%;padding-left:8px;"><b>ORDER CODE</b></td>';
        $pdfContent .= '<td class="width25" style="width:33%;padding-left:8px;"><b>TESTS ORDERED</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:17%;padding-left:8px;height:100px;border-right:0px;" class="width50">';
        $pdfContent .= '<table>';
        for ($i = 0; $i < $procedure_left; $i++) {
            $pdfContent .= '<tr>';
            $pdfContent .= '<td class="width50">' . $all_procedures[$i]['procedure_code'] . '</td>';
            $temp_diag = explode(";", $all_procedures[$i]['diagnoses']);
            $all_diagnoses[] = $temp_diag;
            $pdfContent .= '</tr>';
        }
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td style="width:17%;padding-left:8px;height:100px;" class="width50">';
        $pdfContent .= '<table>';
        for ($i = 0; $i < $procedure_left; $i++) {
            $pdfContent .= '<tr>';
            $pdfContent .= '<td class="width50">' . $all_procedures[$i]['procedure_name'] . '</td>';
            $pdfContent .= '</tr>';
        }
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td style="width:17%;padding-left:8px;height:100px;border-right:0px;" class="width50">';
        $pdfContent .= '<table>';
        for ($i = $procedure_left; $i < $proc_order; $i++) {
            $pdfContent .= '<tr>';
            $pdfContent .= '<td class="width50">' . $all_procedures[$i]['procedure_code'] . '</td>';
            $temp_diag = explode(";", $all_procedures[$i]['diagnoses']);
            $all_diagnoses[] = $temp_diag;
            $pdfContent .= '</tr>';
        }
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td style="width:17%;padding-left:8px;height:100px;" class="width50">';
        $pdfContent .= '<table>';
        for ($i = $procedure_left; $i < $proc_order; $i++) {
            $pdfContent .= '<tr>';
            $pdfContent .= '<td class="width50">' . $all_procedures[$i]['procedure_name'] . '</td>';
            $pdfContent .= '</tr>';
        }
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $vitals = sqlQuery("SELECT height, weight FROM form_vitals v join forms f on f.form_id=v.id WHERE f.pid=? and f.encounter=? ORDER BY v.date DESC LIMIT 1", [$pid, $encounter]);
        $i = 0;
        $aoe_pap = '';
        $allspecs = [];
        $allbltype = [];
        $allblpurpose = [];
        while ($all_procedures[$i]) {
            $aoe_list = sqlStatement(
                "SELECT " .
                "a.question_code, a.answer, q.question_code , q.tips, q.question_text, q.procedure_code FROM procedure_answers AS a " .
                "LEFT JOIN procedure_questions AS q ON q.lab_id = ? " .
                "AND q.procedure_code = ? AND q.question_code = a.question_code " .
                "WHERE a.procedure_order_id = ? AND a.procedure_order_seq = ? " .
                "ORDER BY q.seq, a.answer_seq",
                array($procedure['lab_id'], $all_procedures[$i]['procedure_code'], $form_id, $all_procedures[$i]['procedure_order_seq'])
            );
            foreach ($aoe_list as $aoe_data) {
                if ($aoe_data['question_code']) {
                    if (stripos($all_procedures[$i]['procedure_name'], 'PAP') !== false) {
                        if ($aoe_data['answer']) {
                            $aoe_pap .= '<tr><td style="width:36%;text-align:right;">' . $aoe_data['question_text'] . ':</td><td style="width:64%;padding-left:8px;">' . $aoe_data['answer'] . '</td></tr>';
                            $pap_proc = 'AOE Test: ' . $all_procedures[$i]['procedure_code'];
                        }
                        continue;
                    }
                    if ($aoe_data['question_code'] == "SOURCE" && !empty($aoe_data['answer'])) {
                        $allspecs[] = $aoe_data['answer'];
                    }
                    if ($aoe_data['question_code'] == 'BLSRCE') {
                        $ans = "";
                        switch (trim($aoe_data['answer'])) {
                            case 'V':
                                $ans = "Venous";
                                break;
                            case 'F':
                                $ans = "FingerStick";
                                break;
                        }
                        if ($ans) {
                            $allbltype[] = $ans;
                        }
                    }
                    if ($aoe_data['question_code'] == 'BLPURP') {
                        $ans = "";
                        switch (trim($aoe_data['answer'])) {
                            case 'I':
                                $ans = "Initial";
                                break;
                            case 'R':
                                $ans = "Repeat";
                                break;
                            case 'F':
                                $ans = "Followup";
                                break;
                        }
                        if ($ans) {
                            $allblpurpose[] = $ans;
                        }
                    }
                    if ($aoe_data['question_code'] == 'TOTVOL') {
                        $vitals['urine_total_volume'] = $aoe_data['answer'];
                    }
                }
            }
            $i++;
        }
        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        if (!empty($pap_proc)) {
            $pdfContent .= "<td class='width50' style='padding-left:8px;'><b>$pap_proc</b></td>";
        } else {
            $pdfContent .= '<td class="width50" style="padding-left:8px;"><b>Clinical Information:</b></td>';
        }
        $pdfContent .= '<td class="width50" style="padding-left:8px;"><b>Additional Information:</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="min-height:50px;" class="width50">';
        $pdfContent .= '<table>';
        if ($pap_proc && $aoe_pap) {
            $pdfContent .= $aoe_pap;
        } else {
            $pdfContent .= '<tr><td style="width:36%;text-align:right;">' . $procedure['clinical_hx'] . '</td><td style="width:64%;padding-left:8px;">&nbsp;</td></tr>';
        }
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td style="min-height:50px;" class="width50">';
        $pdfContent .= '<table>';

        $fasting = $procedure['specimen_fasting'] === 'YES' ? "YES" : "NO";
        $pdfContent .= '<tr><td style="text-align:right;width:36%;">Fasting:</td><td style="width:64%;padding-left:8px;">' . $fasting . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Height (in):</td><td style="padding-left:8px;">' . $vitals['height'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Weight (lbs oz):</td><td style="padding-left:8px;">' . $vitals['weight'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Urine Total Volume (mls):</td><td style="padding-left:8px;">' . $vitals['urine_total_volume'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td colspan="2"><b>Micro/Histo Information: </b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        if (!empty($allspecs)) {
            $specs = implode(',', $allspecs);
        }
        $pdfContent .= '<tr><td style="text-align:right;width:36%;">Source:</td><td style="width:64%;padding-left:8px;">' . $specs . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50" style="padding-left:8px;"></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $race = array("declne_to_specfy" => 9, "amer_ind_or_alaska_native" => 3, "Asian" => 4, "black_or_afri_amer" => 2, "native_hawai_or_pac_island" => 5, "white" => 1);
        $hispanic = empty($patient['ethnicity']) ? "9" : null;
        $hispanic = ($patient['ethnicity'] === "hisp_or_latin" && empty($hispanic)) ? 1 : 2;
        $pdfContent .= '<td style="padding-left:8px;" colspan="2"><b>Blood Lead Information: </b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="width:36%;text-align:right;">Race:</td><td style="width:64%;padding-left:8px;">' . $race[$patient['race']] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Hispanic:</td><td style="padding-left:8px;">' . $hispanic . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Blood Lead Type:</td><td style="padding-left:8px;">' . implode(',', $allbltype) . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="width:36%;text-align:right;">Blood Lead Purpose:</td><td style="width:64%;padding-left:8px;">' . implode(',', $allblpurpose) . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Blood Lead County:</td><td style="padding-left:8px;">' . $patient['county'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:30px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="padding-left:8px;" colspan="8"><b>Diagnosis Codes:</b>List all applicable Diagnosis codes. Must be at Highest Level Specificity.</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[0]) . '</td>';
        $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[1]) . '</td>';
        $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[2]) . '</td>';
        $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[3]) . '</td>';
        $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[4]) . '</td>';
        $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[5]) . '</td>';
        $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[6]) . '</td>';
        $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[7]) . '</td>';
        $pdfContent .= '</tr>';
        if (!empty($all_diagnoses[8])) {
            $pdfContent .= '<tr>';
            $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[8]) . '</td>';
            $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[9]) . '</td>';
            $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[10]) . '</td>';
            $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[11]) . '</td>';
            $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[12]) . '</td>';
            $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[13]) . '</td>';
            $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[14]) . '</td>';
            $pdfContent .= '<td style="width:12.5%;" >' . str_replace('ICD10:', '', $all_diagnoses[15]) . '</td>';
            $pdfContent .= '</tr>';
        }

        $primary = sqlQuery("SELECT i.*,ic.name,ic.id FROM insurance_data i join insurance_companies ic ON i.provider=ic.id WHERE i.pid=? and i.type='primary' ORDER BY i.date DESC LIMIT 1", [$pid]);
        $billtype = "Unknown";
        switch (trim($procedure['billing_type'])) {
            case 'T':
                $billtype = "Third Party";
                break;
            case 'P':
                $billtype = "Patient";
                break;
            case 'C':
                $billtype = "Client";
                break;
        }
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="border-right:0px;"><b>Bill Type:</b></td>';
        $pdfContent .= '<td colspan="3" style="padding-left:8px;">' . $billtype . '</td>';
        $pdfContent .= '<td style="border-right:0px;"><b>LCA Ins Code:</b></td>';
        $pdfContent .= '<td colspan="3" style="padding-left:8px;">&nbsp;</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';


        $pdfContent .= '<table class="cor-edi-main-table dotted-table" style="margin-bottom:10px;page-break-after:always;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width25" style="width:25%;">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td colspan="2">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $patient['dob'] . '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>' . $account . '</td>';
        $pdfContent .= '<td>' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width25" style="width:25%;">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td colspan="2">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $patient['dob'] . '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>' . $account . '</td>';
        $pdfContent .= '<td>' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width25" style="width:25%;">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td colspan="2">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $patient['dob'] . '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>' . $account . '</td>';
        $pdfContent .= '<td>' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width25" style="width:25%;">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td colspan="2">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $patient['dob'] . '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>' . $account . '</td>';
        $pdfContent .= '<td>' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width25" style="width:25%;">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td colspan="2">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $patient['dob'] . '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>' . $account . '</td>';
        $pdfContent .= '<td>' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width25" style="width:25%;">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td colspan="2">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $patient['dob'] . '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>' . $account . '</td>';
        $pdfContent .= '<td>' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width25" style="width:25%;">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td colspan="2">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $patient['dob'] . '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>' . $account . '</td>';
        $pdfContent .= '<td>' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width25" style="width:25%;">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td colspan="2">' . $patient['lname'] . ', ' . $patient['fname'] . '</td></tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $patient['dob'] . '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>' . $account . '</td>';
        $pdfContent .= '<td>' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table no-border" style="margin-bottom:30px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="text-align:right;padding-right:20px;" class="no-border"><b>Page 2 of 2</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table no-border" style="margin-bottom:10px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;" class="no-border">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:36%;text-align:right;">Account Number:</td>';
        $pdfContent .= '<td style="width:64%;padding-left:8px;">' . $account . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:36%;text-align:right;">Req/Control#</td>';
        $pdfContent .= '<td style="width:64%;padding-left:8px;">' . $form_id . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:36%;text-align:right;">Collection Date:</td>';
        $pdfContent .= '<td style="width:64%;padding-left:8px;">' . $collection_date . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50" style="width:50%;" class="no-border">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:36%;text-align:right;">Patient Name:</td>';
        $pdfContent .= '<td style="width:64%;padding-left:8px;">' . $patient['lname'] . ', ' . $patient['fname'] . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:36%;text-align:right;">Patient ID:</td>';
        $pdfContent .= '<td style="width:64%;padding-left:8px;">' . $patient['pubpid'] . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="width:36%;text-align:right;">Alt Pat ID:</td>';
        $pdfContent .= '<td style="width:64%;padding-left:8px;">' . $patient['pid'] . '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="padding-left:8px;" ><b>Responsible Party / Guarantor Information:</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td>';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="width:24%;text-align:right;">RP Name:</td><td style="width:76%;padding-left:8px;">' . $primary['subscriber_lname'] . ', ' . $primary['subscriber_fname'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">RP Address:</td><td style="padding-left:8px;">' . $primary['subscriber_street'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">RP City, State Zip:</td><td style="padding-left:8px;">' . $primary['subscriber_city'] . ', ' . $primary['subscriber_state'] . ' ' . $primary['subscriber_postal_code'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">RP Phone:</td><td style="padding-left:8px;">' . $primary['subscriber_phone'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">RP Relation to Pt:</td><td style="padding-left:8px;">' . $primary['subscriber_relationship'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="padding-left:8px;width:33%;" ><b>ABN: &nbsp; </b> &nbsp;</td>';
        $pdfContent .= '<td style="padding-left:8px;width:33%;" ><b>Worker’s Comp: &nbsp; </b> &nbsp;</td>';
        $pdfContent .= '<td style="padding-left:8px;width:33%;" ><b>Date of Injury: &nbsp; </b> &nbsp;</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="cor-edi-main-table" style="margin-bottom:6px;">';
        $pdfContent .= '<tbody>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="padding-left:8px;" colspan="2"><b>Insurance Information:</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="padding-left:8px;"><b>Primary Insurance: </b></td>';
        $pdfContent .= '<td style="padding-left:8px;"><b>Secondary Insurance:</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="width:36%;text-align:right;">LCA Ins Code:</td><td style="width:64%;padding-left:8px;">&nbsp;</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Ins Co Name:</td><td style="padding-left:8px;">' . $primary['name'] . '</td></tr>';
        $paddress = sqlQuery("SELECT * FROM addresses WHERE foreign_id=?", [$primary['id']]);
        $pdfContent .= '<tr><td style="text-align:right;">Ins Address 1:</td><td style="padding-left:8px;">' . $paddress['line1'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Ins Address 2:</td><td style="padding-left:8px;">' . $paddress['line2'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Ins City, State Zip:</td><td style="padding-left:8px;">' . $paddress['city'] . ', ' . $paddress['state'] . ' ' . $paddress['zip'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Policy Number:</td><td style="padding-left:8px;">' . $primary['policy_number'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Group #:</td><td style="padding-left:8px;">' . $primary['group_number'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Emp/Group Name:</td><td style="padding-left:8px;">&nbsp;</td></tr>';
        if (!empty($primary['name'])) {
            $pprovider_id = $provider['id'];
        }
        $pdfContent .= '<tr><td style="text-align:right;">Provider #:</td><td style="padding-left:8px;">' . $pprovider_id . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $secondary = sqlQuery("SELECT i.*, ic.name, ic.id FROM insurance_data i join insurance_companies ic ON i.provider=ic.id WHERE i.pid=? and i.type='secondary' ORDER BY i.date DESC LIMIT 1", [$pid]);
        $pdfContent .= '<tr><td style="width:36%;text-align:right;">LCA Ins Code:</td><td style="width:64%;padding-left:8px;">&nbsp;</td></tr>';
        $saddress = sqlQuery("SELECT * FROM addresses WHERE foreign_id=?", [$secondary['id']]);
        $pdfContent .= '<tr><td style="text-align:right;">Ins Co Name:</td><td style="padding-left:8px;">' . $secondary['name'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Ins Address 1:</td><td style="padding-left:8px;">' . $saddress['line1'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Ins Address 2:</td><td style="padding-left:8px;">' . $saddress['line2'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Ins City, State Zip:</td><td style="padding-left:8px;">' . $saddress['city'] . ', ' . $saddress['state'] . ' ' . $saddress['zip'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Policy Number:</td><td style="padding-left:8px;">' . $secondary['policy_number'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Group #:</td><td style="padding-left:8px;">' . $secondary['group_number'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Emp/Group Name:</td><td style="padding-left:8px;">&nbsp;</td></tr>';
        if (!empty($secondary['name'])) {
            $sprovider_id = $provider['id'];
        }
        $pdfContent .= '<tr><td style="text-align:right;">Provider #:</td><td style="padding-left:8px;">' . $sprovider_id . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td style="padding-left:8px;"><b>Primary Policy Holder / Insured:</b></td>';
        $pdfContent .= '<td style="padding-left:8px;"><b>Secondary Policy Holder / Insured:</b></td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="width:36%;text-align:right;">Insured Name:</td><td style="width:64%;padding-left:8px;">' . $primary['subscriber_lname'] . ', ' . $primary['subscriber_fname'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Insured Address:</td><td style="padding-left:8px;">' . $primary['subscriber_street'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">&nbsp;</td><td style="padding-left:8px;">' . $primary['subscriber_city'] . ', ' . $primary['subscriber_state'] . ' ' . $primary['subscriber_postal_code'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Insured Relation to Pt:</td><td style="padding-left:8px;">' . $primary['subscriber_relationship'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr><td style="width:36%;text-align:right;">Insured Name:</td><td style="width:64%;padding-left:8px;">' . $secondary['subscriber_lname'] . ', ' . $secondary['subscriber_fname'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Insured Address:</td><td style="padding-left:8px;">' . $secondary['subscriber_street'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">&nbsp;</td><td style="padding-left:8px;">' . $secondary['subscriber_city'] . ', ' . $secondary['subscriber_state'] . ' ' . $secondary['subscriber_postal_code'] . '</td></tr>';
        $pdfContent .= '<tr><td style="text-align:right;">Insured Relation to Pt:</td><td style="padding-left:8px;">' . $secondary['subscriber_relationship'] . '</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';

        $pdfContent .= '<table class="">';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td colspan="2" class="no-border" style="padding-top:30px;">';
        $pdfContent .= '<div>Authorization - Please sign and Date</div>';
        $pdfContent .= '<div>I hereby authorize the release of medical information related to the services described hereon and </div>';
        $pdfContent .= '<div>authorize payment directly to Laboratory Corporation of America. I agree to assume responsibility for</div>';
        $pdfContent .= '<div>payment of charges for laboratory services that are not covered by my healthcare insurer.</div>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td colspan="2" class="no-border">';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table style="width:250px;">';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;height: 40px;padding-top:40px;border-bottom:1px solid #000;">&nbsp;</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr><td class="width50" style="width:50%;">Patient Signature</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table style="width:150px;">';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;height: 40px;padding-top:40px;border-bottom:1px solid #000;">&nbsp;</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr><td class="width50" style="width:50%;">Date</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '<table>';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table style="width:250px;">';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;height: 40px;padding-top:40px;border-bottom:1px solid #000;">&nbsp;</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr><td class="width50" style="width:50%;">Patient Signature</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '<td class="width50">';
        $pdfContent .= '<table style="width:150px;">';
        $pdfContent .= '<tr>';
        $pdfContent .= '<td class="width50" style="width:50%;height: 40px;padding-top:40px;border-bottom:1px solid #000;">&nbsp;</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '<tr><td class="width50" style="width:50%;">Date</td></tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</table>';
        $pdfContent .= '</td>';
        $pdfContent .= '</tr>';
        $pdfContent .= '</tbody>';
        $pdfContent .= '</table>';
        $pdfContent .= '</div>';
        $pdfContent .= '</body>';
        $pdfContent .= '</html>';
        $mpdf->WriteHTML($styleSheet, 1);
        $mpdf->WriteHTML($pdfContent, 2);

        $unique = date('y-m-d-His', time());
        $filename = "ereq_" . $unique . "_order_" . $form_id . ".pdf";

//-----------patch in exclude dodoc
        if ($_REQUEST['debug'] || !$doDoc) {
            $mpdf->Output($filename, "I");
            return;
        }
        $mpdfData = $mpdf->Output($filename, "S");

// register the new document
        $category = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", array("LabCorp"));
        if (!$category['id']) {
            $category = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", array('Lab Report'));
        }
        $DOCUMENT_CATEGORY = $category['id'];

        $d = new Document();
        $good = $d->createDocument($pid, $DOCUMENT_CATEGORY, $filename, "application/pdf", $mpdfData);
        if (!empty($good)) {
            echo $good;
            exit;
        }
        $unique = date('y-m-d-H:i:s', time());
        $documentationOf = "$unique";
        sqlStatement(
            "UPDATE documents SET documentationOf = ?, list_id = ? WHERE id = ?",
            array($documentationOf, $form_id, $d->id)
        );
    } catch (Exception $e) {
        echo "Message: " . $e->getMessage();
        echo "";
        echo "getCode(): " . $e->getCode();
        echo "";
        echo "__toString(): " . $e->__toString();
    }
}
if ($_REQUEST['debug']) {
    ereqForm($pid, $encounter, $form_id);
}
