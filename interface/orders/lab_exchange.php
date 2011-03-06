<?php
// Copyright (C) 2010 OpenEMR Support LLC   
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/lab_exchange_api.php");
require_once("lab_exchange_tools.php");
require_once("../main/messages/lab_results_messages.php");
include_once("$srcdir/formdata.inc.php");

$lab_query_report = "";
$lab_patient_success = array();
$lab_patient_errors = array();
$lab_provider_errors = array();

// Create the REST client
$client = new LabExchangeClient($GLOBALS['lab_exchange_siteid'],$GLOBALS['lab_exchange_token'],$GLOBALS['lab_exchange_endpoint']);

// Make the request
$response = $client->sendRequest("results", "GET");
$lab_query_report .= xl("Lab Query Status") . "<hr>";

// Check response for success or error
if ($response->IsError)
    $lab_query_report .= xl("Error retrieving results from Lab Exchange Network") . ": {$response->ErrorMessage} <br><br>";
else {
    $lab_query_report .= xl("Success retrieving results from Lab Exchange Network") . " <br><br>";

    $resultSet = $response->ResponseXml;

    foreach ($resultSet->LabResult as $labResult) {
        $id = $labResult['id'];
        $patient = $labResult->Patient;
        $lastName = $patient->LastName;
        $firstName = $patient->FirstName;
        $middleName = $patient->MiddleName;
        $dob = $patient->DOB;
        $gender = $patient->Gender;
        $externalId = $patient->ExternalId;
        $ssn = $patient->SSN;
        $address = $patient->Address;
        $city = $patient->City;
        $state = $patient->State;
        $zip = $patient->Zip;
        $homePhone = $patient->HomePhone;

        // Match patient
        $patient_id = lab_exchange_match_patient($externalId, $firstName, $middleName, $lastName, $dob, $gender, $ssn, $address);


        if (!$patient_id) {
            $lab_patient_errors[] = $labResult;
            continue;
        }
        else
        {
            $lab_patient_success[] = $labResult;
        }

        // process facility information
        $str_facilityId = processFacility($labResult->Facility);

        echo "facility_ID = $str_facilityId<br>";

        // Loop through all the Result Report
        foreach ($labResult->ResultReport as $resultReport) {
            // Access report fields
            // ResultReport maps to procedure_order and procedure_report tables
            $observationCode = $resultReport->ObservationCode; // => procedure_order.procedure_type_id
            $observationText = $resultReport->ObservationText; // => This text should be the same as procedure_type.name
            $observationDate = $resultReport->ObservationDateTime; // => procedure_report.date_collected
            //$observationStatus = $resultReport->ObservationStatus; // => procedure_report.report_status
            $observationStatus = $resultReport->OrderResultStatus; // => procedure_report.report_status
            $controlId = $resultReport->ForeignAccId;             // This is the CONTROL ID that is sent back
            $orderingProviderId = $resultReport->OrderingProviderId; // =>procedure_order.provider_id  But the ID here is NOT the same ID as OpenEMR. You have to match it correctly
            $orderingProviderLastName = $resultReport->OrderingProviderLastName;  // Use this to match the provider ID
            $orderingProviderFirstName = $resultReport->OrderingProviderFirstName; // Use this to match the provider ID


            // Match provider
            $user_id = lab_exchange_match_provider($orderingProviderId);
            
            if(!$user_id)
            {
                $lab_provider_errors[] = $resultReport;
            }

            $date = '';
            $date = substr($observationDate, 0, 8);

            $check_type = sqlQuery("SELECT COUNT(*) AS count FROM procedure_type WHERE procedure_type_id = '" . add_escape_custom($observationCode) . "'");

            if ($check_type['count'] <= 0) {
                $sql_type_data =
                        "procedure_type_id = '" . add_escape_custom($observationCode) . "', " .
                        "name = '" . add_escape_custom($observationText) . "', " .
                        "procedure_type = 'res'";

                $type_id = sqlInsert("INSERT INTO procedure_type SET $sql_type_data");
            }

            $check_order = sqlQuery("SELECT COUNT(*) AS count, procedure_order_id, provider_id, patient_id FROM procedure_order WHERE control_id = '" . add_escape_custom($controlId) . "' AND procedure_type_id = '" . add_escape_custom($observationCode) . "'");

            if ($check_order['count'] <= 0) {
                $sql_order_data =
                        "procedure_type_id = '" . add_escape_custom($observationCode) . "', " .
                        "provider_id = '" . add_escape_custom($user_id) . "', " .
                        "patient_id = '" . add_escape_custom($patient_id) . "', " .
                        "date_collected = DATE_FORMAT('" . add_escape_custom($observationDate . '00') . "', '%Y%m%d%H%i%s'), " .
                        "date_ordered = DATE_FORMAT('" . add_escape_custom($date) . "', '%Y%m%d'), " .
                        "order_priority = 'normal', " .
                        "order_status = 'complete', " .
                        "control_id = '" . add_escape_custom($controlId) . "'";

                $order_id = sqlInsert("INSERT INTO procedure_order SET $sql_order_data");
            } else {
                $sql_order_data =
                        "provider_id = '" . add_escape_custom($user_id) . "', " .
                        "date_collected = DATE_FORMAT('" . add_escape_custom($observationDate . '00') . "', '%Y%m%d%H%i%s'), " .
                        "order_priority = 'normal', " .
                        "order_status = 'complete'";

                if ($check_order['patient_id'] == "") {
                    $sql_order_data .=
                            ", patient_id = '" . add_escape_custom($patient_id) . "'";
                } else {
                    $patient_id = $check_order['patient_id'];
                }

                if ($check_order['provider_id'] == "" or $check_order['provider_id'] <= 0) {
                    $sql_order_data .=
                            ", provider_id = '" . add_escape_custom($user_id) . "'";
                } else {
                    $user_id = $check_order['provider_id'];
                }

                $order_id = $check_order['procedure_order_id'];
                sqlStatement("UPDATE procedure_order SET $sql_order_data WHERE procedure_order_id = '" . add_escape_custom($order_id) . "'");
            }

            $report_status = mapReportStatus($observationStatus);

            $check_report = sqlQuery("SELECT COUNT(*) AS count, procedure_report_id FROM procedure_report WHERE procedure_order_id = '" . add_escape_custom($order_id) . "'");

            if ($check_report['count'] <= 0) {
                $sql_report_data =
                        "procedure_order_id = '" . add_escape_custom($order_id) . "', " .
                        "date_collected = DATE_FORMAT('" . add_escape_custom($observationDate . "00") . "', '%Y%m%d%H%i%s'), " .
                        "source = '" . add_escape_custom($user_id) . "', " .
                        "date_report = DATE_FORMAT('" . add_escape_custom($date) . "', '%Y%m%d'), " .
                        "report_status = '" . add_escape_custom($report_status) . "', " .
                        "review_status = 'received'";

                $report_id = sqlInsert("INSERT INTO procedure_report SET $sql_report_data");
            } else {
                $sql_report_data =
                        "date_collected = DATE_FORMAT('" . add_escape_custom($observationDate . "00") . "', '%Y%m%d%H%i%s'), " .
                        "source = '" . add_escape_custom($user_id) . "', " .
                        "report_status = '" . add_escape_custom($report_status) . "', " .
                        "review_status = 'received'";

                $report_id = $check_report['procedure_report_id'];
                sqlStatement("UPDATE procedure_report SET $sql_report_data WHERE procedure_report_id = '" . add_escape_custom($check_report['procedure_report_id']) . "' AND procedure_order_id = '" . add_escape_custom($order_id) . "'");
            }

            // Loop through all Results
            // Result maps to procedure_report table
            foreach ($resultReport->Result as $result) {
                // Access result fields
                $resultCode = $result->ObservationId;   // => procedure_result.procedure_type_id
                $resultCodeTex = $result->ObservationText;  // => This text should be the same as procedure_type.name
                $value = $result->Value;  // => procedure_result.result
                $unit = $result->Unit;      // => procedure_result.units
                $referenceRange = $result->ReferenceRange;  //=> procedure_result.range
                $abnormalFlag = $result->AbnormalFlag;        // => procedure_result.abnormal
                $resultStatus = $result->ResultStatus;        // => procedure_result.result_status
                $resultDateTime = $result->ResultDateTime;  // => procedure_result.date
                $comment = $result->CommentText;            //=> procedure_result.comments
                //Do something with result, ie put in DB
                $check_type2 = sqlQuery("SELECT COUNT(*) AS count FROM procedure_type WHERE procedure_type_id = '" . add_escape_custom($resultCode) . "'");
                if ($check_type2['count'] <= 0) {
                    $sql_type_data =
                            "procedure_type_id = '" . add_escape_custom($resultCode) . "', " .
                            "parent = '" . add_escape_custom($observationCode) . "', " .
                            "name = '" . add_escape_custom($resultCodeTex) . "', " .
                            "procedure_type = 'res'";

                    $type_id = sqlInsert("INSERT INTO procedure_type SET $sql_type_data");
                }

                $result_status = mapResultStatus($resultStatus);

                $abnormalFlag = mapAbnormalStatus($abnormalFlag);

                $check_result = sqlQuery("SELECT COUNT(*) AS count, procedure_result_id FROM procedure_result WHERE procedure_report_id = '" . add_escape_custom($report_id) . "' AND procedure_type_id = '" . add_escape_custom($resultCode) . "'");

                if ($check_result['count'] <= 0) {

                    $sql_result_data =
                            "procedure_report_id = '" . add_escape_custom($report_id) . "', " .
                            "procedure_type_id = '" . add_escape_custom($resultCode) . "', " .
                            "date = DATE_FORMAT('" . add_escape_custom($resultDateTime . '00') . "', '%Y%m%d%H%i%s'), " .
                            "facility = '" . add_escape_custom($str_facilityId) . "', " .
                            "units = '" . add_escape_custom($unit) . "', " .
                            "result = '" . add_escape_custom($value) . "', " .
                            "`range` = '" . add_escape_custom($referenceRange) . "', " .
                            "abnormal = '" . add_escape_custom($abnormalFlag) . "', " .
                            "comments = '" . add_escape_custom($comment) . "', " .
                            "result_status = '" . add_escape_custom($result_status) . "'";

                    sqlInsert("INSERT INTO procedure_result SET $sql_result_data");
                } else {
                    $sql_result_data =
                            "date = DATE_FORMAT('" . add_escape_custom($resultDateTime . '00') . "', '%Y%m%d%H%i%s'), " .
                            "facility = '" . add_escape_custom($str_facilityId) . "', " .
                            "units = '" . add_escape_custom($unit) . "', " .
                            "result = '" . add_escape_custom($value) . "', " .
                            "`range` = '" . add_escape_custom($referenceRange) . "', " .
                            "abnormal = '" . add_escape_custom($abnormalFlag) . "', " .
                            "comments = '" . add_escape_custom($comment) . "', " .
                            "result_status = '" . add_escape_custom($result_status) . "'";

                    sqlStatement("UPDATE procedure_result SET $sql_result_data WHERE procedure_result_id = '" . add_escape_custom($check_result['procedure_result_id']) . "'");
                }
            }

            // Send a message regarding a report with pending review status.
            //echo "Sending message for " . $patient_id . " ordered by " . $user_id . "<br>\n";
            lab_results_messages($patient_id, $report_id, $user_id);
        }

        // Need to confirm that the lab result message has been received.
        // This is the url of the confirm request.
        $url = "confirm/" . $id;
        // Make the confirmation request.
        $response = $client->sendRequest($url, "POST");
        // Check response for success or error.
        if ($response->IsError)
            echo xl("Error confirming receipt of lab results") . ": {$response->ErrorMessage}<br>\n";
//        else {
//            echo xl("Success confirming receipt of lab result") . " <br>\n";
//            echo $response->ResponseXml;
//        }
    }

        // report on lab_patient_errors
    $lab_query_report .= "<br>". xl("Provider Matching Errors") . ":<hr><table style=\"font-size:12px;\" cellpadding='3px'>";

    if(count($lab_provider_errors) == 0)
        $lab_query_report .= "<tr><td>" .xl("No errors found"). "</td></tr>";
    else
    {
        $lab_query_report .= "<tr><td>" .
            xl("First Name") . "</td><td>" .
            xl("Last Name") . "</td><td>" .
            xl("NPI") . "</td><tr>";

        foreach ($lab_provider_errors as $labResultReport)
        {
            $lab_query_report .=
                "<tr><td>{$labResultReport->OrderingProviderFirstName}</td>".
                "<td>{$labResultReport->OrderingProviderLastName}</td>".
                "<td>{$labResultReport->OrderingProviderId}</td></tr>";
        }
    }
    $lab_query_report .= "</table>";


    // report on lab_patient_errors
    $lab_query_report .= "<br>". xl("Patient Lookup Errors") . ":<hr><table style=\"font-size:12px;\" cellpadding='3px'>";

    if(count($lab_patient_errors) == 0)
        $lab_query_report .= "<tr><td>" .xl("No errors found"). "</td></tr>";
    else
    {
        $lab_query_report .= "<tr><td>" . 
            xl("First Name") . "</td><td>" .
            xl("Middle Name") . "</td><td>" .
            xl("Last Name") . "</td><td>" .
            xl("DOB") . "</td><td>" .
            xl("Gender") . "</td><td>" .
            xl("External Id") . "</td><td>" .
            xl("SSN") . "</td><td>" .
            xl("Address") . "</td><td>" .
            xl("City") . "</td><td>".
            xl("State") . "</td><td>" .
            xl("Zip") . "</td><td>" .
            xl("Home Phone") . "</td></tr>";
        foreach ($lab_patient_errors as $labResult) {
            $patient = $labResult->Patient;
             $lab_query_report .= "<tr><td>{$patient->FirstName}</td>" .
                "<td>{$patient->MiddleName}</td>" .
                "<td>{$patient->LastName}</td>".
                "<td>{$patient->DOB}</td>".
                "<td>{$patient->Gender}</td>".
                "<td>{$patient->ExternalId}</td>".
                "<td>{$patient->SSN}</td>".
                "<td>{$patient->Address}</td>".
                "<td>{$patient->City}</td>".
                "<td>{$patient->State}</td>".
                "<td>{$patient->Zip}</td>".
                "<td>{$patient->HomePhone}</td></tr>";
        }
    }
    $lab_query_report .= "</table>";

    // report on lab_patient_success
    $lab_query_report .= "<br><br>". xl("New results from Lab Exchange") . ":<hr><table style=\"font-size:12px;\" >";

    if(count($lab_patient_success) == 0)
        $lab_query_report .= "<tr><td>" . xl("No new results found") . "</td></tr>";
    else
    {
        $lab_query_report .= "<tr><td>" . 
            xl("First Name") . "</td><td>" .
            xl("Middle Name") . "</td><td>" .
            xl("Last Name") .   "</td><td>" .
            xl("DOB") . "</td><td>" .
            xl("Gender") . "</td><td>" .
            xl("External Id") . "</td><td>" .
            xl("SSN") . "</td><td>" .
            xl("Address") . "</td><td>" .
            xl("City") . "</td><td>" .
            xl("State") . "</td><td>" .
            xl("Zip") . "</td><td>" .
            xl("Home Phone") . "</td></tr>";
        foreach ($lab_patient_success as $labResult) {
            $patient = $labResult->Patient;
             $lab_query_report .= "<tr><td>{$patient->FirstName}</td>" .
                "<td>{$patient->MiddleName}</td>" .
                "<td>{$patient->LastName}</td>".
                "<td>{$patient->DOB}</td>".
                "<td>{$patient->Gender}</td>".
                "<td>{$patient->ExternalId}</td>".
                "<td>{$patient->SSN}</td>".
                "<td>{$patient->Address}</td>".
                "<td>{$patient->City}</td>".
                "<td>{$patient->State}</td>".
                "<td>{$patient->Zip}</td>".
                "<td>{$patient->HomePhone}</td></tr>";
        }
    }
    $lab_query_report .= "</table>";

}
?>
<html>
    <head>

<?php html_header_show(); ?>
        <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
        <script type="text/javascript" src="../../../library/dialog.js"></script>
        <script type="text/javascript" src="../../../library/textformat.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
    </head>

    <body class="body_top">
        <div id="lab_report">
            <center>
            <table style="width: 80%;"><tr><td><span class="title"><?php echo htmlspecialchars(xl('Lab Results Report'), ENT_NOQUOTES); ?></span></td></tr></table>
            <br>
            <table style="width: 90%; border: 1px solid black; font-size:12px;">
                <tr><td><?php echo $lab_query_report; ?></td></tr>
            </table>
            </center>
        </div>
    </body>
</html>
