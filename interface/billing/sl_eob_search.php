<?php
/**
 * This the first of two pages to support posting of EOBs.
 * The second is sl_eob_invoice.php.
 * Windows compatibility and statement downloading:
 *      2009 Bill Cernansky and Tony McCormick [mi-squared.com]
 *
 * Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */
require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/appointments.inc.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");
require_once("$srcdir/parse_era.inc.php");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/../controllers/C_Document.class.php");
require_once("$srcdir/documents.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");

$DEBUG = 0; // set to 0 for production, 1 to test

$alertmsg = '';
$where = '';
$eraname = '';
$eracount = 0;
/* Load dependencies only if we need them */
if( ( isset($GLOBALS['portal_onsite_enable'])) || ($GLOBALS['portal_onsite_enable']) ){
	require_once("$srcdir/pnotes.inc");
	require_once("../../patients/lib/appsql.class.php");
	
	function is_auth_portal( $pid = 0){
		if ($pData = sqlQuery("SELECT * FROM `patient_data` WHERE `pid` = ?", array($pid) )) {
			if($pData['allow_patient_portal'] != "YES")
				return false;
				else return true;
		}
		else return false;
	}
	function notify_portal($thispid, array $invoices, $template, $invid){
		$builddir = $GLOBALS['OE_SITE_DIR'] .  '/onsite_portal_documents/templates/' . $thispid;
		if( ! is_dir($builddir) )
			mkdir($builddir, 0755, true);
		if( fixup_invoice($template, $builddir.'/invoice'.$invid.'.tpl') != true ) return false; 
		if( SavePatientAudit( $thispid, $invoices ) != true ) return false; // this is all the invoice data for new invoicing feature to come
		$note =  xl('You have an invoice due for payment. You may view and pay in your Patient Documents.');
		addPnote($thispid, $note,1,1, xlt('Bill/Collect'), '-patient-');
		return true;
	}
	function fixup_invoice($template, $ifile){
		$data = file_get_contents($template);
		if($data == "") return false;
		if( !file_put_contents($ifile, $data) ) return false;
		return true;
	}
	function SavePatientAudit( $pid, $invs ){
		$appsql = new ApplicationTable();
		try{
			$audit = Array ();
			$audit['patient_id'] = $pid;
			$audit['activity'] = "invoice";
			$audit['require_audit'] = "0";
			$audit['pending_action'] = "payment";
			$audit['action_taken'] = "";
			$audit['status'] = "waiting transaction";
			$audit['narrative'] = "Request patient online payment.";
			$audit['table_action'] = '';
			$audit['table_args'] =  json_encode($invs);
			$audit['action_user'] = $pid;
			$audit['action_taken_time'] = "";
			$audit['checksum'] = "";
			$edata = $appsql->getPortalAudit( $pid, 'payment', 'invoice', "waiting transaction", 0 );
			//$audit['date'] = $edata['date'];
			if( $edata['id'] > 0 ) $appsql->portalAudit( 'update', $edata['id'], $audit );
			else{
				$appsql->portalAudit( 'insert', '', $audit );
			}
		} catch( Exception $ex ){
			return $ex;
		}
		return true;
	}
}
// This is called back by parse_era() if we are processing X12 835's.
function era_callback(&$out) {
  global $where, $eracount, $eraname;
  // print_r($out); // debugging
  ++$eracount;
  // $eraname = $out['isa_control_number'];
  $eraname = $out['gs_date'] . '_' . ltrim($out['isa_control_number'], '0') .
  '_' . ltrim($out['payer_id'], '0');
  list($pid, $encounter, $invnumber) = slInvoiceNumber($out);

  if ($pid && $encounter) {
    if ($where) $where .= ' OR ';
    $where .= "( f.pid = '$pid' AND f.encounter = '$encounter' )";
  }
}

function bucks($amount) {
  if ($amount) echo oeFormatMoney($amount);
}

// Upload a file to the client's browser
//
function upload_file_to_client($file_to_send) {
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
function upload_file_to_client_pdf($file_to_send) {
  //Function reads a HTML file and converts to pdf.

  global $STMT_TEMP_FILE_PDF;
  global $srcdir;

  if ($GLOBALS['statement_appearance'] == '1') {
    require_once("$srcdir/html2pdf/vendor/autoload.php");
    $pdf2 = new HTML2PDF ($GLOBALS['pdf_layout'],
    $GLOBALS['pdf_size'],
    $GLOBALS['pdf_language'],
                           true, // default unicode setting is true
                           'UTF-8', // default encoding setting is UTF-8
                           array($GLOBALS['pdf_left_margin'],$GLOBALS['pdf_top_margin'],$GLOBALS['pdf_right_margin'],$GLOBALS['pdf_bottom_margin']),
                           $_SESSION['language_direction'] == 'rtl' ? true : false
                           );
    ob_start();
    echo readfile($file_to_send, "r");//this file contains the HTML to be converted to pdf.
    //echo $file;
    $content = ob_get_clean();

    // Fix a nasty html2pdf bug - it ignores document root!
    global $web_root, $webserver_root;
    $i = 0;
    $wrlen = strlen($web_root);
    $wsrlen = strlen($webserver_root);
    while (true) {
      $i = stripos($content, " src='/", $i + 1);
      if ($i === false) break;
      if (substr($content, $i+6, $wrlen) === $web_root &&
        substr($content, $i+6, $wsrlen) !== $webserver_root) {
        $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
      }
    }
    $pdf2->WriteHTML($content);
    $temp_filename = $STMT_TEMP_FILE_PDF;
    $content_pdf = $pdf2->Output($STMT_TEMP_FILE_PDF,'F');
  } else {
    $pdf = new Cezpdf('LETTER');//pdf creation starts
    $pdf->ezSetMargins(45,9,36,10);
    $pdf->selectFont('Courier');
    $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
    $countline=1;
    $file = fopen($file_to_send, "r");//this file contains the text to be converted to pdf.
    while(!feof($file)) {
      $OneLine=fgets($file);//one line is read
      if(stristr($OneLine, "\014") == true && !feof($file))//form feed means we should start a new page.
      {
        $pdf->ezNewPage();
        $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
        str_replace("\014", "", $OneLine);
      }

      if(stristr($OneLine, 'REMIT TO') == true || stristr($OneLine, 'Visit Date') == true || stristr($OneLine, 'Future Appointments') == true || stristr($OneLine, 'Current') == true)//lines are made bold when 'REMIT TO' or 'Visit Date' is there.
        $pdf->ezText('<b>'.$OneLine.'</b>', 12, array('justification' => 'left', 'leading' => 6));
      else
        $pdf->ezText($OneLine, 12, array('justification' => 'left', 'leading' => 6));
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
if (($_POST['form_print'] || $_POST['form_download'] || $_POST['form_pdf']) || $_POST['form_portalnotify'] && $_POST['form_cb']) {

  $fhprint = fopen($STMT_TEMP_FILE, 'w');
  $sqlBindArray = array();
  $where = "";
  foreach ($_POST['form_cb'] as $key => $value) {
    $where .= " OR f.id = ?";
    array_push($sqlBindArray, $key);
  }
  $where = substr($where, 4);

  $res = sqlStatement("SELECT " .
    "f.id, f.date, f.pid, f.encounter, f.stmt_count, f.last_stmt_date, f.last_level_closed, f.last_level_billed, f.billing_note as enc_billing_note, " .
    "p.fname, p.mname, p.lname, p.street, p.city, p.state, p.postal_code, p.billing_note as pat_billing_note " .
    "FROM form_encounter AS f, patient_data AS p " .
    "WHERE ( $where ) AND " .
    "p.pid = f.pid " .
    "ORDER BY p.lname, p.fname, f.pid, f.date, f.encounter", $sqlBindArray);

  $stmt = array();
  $stmt_count = 0;

    // This loops once for each invoice/encounter.
    //
  while ($row = sqlFetchArray($res)) {
    $svcdate = substr($row['date'], 0, 10);
    $duedate = $svcdate; // TBD?
    $duncount = $row['stmt_count'];
    $enc_note = $row['enc_billing_note'];

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
      if (!empty($stmt)) ++$stmt_count;
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
      if(strlen($row['guardiansname']) == 0) {
        $stmt['to'] = array($row['fname'] . ' ' . $row['lname']);
      }
      else
      {
       $stmt['to'] = array($row['guardiansname']);
     }
     if ($row['street']) $stmt['to'][] = $row['street'];
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
    if ($_POST['form_portalnotify']) {
    	if( ! is_auth_portal($stmt['pid']) ){
    		$alertmsg = xlt('Notification FAILED: Not Portal Authorized');
    		break;
    	}
    	$inv_count += 1;
    	$pvoice[] = $stmt;
    	$c = count($form_cb);
    	if($inv_count == $c){
    		fwrite($fhprint, make_statement($stmt));
    		if( !notify_portal($stmt['pid'], $pvoice, $STMT_TEMP_FILE, $stmt['pid'] . "-" . $stmt['encounter']))
    			$alertmsg = xlt('Notification FAILED');
    	}
    	else	continue;
    }
    else
    	fwrite($fhprint, make_statement($stmt));

  } // end while

    if (!empty($stmt)) ++$stmt_count;
    fclose($fhprint);
    sleep(1);
    // Download or print the file, as selected
    if ($_POST['form_download']) {
      upload_file_to_client($STMT_TEMP_FILE);
    } elseif ($_POST['form_pdf']) {
      upload_file_to_client_pdf($STMT_TEMP_FILE);
    } elseif ($_POST['form_portalnotify']) {
    	if($alertmsg == "")
    		$alertmsg = xl('Sending Invoice to Patient Portal Completed');
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
    <?php html_header_show(); ?>
    <link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
    <title><?php xl('EOB Posting - Search','e'); ?></title>
    <script type="text/javascript" src="../../library/textformat.js"></script>

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

 </script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
  <center>

    <form method='post' action='sl_eob_search.php' enctype='multipart/form-data'>

      <table border='0' cellpadding='5' cellspacing='0'>
       <tr>

        <?php
  // Identify the payer to support resumable posting sessions.
        echo "  <td>\n";
        echo "   " . xl('Payer') . ":\n";
        echo "  </td>\n";
        echo "  <td>\n";
        $insurancei = getInsuranceProviders();
        echo "   <select name='form_payer_id'>\n";
        echo "    <option value='0'>-- " . xl('Patient') . " --</option>\n";
        foreach ($insurancei as $iid => $iname) {
          echo "<option value='$iid'";
          if ($iid == $_POST['form_payer_id']) echo " selected";
          echo ">" . $iname . "</option>\n";
        }
        echo "   </select>\n";
        echo "  </td>\n";
        ?>

        <td>
         <?php xl('Source:','e'); ?>
       </td>
       <td>
         <input type='text' name='form_source' size='10' value='<?php echo $_POST['form_source']; ?>'
         title='<?php xl("A check number or claim number to identify the payment","e"); ?>'>
       </td>
       <td>
         <?php xl('Pay Date:','e'); ?>
       </td>
       <td>
         <input type='text' name='form_paydate' size='10' value='<?php echo $_POST['form_paydate']; ?>'
         onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
         title='<?php xl("Date of payment yyyy-mm-dd","e"); ?>'>
       </td>

       <td>
         <?php xl('Deposit Date:','e'); ?>
       </td>
       <td>
         <input type='text' name='form_deposit_date' size='10' value='<?php echo $_POST['form_deposit_date']; ?>'
         onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
         title='<?php xl("Date of bank deposit yyyy-mm-dd","e"); ?>'>
       </td>

       <td>
         <?php xl('Amount:','e'); ?>
       </td>
       <td>
         <input type='text' name='form_amount' size='10' value='<?php echo $_POST['form_amount']; ?>'
         title='<?php xl("Paid amount that you will allocate","e"); ?>'>
       </td>
       <td align='right'>
         <a href='sl_eob_help.php' target='_blank'><?php xl('Help','e'); ?></a>
       </td>

     </tr>
   </table>

   <table border='0' cellpadding='5' cellspacing='0'>

     <tr bgcolor='#ddddff'>
      <td>
       <?php xl('Name:','e'); ?>
     </td>
     <td>
       <input type='text' name='form_name' size='10' value='<?php echo $_POST['form_name']; ?>'
       title='<?php xl("Any part of the patient name, or \"last,first\", or \"X-Y\"","e"); ?>'>
     </td>
     <td>
       <?php xl('Chart ID:','e'); ?>
     </td>
     <td>
       <input type='text' name='form_pid' size='10' value='<?php echo $_POST['form_pid']; ?>'
       title='<?php xl("Patient chart ID","e"); ?>'>
     </td>
     <td>
       <?php xl('Encounter:','e'); ?>
     </td>
     <td>
       <input type='text' name='form_encounter' size='10' value='<?php echo $_POST['form_encounter']; ?>'
       title='<?php xl("Encounter number","e"); ?>'>
     </td>
     <td>
       <?php xl('Svc Date:','e'); ?>
     </td>
     <td>
       <input type='text' name='form_date' size='10' value='<?php echo $_POST['form_date']; ?>'
       title='<?php xl("Date of service mm/dd/yyyy","e"); ?>'>
     </td>
     <td>
       <?php xl('To:','e'); ?>
     </td>
     <td>
       <input type='text' name='form_to_date' size='10' value='<?php echo $_POST['form_to_date']; ?>'
       title='<?php xl("Ending DOS mm/dd/yyyy if you wish to enter a range","e"); ?>'>
     </td>
     <td>
       <select name='form_category'>
        <?php
        foreach (array(xl('Open'), xl('All'), xl('Due Pt'), xl('Due Ins')) as $value) {
          echo "    <option value='$value'";
          if ($_POST['form_category'] == $value) echo " selected";
          echo ">$value</option>\n";
        }
        ?>
      </select>
    </td>
    <td>
     <input type='submit' name='form_search' value='<?php xl("Search","e"); ?>'>
   </td>
 </tr>

 <!-- Support for X12 835 upload -->
 <tr bgcolor='#ddddff'>
  <td colspan='12'>
   <?php xl('Or upload ERA file:','e'); ?>
   <input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
   <input name="form_erafile" type="file" />
 </td>
</tr>

<tr>
  <td height="1" colspan="10">
  </td>
</tr>

</table>

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
      if (is_file($GLOBALS['OE_SITE_DIR'] . "/era/$eraname.html"))
        $alertmsg .= "and processed. ";
      else
        $alertmsg .= "but not yet processed. ";
    }
    rename($tmp_name, $erafullname);
  } // End 835 upload

  if ($eracount) {
      // Note that parse_era() modified $eracount and $where.
    if (! $where) $where = '1 = 2';
  }
  else {
    if ($form_name) {
      if ($where) $where .= " AND ";
        // Allow the last name to be followed by a comma and some part of a first name.
      if (preg_match('/^(.*\S)\s*,\s*(.*)/', $form_name, $matches)) {
        $where .= "p.lname LIKE '" . $matches[1] . "%' AND p.fname LIKE '" . $matches[2] . "%'";
        // Allow a filter like "A-C" on the first character of the last name.
      } else if (preg_match('/^(\S)\s*-\s*(\S)$/', $form_name, $matches)) {
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
      if ($where) $where .= " AND ";
      $where .= "f.pid = '$form_pid'";
    }
    if ($form_encounter) {
      if ($where) $where .= " AND ";
      $where .= "f.encounter = '$form_encounter'";
    }
    if ($form_date) {
      if ($where) $where .= " AND ";
      if ($form_to_date) {
        $where .= "f.date >= '$form_date' AND f.date <= '$form_to_date'";
      }
      else {
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

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   &nbsp;<?php xl('Patient','e'); ?>
 </td>
 <td class="dehead">
   &nbsp;<?php xl('Invoice','e'); ?>
 </td>
 <td class="dehead">
   &nbsp;<?php xl('Svc Date','e'); ?>
 </td>
 <td class="dehead">
   &nbsp;<?php xl('Last Stmt','e'); ?>
 </td>
 <td class="dehead" align="right">
   <?php xl('Charge','e'); ?>&nbsp;
 </td>
 <td class="dehead" align="right">
   <?php xl('Adjust','e'); ?>&nbsp;
 </td>
 <td class="dehead" align="right">
   <?php xl('Paid','e'); ?>&nbsp;
 </td>
 <td class="dehead" align="right">
   <?php xl('Balance','e'); ?>&nbsp;
 </td>
 <td class="dehead" align="center">
   <?php xl('Prv','e'); ?>
 </td>
 <?php if (!$eracount) { ?>
 <td class="dehead" align="left">
   <?php xl('Sel','e'); ?>
 </td>
 <?php } ?>
</tr>

<?php
$orow = -1;

while ($row = sqlFetchArray($t_res)) {
  $balance = sprintf("%.2f", $row['charges'] + $row['copays'] - $row['payments'] - $row['adjustments']);

  if ($_POST['form_category'] != 'All' && $eracount == 0 && $balance == 0) continue;

      // $duncount was originally supposed to be the number of times that
      // the patient was sent a statement for this invoice.
      //
  $duncount = $row['stmt_count'];

      // But if we have not yet billed the patient, then compute $duncount as a
      // negative count of the number of insurance plans for which we have not
      // yet closed out insurance.
      //
  if (! $duncount) {
    for ($i = 1; $i <= 3 && arGetPayerID($row['pid'], $row['date'], $i); ++$i) ;
      $duncount = $row['last_level_closed'] + 1 - $i;
  }

  $isdueany = ($balance > 0);

      // An invoice is now due from the patient if money is owed and we are
      // not waiting for insurance to pay.
      //
  $isduept = ($duncount >= 0 && $isdueany) ? " checked" : "";

      // Skip invoices not in the desired "Due..." category.
      //
  if (substr($_POST['form_category'], 0, 3) == 'Due' && !$isdueany) continue;
  if ($_POST['form_category'] == 'Due Ins' && ($duncount >= 0 || !$isdueany)) continue;
  if ($_POST['form_category'] == 'Due Pt'  && ($duncount <  0 || !$isdueany)) continue;

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
     &nbsp;<a href="" onclick="return npopup(<?php echo $row['pid'] ?>)"
     ><?php echo $row['lname'] . ', ' . $row['fname']; ?></a>
   </td>
   <td class="detail">
     &nbsp;<a href="sl_eob_invoice.php?id=<?php echo $row['id'] ?>"
     target="_blank"><?php echo $row['pid'] . '.' . $row['encounter']; ?></a>
   </td>
   <td class="detail">
     &nbsp;<?php echo oeFormatShortDate($svcdate) ?>
   </td>
   <td class="detail">
     &nbsp;<?php echo oeFormatShortDate($last_stmt_date) ?>
   </td>
   <td class="detail" align="right">
     <?php bucks($row['charges']) ?>&nbsp;
   </td>
   <td class="detail" align="right">
     <?php bucks($row['adjustments']) ?>&nbsp;
   </td>
   <td class="detail" align="right">
     <?php bucks($row['payments'] - $row['copays']); ?>&nbsp;
   </td>
   <td class="detail" align="right">
     <?php bucks($balance); ?>&nbsp;
   </td>
   <td class="detail" align="center">
     <?php echo $duncount ? $duncount : "&nbsp;" ?>
   </td>
   <?php if (!$eracount) { ?>
   <td class="detail" align="left">
     <input type='checkbox' name='form_cb[<?php echo($row['id']) ?>]'<?php echo $isduept ?> />
     <?php if ($in_collections) echo "<b><font color='red'>IC</font></b>"; ?>
     <?php if ( function_exists('is_auth_portal') ? is_auth_portal( $row['pid'] ) : false){ echo ' PPt'; $is_portal = true;}?>
   </td>
   <?php } ?>
 </tr>
 <?php
    } // end while
} // end search/print logic

?>

</table>

<p>
  <?php if ($eracount) { ?>
  <input type='button' value='<?php xl('Process ERA File','e')?>' onclick='processERA()' /> &nbsp;
  <?php } else { ?>
  <input type='button' value='<?php xl('Select All','e')?>' onclick='checkAll(true)' /> &nbsp;
  <input type='button' value='<?php xl('Clear All','e')?>' onclick='checkAll(false)' /> &nbsp;
  <?php if ($GLOBALS['statement_appearance'] != '1') { ?>
    <input type='submit' name='form_print' value='<?php xl('Print Selected Statements','e'); ?>' /> &nbsp;
    <input type='submit' name='form_download' value='<?php xl('Download Selected Statements','e'); ?>' /> &nbsp;
  <?php } ?>
  <input type='submit' name='form_pdf' value='<?php xl('PDF Download Selected Statements','e'); ?>' /> &nbsp;
<?php if ($is_portal ){?>
  <input type='submit' name='form_portalnotify' value='<?php xl('Notify via Patient Portal','e'); ?>' /> &nbsp;
  <?php } }?>
  <input type='checkbox' name='form_without' value='1' /> <?php xl('Without Update','e'); ?>
</p>

</form>
</center>
<script language="JavaScript">
function processERA() {
  var f = document.forms[0];
  var debug = f.form_without.checked ? '1' : '0';
  var paydate = f.form_paydate.value;
  window.open('sl_eob_process.php?eraname=<?php echo $eraname ?>&debug=' + debug + '&paydate=' + paydate + '&original=original', '_blank');
  return false;
}
<?php
if ($alertmsg) {
  echo "alert('" . htmlentities($alertmsg) . "');\n";
}

?>
</script>
</body>
</html>
