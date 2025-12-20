<?php

/**
 *  php/taskman_functions.php
 *
 * Function which extend taskman.php, current a email-to-fax gateway
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * Copyright (C) 2016 Raymond Magauran <rmagauran@gmail.com>
 * @author    Ray Magauran <rmagauran@gmail.com>
 *
 * @copyright Copyright (c) 2016 Raymond Magauran <rmagauran@gmail.com>
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Mpdf\Mpdf;
use OpenEMR\Pdf\Config_Mpdf;
use OpenEMR\Services\FacilityService;
use PHPMailer\PHPMailer\PHPMailer;

$facilityService = new FacilityService();

function taskman_debug_log($msg): void
{
    $logFile = __DIR__ . '/../taskman_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $msg\n", FILE_APPEND);
}

/**
 *  This function creates a task as a record in the form_taskman DB_table.
 */
function make_task($ajax_req): void
{
    taskman_debug_log("Entering make_task");
    global $send;

    $from_id    = $ajax_req['from_id'];
    $to_id      = $ajax_req['to_id'];
    $patient_id = $ajax_req['pid'];
    $doc_type   = $ajax_req['doc_type'];
    $doc_id     = $ajax_req['doc_id'] ?? '';
    $enc        = $ajax_req['enc'];

    $query      = "SELECT * FROM users WHERE id=?";
    $to_data    =  sqlQuery($query, [$to_id]);
    $filename   = "Fax_" . $enc . "_" . $to_data['lname'] . ".pdf";

    $query = "SELECT * FROM documents where encounter_id=? and foreign_id=? and url like ? and deleted = 0";
    $doc = sqlQuery($query, [$enc,$patient_id,'%' . $filename . '%' ]);


    $sql = "SELECT * from form_taskman where FROM_ID=? and TO_ID=? and PATIENT_ID=? and ENC_ID=?";
    $task = sqlQuery($sql, [$from_id,$to_id,$patient_id,$enc]);

    if ($task) {
        $task['to_name'] = $to_data['fname'] . ' ' . $to_data['lname'];
        $task['to_fax'] = $to_data['fax'];
    }

    if (!empty($task['COMPLETED_DATE'])) {
        $dated = new DateTime($task['COMPLETED_DATE']);
        $dated = $dated->format('Y/m/d');
        $sent_date = oeFormatShortDate($dated);
    }
    if (!($doc['ID'] ?? '') && $task['ID'] && (strtotime((string) $task['REQ_DATE']) < (time() - 60))) {
        // The task was requested more than a minute ago (prevents multi-clicks from "re-generating" the PDF),
        // but the document was deleted (to redo it)...
        // Delete the task, recreate the task, and send the newly made PDF.
        $sql = "DELETE from form_taskman where FROM_ID=? and TO_ID=? and PATIENT_ID=? and ENC_ID=?";
        $task = sqlQuery($sql, [$from_id,$to_id,$patient_id,$enc]);
    }
    if (($task['ID'] ?? '') && $task['COMPLETED'] == '2') {
        $send['comments'] = xlt('This fax has already been sent to') . " " . text($task['to_name']) . " " . xlt('via') . " " . text($task['to_fax']) . " on " . text($sent_date) . ". " .
                            xlt('If you made changes and want to re-send it, delete the original (in Communications) or wait 60 seconds, and try again.') . " " .
                            xlt('Filename') . ": " . text($filename);
        echo json_encode($send);
        exit;
    } elseif (($task['ID'] ?? '') && $task['COMPLETED'] >= '1') {
        if ($task['DOC_TYPE'] == 'Fax') {
            $send['DOC_link'] = "<a href=\"JavaScript:void(0);\"
                                    onclick=\"openNewForm('" . attr($GLOBALS['webroot']) . "/controller.php?document&view&patient_id=" . attr($task['PATIENT_ID']) . "&doc_id=" . attr($task['DOC_ID']) . "', 'Fax Report');\"
                                    title='" . xla('View the Summary Report sent to') .
                                            text($task['to_name']) . " " . xla('via') . " " . text($task['to_fax']) . " " . xla('on') . " " . text($sent_date) . "'>
								    <i class='far fa-file-pdf fa-fw'></i>
                                 </a>
								 <i class='fas fa-redo-alt fa-fw'
									onclick=\"top.restoreSession(); create_task('" . attr($to_id) . "', 'Fax-resend', 'ref'); return false;\">
								 </i>
							    ";
            //add a resend option.
            $send['comments'] = xlt('This fax has already been sent.');
            echo json_encode($send);
            exit;
        } elseif ($task['DOC_TYPE'] == "Fax-resend") {
            //we need to resend this fax????  Remake it with latest data and send ot out.
            $sql = "DELETE from form_taskman where FROM_ID=? and TO_ID=? and PATIENT_ID=? and ENC_ID=?";
            $task = sqlQuery($sql, [$from_id,$to_id,$patient_id,$enc]);

            $sql = "INSERT into form_taskman
				(REQ_DATE, FROM_ID,  TO_ID,  PATIENT_ID,  DOC_TYPE,  DOC_ID,  ENC_ID) VALUES
				(NOW(), ?, ?, ?, ?, ?, ?)";
            sqlQuery($sql, [$from_id, $to_id, $patient_id, $doc_type, $doc_id, $enc]);

            $send['comments'] = xlt('Resending this report.');
            echo json_encode($send);
        } else { //DOC_TYPE is a Report
            $send['comments'] = xlt('Currently working on making this document') . "...\n";
        }
    } elseif (!($task['ID'] ?? '')) {
        $sql = "INSERT into form_taskman
				(REQ_DATE, FROM_ID,  TO_ID,  PATIENT_ID,  DOC_TYPE,  DOC_ID,  ENC_ID) VALUES
				(NOW(), ?, ?, ?, ?, ?, ?)";
        sqlQuery($sql, [$from_id, $to_id, $patient_id, $doc_type, $doc_id, $enc]);
    } else {
        $send['comments'] = xlt('Currently working on making this document') . "...\n";
    }
}

/**
 *  This function reads and processes an order (or task).
 */
function process_tasks($task)
{
    global $send;

    // Fetch recipient details since they aren't in the task table
    $query = "SELECT * FROM users WHERE id=?";
    $to_data = sqlQuery($query, [$task['TO_ID']]);
    $task['to_name'] = $to_data['fname'] . ' ' . $to_data['lname'];
    $task['to_fax'] = $to_data['fax'];

    /**
     *  First see if the doc_ID exists
     *  if not we need to create this
     */
    $task = make_document($task);
    update_taskman($task, 'created', '1');

    if (($task['DOC_TYPE'] == 'Fax') || ($task['DOC_TYPE'] == 'Fax-resend')) {
        deliver_document($task);
    }

    update_taskman($task, 'completed', '1');

    if ($task['DOC_TYPE'] == "Fax") {
        //now return any objects you need to Eye Form
        $send['DOC_link'] = "<a onclick=\"openNewForm('" . attr($GLOBALS['webroot']) . "/controller.php?document&view&patient_id=" . attr($task['PATIENT_ID']) . "&doc_id=" . attr($task['DOC_ID']) . "', 'Fax Report');\"
                                href=\"JavaScript:void(0);\"
                                title='" . xlt('Report was faxed to') . " " . attr($task['to_name']) . " @ " . attr($task['to_fax']) . " on " .
                                text($task['COMPLETED_DATE']) . ". " . xla('Click to view.') . "'><i class='far fa-file-pdf fa-fw'></i></a>";
                            //if we want a "resend" icon, add it here.
    }

    return $send;
}

 /**
 *  This function updates the taskman record in the form_taskman table.
 */
function update_taskman($task, $action, $value): void
{
    global $send;
    if ($action == 'created') {
        $sql = "UPDATE form_taskman set DOC_ID=?,COMMENT=concat('Created: ',NOW()) where ID=?";
        sqlQuery($sql, [$task['DOC_ID'],$task['ID']]);
        if (!empty($send['comments'])) {
            $send['comments'] .= "Document created. ";
        } else {
            $send['comments'] = "Document created. ";
        }
    }

    if ($action == 'completed') {
        $sql = "UPDATE form_taskman set DOC_ID=?,COMPLETED =?,COMPLETED_DATE=NOW(),COMMENT=concat(COMMENT,'Completed: ', NOW(),'\n') where ID=?";
        sqlQuery($sql, [$task['DOC_ID'],$value,$task['ID']]);
        $send['comments'] .= "Task completed. ";
    }

    if ($action == 'refaxed') {
        $sql = "UPDATE form_taskman set DOC_ID=?,COMPLETED =?,COMPLETED_DATE=NOW(),COMMENT=concat(COMMENT,'Refaxed: ', NOW(),'\n') where ID=?";
        sqlQuery($sql, [$task['DOC_ID'],$value,$task['ID']]);
        $send['comments'] .= "Ok, we resent it to the Fax Server.\n";
    }
}


/**
 *  This function delivers a document to the intended recipient.
 *  Will need to test for Hylafax.
 *  Will need code for Direct messaging.
 *  Will need expansion to other methods of delivery.
 *  Works for email-to-fax.
 *      To be HIPPA compliant fax address must be behind secure firewall with this server.
 *      Some suggest the fax server to fax machine portion of efaxing is not HIPPA compliant, no matter how it is done.
 *      Thus faxing is not HIPPA compliant, and if that affects you, don't deliver this way.
 */
function deliver_document($task)
{
    global $facilityService;
    taskman_debug_log("Entering deliver_document");

    $facility_data  = $facilityService->getPrimaryBillingLocation();

    $query          = "SELECT * FROM users WHERE id=?";
    $from_data      = sqlQuery($query, [$task['FROM_ID']]);
    $from_name      = $from_data['fname'] . " " . $from_data['lname'];
    if (!empty($from_data['suffix'])) {
        $from_name .= ", " . $from_data['suffix'];
    }
    $from_fax       = preg_replace("/[^0-9]/", "", (string) $facility_data['fax']);

    // Use SMTP User as the sender email
    $email_sender = $GLOBALS['SMTP_USER'];

    if (empty($email_sender)) {
         $email_sender = $facility_data['email'] ?? 'noreply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
         taskman_debug_log("Warning: SMTP User global not set. Using fallback: $email_sender");
    } else {
         taskman_debug_log("Using SMTP User as sender: $email_sender");
    }

    $to_data        = sqlQuery($query, [$task['TO_ID']]);
    $to_fax         = preg_replace("/[^0-9]/", "", (string) $to_data['fax']);

    $mail           = new MyMailer();

    // Auto-fix for Port 465 (Implicit SSL) if user forgot to set 'ssl' in Globals
    if (($GLOBALS['SMTP_PORT'] == 465) && empty($GLOBALS['SMTP_SECURE'])) {
        $mail->SMTPSecure = 'ssl';
    }

    $fax_domain = $GLOBALS['hylafax_server'] ?? '';
    if (empty($fax_domain)) {
        taskman_debug_log("Error: hylafax_server global not set.");
        error_log("Taskman Error: hylafax_server global not set.", 0);
        return false;
    }

    $to_email       = $to_fax . "@" . $fax_domain;
    taskman_debug_log("Sending fax to email: $to_email");

    // Retrieve document content using Document class to handle encryption/storage abstraction
    $doc_id = $task['DOC_ID'];
    try {
        $doc = new Document($doc_id);
        $file_content = $doc->get_data();
    } catch (\Exception $e) {
        taskman_debug_log("CRITICAL ERROR: Failed to retrieve document data: " . $e->getMessage());
        error_log("Taskman Error: Failed to retrieve document data: " . $e->getMessage(), 0);
        return false;
    }

    if ($file_content === false) {
        taskman_debug_log("CRITICAL ERROR: Document data is empty or invalid for Doc ID: $doc_id");
        return false;
    }

    $file_name = preg_replace('/^.*\//', "", (string) $task['DOC_url']);

    // Ensure the attachment filename ends in .pdf
    if (strlen((string) $file_name) < 4 || strcasecmp(substr((string) $file_name, -4), '.pdf') != 0) {
        $file_name .= ".pdf";
    }

    taskman_debug_log("Attaching document ID: $doc_id (Size: " . strlen($file_content) . " bytes)");

    $mail->AddReplyTo($email_sender, $from_name);
    $mail->SetFrom($email_sender, $from_name);
    $mail->AddAddress($to_email);
    if ($from_fax == '') {
        taskman_debug_log("Taskman Error: No from_fax value found");
        error_log("Taskman Error: No from_fax value found", 0);
        exit();
    }
    $mail->Subject = $from_fax;
    $mail->Body = ' ';
    $mail->AddStringAttachment($file_content, $file_name);
    if ($mail->Send()) {
        taskman_debug_log("NO ERROR: email sent to " . $to_email);
        error_log("NO ERROR: email sent to " . $to_email, '0');
        return true;
    } else {
        $email_status = $mail->ErrorInfo;
        taskman_debug_log("EMAIL ERROR: " . $email_status);
        error_log("EMAIL ERROR: " . errorLogEscape($email_status), 0);
        return false;
    }
}


/**
 *  This function will display the form_taskman table as requested, by date or by status?
 *  Currently it is not used.
 */
function show_task($ajax_req): void
{
    //$_REQUEST['show_task'] = task_id, or all or by date range?
    //Could be a HTML5 table layout?
    //Think about how to display this and should it link to things/documents that were sent, or just status of the request
    //as listed in the field COMMENTS?  Think, think, think...A bear of little brain...
}

/**
 *  This function makes and stores a document that we want to deliver.
 */
function make_document($task)
{
    global $providerNAME;
    global $encounter;
    global $facilityService;
    global $web_root, $webserver_root;

    $facility_data  = $facilityService->getPrimaryBillingLocation();

    $query          = "SELECT * FROM users WHERE id=?";
    $from_data      = sqlQuery($query, [$task['FROM_ID']]);
    $from_name      = $from_data['fname'] . " " . $from_data['lname'];
    if (!empty($from_data['suffix'])) {
        $from_name .= ", " . $from_data['suffix'];
    }

    $to_data        = sqlQuery($query, [$task['TO_ID']]);
    $to_name        = $to_data['fname'] . " " . $to_data['lname'];
    if ($to_data['suffix']) {
        $to_name .= ", " . $to_data['suffix'];
    }
    $task['to_name'] = $to_name;
    $to_fax         = preg_replace("/[^0-9]/", "", (string) $to_data['fax']);
    $task['to_fax'] = $to_fax;

    $query          = "SELECT * FROM patient_data where pid=?";
    $patientData    = sqlQuery($query, [$task['PATIENT_ID']]);
    $pt_name        = $patientData['fname'] . ' ' . $patientData['lname'];
    $pid            = $task['PATIENT_ID'];
    $encounter      = $task['ENC_ID'];

    // Get encounter date
    $query = "SELECT date FROM form_encounter WHERE encounter=?";
    $enc_res = sqlQuery($query, [$encounter]);
    $dated = new DateTime($enc_res['date']);
    $visit_date = oeFormatShortDate($dated->format('Y-m-d'));

    $filepath = $GLOBALS['oer_config']['documents']['repository'] . $task['PATIENT_ID'];

    if (($task['DOC_TYPE'] == 'Fax') || ($task['DOC_TYPE'] == 'Fax-resend')) {
        $category_name  = "Communication%";
        $query          = "select id from categories where name  like ?";
        $ID             = sqlQuery($query, [$category_name]);
        $category_id    = $ID['id'];
        $filename       = "Fax_" . $encounter . "_" . $to_data['lname'] . ".pdf";
        $count          = 0;
        while (file_exists($filepath . '/' . $filename)) {
            $count++;
            $filename = "FAX_" . $encounter . "_" . $to_data['lname'] . "_" . $count . ".pdf";
        }
    } else {
        $category_name  = "Encounters%";
        $query          = "select id from categories where name like ?";
        $ID             = sqlQuery($query, [$category_name]);
        $category_id    = $ID['id'];
        $filename       = "Report_" . $encounter . ".pdf";
    }

    $config_mpdf = Config_Mpdf::getConfigMpdf();

    // Suppress warnings during PDF generation
    $old_level = error_reporting(0);

    try {
        $pdf = new mPDF($config_mpdf);
    } catch (\Throwable $e) {
        taskman_debug_log("Failed to instantiate mPDF: " . $e->getMessage());
        error_reporting($old_level);
        throw $e;
    }

    $html = '
    <html>
    <head>
        <style>
            body { font-family: sans-serif; font-size: 11pt; }
            .header-box { 
                border: 2px solid #000; 
                padding: 15px; 
                margin-bottom: 20px; 
                background-color: #f5f5f5;
            }
            h1 { text-align: center; margin: 0; padding-bottom: 10px; border-bottom: 2px solid #000; font-size: 24pt; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            td { padding: 8px; vertical-align: top; }
            .label { font-weight: bold; width: 15%; text-align: right; padding-right: 10px; color: #333; }
            .value { width: 35%; border-bottom: 1px solid #ccc; }
            .full-width { width: 100%; }
            .comments-box { 
                border: 1px solid #000; 
                padding: 15px; 
                min-height: 200px; 
                margin-top: 10px;
                background-color: #fff;
            }
            .footer { 
                margin-top: 40px; 
                font-size: 8pt; 
                text-align: justify; 
                border-top: 1px solid #000;
                padding-top: 10px;
                color: #555;
            }
            .facility-info { text-align: center; margin-top: 10px; font-size: 10pt; }
        </style>
    </head>
    <body>
        <div class="header-box">
            <h1>FAX COVER SHEET</h1>
            <div class="facility-info">
                <strong>' . text($facility_data['name']) . '</strong><br>
                ' . text($facility_data['street']) . '<br>
                ' . text($facility_data['city']) . ', ' . text($facility_data['state']) . ' ' . text($facility_data['zip']) . '<br>
                Phone: ' . text($facility_data['phone']) . ' | Fax: ' . text($facility_data['fax']) . '
            </div>
        </div>

        <table>
            <tr>
                <td class="label">To:</td>
                <td class="value">' . text($to_name) . '</td>
                <td class="label">From:</td>
                <td class="value">' . text($from_name) . '</td>
            </tr>
            <tr>
                <td class="label">Fax:</td>
                <td class="value">' . text($to_data['fax']) . '</td>
                <td class="label">Date:</td>
                <td class="value">' . date('F j, Y') . '</td>
            </tr>
            <tr>
                <td class="label">Phone:</td>
                <td class="value">' . text($to_data['phonew1']) . '</td>
                <td class="label">Pages:</td>
                <td class="value">1 (including cover)</td>
            </tr>
            <tr>
                <td class="label">Re:</td>
                <td class="value" colspan="3">Patient Report: ' . text($pt_name) . ' (DOB: ' . text($patientData['DOB']) . ')</td>
            </tr>
        </table>

        <div style="margin-top: 25px;">
            <strong>Comments:</strong>
            <div class="comments-box">
                <p>Report of visit for <strong>' . text($pt_name) . '</strong> on <strong>' . text($visit_date) . '</strong>.</p>
                <p>Please find the attached medical records.</p>
            </div>
        </div>

        <div class="footer">
            <strong>CONFIDENTIALITY NOTICE:</strong> The information contained in this facsimile message is privileged and confidential information intended only for the use of the individual or entity named above. If the reader of this message is not the intended recipient, you are hereby notified that any dissemination, distribution or copying of this communication is strictly prohibited. If you have received this communication in error, please immediately notify us by telephone and return the original message to us at the above address via the U.S. Postal Service.
        </div>
    </body>
    </html>';

    // Write the Cover Sheet
    $pdf->WriteHTML($html);

    // --- Clinical Content Generation ---

    // 1. Fetch Form ID
    $query = "SELECT form_id FROM forms WHERE encounter = ? AND formdir = 'eye_mag' AND deleted = 0 LIMIT 1";
    $formData = sqlQuery($query, [$encounter]);
    $form_id = $formData['form_id'] ?? 0;

    taskman_debug_log("Taskman: Encounter $encounter has Form ID: $form_id");

    if ($form_id) {
        // Add a new page for clinical data
        $pdf->AddPage();

        // Fetch Provider Data
        $providerID = getProviderIdOfEncounter($encounter);
        $providerNAME = getProviderName($providerID);

        // Fetch HPI Data
        $query = "SELECT CC1, CC2, CC3, HPI1 FROM form_eye_hpi WHERE id = ?";
        $hpiData = sqlQuery($query, [$form_id]);

        $clinical_html = '
        <style>
            body { font-family: sans-serif; font-size: 11pt; }
            .letter-header { text-align: center; margin-bottom: 30px; }
            .letter-date { text-align: right; margin-bottom: 20px; }
            .recipient-block { margin-bottom: 20px; }
            .re-block { font-weight: bold; margin-bottom: 20px; }
            .salutation { margin-bottom: 15px; }
            .body-text { margin-bottom: 15px; line-height: 1.4; }
            .section-title { font-weight: bold; text-decoration: underline; margin-top: 15px; margin-bottom: 5px; }
            .item-block { margin-bottom: 10px; margin-left: 10px; }
            .item-title { font-weight: bold; }
            .item-detail { margin-left: 15px; font-style: italic; }
            .closing { margin-top: 40px; }
            .signature { font-weight: bold; margin-top: 40px; }
        </style>
        
        <div class="letter-header">
            <h2>' . text($facility_data['name']) . '</h2>
            <div>' . text($facility_data['street']) . '</div>
            <div>' . text($facility_data['city']) . ', ' . text($facility_data['state']) . ' ' . text($facility_data['zip']) . '</div>
            <div>Phone: ' . text($facility_data['phone']) . ' | Fax: ' . text($facility_data['fax']) . '</div>
        </div>

        <div class="letter-date">' . date('F j, Y') . '</div>

        <div class="recipient-block">
            ' . text($to_name) . '<br>
            Fax: ' . text($to_data['fax']) . '
        </div>

        <div class="re-block">
            RE: ' . text($pt_name) . '<br>
            DOB: ' . text($patientData['DOB']) . '<br>
            Date of Visit: ' . text($visit_date) . '
        </div>

        <div class="salutation">Dear ' . text($to_name) . ',</div>

        <div class="body-text">
            It was a pleasure to see ' . text($pt_name) . ' in our office on ' . text($visit_date) . '. 
            Below is a summary of the examination.
        </div>';

        // Chief Complaint Section
        $clinical_html .= '<div class="section-title">Chief Complaint:</div>';
        if (!empty($hpiData['CC1'])) {
            $clinical_html .= '<div class="item-block">1. ' . text($hpiData['CC1']) . '</div>';
        }
        if (!empty($hpiData['CC2'])) {
            $clinical_html .= '<div class="item-block">2. ' . text($hpiData['CC2']) . '</div>';
        }
        if (!empty($hpiData['CC3'])) {
            $clinical_html .= '<div class="item-block">3. ' . text($hpiData['CC3']) . '</div>';
        }

        // HPI Section
        if (!empty($hpiData['HPI1'])) {
             $clinical_html .= '<div class="section-title">History of Present Illness:</div>';
             $clinical_html .= '<div class="body-text">' . text($hpiData['HPI1']) . '</div>';
        }

        // Impression/Plan Section
        $query = "SELECT * FROM form_eye_mag_impplan WHERE form_id=? AND pid=? ORDER BY IMPPLAN_order ASC";
        $impplan_result = sqlStatement($query, [$form_id, $pid]);

        if (sqlNumRows($impplan_result) > 0) {
            $clinical_html .= '<div class="section-title">Impression/Plan:</div>';
            while ($row = sqlFetchArray($impplan_result)) {
                $clinical_html .= '<div class="item-block">';
                $clinical_html .= '<span class="item-title">' . ($row['IMPPLAN_order'] + 1) . '. ' . text($row['title']) . '</span>';

                $code_text = '';
                if ($row['codetext']) {
                    $code_text = text($row['codetext']);
                } elseif ($row['code'] && !preg_match('/Code/', (string) $row['code'])) {
                    $code_text = ($row['codetype'] ? $row['codetype'] . ": " : "") . $row['code'];
                }

                if ($code_text) {
                    $clinical_html .= '<div class="item-detail">' . $code_text . '</div>';
                }

                if ($row['plan']) {
                    $plan_text = str_replace(["\r\n", "\n", "\r"], "<br />", $row['plan']);
                    $clinical_html .= '<div class="item-detail" style="font-style: normal;">' . $plan_text . '</div>';
                }
                $clinical_html .= '</div>';
            }
        }

        // Orders Section
        $query = "SELECT * FROM form_eye_mag_orders WHERE form_id=? AND pid=? ORDER BY id ASC";
        $orders_result = sqlStatement($query, [$form_id, $pid]);

        if (sqlNumRows($orders_result) > 0) {
            $clinical_html .= '<div class="section-title">Orders / Next Visit:</div>';
            while ($row = sqlFetchArray($orders_result)) {
                $clinical_html .= '<div class="item-block">' . text($row['ORDER_DETAILS']) . '</div>';
            }
        }

        $clinical_html .= '
        <div class="closing">
            Sincerely,<br><br><br>
            <strong>' . text($providerNAME) . '</strong><br>
            ' . text($facility_data['name']) . '
        </div>';

        $pdf->WriteHTML($clinical_html);
    }

    $temp_filename = tempnam(sys_get_temp_dir(), 'fax_pdf_');
    $pdf->Output($temp_filename, 'F');

    // Restore error reporting
    error_reporting($old_level);

    $type = "application/pdf";
    $size = filesize($temp_filename);
    $return = addNewDocument($filename, $type, $temp_filename, 0, $size, $task['FROM_ID'], $task['PATIENT_ID'], $category_id, '', 1, true);

    if (file_exists($temp_filename)) {
        unlink($temp_filename);
    }

    $task['DOC_ID'] = $return['doc_id'];
    // Use the actual URL returned by the document system to ensure we have the correct filename (e.g. if renamed)
    $task['DOC_url'] = $return['url'];
    $sql = "UPDATE documents set encounter_id=? where id=?";
    sqlQuery($sql, [$encounter,$task['DOC_ID']]);

    return $task;
}
