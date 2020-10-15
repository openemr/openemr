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
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

/**
 *  This function creates a task as a record in the form_taskman DB_table.
 */
function make_task($ajax_req)
{
    global $send;
    $from_id    = $ajax_req['from_id'];
    $to_id      = $ajax_req['to_id'];
    $patient_id = $ajax_req['pid'];
    $doc_type   = $ajax_req['doc_type'];
    $doc_id     = $ajax_req['doc_id'];
    $enc        = $ajax_req['enc'];

    $query      = "SELECT * FROM users WHERE id=?";
    $to_data    =  sqlQuery($query, array($to_id));
    $filename   = "Fax_" . $encounter . "_" . $to_data['lname'] . ".pdf";

    $query = "SELECT * FROM documents where encounter_id=? and foreign_id=? and url like ? and deleted = 0";
    $doc = sqlQuery($query, array($encounter,$pid,'%' . $filename . '%' ));


    $sql = "SELECT * from form_taskman where FROM_ID=? and TO_ID=? and PATIENT_ID=? and ENC_ID=?";
    $task = sqlQuery($sql, array($from_id,$to_id,$patient_id,$enc));

    if (!$doc['ID'] && $task['ID'] && (strtotime($task['REQ_DATE']) < (time() - 60))) {
        // The task was requested more than a minute ago (prevents multi-clicks from "re-generating" the PDF),
        // but the document was deleted (to redo it)...
        // Delete the task, recreate the task, and send the newly made PDF.
        $sql = "DELETE from form_taskman where FROM_ID=? and TO_ID=? and PATIENT_ID=? and ENC_ID=?";
        $task = sqlQuery($sql, array($from_id,$to_id,$patient_id,$enc));
    }

    if ($task['ID'] && $task['COMPLETED'] == '2') {
        $send['comments'] = xlt('This fax has already been sent.') . " " .
                            xlt('If you made changes and want to re-send it, delete the original (in Communications) or wait 60 seconds, and try again.') . " " .
                            xlt('Filename') . ": " . text($filename);
        echo json_encode($send);
        exit;
    } elseif ($task['ID'] && $task['COMPLETED'] >= '1') {
        if ($task['DOC_TYPE'] == 'Fax') {
            $send['DOC_link'] = "<a href='" . $webroot . "/openemr/controller.php?document&view&patient_id=" . attr($task['PATIENT_ID']) . "&doc_id=" . attr($task['DOC_ID']) . "'
								target='_blank' title='" . xla('View the Summary Report sent to Fax Server.') . "'>
								<i class='fa fa-file-pdf-o fa-fw'></i></a>
								<i class='fa fa-repeat fa-fw'
									onclick=\"top.restoreSession(); create_task('" . attr($pat_data['ref_providerID']) . "','Fax-resend','ref'); return false;\">
									</i>
							";
                            //add a resend option.
            $send['comments'] = xlt('This fax has already been sent.');
            echo json_encode($send);
            exit;
        } elseif ($task['DOC_TYPE'] == "Fax-resend") {
            //we need to resend this fax????  Remake it with latest data and send ot out.
            $sql = "DELETE from form_taskman where FROM_ID=? and TO_ID=? and PATIENT_ID=? and ENC_ID=?";
            $task = sqlQuery($sql, array($from_id,$to_id,$patient_id,$enc));

            $sql = "INSERT into form_taskman
				(REQ_DATE, FROM_ID,  TO_ID,  PATIENT_ID,  DOC_TYPE,  DOC_ID,  ENC_ID) VALUES
				(NOW(), ?, ?, ?, ?, ?, ?)";
            sqlQuery($sql, array($from_id, $to_id, $patient_id, $doc_type, $doc_id, $enc));

            $send['comments'] = xlt('Resending this report.');
            echo json_encode($send);
        } else { //DOC_TYPE is a Report
            $send['comments'] = xlt('Currently working on making this document') . "...\n";
        }
    } elseif (!$task['ID']) {
        $sql = "INSERT into form_taskman
				(REQ_DATE, FROM_ID,  TO_ID,  PATIENT_ID,  DOC_TYPE,  DOC_ID,  ENC_ID) VALUES
				(NOW(), ?, ?, ?, ?, ?, ?)";
        sqlQuery($sql, array($from_id, $to_id, $patient_id, $doc_type, $doc_id, $enc));
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
        $send['DOC_link'] = "<a href='" . $webroot . "/openemr/controller.php?document&view&patient_id=" . attr($task['PATIENT_ID']) . "&doc_id=" . attr($task['DOC_ID']) . "'
								target='_blank' title=" . xlt('Report was faxed. Click to view.') . ">
								<i class='fa fa-file-pdf-o fa-fw'></i>
							</a>";
                            //if we want a "resend" icon, add it here.
    }

    return $send;
}

 /**
 *  This function updates the taskman record in the form_taskman table.
 */
function update_taskman($task, $action, $value)
{
    global $send;
    if ($action == 'created') {
        $sql = "UPDATE form_taskman set DOC_ID=?,COMMENT=concat('Created: ',NOW()) where ID=?";
        sqlQuery($sql, array($task['DOC_ID'],$task['ID']));
        $send['comments'] .= "Document created.";
    }

    if ($action == 'completed') {
        $sql = "UPDATE form_taskman set DOC_ID=?,COMPLETED =?,COMPLETED_DATE=NOW(),COMMENT=concat(COMMENT,'Completed: ', NOW(),'\n') where ID=?";
        sqlQuery($sql, array($task['DOC_ID'],$value,$task['ID']));
        $send['comments'] .= "Task completed.\n";
    }

    if ($action == 'refaxed') {
        $sql = "UPDATE form_taskman set DOC_ID=?,COMPLETED =?,COMPLETED_DATE=NOW(),COMMENT=concat(COMMENT,'Refaxed: ', NOW(),'\n') where ID=?";
        sqlQuery($sql, array($task['DOC_ID'],$value,$task['ID']));
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

    $query          = "SELECT * FROM users WHERE id=?";
    $to_data        =  sqlQuery($query, array($task['TO_ID']));
    $from_data      =  sqlQuery($query, array($task['FROM_ID']));
    $facility_data  =  $facilityService->getPrimaryBillingLocation();
    $query          = "SELECT * FROM patient_data where pid=?";
    $patientData    =  sqlQuery($query, array($task['PATIENT_ID']));

    $from_fax   = preg_replace("/[^0-9]/", "", $facility_data['fax']);
    $from_name  = $from_data['fname'] . " " . $from_data['lname'];
    $from_fac   = $from_facility['name'];
    $to_fax     = preg_replace("/[^0-9]/", "", $to_data['fax']);

    $to_name    = $to_data['fname'] . " " . $to_data['lname'];
    $pt_name    = $patientData['fname'] . ' ' . $patientData['lname'];

    $encounter = $task['ENC_ID'];

    $mail = new MyMailer();

    $to_email = $to_fax . "@" . $GLOBALS['hylafax_server'];
    $email_sender = $GLOBALS['patient_reminder_sender_email'];
    //consider using admin email = Notification Email Address
    //this must be a fax server approved From: address
    $file_to_attach =   preg_replace('/^file:\/\//', "", $task['DOC_url']);
    $file_name =  preg_replace('/^.*\//', "", $task['DOC_url']);

    $mail->AddReplyTo($email_sender, $from_name);
    $mail->SetFrom($email_sender, $from_name);
    $mail->AddAddress($to_email); //, $to_name);
    $mail->Subject = $from_fax;
    $mail->MsgHTML("<html><HEAD> <TITLE>Fax Central OpenEMR</TITLE> </HEAD><body><div class='wrapper'>" . $cover_page . "</div></body></html>");
    $mail->IsHTML(true);
    $mail->AltBody = $cover_page;
    $mail->AddAttachment($file_to_attach, $file_name);
    if ($mail->Send()) {
        return true;
    } else {
        $email_status = $mail->ErrorInfo;
        error_log("EMAIL ERROR: " . errorLogEscape($email_status), 0);
        return false;
    }
}


/**
 *  This function will display the form_taskman table as requested, by date or by status?
 *  Currently it is not used.
 */
function show_task($ajax_req)
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

    /**
     * We want to store the current PDF version of this task.
     */
    $query          = "SELECT * FROM users WHERE id=?";
    $to_data        =  sqlQuery($query, array($task['TO_ID']));
    $from_data      =  sqlQuery($query, array($task['FROM_ID']));
    $facility_data  =  $facilityService->getPrimaryBillingLocation();
    $query          = "SELECT * FROM patient_data where pid=?";
    $patientData    =  sqlQuery($query, array($task['PATIENT_ID']));

    $from_fax   = preg_replace("/[^0-9]/", "", $facility_data['fax']);
    $from_name  = $from_data['fname'] . " " . $from_data['lname'];
    if ($from_data['suffix']) {
        $from_name .= ", " . $from_data['suffix'];
    }

    $from_fac   = $from_facility['name'];
    $to_fax     = preg_replace("/[^0-9]/", "", $to_data['fax']);
    $to_name    = $to_data['fname'] . " " . $to_data['lname'];
    if ($to_data['suffix']) {
        $to_name .= ", " . $to_data['suffix'];
    }

    $pt_name    = $patientData['fname'] . ' ' . $patientData['lname'];
    $encounter = $task['ENC_ID'];
    $query = "select  *,form_encounter.date as encounter_date

               from forms,form_encounter,form_eye_base,
                form_eye_hpi,form_eye_ros,form_eye_vitals,
                form_eye_acuity,form_eye_refraction,form_eye_biometrics,
                form_eye_external, form_eye_antseg,form_eye_postseg,
                form_eye_neuro,form_eye_locking
                    where
                    forms.deleted != '1'  and
                    forms.formdir='eye_mag' and
                    forms.encounter=form_encounter.encounter  and
                    forms.form_id=form_eye_base.id and
                    forms.form_id=form_eye_hpi.id and
                    forms.form_id=form_eye_ros.id and
                    forms.form_id=form_eye_vitals.id and
                    forms.form_id=form_eye_acuity.id and
                    forms.form_id=form_eye_refraction.id and
                    forms.form_id=form_eye_biometrics.id and
                    forms.form_id=form_eye_external.id and
                    forms.form_id=form_eye_antseg.id and
                    forms.form_id=form_eye_postseg.id and
                    forms.form_id=form_eye_neuro.id and
                    forms.form_id=form_eye_locking.id and
                    forms.encounter =? and
                    forms.pid=?";

    $encounter_data = sqlQuery($query, array($encounter,$task['PATIENT_ID']));
    @extract($encounter_data);
    $providerID  =  getProviderIdOfEncounter($encounter);
    $providerNAME = getProviderName($providerID);
    $dated = new DateTime($encounter_date);
    $dated = $dated->format('Y/m/d');
    $visit_date = oeFormatShortDate($dated);
    $pid = $task['PATIENT_ID'];
    $PDF_OUTPUT = '1';

    $filepath = $GLOBALS['oer_config']['documents']['repository'] . $task['PATIENT_ID'];

    //so far we make A "Report", one per encounter, and "Faxes", as many as we need per encounter.
    //So delete any prior report if that is what we are doing. and replace it.
    //If it is a fax, can we check to see if the report is already here, and if it is add it, or do we have to
    // always remake it?  For now, REMAKE IT...

    if (($task['DOC_TYPE'] == 'Fax') || ($task['DOC_TYPE'] == 'Fax-resend')) {
        $category_name = "Communication"; //Faxes are stored in the Documents->Communication category.  Do we need to translate this?
        //$category_name = xl('Communication');
        $query = "select id from categories where name =?";
        $ID = sqlQuery($query, array($category_name));
        $category_id = $ID['id'];

        $filename = "Fax_" . $encounter . "_" . $to_data['lname'] . ".pdf";
        while (file_exists($filepath . '/' . $filename)) {
            $count++;
            $filename = "FAX_" . $encounter . "_" . $to_data['lname'] . "_" . $count . ".pdf";
        }
    } else {
        $category_name = "Encounters - Eye";
        //$category_name = "Encounters";
        // $category_name = "Encounters - Eye"; //<---- openEMR base requires this.  My Category names don't use the "- Eye" part...
        // Should the end user change the Document Category Names, this as is will fail.
        // Is this an issue also for foreign language users?
        //  -- perhaps not as I suspect translations occur after DB work just before display to end user.
        // Maybe we should search category_names for the first match to 'Encounters%' and use that id?
        $query = "select id from categories where name =?";
        $ID = sqlQuery($query, array($category_name));
        $category_id = $ID['id'];

        $filename = "Report_" . $encounter . ".pdf";
        foreach (glob($filepath . '/' . $filename) as $file) {
            unlink($file); //maybe shorten to just unlink($filepath.'/'.$filename); - well this does test to see if it is there
        }

        $sql = "DELETE from categories_to_documents where document_id IN (SELECT id from documents where documents.url like ?)";
        sqlQuery($sql, array("%" . $filename));
        $sql = "DELETE from documents where documents.url like ?";
        sqlQuery($sql, array("%" . $filename));
    }

    $config_mpdf = array(
        'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
        'mode' => $GLOBALS['pdf_language'],
        'format' => $GLOBALS['pdf_size'],
        'default_font_size' => '9',
        'default_font' => '',
        'margin_left' => $GLOBALS['pdf_left_margin'],
        'margin_right' => $GLOBALS['pdf_right_margin'],
        'margin_top' => $GLOBALS['pdf_top_margin'],
        'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
        'margin_header' => '',
        'margin_footer' => '',
        'orientation' => $GLOBALS['pdf_layout'],
        'shrink_tables_to_fit' => 1,
        'use_kwt' => true,
        'keep_table_proportions' => true
    );

    $pdf = new mPDF($config_mpdf);
    if ($_SESSION['language_direction'] == 'rtl') {
        $pdf->SetDirectionality('rtl');
    }

    ob_start();
    ?><html>
    <head>
        <TITLE><?php echo xlt('Taskman: Documents in openEMR'); ?></TITLE>
        <style>
            .wrapper {
                margin:20px;
            }
            .col1 {
                font-weight:bold;
                width:100px;
                padding:10px;
                text-align:right;
            }
            .col2 {
                width:375px;
                padding:10px;
            }
        </style>
        <link rel="stylesheet" href="<?php echo $webserver_root; ?>/interface/themes/style_pdf.css">
    </head>
    <body>
    <?php
    if (($task['DOC_TYPE'] == 'Fax') || ($task['DOC_TYPE'] == 'Fax-resend')) {
        ?>
        <div class='wrapper'>
        <?php echo report_header($task['PATIENT_ID'], 'PDF'); ?>
            <br />
            <br />
            <br />
            <br />
            <br />
            <br />
            <br />
            <hr />
            <table>
                <tr>
                <td class='col1'><?php echo xlt('From'); ?>:</td>
                    <td class='col2'>
                    <?php echo text($from_name); ?><br />

                    </td>
                </tr>
                <tr>
                <td class='col1'><?php echo xlt('Address'); ?>:</td>
                    <td class='col2'>
                    <?php if ($from_data['name']) {
                        echo text($from_data['name']) . "<br />";
                    } ?>
                    <?php echo text($from_data['street']); ?><br />
                    <?php echo text($from_data['city']); ?>, <?php echo text($from_data['state']) . " " . text($from_data['zip']); ?>
                        <br />
                    </td>
                </tr>
                <tr>
                    <td class='col1'>
                    <?php echo xlt('Phone'); ?>:
                    </td>
                    <td class='col2'>
                    <?php echo text($from_data['phonew1']); ?>
                    </td>
                </tr>
                <tr>
                    <td class='col1'>
                    <?php echo xlt('Fax'); ?>:
                    </td>
                <td class='col2'><?php echo text($from_data['fax']); ?><br />
                    </td>
                </tr>
                <tr>
                <td class='col1'><?php echo xlt('To{{Destination}}'); ?>:</td>
                <td class='col2'><?php echo text($to_name); ?></td>
                </tr>
                <tr>
                <td class='col1'><?php echo xlt('Address'); ?>:</td>
                    <td class='col2'>
                    <?php echo text($to_data['street']) . "<br />
				 			" . text($to_data['city']) . ", " . text($to_data['state']) . " " . text($to_data['zip']); ?>
                            <br />
                        </td>
                    </tr>
                    <tr>
                        <td class='col1'>
                            <?php echo xlt('Phone'); ?>:
                        </td>
                        <td class='col2'>
                            <?php echo text($to_data['phonew1']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class='col1'>
                            <?php echo xlt('Fax'); ?>:
                        </td>
                        <td class='col2'>
                            <?php echo text($to_data['fax']); ?>
                        </td>
                    </tr>
                    <tr><td colspan="2"><br /><hr /></td></tr>
                    <tr>
                        <td class='col1'>
                            <?php echo xlt('Comments'); ?>:
                        </td>
                        <td class='col2'><?php echo xlt('Report of visit'); ?>: <?php echo text($pt_name); ?> on <?php echo text($visit_date); ?>
                        </td>
                    </tr>
            </table>
        </div>
        <?php
        echo '<page></page><div style="page-break-after:always; clear:both"></div>';
    }

        //  If the doc_id does exit, why remake it?
        //  We could just add another attachment, stopping here at the coversheet, and adding the doc_name that we sent...
        //  No.  We actually need a physical copy of what we sent, since the report itself can be overwritten.  Keep it legal.
        //  Unless the Report.pdf can be merged with the cover sheet.  Until then, just redo it all.
        $tmp_files_remove = array();
        echo narrative($pid, $encounter, $task['DOC_TYPE'], $form_id);
    ?>
    </body>
    </html>
    <?php
    global $web_root, $webserver_root;
    $content = ob_get_clean();

    // Fix a nasty html2pdf bug - it ignores document root!
    $i = 0;
    $wrlen = strlen($web_root);
    $wsrlen = strlen($webserver_root);
    while (true) {
        $i = stripos($content, " src='/", $i + 1);
        if ($i === false) {
            break;
        }

        if (
            substr($content, $i + 6, $wrlen) === $web_root &&
            substr($content, $i + 6, $wsrlen) !== $webserver_root
        ) {
            $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
        }
    }

    // Below is for including style sheet for report specific styles. Left here for future use.
    //$styles = file_get_contents('../css/report.css');
    //$pdf->writeHTML($styles, 1);
    //$pdf->writeHTML($content, 2);

    $pdf->writeHTML($content);

    // tmp file in temporary_files_dir
    $temp_filename = tempnam($GLOBALS['temporary_files_dir'], "oer");
    $pdf->Output($temp_filename, 'F');
    foreach ($tmp_files_remove as $tmp_file) {
        // Remove the tmp files that were created
        unlink($tmp_file);
    }

    $type = "application/pdf";
    $size = filesize($temp_filename);
    $return = addNewDocument($filename, $type, $temp_filename, 0, $size, $task['FROM_ID'], $task['PATIENT_ID'], $category_id);
    unlink($temp_filename);

    $task['DOC_ID'] = $return['doc_id'];
    $task['DOC_url'] = $filepath . '/' . $filename;
    $sql = "UPDATE documents set encounter_id=? where id=?"; //link it to this encounter
    sqlQuery($sql, array($encounter,$task['DOC_ID']));

    return $task;
}
