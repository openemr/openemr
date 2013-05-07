<?php
/**
 * api/visitsummery.php Get patient visit summary.
 *
 * API is allowed to get the patient most recent visit summary with complete 
 * details.
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

require_once('classes.php');


require_once("$srcdir/pdflibrary/config/lang/eng.php");
require_once("$srcdir/pdflibrary/tcpdf.php");


$xml_string = "";

$token = $_POST['token'];
$visit_id = $_POST['visit_id'];

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    if ($acl_allow) {
        $strQuery = "SELECT * , MAX( DATE ) , MAX( form_id ) AS form_id_max, COUNT( 0 ) AS count
                            FROM  `forms` 
                            WHERE encounter = ?
                            GROUP BY form_name";

        $result = sqlStatement($strQuery,array($visit_id));

        $ros_check = false;
        $rosc_check = false;
        $soap_check = false;
        $vitals_check = false;

        $total_ros = 0;
        $ros_data = array();

        $total_ros_checks = 0;
        $ros_data_checks = array();

        $total_vitals = 0;
        $vital_data = array();

        $total_soap = 0;
        $soap_data = array();

        if ($result->_numOfRows > 0) {

            while ($res = sqlFetchArray($result)) {
                switch ($res['form_name']) {
                    case 'Review Of Systems':
                        $strQuery1 = "SELECT * 
                                        FROM  `form_ros` 
                                        WHERE  `id` = ?";
                        $ros = sqlStatement($strQuery1,array($res['form_id_max']));
                        if ($ros->_numOfRows > 0) {
                            while ($ros_res = sqlFetchArray($ros)){
                                $xml_string .= "<ROS>\n";
                                $xml_string .= "<records>{$res['count']}</records>\n";
                                foreach ($ros_res as $fieldName => $fieldValue) {
                                    $rowValue = xmlsafestring($fieldValue);
                                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                                }
                                $xml_string .= "</ROS>\n";
                                $ros_data = $ros_res;
                            }
                        } else {
                            $xml_string .= "<ROS><records>0</records></ROS>\n";
                        }
                        $total_ros = $res['count'];
                        $ros_check = true;
                        break;
                    case 'Review of Systems Checks':
                        $strQuery2 = "SELECT * 
                                        FROM  `form_reviewofs` 
                                        WHERE  `id` = ?";
                        $rosc = sqlStatement($strQuery2,array($res['form_id_max']));

                        if ($rosc->_numOfRows > 0) {
                            while ($rosc_res = sqlFetchArray($rosc)) {
                                $xml_string .= "<ROSchecks>\n";
                                $xml_string .= "<records>{$res['count']}</records>\n";

                                foreach ($rosc_res as $fieldName => $fieldValue) {
                                    $rowValue = xmlsafestring($fieldValue);
                                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                                }
                                $xml_string .= "</ROSchecks>\n";
                                $ros_data_checks = $rosc_res;
                            }
                        } else {
                            $xml_string .= "<ROSchecks><records>0</records></ROSchecks>\n";
                        }
                        $rosc_check = true;
                        $total_ros_checks = $res['count'];
                        break;
                    case 'SOAP':
                        $strQuery3 = "SELECT * 
                                        FROM  `form_soap` 
                                        WHERE  `id` = ?";
                        $soap = sqlStatement($strQuery3,array($res['form_id_max']));
                        if ($soap->_numOfRows > 0) {
                            while ($soap_res = sqlFetchArray($soap)) {
                                $xml_string .= "<SOAP>\n";
                                $xml_string .= "<records>{$res['count']}</records>\n";

                                foreach ($soap_res as $fieldName => $fieldValue) {
                                    $rowValue = xmlsafestring($fieldValue);
                                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                                }
                                $xml_string .= "</SOAP>\n";
                                $soap_data = $soap_res;
                            }
                        } else {
                            $xml_string .= "<SOAP><records>0</records></SOAP>\n";
                        }
                        $total_soap = $res['count'];
                        $soap_check = true;
                        break;
                    case 'Vitals':
                        $strQuery4 = "SELECT * 
                                        FROM  `form_vitals` 
                                        WHERE  `id` = ?";
                        $vitals = sqlStatement($strQuery4,array($res['form_id_max']));
                        if ($vitals->_numOfRows > 0) {
                            while ($vitals_res = sqlFetchArray($vitals)) {
                                $xml_string .= "<Vitals>\n";
                                $xml_string .= "<records>{$res['count']}</records>\n";

                                foreach ($vitals_res as $fieldName => $fieldValue) {
                                    $rowValue = xmlsafestring($fieldValue);
                                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                                }
                                $xml_string .= "</Vitals>\n";

                                $vital_data = $vitals_res;
                            }
                            $total_vitals = $res['count'];
                        } else {
                            $xml_string .= "<Vitals><records>0</records></Vitals>\n";
                        }
                        $vitals_check = true;
                        break;
                }
            }
        }

        if (!$ros_check) {
            $xml_string .= "<ROS><records>0</records></ROS>\n";
        }

        if (!$rosc_check) {
            $xml_string .= "<ROSchecks><records>0</records></ROSchecks>\n";
        }

        if (!$soap_check) {
            $xml_string .= "<SOAP><records>0</records></SOAP>\n";
        }

        if (!$vitals_check) {
            $xml_string .= "<Vitals><records>0</records></Vitals>\n";
        }



        $count_query = "SELECT  `type` , COUNT( 0 ) AS count
                                                        FROM  `issue_encounter` AS ie
                                                        INNER JOIN  `lists` AS l ON ie.list_id = l.id
                                                        WHERE ie.encounter = ?
                                                        GROUP BY  `type`";
        $medication_count = 0;
        $allergy_count = 0;
        $medical_problem_count = 0;
        $dental_count = 0;
        $surgery_count = 0;

        $count_results = sqlStatement($count_query,array($visit_id));

        $xml_string .= "<Issues>";

        if ($count_results->_numOfRows > 0) {

                while ($count_result = sqlFetchArray($count_results)) {
                switch ($count_result['type']) {
                    case 'allergy':
                        $allergy_count = $count_result['count'];
                        break;
                    case 'dental':
                        $dental_count = $count_result['count'];
                        break;
                    case 'medical_problem':
                        $medical_problem_count = $count_result['count'];
                        break;
                    case 'medication':
                        $medication_count = $count_result['count'];
                        break;
                    case 'surgery':
                        $surgery_count = $count_result['count'];
                        break;
                }
            }

            $sql_visits = "SELECT type,title,begdate,diagnosis
                                                                FROM `issue_encounter` AS ie
                                                                INNER JOIN `lists` AS l ON ie.list_id = l.id
                                                                WHERE ie.encounter = ?";

            $list_result = sqlStatement($sql_visits,array($visit_id));

            if ($list_result->_numOfRows > 0) {
                while ($list_res = sqlFetchArray($list_result)){
                    $xml_string .= "<Issue>\n";
                    foreach ($list_res as $fieldName => $fieldValue) {
                        $rowValue = xmlsafestring($fieldValue);
                        $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                    }
                    $xml_string .= "</Issue>\n";
                }
            }
        }
        $xml_string .= "<allergy_count>{$allergy_count}</allergy_count>";
        $xml_string .= "<dental_count>{$dental_count}</dental_count>";
        $xml_string .= "<medical_problem_count>{$medical_problem_count}</medical_problem_count>";
        $xml_string .= "<medication_count>{$medication_count}</medication_count>";
        $xml_string .= "<surgery_count>{$surgery_count}</surgery_count>";
        $xml_string .= "</Issues>";

        if (!$count_results && !$result) {
            $xml_string1 .= "<PatientVisit>";
            $xml_string1 .= "<status>-1</status>";
            $xml_string1 .= "<reason>Rocord not found</reason>";
            $xml_string = $xml_string1 . $xml_string;
        } else {
            $xml_string1 .= "<PatientVisit>";
            $xml_string1 .= "<status>0</status>";
            $xml_string1 .= "<reason>Patient visit processing</reason>";
            $xml_string = $xml_string1 . $xml_string;
        }

    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<PatientVisit>";
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token.</reason>";
}

$html = visitSummeryHtml($total_vitals, $vital_data, $total_soap, $soap_data, $total_ros, $ros_data, $total_ros_checks, $ros_data_checks, $medical_problem_count, $medication_count, $allergy_count, $dental_count, $surgery_count, $visit_id);

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$encoded_pdf = createPdf($html, $pdf);
$xml_string .= "<html>" . base64_encode($html) . "</html>";
$xml_string .= "<pdf>" . $encoded_pdf . "</pdf>";

$xml_string .= "</PatientVisit>";
echo $xml_string;
?>