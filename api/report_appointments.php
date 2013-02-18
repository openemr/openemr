<?php
/**
 * api/report_appointments.php Appointments report.
 *
 * API is allowed to get patient appointments report.
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

require_once ('includes/pdflibrary/config/lang/eng.php');
require_once ('includes/pdflibrary/tcpdf.php');
require_once 'classes.php';


$xml_string = "";
$xml_string = "<list>";


$token = $_POST['token'];
$facility = $_POST['facility'];
$provider = $_POST['provider'];
$show_available_times = $_POST['show_available_times'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];


if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    if ($acl_allow) {


        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor("Haroon");
        $pdf->SetTitle("My Report");
        $pdf->SetSubject("My Report");
//        $pdf->SetKeywords("TCPDF, PDF, example, test, guide");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $appointments = fetchAppointments($from_date, $to_date, $patientId = '', $provider, $facility);

        if ($show_available_times) {
            $availableSlots = getAvailableSlots($from_date, $to_date, $provider, $facility);
            $appointments = array_merge($appointments, $availableSlots);
        }
        $form_orderby = 'date';
        $appointments = sortAppointments($appointments, $form_orderby);
        //$pid_list = array();  // Initialize list of PIDs for Superbill option

        $single_record_header = "";
        $single_record = '';
        $html = "<html>
            <head>
                
            </head>
            <body>
            <div id='report_results' style=\" margin-top:10px;\">
            <h3>Report - Appointments {$from_date} to {$to_date}</h3>
            <table style=\"border-top: 1px solid black;
                                border-bottom: 1px solid black;
                                border-left: 1px solid black;
                                border-right: 1px solid black;
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 1px;\">
                <thead style=\"padding: 5px;
                                    display: table-header-group;
                                    background-color: #ddd;
                                        text-align:left;
                                        font-weight: bold;
                                        font-size: 0.7em;\">
                <tr>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Provider</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Date</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Time</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Patient</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">ID</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Home</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Cell</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Type</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Comments</th>
                </tr>
                </thead>
            ";
        $single_record_header .= $html;

//    echo var_dump($appointments);exit;

        if ($appointments) {
            //newEvent($event = 'soap-record-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);	
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>Appointments Processed successfully</reason>";

            for ($i = 0; $i < count($appointments); $i++) {
                $xml_string .= "<appointment>\n";

                foreach ($appointments[$i] as $fieldName => $fieldValue) {
                    $rowValue = xmlsafestring($fieldValue);
                    $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
                }
                $provider_name = $appointments[$i]['ufname'] == $last_provider ? '' : $appointments[$i]['ufname'] . " " . $appointments[$i]['umname'] . " " . $appointments[$i]['ulname'];
                $single_record = "
                <tr>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$provider_name}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$appointments[$i]['pc_eventDate']} </td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$appointments[$i]['pc_startTime']}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$appointments[$i]['fname']} {$appointments[$i]['mname']} {$appointments[$i]['lname']}</td>
                            
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$appointments[$i]['pubpid']}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$appointments[$i]['phone_home']}</td>    
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$appointments[$i]['phone_cell']}</td>

                    
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$appointments[$i]['pc_catname']}</td>
                    <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$appointments[$i]['pc_hometext']}</td>
                </tr>
                ";

                $last_provider = $appointments[$i]['ufname'];
                $html .= $single_record;

                $complete_single_record = $single_record_header . $single_record . "<table></div></body></html>";

                $xml_string .= "<appointment_html>" . base64_encode($complete_single_record) . "</appointment_html>";

//            $pdf1 = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//            $pdf1->SetCreator(PDF_CREATOR);
//            $pdf1->SetAuthor("Haroon");
//            $pdf1->SetTitle("My Report");
//            $pdf1->SetSubject("My Report");
////        $pdf->SetKeywords("TCPDF, PDF, example, test, guide");
//            $pdf1->setPrintHeader(false);
//            $pdf1->setPrintFooter(false);
//            $pdf1->AliasNbPages();
//            $pdf1->AddPage();
//            $pdf1->writeHTML($complete_single_record, true, false, true, false, '');
//            $pdf_base64 = $pdf1->Output("", "E");
//            $temp = explode('filename=""', $pdf_base64);
//            
//            $xml_string .= "<appointment_pdf>" . $temp[1] . "</appointment_pdf>";
                $xml_string .= "</appointment>\n";

//            echo $single_record_header .$single_record. "<table></div></body></html>";
            }

            $html .= "
                    <table>
                    </div>
                </body>
            </html>";


            $pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
//$pdf->lastPage();
//header("Content-Type: application/pdf");
            $pdf_base64 = $pdf->Output("", "E");
//        echo $pdf_base64;
            $temp = explode('filename=""', $pdf_base64);
//        echo base64_decode($temp[1]);
//        echo $html;
//        exit;
            $xml_string .= "<html_report>" . base64_encode($html) . "</html_report>";
            $xml_string .= "<pdf_report>" . $temp[1] . "</pdf_report>";
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

$xml_string .= "</list>";
echo $xml_string;
?>