<?php

// Copyright (C) 2010 OpenEMR Support LLC   
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
    
    require_once("../globals.php");
    require_once("$srcdir/lab_exchange_api.php");
    require_once("lab_exchange_match.php");
    require_once("../main/messages/lab_results_messages.php");
    include_once("$srcdir/formdata.inc.php");
    

    // Create the REST client
    $client = new LabExchangeClient($siteId, $token, $endpoint);

    // Make the request
    $response = $client->sendRequest("results", "GET");

    // Check response for success or error
    if($response->IsError)
        echo "Error getting lab results from Lab Exchange Network: {$response->ErrorMessage}\n";
    else {
        echo "Success getting lab results \n";
        $resultSet = $response->ResponseXml;

        // For each lab result message
        foreach($resultSet->LabResult as $labResult) {
            // Get the id for this message
            $id = $labResult['id'];
            // echo "ID:" . $id . "\n";
            // Get the patient array
            $patient = $labResult->Patient;
            // Access patient fields
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
            // Do something with patient, ie, find matching patient in DB
            // echo "Patient:" . $lastName . "\n";
            
            // Match patient
            $patient_id = lab_exchange_match_patient($externalId, $firstName, $middleName, $lastName, $dob, $gender, $ssn, $address);
            if (!$patient_id) continue;
            
            // Loop through the facility
            // There can be several facilities. You can either aggregate them with comma to fit into one field
            unset($facilityId);
            
            foreach ($resultSet->Facility as $facility){
                // Access facility fields
                $facilityId[] = $facility->FacilityID;   //=>procedure_result.facility
                $facilityName = $facility->FacilityName;  //not mapped
            }
            
            if (count($facilityId) > 0) {
                $str_facilityId = implode(":", $facilityId);
            }
            // Echo "facility:".$str_facilityId ."<br>";
            
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
                
                // Do something with the report, ie put in DB
                // echo $observationCode .":".$observationText.":".$observationStatus. "\n";
                // Match provider
                $user_id = '';
                $user_id = lab_exchange_match_provider($orderingProviderLastName, $orderingProviderFirstName);
                
                $date = '';
                $date = substr($observationDate,0,8);
            
                $check_type = sqlQuery("SELECT COUNT(*) AS count FROM procedure_type WHERE procedure_type_id = '".formDataCore($observationCode)."'");
                
                if ($check_type['count'] <= 0) {
                    $sql_type_data = 
                        "procedure_type_id = '".formDataCore($observationCode)."', " .
                        "name = '".formDataCore($observationText)."', ".
                        "procedure_type = 'res'";
                        
                    $type_id = sqlInsert("INSERT INTO procedure_type SET $sql_type_data");
                }
                
                $check_order = sqlQuery("SELECT COUNT(*) AS count, procedure_order_id, provider_id, patient_id FROM procedure_order WHERE control_id = '".formDataCore($controlId)."' AND procedure_type_id = '".formDataCore($observationCode)."'");
                
                if ($check_order['count'] <= 0) {
                    $sql_order_data = 
                        "procedure_type_id = '".formDataCore($observationCode)."', " .
                        "provider_id = '".formDataCore($user_id)."', " .
                        "patient_id = '".formDataCore($patient_id)."', " .
                        "date_collected = DATE_FORMAT('".formDataCore($observationDate.'00')."', '%Y%m%d%H%i%s'), " .
                        "date_ordered = DATE_FORMAT('".formDataCore($date)."', '%Y%m%d'), " .
                        "order_priority = 'normal', " .
                        "order_status = 'Complete', " .
                        "control_id = '".formDataCore($controlId)."'";
                        
                    $order_id = sqlInsert("INSERT INTO procedure_order SET $sql_order_data");
                }
                else {
                    $sql_order_data =
                        "provider_id = '".formDataCore($user_id)."', " .
                        "date_collected = DATE_FORMAT('".formDataCore($observationDate.'00')."', '%Y%m%d%H%i%s'), " .
                        "order_priority = 'normal', " .
                        "order_status = 'Complete'";
                        
                    if ($check_order['patient_id'] == "") {
                        $sql_order_data .=
                        ", patient_id = '".formDataCore($patient_id)."'";
                    }
                    else {
                        $patient_id = $check_order['patient_id'];
                    }
                    
                    if ($check_order['provider_id'] == "" or $check_order['provider_id'] <= 0) {
                        $sql_order_data .=
                        ", provider_id = '".formDataCore($user_id)."'";
                    }
                    else {
                        $user_id = $check_order['provider_id'];
                    }
                        
                    $order_id = $check_order['procedure_order_id'];
                    sqlStatement("UPDATE procedure_order SET $sql_order_data WHERE procedure_order_id = '".formDataCore($order_id)."'");
                }
                
                $report_status = mapReportStatus($observationStatus);
                
                $check_report = sqlQuery("SELECT COUNT(*) AS count, procedure_report_id FROM procedure_report WHERE procedure_order_id = '".formDataCore($order_id)."'");
                
                if ($check_report['count'] <= 0) {
                    $sql_report_data = 
                        "procedure_order_id = '".formDataCore($order_id)."', ".
                        "date_collected = DATE_FORMAT('".formDataCore($observationDate."00")."', '%Y%m%d%H%i%s'), " .
                        "source = '".formDataCore($user_id)."', " .
                        "date_report = DATE_FORMAT('".formDataCore($date)."', '%Y%m%d'), " .
                        "report_status = '".formDataCore($report_status)."', ".
                        "review_status = 'received'";
                        
                    $report_id = sqlInsert("INSERT INTO procedure_report SET $sql_report_data");
                }
                else {
                    $sql_report_data =
                        "date_collected = DATE_FORMAT('".formDataCore($observationDate."00")."', '%Y%m%d%H%i%s'), " .
                        "source = '".formDataCore($user_id)."', " .
                        "report_status = '".formDataCore($report_status)."', ".
                        "review_status = 'received'";
                        
                    $report_id = $check_report['procedure_report_id'];
                    sqlStatement("UPDATE procedure_report SET $sql_report_data WHERE procedure_report_id = '".formDataCore($check_report['procedure_report_id'])."' AND procedure_order_id = '".formDataCore($order_id)."'");
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
                    //echo $resultCode . ":" .$value . " " . $unit. "\n";
                     $check_type2 = sqlQuery("SELECT COUNT(*) AS count FROM procedure_type WHERE procedure_type_id = '".formDataCore($resultCode)."'");
                    if ($check_type2['count'] <= 0) {
                        $sql_type_data = 
                            "procedure_type_id = '".formDataCore($resultCode)."', " .
                            "parent = '".formDataCore($observationCode)."', " .
                            "name = '".formDataCore($resultCodeTex)."', ".
                            "procedure_type = 'res'";
                            
                        $type_id = sqlInsert("INSERT INTO procedure_type SET $sql_type_data");
                    }
                    
                    $result_status = mapResultStatus($resultStatus);
                    
                    $abnormalFlag = mapAbnormalStatus($abnormalFlag);
                    
                    $check_result = sqlQuery("SELECT COUNT(*) AS count, procedure_result_id FROM procedure_result WHERE procedure_report_id = '".formDataCore($report_id)."' AND procedure_type_id = '".formDataCore($resultCode)."'");
                
                    if ($check_result['count'] <= 0) {
                    
                        $sql_result_data = 
                            "procedure_report_id = '".formDataCore($report_id)."', ".
                            "procedure_type_id = '".formDataCore($resultCode)."', ".
                            "date = DATE_FORMAT('".formDataCore($resultDateTime.'00')."', '%Y%m%d%H%i%s'), ".
                            "facility = '".formDataCore($str_facilityId)."', " .
                            "units = '".formDataCore($unit)."', ".
                            "result = '".formDataCore($value)."', ".
                            "`range` = '".formDataCore($referenceRange)."', ".
                            "abnormal = '".formDataCore($abnormalFlag)."', ".
                            "comments = '".formDataCore($comment)."', ".
                            "result_status = '".formDataCore($result_status)."'";
                        
                        sqlInsert("INSERT INTO procedure_result SET $sql_result_data");
                    }
                    else {
                        $sql_result_data =
                            "date = DATE_FORMAT('".formDataCore($resultDateTime.'00')."', '%Y%m%d%H%i%s'), ".
                            "facility = '".formDataCore($str_facilityId)."', " .
                            "units = '".formDataCore($unit)."', ".
                            "result = '".formDataCore($value)."', ".
                            "`range` = '".formDataCore($referenceRange)."', ".
                            "abnormal = '".formDataCore($abnormalFlag)."', ".
                            "comments = '".formDataCore($comment)."', ".
                            "result_status = '".formDataCore($result_status)."'";
                        
                        sqlStatement("UPDATE procedure_result SET $sql_result_data WHERE procedure_result_id = '".formDataCore($check_result['procedure_result_id'])."'");
                    }
                
                }
                
                // Send a message regarding a report with pending review status.
                lab_results_messages($patient_id, $report_id, $user_id);
            
            }
            
            // Need to confirm that the lab result message has been received.
            // This is the url of the confirm request.
            $url = "confirm/" . $id;
            // Make the confirmation request
            $response = $client->sendRequest($url, "POST");
            // Check response for success or error.
            if($response->IsError)
                echo "Error confirming receipt of lab results: {$response->ErrorMessage}\n";
            else{ 
                echo "Success confirming receipt of lab result \n";
                echo $response->ResponseXml;
            }
        }
    }
    
?>
