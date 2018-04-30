<?php
/**
 * This the first of two pages to support posting of EOBs.
 * The second is sl_eob_invoice.php.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Bill Cernansky
 * @author    Tony McCormick
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
use OpenEMR\Core\Header;

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/appointments.inc.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");
require_once("$srcdir/parse_era.inc.php");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/../controllers/C_Document.class.php");
require_once("$srcdir/documents.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");

$DEBUG = 0; // set to 0 for production, 1 to test

$alertmsg = '';
$where = '';
$eraname = '';
$eracount = 0;
/* Load dependencies only if we need them */
if (! empty($GLOBALS['portal_onsite_two_enable'])) {
    /* Addition of onsite portal patient notify of invoice and reformated invoice - sjpadgett 01/2017 */
    require_once("../../portal/lib/portal_mail.inc");
    require_once("../../portal/lib/appsql.class.php");

    function is_auth_portal($pid = 0)
    {
        if ($pData = sqlQuery("SELECT * FROM `patient_data` WHERE `pid` = ?", array($pid))) {
            if ($pData['allow_patient_portal'] != "YES") {
                return false;
            } else {
                $_SESSION['portalUser'] = strtolower($pData['fname']) . $pData['id'];
                return true;
            }
        } else {
            return false;
        }
    }

    function notify_portal($thispid, array $invoices, $template, $invid)
    {
        $builddir = $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/' . $thispid;
        if (! is_dir($builddir)) {
            mkdir($builddir, 0755, true);
        }

        if (fixup_invoice($template, $builddir . '/invoice' . $invid . '.tpl') != true) {
            return false;
        }

        if (SavePatientAudit($thispid, $invoices) != true) {
            return false;
        } // this is all the invoice data for portal auditing
        $note = xl('You have an invoice due for payment in your Patient Documents. There you may pay, download or print the invoice. Thank you.');
        if (sendMail($_SESSION['authUser'], $note, xlt('Bill/Collect'), '', '0', $_SESSION['authUser'], $_SESSION['authUser'], $_SESSION['portalUser'], $invoices[0]['patient'], "New", '0') == 1) { // remind admin this was sent
            sendMail($_SESSION['portalUser'], $note, xlt('Bill/Collect'), '', '0', $_SESSION['authUser'], $_SESSION['authUser'], $_SESSION['portalUser'], $invoices[0]['patient'], "New", '0'); // notify patient
        } else {
            return false;
        }

        return true;
    }

    function fixup_invoice($template, $ifile)
    {
        $data = file_get_contents($template);
        if ($data == "") {
            return false;
        }

        if (! file_put_contents($ifile, $data)) {
            return false;
        }

        return true;
    }

    function SavePatientAudit($pid, $invs)
    {
        $appsql = new ApplicationTable();
        try {
            $audit = array();
            $audit['patient_id'] = $pid;
            $audit['activity'] = "invoice";
            $audit['require_audit'] = "0";
            $audit['pending_action'] = "payment";
            $audit['action_taken'] = "";
            $audit['status'] = "waiting transaction";
            $audit['narrative'] = "Request patient online payment.";
            $audit['table_action'] = '';
            $audit['table_args'] = json_encode($invs);
            $audit['action_user'] = $pid;
            $audit['action_taken_time'] = "";
            $audit['checksum'] = "";
            $edata = $appsql->getPortalAudit($pid, 'payment', 'invoice', "waiting transaction", 0);
            if ($edata['id'] > 0) {
                $appsql->portalAudit('update', $edata['id'], $audit);
            } else {
                $appsql->portalAudit('insert', '', $audit);
            }
        } catch (Exception $ex) {
            return $ex;
        }

        return true;
    }
}

// This is called back by parse_era() if we are processing X12 835's.
function era_callback(&$out)
{
    global $where, $eracount, $eraname;
  // print_r($out); // debugging
    ++$eracount;
  // $eraname = $out['isa_control_number'];
    $eraname = $out['gs_date'] . '_' . ltrim($out['isa_control_number'], '0') .
    '_' . ltrim($out['payer_id'], '0');
    list($pid, $encounter, $invnumber) = slInvoiceNumber($out);

    if ($pid && $encounter) {
        if ($where) {
            $where .= ' OR ';
        }

        $where .= "( f.pid = '$pid' AND f.encounter = '$encounter' )";
    }
}

function bucks($amount)
{
    if ($amount) {
        echo oeFormatMoney($amount);
    }
}

function validEmail($email)
{
    if (preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email)) {
        return true;
    }

    return false;
}

function emailLogin($patient_id, $message)
{
    $patientData = sqlQuery("SELECT * FROM `patient_data` WHERE `pid`=?", array($patient_id));
    if ($patientData['hipaa_allowemail'] != "YES" || empty($patientData['email']) || empty($GLOBALS['patient_reminder_sender_email'])) {
        return false;
    }

    if (!(validEmail($patientData['email']))) {
        return false;
    }

    if (!(validEmail($GLOBALS['patient_reminder_sender_email']))) {
        return false;
    }

    if ($_SESSION['pc_facility']) {
        $sql = "select * from facility where id=?";
        $facility = sqlQuery($sql, array($_SESSION['pc_facility']));
    } else {
        $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
        $facility = sqlQuery($sql);
    }

    $mail = new MyMailer();
    $pt_name=$patientData['fname'].' '.$patientData['lname'];
    $pt_email=$patientData['email'];
    $email_subject=($facility['name'] . ' ' . xl('Patient Statement Bill'));
    $email_sender=$GLOBALS['patient_reminder_sender_email'];
    $mail->AddReplyTo($email_sender, $email_sender);
    $mail->SetFrom($email_sender, $email_sender);
    $mail->AddAddress($pt_email, $pt_name);
    $mail->Subject = $email_subject;
    $mail->MsgHTML("<html><body><div class='wrapper'>".$message."</div></body></html>");
    $mail->IsHTML(true);
    $mail->AltBody = $message;

    if ($mail->Send()) {
        return true;
    } else {
        $email_status = $mail->ErrorInfo;
        error_log("EMAIL ERROR: ".$email_status, 0);
        return false;
    }
}

// Upload a file to the client's browser
//
function upload_file_to_client($file_to_send)
{
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Length: " . filesize($file_to_send));
    header("Content-Disposition: attachment; filename=" . basename($file_to_send));
    header("Content-Description: File Transfer");
    readfile($file_to_send);
  // flush the content to the browser. If you don't do this, the text from the subsequent
  // output from this script will be in the file instead of sent to the browser.
    flush();
    exit(); //added to exit from process properly in order to stop bad html code -ehrlive
  // sleep one second to ensure there's no follow-on.
    sleep(1);
}

function upload_file_to_client_email($ppid, $file_to_send)
{
    $message = "";
    global $STMT_TEMP_FILE_PDF;
    $file = fopen($file_to_send, "r");//this file contains the text to be converted to pdf.
    while (!feof($file)) {
        $OneLine=fgets($file);//one line is read

         $message = $message.$OneLine.'<br>';

        $countline++;
    }

    emailLogin($ppid, $message);
}

function upload_file_to_client_pdf($file_to_send, $aPatFirstName = '', $aPatID = null, $flagCFN = false)
{
 //modified for statement title name
  //Function reads a HTML file and converts to pdf.

    $aPatFName = convert_safe_file_dir_name($aPatFirstName); //modified for statement title name
    if ($flagCFN) {
        $STMT_TEMP_FILE_PDF = $GLOBALS['temporary_files_dir'] . "/Stmt_{$aPatFName}_{$aPatID}.pdf";
    } else {
        global $STMT_TEMP_FILE_PDF;
    }

    global $srcdir;

    if ($GLOBALS['statement_appearance'] == '1') {
        require_once("$srcdir/html2pdf/vendor/autoload.php");
        $pdf2 = new HTML2PDF(
            $GLOBALS['pdf_layout'],
            $GLOBALS['pdf_size'],
            $GLOBALS['pdf_language'],
            true, // default unicode setting is true
            'UTF-8', // default encoding setting is UTF-8
            array($GLOBALS['pdf_left_margin'], $GLOBALS['pdf_top_margin'], $GLOBALS['pdf_right_margin'], $GLOBALS['pdf_bottom_margin']),
            $_SESSION['language_direction'] == 'rtl' ? true : false
        );
        ob_start();
        readfile($file_to_send, "r");//this file contains the HTML to be converted to pdf.
        //echo $file;
        $content = ob_get_clean();

        // Fix a nasty html2pdf bug - it ignores document root!
        global $web_root, $webserver_root;
        $i = 0;
        $wrlen = strlen($web_root);
        $wsrlen = strlen($webserver_root);
        while (true) {
              $i = stripos($content, " src='/", $i + 1);
            if ($i === false) {
                break;
            }

            if (substr($content, $i+6, $wrlen) === $web_root &&
              substr($content, $i+6, $wsrlen) !== $webserver_root) {
                $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
            }
        }

        $pdf2->WriteHTML($content);
        $temp_filename = $STMT_TEMP_FILE_PDF;
        $content_pdf = $pdf2->Output($STMT_TEMP_FILE_PDF, 'F');
    } else {
        $pdf = new Cezpdf('LETTER');//pdf creation starts
        $pdf->ezSetMargins(45, 9, 36, 10);
        $pdf->selectFont('Courier');
        $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
        $countline=1;
        $file = fopen($file_to_send, "r");//this file contains the text to be converted to pdf.
        while (!feof($file)) {
            $OneLine=fgets($file);//one line is read
            if (stristr($OneLine, "\014") == true && !feof($file)) {//form feed means we should start a new page.
                $pdf->ezNewPage();
                $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
                str_replace("\014", "", $OneLine);
            }

            if (stristr($OneLine, 'REMIT TO') == true || stristr($OneLine, 'Visit Date') == true || stristr($OneLine, 'Future Appointments') == true || stristr($OneLine, 'Current') == true) { //lines are made bold when 'REMIT TO' or 'Visit Date' is there.
                $pdf->ezText('<b>'.$OneLine.'</b>', 12, array('justification' => 'left', 'leading' => 6));
            } else {
                $pdf->ezText($OneLine, 12, array('justification' => 'left', 'leading' => 6));
            }

            $countline++;
        }

        $fh = @fopen($STMT_TEMP_FILE_PDF, 'w');//stored to a pdf file
        if ($fh) {
            fwrite($fh, $pdf->ezOutput());
            fclose($fh);
        }
    }

    header("Pragma: public");//this section outputs the pdf file to browser
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Length: " . filesize($STMT_TEMP_FILE_PDF));
    header("Content-Disposition: attachment; filename=" . basename($STMT_TEMP_FILE_PDF));
    header("Content-Description: File Transfer");
    readfile($STMT_TEMP_FILE_PDF);
  // flush the content to the browser. If you don't do this, the text from the subsequent
  // output from this script will be in the file instead of sent to the browser.
    flush();
    exit(); //added to exit from process properly in order to stop bad html code -ehrlive
  // sleep one second to ensure there's no follow-on.
    sleep(1);
}


$today = date("Y-m-d");
  // Print or download statements if requested.
  //
if (($_POST['form_print'] || $_POST['form_download'] || $_POST['form_email'] || $_POST['form_pdf']) || $_POST['form_portalnotify'] && $_POST['form_cb']) {
    $fhprint = fopen($STMT_TEMP_FILE, 'w');

    $sqlBindArray = array();
    $where = "";
    foreach ($_POST['form_cb'] as $key => $value) {
        $where .= " OR f.id = ?";
        array_push($sqlBindArray, $key);
    }

    if (!empty($where)) {
        $where = substr($where, 4);
        $where = '( ' . $where . ' ) AND';
    }

    $res = sqlStatement("SELECT " .
    "f.id, f.date, f.pid, f.encounter, f.stmt_count, f.last_stmt_date, f.last_level_closed, f.last_level_billed, f.billing_note as enc_billing_note, " .
    "p.fname, p.mname, p.lname, p.street, p.city, p.state, p.postal_code, p.billing_note as pat_billing_note " .
    "FROM form_encounter AS f, patient_data AS p " .
    "WHERE $where " .
    "p.pid = f.pid " .
    "ORDER BY p.lname, p.fname, f.pid, f.date, f.encounter", $sqlBindArray);

    $stmt = array();
    $stmt_count = 0;

    $flagT = true;
    $aPatientFirstName = '';
    $aPatientID = null;
    $multiplePatients = false;
    $usePatientNamePdf = false;

    // get pids for delimits
    // need to only use summary invoice for multi visits
    $inv_pid = array();
    $inv_count = -1;
    if ($_POST['form_portalnotify']) {
        foreach ($_POST['form_invpids'] as $key => $v) {
            if ($_POST['form_cb'][$key]) {
                array_push($inv_pid, key($v));
            }
        }
    }
    $rcnt = 0;
    while ($row = sqlFetchArray($res)) {
        $rows[] = $row;
        if (!$inv_pid[$rcnt]) {
            array_push($inv_pid, $row['pid']);
        }
        $rcnt++;
    }
   // This loops once for each invoice/encounter.
   //
    $rcnt = 0;
    while ($row = $rows[$rcnt++]) {
        $svcdate = substr($row['date'], 0, 10);
        $duedate = $svcdate; // TBD?
        $duncount = $row['stmt_count'];
        $enc_note = $row['enc_billing_note'];

        if ($flagT) {
            $flagT = false;
            $aPatientFirstName = $row['fname'];
            $aPatientID = $row['pid'];
            $usePatientNamePdf = true;
        } elseif (!$multiplePatients) {
            if ($aPatientID != $row['pid']) {
                $multiplePatients = true;
                $aPatientFirstName = '';
                $aPatientID = null;
                $usePatientNamePdf = false;
            }
        }

       // If this is a new patient then print the pending statement
       // and start a new one.  This is an associative array:
       //
       //  cid     = same as pid
       //  pid     = OpenEMR patient ID
       //  patient = patient name
       //  amount  = total amount due
       //  adjust  = adjustments (already applied to amount)
       //  duedate = due date of the oldest included invoice
       //  age     = number of days from duedate to today
       //  to      = array of addressee name/address lines
       //  lines   = array of:
       //    dos     = date of service "yyyy-mm-dd"
       //    desc    = description
       //    amount  = charge less adjustments
       //    paid    = amount paid
       //    notice  = 1 for first notice, 2 for second, etc.
       //    detail  = array of details, see invoice_summary.inc.php
       //
        if ($stmt['cid'] != $row['pid']) {
            if (!empty($stmt)) {
                ++$stmt_count;
            }

            $stmt['cid'] = $row['pid'];
            $stmt['pid'] = $row['pid'];
            $stmt['dun_count'] = $row['stmt_count'];
            $stmt['bill_note'] = $row['pat_billing_note'];
            $stmt['enc_bill_note'] = $row['enc_billing_note'];
            $stmt['bill_level'] = $row['last_level_billed'];
            $stmt['level_closed'] = $row['last_level_closed'];
            $stmt['patient'] = $row['fname'] . ' ' . $row['lname'];
            $stmt['encounter'] = $row['encounter'];
            #If you use the field in demographics layout called
            #guardiansname this will allow you to send statements to the parent
            #of a child or a guardian etc
            if (strlen($row['guardiansname']) == 0) {
                $stmt['to'] = array($row['fname'] . ' ' . $row['lname']);
            } else {
                $stmt['to'] = array($row['guardiansname']);
            }

            if ($row['street']) {
                $stmt['to'][] = $row['street'];
            }

            $stmt['to'][] = $row['city'] . ", " . $row['state'] . " " . $row['postal_code'];
            $stmt['lines'] = array();
            $stmt['amount'] = '0.00';
            $stmt['ins_paid'] = 0;
            $stmt['today'] = $today;
            $stmt['duedate'] = $duedate;
        } else {
            // Report the oldest due date.
            if ($duedate < $stmt['duedate']) {
                $stmt['duedate'] = $duedate;
            }
        }

        // Recompute age at each invoice.
        $stmt['age'] = round((strtotime($today) - strtotime($stmt['duedate'])) / (24 * 60 * 60));

        $invlines = ar_get_invoice_summary($row['pid'], $row['encounter'], true);
        foreach ($invlines as $key => $value) {
            $line = array();
            $line['dos']     = $svcdate;
            if ($GLOBALS['use_custom_statement']) {
                $line['desc']    = ($key == 'CO-PAY') ? "Patient Payment" : $value['code_text'];
            } else {
                $line['desc']    = ($key == 'CO-PAY') ? "Patient Payment" : "Procedure $key";
            }

             $line['amount']  = sprintf("%.2f", $value['chg']);
             $line['adjust']  = sprintf("%.2f", $value['adj']);
             $line['paid']    = sprintf("%.2f", $value['chg'] - $value['bal']);
             $line['notice']  = $duncount + 1;
             $line['detail']  = $value['dtl'];
             $stmt['lines'][] = $line;
             $stmt['amount']  = sprintf("%.2f", $stmt['amount'] + $value['bal']);
             $stmt['ins_paid']  = $stmt['ins_paid'] + $value['ins'];
        }

        // Record that this statement was run.
        if (! $DEBUG && ! $_POST['form_without']) {
            sqlStatement("UPDATE form_encounter SET " .
            "last_stmt_date = '$today', stmt_count = stmt_count + 1 " .
            "WHERE id = " . $row['id']);
        }
        $inv_count += 1;
        if ($_POST['form_portalnotify']) {
            if (! is_auth_portal($stmt['pid'])) {
                $alertmsg = xlt('Notification FAILED: Not Portal Authorized');
                break;
            }
            $pvoice[] = $stmt;
            // we don't want to send the portal multiple invoices, thus this. Last invoice for pid is summary.
            if ($inv_pid[$inv_count] != $inv_pid[$inv_count + 1]) {
                fwrite($fhprint, make_statement($stmt));
                if (! notify_portal($stmt['pid'], $pvoice, $STMT_TEMP_FILE, $stmt['pid'] . "-" . $stmt['encounter'])) {
                    $alertmsg = xlt('Notification FAILED');
                    break;
                }

                $pvoice = array();
                flush();
                ftruncate($fhprint, 0);
            } else {
                continue;
            }
        } else {
            if ($inv_pid[$inv_count] != $inv_pid[$inv_count + 1]) {
                $tmp = make_statement($stmt);
                if (empty($tmp)) {
                    $tmp = xlt("This EOB item does not meet minimum print requirements setup in Globals or there is an unknown error.") . " " . xlt("EOB Id") . ":" . $inv_pid[$inv_count] . " " . xlt("Encounter") . ":" . $stmt[encounter] . "\n";
                    $tmp .= "<br />\n\014<br /><br />";
                }
                fwrite($fhprint, $tmp);
            }
        }
    } // end while

    if (!empty($stmt)) {
        ++$stmt_count;
    }

    fclose($fhprint);
    sleep(1);
    // Download or print the file, as selected
    if ($_POST['form_download']) {
        upload_file_to_client($STMT_TEMP_FILE);
    } elseif ($_POST['form_pdf']) {
        upload_file_to_client_pdf($STMT_TEMP_FILE, $aPatientFirstName, $aPatientID, $usePatientNamePdf);
    } elseif ($_POST['form_email']) {
                upload_file_to_client_email($stmt['pid'], $STMT_TEMP_FILE);
    } elseif ($_POST['form_portalnotify']) {
        if ($alertmsg == "") {
            $alertmsg = xl('Sending Invoice to Patient Portal Completed');
        }
    } else { // Must be print!
        if ($DEBUG) {
            $alertmsg = xl("Printing skipped; see test output in") .' '. $STMT_TEMP_FILE;
        } else {
            exec("$STMT_PRINT_CMD $STMT_TEMP_FILE");
            if ($_POST['form_without']) {
                $alertmsg = xl('Now printing') .' '. $stmt_count .' '. xl('statements; invoices will not be updated.');
            } else {
                $alertmsg = xl('Now printing') .' '. $stmt_count .' '. xl('statements and updating invoices.');
            }
        } // end not debug
    } // end not form_download
} // end statements requested
    ?>
  <html>
  <head>
        <?php Header::setupHeader(['datetime-picker']);?>
    <title><?php xl('EOB Posting - Search', 'e'); ?></title>
    <script language="JavaScript">
    var mypcc = '1';

    function checkAll(checked) {
        var f = document.forms[0];
        for (var i = 0; i < f.elements.length; ++i) {
        var ename = f.elements[i].name;
        if (ename.indexOf('form_cb[') == 0)
        f.elements[i].checked = checked;
        }
    }

    function npopup(pid) {
    window.open('sl_eob_patient_note.php?patient_id=' + pid, '_blank', 'width=500,height=250,resizable=1');
    return false;
    }
    $(document).ready(function() {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });

 </script>
 <style>
    @media only screen and (max-width: 768px) {
       [class*="col-"] {
       width: 100%;
       text-align:left!Important;
        }
    }
    @media only screen and (max-width: 1004px) and (min-width: 641px)  {
        .oe-large {
            display: none;
        }
        .oe-small {
            display: inline-block;
        }
    }
    @media print {
      body * {
        visibility: hidden;
      }
    .modal-body, .modal-body * {
        visibility: visible;
      }
      .modal-body {
        position: absolute;
        left: 0;
        top: 0;
        width:100%;
        height:10000px;
       }
    }
    .superscript {
        position: relative; 
        top: -0.5em; 
        font-size: 70%;
    }
</style>

</head>

<body>
    <div class="container">
        <div class="row">
             <div class="page-header">
                <h2 class="clearfix"><span id='header_text'><?php echo xlt('EOB Posting - Search'); ?></span>&nbsp;&nbsp;  <a href='sl_eob_search.php' onclick='top.restoreSession()'  title="<?php echo xlt('Reset'); ?>"><i id='advanced-tooltip' class='fa fa-undo fa-2x small' aria-hidden='true'></i> </a><a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#000000"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
            </div>
        </div>
        <div class="row">
            <form action='sl_eob_search.php' enctype='multipart/form-data' method='post'>
                <fieldset id="payment-allocate" class="oe-show-hide">
                    <legend>
                        &nbsp;<?php echo xlt('Post Item');?><i id="payment-info-do-not-remove"> </i>
                    </legend>
                    <div class="col-xs-12 oe-custom-line">
                        <div class="col-xs-3">
                            <label class="control-label" for="form_payer_id"> <?php echo xlt('Payer'); ?>:</label>
                            <?php
                                $insurancei = getInsuranceProviders();
                                echo "   <select name='form_payer_id'id='form_payer_id' class='form-control'>\n";
                                echo "    <option value='0'>-- " . xl('Patient') . " --</option>\n";
                            foreach ($insurancei as $iid => $iname) {
                                echo "<option value='$iid'";
                                if ($iid == $_POST['form_payer_id']) {
                                    echo " selected";
                                }
                                echo ">" . $iname . "</option>\n";
                            }
                                echo "   </select>\n";
                            ?>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_source"><?php echo xlt('Source'); ?>:</label>
                            <input type='text' name='form_source' id='form_source' class='form-control' value='<?php echo attr($_POST['form_source']); ?>' title='<?php echo xlt("A check number or claim number to identify the payment"); ?>'>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_paydate"><?php echo xlt('Pay Date'); ?>:</label>
                            <input type='text' name='form_paydate' id='form_paydate' class='form-control datepicker' value='<?php echo attr($_POST['form_paydate']); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php xlt("Date of payment yyyy-mm-dd"); ?>'>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label oe-large" for="form_deposit_date"><?php echo xlt('Deposit Date'); ?>:</label>
                            <label class="control-label oe-small" for="form_deposit_date"><?php echo xlt('Dep Date'); ?>:</label>
                            <input type='text' name='form_deposit_date' id=='form_deposit_date' class='form-control datepicker' value='<?php echo attr($_POST['form_deposit_date']); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php xlt("Date of bank deposit yyyy-mm-dd"); ?>'>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_amount"><?php echo xlt('Amount'); ?>:</label>
                            <input type='text' name='form_amount' id='form_amount'  class='form-control' value='<?php echo attr($_POST['form_amount']); ?>' title='<?php xlt("Paid amount that you will allocate"); ?>'>
                        </div>
                        <div class="col-xs-1">
                            <label class="control-label oe-large" for="only_with_debt"><?php echo xlt('Pt Debt');?>:</label>
                            <label class="control-label oe-small" for="only_with_debt"><?php echo xlt('Debt');?>:</label>
                            <div class="text-center">
                                <input <?php echo $_POST['only_with_debt']?'checked=checked':'';?> type="checkbox" name="only_with_debt" id="only_with_debt" />
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset id="search-upload">
                    <legend>
                        &nbsp;<span><?php echo xlt('Select Method');?></span>&nbsp;<i id='select-method-tooltip' class="fa fa-info-circle superscript" aria-hidden="true"></i>
                        <div id="radio-div" class="pull-right oe-legend-radio">
                                <label class="radio-inline">
                                  <input type="radio" id="invoice_search" name="radio-search" onclick="" value="inv-search"><?php echo xlt('Invoice Search'); ?> 
                                </label>
                                <label class="radio-inline">
                                  <input type="radio" id="era_upload" name="radio-search" onclick=""  value="era-upld"><?php echo xlt('ERA Upload'); ?>
                                </label>
                        </div>
                        
                        <input type="hidden" id="hid1" value="<?php echo xlt('Invoice Search');?>">
                        <input type="hidden" id="hid2" value="<?php echo xlt('ERA Upload');?>">
                        <input type="hidden" id="hid3" value="<?php echo xlt('Select Method');?>">
                    </legend>
                    <div class="col-xs-12 .oe-custom-line oe-show-hide" id = 'inv-search'>
                        <div class="col-xs-3">
                            <label class="control-label" for="form_name"><?php echo xlt('Name'); ?>:</label>
                            <input type='text' name='form_name' id='form_name' class='form-control' value='<?php echo attr($_POST['form_name']); ?>' title='<?php xl("Any part of the patient name, or \"last,first\", or \"X-Y\"", "e"); ?>' placeholder= '<?php echo xlt('Last name, First name');?>'>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_pid"><?php echo xlt('Chart ID'); ?>:</label>
                            <input type='text' name='form_pid' id='form_pid' class='form-control' value='<?php echo attr($_POST['form_pid']); ?>' title='<?php xl("Patient chart ID", "e"); ?>'>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_encounter"><?php echo xlt('Encounter'); ?>:</label>
                            <input type='text' name='form_encounter' id='form_encounter' class='form-control' value='<?php echo attr($_POST['form_encounter']); ?>' title='<?php xl("Encounter number", "e"); ?>'>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label oe-large" for="form_date"><?php echo xlt('Service Date From'); ?>:</label>
                            <label class="control-label oe-small" for="form_date"><?php echo xlt('Svc Date'); ?>:</label>
                            <input type='text' name='form_date' id='form_date' class='form-control datepicker' value='<?php echo attr($_POST['form_date']); ?>' title='<?php xl("Date of service mm/dd/yyyy", "e"); ?>'>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_to_date"><?php echo xlt('Service Date To'); ?>:</label>
                            <input type='text' name='form_to_date' id='form_to_date' class='form-control datepicker' value='<?php echo attr($_POST['form_to_date']); ?>' title='<?php xl("Ending DOS mm/dd/yyyy if you wish to enter a range", "e"); ?>'>
                        </div>
                        <div class="col-xs-1" style="padding-right:0px">
                            <label class="control-label" for="type_name"><?php echo xlt('Type'); ?>:</label>
                            <select name='form_category' id='form_category' class='form-control'>
                                <?php
                                foreach (array(xl('Open'), xl('All'), xl('Due Pt'), xl('Due Ins')) as $value) {
                                    echo "    <option value='$value'";
                                    if ($_POST['form_category'] == $value) {
                                        echo " selected";
                                    }
                                    echo ">$value</option>\n";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 .oe-custom-line oe-show-hide" id = 'era-upld'>
                        <div class="form-group col-xs9 oe-file-div">
                            <div class="input-group"> 
                                <label class="input-group-btn">
                                    <span class="btn btn-default">
                                        Browse&hellip;<input type="file" id="uploadedfile" name="form_erafile" style="display: none;" >
                                        <input name="MAX_FILE_SIZE" type="hidden" value="5000000"> 
                                    </span>
                                </label>
                                <input type="text" class="form-control" placeholder="<?php echo xlt('Click Browse and select one Electronic Remittance Advice (ERA) file...'); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
                <div class="form-group clearfix">
                    <div class="col-sm-12 position-override oe-show-hide" id="search-btn">
                        <div class="btn-group" role="group">
                            <button type='submit' class="btn btn-default btn-search oe-show-hide" name='form_search' 
                            id="btn-inv-search" value='<?php echo xla("Search"); ?>'><?php echo xlt("Search"); ?></button>
                            <button type='submit' class="btn btn-default btn-save oe-show-hide" name='form_search' 
                            id="btn-era-upld" value='<?php echo xla("Upload"); ?>'><?php echo xlt("Upload"); ?></button>
                        </div>
                    </div>
                </div>
                <fieldset id="search-results" class= "oe-show-hide">
                    <legend><?php echo xlt('Search Results');?></legend>
                    <div class = "table-responsive">
                        <?php
                        if ($_POST['form_search'] || $_POST['form_print']) {
                            $form_name      = trim($_POST['form_name']);
                            $form_pid       = trim($_POST['form_pid']);
                            $form_encounter = trim($_POST['form_encounter']);
                            $form_date      = fixDate($_POST['form_date'], "");
                            $form_to_date   = fixDate($_POST['form_to_date'], "");

                            $where = "";

                            // Handle X12 835 file upload.
                            //
                            if ($_FILES['form_erafile']['size']) {
                                $tmp_name = $_FILES['form_erafile']['tmp_name'];

                                // Handle .zip extension if present.  Probably won't work on Windows.
                                if (strtolower(substr($_FILES['form_erafile']['name'], -4)) == '.zip') {
                                    rename($tmp_name, "$tmp_name.zip");
                                    exec("unzip -p $tmp_name.zip > $tmp_name");
                                    unlink("$tmp_name.zip");
                                }
                                
                                echo "<!-- Notes from ERA upload processing:\n";
                                $alertmsg .= parse_era($tmp_name, 'era_callback');
                                echo "-->\n";
                                $erafullname = $GLOBALS['OE_SITE_DIR'] . "/era/$eraname.edi";

                                if (is_file($erafullname)) {
                                    $alertmsg .= "Warning: Set $eraname was already uploaded ";
                                    if (is_file($GLOBALS['OE_SITE_DIR'] . "/era/$eraname.html")) {
                                        $alertmsg .= "and processed. ";
                                    } else {
                                        $alertmsg .= "but not yet processed. ";
                                    }
                                }
                                rename($tmp_name, $erafullname);
                            } // End 835 upload

                            if ($eracount) {
                                // Note that parse_era() modified $eracount and $where.
                                if (! $where) {
                                    $where = '1 = 2';
                                }
                            } else {
                                if ($form_name) {
                                    if ($where) {
                                        $where .= " AND ";
                                    }
                                    // Allow the last name to be followed by a comma and some part of a first name.
                                    if (preg_match('/^(.*\S)\s*,\s*(.*)/', $form_name, $matches)) {
                                        $where .= "p.lname LIKE '" . $matches[1] . "%' AND p.fname LIKE '" . $matches[2] . "%'";
                                        // Allow a filter like "A-C" on the first character of the last name.
                                    } elseif (preg_match('/^(\S)\s*-\s*(\S)$/', $form_name, $matches)) {
                                        $tmp = '1 = 2';
                                        while (ord($matches[1]) <= ord($matches[2])) {
                                            $tmp .= " OR p.lname LIKE '" . $matches[1] . "%'";
                                            $matches[1] = chr(ord($matches[1]) + 1);
                                        }
                                        $where .= "( $tmp ) ";
                                    } else {
                                        $where .= "p.lname LIKE '%$form_name%'";
                                    }
                                }
                                if ($form_pid) {
                                    if ($where) {
                                        $where .= " AND ";
                                    }
                                    $where .= "f.pid = '$form_pid'";
                                }
                                if ($form_encounter) {
                                    if ($where) {
                                        $where .= " AND ";
                                    }
                                    $where .= "f.encounter = '$form_encounter'";
                                }
                                if ($form_date) {
                                    if ($where) {
                                        $where .= " AND ";
                                    }
                                    if ($form_to_date) {
                                        $where .= "f.date >= '$form_date' AND f.date <= '$form_to_date'";
                                    } else {
                                        $where .= "f.date = '$form_date'";
                                    }
                                }
                                if (! $where) {
                                    if ($_POST['form_category'] == 'All') {
                                        die(xl("At least one search parameter is required if you select All."));
                                    } else {
                                        $where = "1 = 1";
                                    }
                                }
                            }

                            // Notes that as of release 4.1.1 the copays are stored
                            // in the ar_activity table marked with a PCP in the account_code column.
                            $query = "SELECT f.id, f.pid, f.encounter, f.date, " .
                            "f.last_level_billed, f.last_level_closed, f.last_stmt_date, f.stmt_count, " .
                            "p.fname, p.mname, p.lname, p.pubpid, p.billing_note, " .
                            "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
                            "b.pid = f.pid AND b.encounter = f.encounter AND " .
                            "b.activity = 1 AND b.code_type != 'COPAY' ) AS charges, " .
                            "( SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE " .
                            "a.pid = f.pid AND a.encounter = f.encounter AND a.payer_type = 0 AND a.account_code = 'PCP')*-1 AS copays, " .
                            "( SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE " .
                            "a.pid = f.pid AND a.encounter = f.encounter AND a.account_code != 'PCP') AS payments, " .
                            "( SELECT SUM(a.adj_amount) FROM ar_activity AS a WHERE " .
                            "a.pid = f.pid AND a.encounter = f.encounter ) AS adjustments " .
                            "FROM form_encounter AS f " .
                            "JOIN patient_data AS p ON p.pid = f.pid " .
                            "WHERE $where " .
                            "ORDER BY p.lname, p.fname, p.mname, f.pid, f.encounter";

                            // Note that unlike the SQL-Ledger case, this query does not weed
                            // out encounters that are paid up.  Also the use of sub-selects
                            // will require MySQL 4.1 or greater.

                            // echo "<!-- $query -->\n"; // debugging

                            $t_res = sqlStatement($query);

                            $num_invoices = sqlNumRows($t_res);
                            if ($eracount && $num_invoices != $eracount) {
                                $alertmsg .= "Of $eracount remittances, there are $num_invoices " .
                                "matching encounters in OpenEMR. ";
                            }
                        ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="id dehead"><?php xl('id', 'e');?></th>
                                    <th class="dehead">&nbsp;<?php xl('Patient', 'e'); ?></th>
                                    <th class="dehead">&nbsp;<?php xl('Invoice', 'e'); ?></th>
                                    <th class="dehead">&nbsp;<?php xl('Svc Date', 'e'); ?></th>
                                    <th class="dehead">&nbsp;<?php xl('Last Stmt', 'e'); ?></th>
                                    <th align="right" class="dehead"><?php xl('Charge', 'e'); ?>&nbsp;</th>
                                    <th align="right" class="dehead"><?php xl('Adjust', 'e'); ?>&nbsp;</th>
                                    <th align="right" class="dehead"><?php xl('Paid', 'e'); ?>&nbsp;</th>
                                    <th align="right" class="dehead"><?php xl('Balance', 'e'); ?>&nbsp;</th>
                                    <th align="center" class="dehead"><?php xl('Prv', 'e'); ?></th>
                                    <?php
                                    if (!$eracount) { ?>
                                    <th align="left" class="dehead"><?php xl('Sel', 'e'); ?></th>
                                    <th align="center" class="dehead"><?php xl('Email', 'e'); ?></th>
                                    <?php
                                    } ?>
                                </tr>
                            </thead>
                            <?php
                            $orow = -1;

                            while ($row = sqlFetchArray($t_res)) {
                                $balance = sprintf("%.2f", $row['charges'] + $row['copays'] - $row['payments'] - $row['adjustments']);
                                //new filter only patients with debt.
                                if ($_POST['only_with_debt'] && $balance <= 0) {
                                    continue;
                                }


                                if ($_POST['form_category'] != 'All' && $eracount == 0 && $balance == 0) {
                                    continue;
                                }

                                // $duncount was originally supposed to be the number of times that
                                // the patient was sent a statement for this invoice.
                                //
                                $duncount = $row['stmt_count'];

                                // But if we have not yet billed the patient, then compute $duncount as a
                                // negative count of the number of insurance plans for which we have not
                                // yet closed out insurance.
                                //
                                if (! $duncount) {
                                    for ($i = 1; $i <= 3 && arGetPayerID($row['pid'], $row['date'], $i);
                                    ++$i) {
                                    }
                                    $duncount = $row['last_level_closed'] + 1 - $i;
                                }

                                $isdueany = ($balance > 0);

                                // An invoice is now due from the patient if money is owed and we are
                                // not waiting for insurance to pay.
                                //
                                $isduept = ($duncount >= 0 && $isdueany) ? " checked" : "";

                                // Skip invoices not in the desired "Due..." category.
                                //
                                if (substr($_POST['form_category'], 0, 3) == 'Due' && !$isdueany) {
                                    continue;
                                }
                                if ($_POST['form_category'] == 'Due Ins' && ($duncount >= 0 || !$isdueany)) {
                                    continue;
                                }
                                if ($_POST['form_category'] == 'Due Pt'  && ($duncount <  0 || !$isdueany)) {
                                    continue;
                                }

                                $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");

                                $svcdate = substr($row['date'], 0, 10);
                                $last_stmt_date = empty($row['last_stmt_date']) ? '' : $row['last_stmt_date'];

                                // Determine if customer is in collections.
                                //
                                $billnote = $row['billing_note'];
                                $in_collections = stristr($billnote, 'IN COLLECTIONS') !== false;
                            ?>
                                <tr bgcolor='<?php echo $bgcolor ?>'>
                                    <td class="detail">
                                        <a href="" onclick="return npopup(<?php echo $row['pid'] ?>)"><?php echo $row['pid'];?></a>
                                    </td>
                                    <td class="detail">
                                        &nbsp;<a href="" onclick="return npopup(<?php echo $row['pid'] ?>)"><?php echo $row['lname'] . ', ' . $row['fname']; ?></a>
                                    </td>
                                    <td class="detail">
                                        &nbsp;<a href="sl_eob_invoice.php?id=<?php echo $row['id'] ?>" target="_blank"><?php echo $row['pid'] . '.' . $row['encounter']; ?></a>
                                    </td>
                                    <td class="detail">&nbsp;<?php echo text(oeFormatShortDate($svcdate)); ?></td>
                                    <td class="detail">&nbsp;<?php echo text(oeFormatShortDate($last_stmt_date)); ?></td>
                                    <td align="right" class="detail"><?php bucks($row['charges']) ?>&nbsp;</td>
                                    <td align="right" class="detail"><?php bucks($row['adjustments']) ?>&nbsp;</td>
                                    <td align="right" class="detail"><?php bucks($row['payments'] - $row['copays']); ?>&nbsp;</td>
                                    <td align="right" class="detail"><?php bucks($balance); ?>&nbsp;</td>
                                    <td align="center" class="detail"><?php echo $duncount ? $duncount : "&nbsp;" ?></td>
                                    <?php if (!$eracount) { ?>
                                    <td class="detail" align="left">
                                        <input type='checkbox' name='form_cb[<?php echo($row['id']) ?>]'<?php echo $isduept ?> />
                                        <?php
                                        if ($in_collections) {
                                            echo "<b><font color='red'>IC</font></b>";
                                        } ?>
                                        <?php
                                        if (function_exists('is_auth_portal') ? is_auth_portal($row['pid']) : false) {
                                            echo(' PPt');
                                            echo("<input type='hidden' name='form_invpids[". $row['id'] ."][". $row['pid'] ."]' />");
                                            $is_portal = true;
                                        }?>
                                    </td>
                                    <?php } ?>
                                    <td align="left" class="detail">
                                        <?php
                                            $patientData = sqlQuery("SELECT * FROM `patient_data` WHERE `pid`=?", array($row['pid']));
                                        if ($patientData['hipaa_allowemail'] == "YES" && $patientData['allow_patient_portal'] == "YES" && $patientData['hipaa_notice'] == "YES" && validEmail($patientData['email'])) {
                                            echo xlt("YES");
                                        } else {
                                            echo xlt("NO");
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php
                            } // end while
                        } // end search/print logic
                        ?>
                        </table>
                    </div><!--End of table-responsive div-->
                </fieldset>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
                <div class="form-group clearfix">
                    <div class="col-sm-12 text-left position-override oe-show-hide" id="statement-download">
                        <div class="btn-group" role="group">
                            <?php
                            if ($eracount) { ?>
                               <button type="button" class="btn btn-default btn-save" name="Submit"
                               onclick='processERA()' value="<?php echo xla('Process ERA File');?>">
                                <?php echo xlt('Process ERA File');?></button>
                            <?php
                            } else { ?>
                                <button type="button" class="btn btn-default btn-save" name="Submit1"
                                onclick='checkAll(true)'><?php echo xlt('Select All');?></button>
                                <button type="button" class="btn btn-default btn-undo" name="Submit2"
                                onclick='checkAll(false)'><?php echo xlt('Clear All');?></button>
                                <?php if ($GLOBALS['statement_appearance'] != '1') { ?>
                                    <button type="submit" class="btn btn-default btn-print" name='form_print' 
                                    value="<?php echo xla('Print Selected Statements'); ?>">
                                    <?php echo xlt('Print Selected Statements');?></button>
                                    <button type="submit" class="btn btn-default btn-download" name='form_download' 
                                    value="<?php echo xla('Download Selected Statements'); ?>">
                                    <?php echo xlt('Download Selected Statements');?></button>
                                <?php } ?>
                                    <button type="submit" class="btn btn-default btn-download" name='form_pdf' 
                                    value="<?php echo xla('PDF Download Selected Statements'); ?>">
                                    <?php echo xlt('PDF Download Selected Statements');?></button>
                                    <button type="submit" class="btn btn-default btn-mail" name='form_download'
                                    value="<?php echo xla('Email Selected Statements'); ?>">
                                    <?php echo xlt('Email Selected Statements');?></button>
                                    <?php
                                    if ($is_portal) {?>
                                    <button type="submit" class="btn btn-default btn-save"  name='form_portalnotify'
                                    value="<?php echo xla('Notify via Patient Portal'); ?>">
                                    <?php echo xlt('Notify via Patient Portal');?></button>
                                    <?php
                                    }
                            }
                        ?>
                            <input type='checkbox' class="btn-separate-left" name='form_without'    value='1' /><?php xl('Without Update', 'e');?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div> <!--End of Container div-->
    <div class="row">
            <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="myModal" role="dialog" tabindex="-1">
                <div class="modal-dialog modal-lg">
                     <div class="modal-content oe-modal-content">
                        <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                        <div class="modal-body" id="modal-content" name="modal-content">
                            <iframe src="" id="targetiframe" style="height:75%; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>  
                        </div>
                        <div class="modal-footer" style="margin-top:0px;">
                            <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <script language="JavaScript">
    function processERA() {
     var f = document.forms[0];
     var debug = f.form_without.checked ? '1' : '0';
     var paydate = f.form_paydate.value;
     window.open('sl_eob_process.php?eraname=<?php echo $eraname ?>&debug=' + debug + '&paydate=' + paydate + '&original=original', '_blank');
     return false;
    }
    $(function() {
        //https://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3
        // We can attach the `fileselect` event to all file inputs on the page
        $(document).on('change', ':file', function() {
            var input = $(this),
            numFiles = input.get(0).files ? input.get(0).files.length : 1,
            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });

        // We can watch for our custom `fileselect` event like this
        $(document).ready( function() {
            $(':file').on('fileselect', function(event, numFiles, label) {
                var input = $(this).parents('.input-group').find(':text'),
                log = numFiles > 1 ? numFiles + ' files selected' : label;
                
                if( input.length ) {
                input.val(log);
                } 
                else {
                if( log ) alert(log);
                }
            });
        });

    });
    //to dynamically show /hide relevant divs and change Fieldset legends
    $(document).ready(function() {
        $("input[name=radio-search]").on( "change", function() {

             var flip = $(this).val();
             $(".oe-show-hide").hide();
             $("#"+flip).show();
            if(flip == 'inv-search'){
                $("#search-upload").insertAfter("#payment-allocate");
                $('#payment-allocate').show();
                $('#search-btn').show();
                $('#btn-inv-search').show();
                var legend_text = $('#hid1').val();
                $('#search-upload').find('legend').find('span').text(legend_text);
                $('#search-upload').find('#form_name').focus();
                $('#select-method-tooltip').hide();
            }
            else if (flip == 'era-upld'){
                $('#payment-allocate').hide();
                $('#search-btn').show();
                $('#btn-era-upld').show();
                var legend_text = $('#hid2').val();
                $('#search-upload').find('legend').find('span').text(legend_text);
                $('#select-method-tooltip').hide();
            }
            else{
                $('#payment-allocate').hide();
                $('#search-btn').hide();
                var legend_text = $('#hid3').val();
                $('#search-upload').find('legend').find('span').text(legend_text);
                $('#select-method-tooltip').show();
            }
        });
        
        
            //if (post-btn == 'search'){$('#payment-allocate').show();}
            //else if (post-btn == 'upload'){$('#payment-allocate').hide();}
    });
    <?php
    if ($alertmsg) {
        echo "alert('" . htmlentities($alertmsg) . "');\n";
    }

    ?>
    /*function loadiframe(htmlHref) //load iframe
    {
    document.getElementById('targetiframe').src = htmlHref;
    }*/
    
    
    $( document ).ready(function() {
        $('#help-href').click (function(){
            var radioVal = $("input[name='radio-search']:checked").val();
            
            if (radioVal == 'inv-search'){
                document.getElementById('targetiframe').src ='../../interface/billing/sl_eob_help.php#invoice_search';
            }
            else if (radioVal == 'era-upld'){
                document.getElementById('targetiframe').src ='../../interface/billing/sl_eob_help.php#electonic_remits';
            }
            else{
                document.getElementById('targetiframe').src ='../../interface/billing/sl_eob_help.php#entire_doc';
            }
        })
        $('#select-method-tooltip').tooltip({title: "<?php echo xla(' Click on either the Invoice Search button on the far right, for manual entry or ERA Upload button for uploading an entire electronic remittance advice ERA file'); ?>"});
    });
    </script>
    <?php
        $tr_str = xl('Search');
    if ($_POST['form_search'] == "$tr_str") {?>
            <script>
                $("#payment-allocate").insertAfter("#search-upload");
                $('#payment-allocate').show();
                $("#search-results").show();
                $("#statement-download").show();
            </script>
        <?php
    }
    ?>
    <?php
        $tr_str = xl('Upload');
    if ($_POST['form_search'] == "$tr_str") {?>
        <script>
            $('#era-upld').show();
            $('#search-results').show();
            $("#statement-download").show();
        </script>
    <?php
    }
    ?>
    
</body>
</html>
