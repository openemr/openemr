<?php
include_once("../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/billrep.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/gen_x12_837.inc.php");
include_once("$srcdir/gen_hcfa_1500.inc.php");
include_once(dirname(__FILE__) . "/../../library/classes/WSClaim.class.php");
require_once("$srcdir/classes/class.ezpdf.php");

$EXPORT_INC = "$webserver_root/custom/BillingExport.php";
if (file_exists($EXPORT_INC)) {
  include_once($EXPORT_INC);
  $BILLING_EXPORT = true;
}

// This is a kludge to enter some parameters that are not in the X12
// Partners table, but should be.
//
// The following works for Zirmed:
$ISA07 = 'ZZ'; // ZZ = mutually defined, 01 = Duns, etc.
$ISA14 = '0';  // 1 = Acknowledgment requested, else 0
$ISA15 = 'P';  // T = testing, P = production
$GS02  = '';   // Empty to use the sender ID from the ISA segment
$PER06 = '';   // The submitter's EDI Access Number, if any
/**** For Availity:
$ISA07 = '01'; // ZZ = mutually defined, 01 = Duns, etc.
$ISA14 = '1';  // 1 = Acknowledgment requested, else 0
$ISA15 = 'T';  // T = testing, P = production
$GS02  = 'AV01101957';
$PER06 = 'xxxxxx'; // The submitter's EDI Access Number
****/

$fconfig = $GLOBALS['oer_config']['freeb'];
$bill_info = array();

$bat_type     = ''; // will be edi or hcfa
$bat_sendid   = '';
$bat_recvid   = '';
$bat_content  = '';
$bat_stcount  = 0;
$bat_time     = time();
$bat_hhmm     = date('Hi' , $bat_time);
$bat_yymmdd   = date('ymd', $bat_time);
$bat_yyyymmdd = date('Ymd', $bat_time);
// Minutes since 1/1/1970 00:00:00 GMT will be our interchange control number:
$bat_icn = sprintf('%09.0f', $bat_time/60);
$bat_filename = date("Y-m-d-Hi", $bat_time) . "-batch.";
$bat_filename .= isset($_POST['bn_process_hcfa']) ? 'pdf' : 'txt';

if (isset($_POST['bn_process_hcfa'])) {
  $pdf =& new Cezpdf('LETTER');
  $pdf->ezSetMargins(trim($_POST['top_margin'])+0,0,trim($_POST['left_margin'])+0,0);
  $pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Courier.afm");
}

function append_claim(&$segs) {
  global $bat_content, $bat_sendid, $bat_recvid, $bat_sender, $bat_stcount;
  global $bat_yymmdd, $bat_yyyymmdd, $bat_hhmm, $bat_icn;
  global $GS02, $ISA07, $ISA14, $ISA15, $PER06;

  foreach ($segs as $seg) {
    if (!$seg) continue;
    $elems = explode('*', $seg);
    if ($elems[0] == 'ISA') {
      if (!$bat_content) {
        $bat_sendid = trim($elems[6]);
        $bat_recvid = trim($elems[8]);
        $bat_sender = $GS02 ? $GS02 : $bat_sendid;
        $bat_content = substr($seg, 0, 51) .
          $ISA07 . substr($seg, 53, 17) .
          "$bat_yymmdd*$bat_hhmm*U*00401*$bat_icn*$ISA14*$ISA15*:~" .
          "GS*HC*$bat_sender*$bat_recvid*$bat_yyyymmdd*$bat_hhmm*1*X*004010X098A1~";
      }
      continue;
    } else if (!$bat_content) {
      die("Error:<br>\nInput must begin with 'ISA'; " .
        "found '" . htmlentities($elems[0]) . "' instead");
    }
    if ($elems[0] == 'ST') {
      ++$bat_stcount;
      $bat_content .= sprintf("ST*837*%04d~", $bat_stcount);
      continue;
    }
    if ($elems[0] == 'SE') {
      $bat_content .= sprintf("SE*%d*%04d~", $elems[1], $bat_stcount);
      continue;
    }
    if ($elems[0] == 'GS' || $elems[0] == 'GE' || $elems[0] == 'IEA') continue;
    if ($elems[0] == 'PER' && $PER06 && !$elems[5]) {
      $seg .= "*ED*$PER06";
    }
    $bat_content .= $seg . '~';
  }
}

function append_claim_close() {
  global $bat_content, $bat_stcount, $bat_icn;
  $bat_content .= "GE*$bat_stcount*1~IEA*1*$bat_icn~";
}

function send_batch() {
  global $bat_content, $bat_filename, $webserver_root;
  // If a writable edi directory exists, log the batch to it.
  // I guarantee you'll be glad we did this.  :-)
  $fh = @fopen("$webserver_root/edi/$bat_filename", 'a');
  if ($fh) {
    fwrite($fh, $bat_content);
    fclose($fh);
  }
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=$bat_filename");
  header("Content-Description: File Transfer");
  header("Content-Length: " . strlen($bat_content));
  echo $bat_content;
}

process_form($_POST);

function process_form($ar) {
  global $bill_info, $webserver_root, $bat_filename, $pdf;

  if (isset($ar['bn_x12']) || isset($ar['bn_process_hcfa'])) {
    $hlog = fopen("$webserver_root/library/freeb/process_bills.log", 'w');
  }

  if (isset($ar['bn_external'])) {
    // Open external billing file for output.
    $be = new BillingExport();
  }

  $db = $GLOBALS['adodb']['db'];

  if (empty($ar['claims'])) {
    $ar['claims'] = array();
  }
  $claim_count = 0;
  foreach ($ar['claims'] as $claimid => $claim_array) {

    $ta = split("-",$claimid);
    $patient_id = $ta[0];
    $encounter = $ta[1];
    $payer = $claim_array['payer'];

    if (isset($claim_array['bill'])) {

      if (isset($ar['bn_external'])) {
        // Write external claim.
        $be->addClaim($patient_id, $encounter);
      }
      else {
        $sql = "SELECT x.processing_format from x12_partners as x where x.id =" .
          $db->qstr($claim_array['partner']);
        $result = $db->Execute($sql);
        $target = "x12";
        if ($result && !$result->EOF) {
            $target = $result->fields['processing_format'];
        }
      }

      $tmp = 1;

      if (isset($ar['bn_x12'])) {
        $tmp = updateClaim(true, $patient_id, $encounter, $payer, 1, 1, '', $target, $claim_array['partner']);
      } else if (isset($ar['bn_process_hcfa'])) {
        $tmp = updateClaim(true, $patient_id, $encounter, $payer, 1, 1, '', 'hcfa');
      } else if (isset($ar['bn_mark'])) {
        // $sql .= " billed = 1, ";
        $tmp = updateClaim(true, $patient_id, $encounter, $payer, 2);
      } else if (isset($ar['bn_reopen'])) {
        $tmp = updateClaim(true, $patient_id, $encounter, $payer, 1, 0);
      } else if (isset($ar['bn_external'])) {
        // $sql .= " billed = 1, ";
        $tmp = updateClaim(true, $patient_id, $encounter, $payer, 2);
      }

      if (!$tmp) {
        die(xl("Claim ") . $claimid . xl(" update failed, not in database?"));
      }
      else {
        if(isset($ar['bn_mark'])) {
          $bill_info[] = xl("Claim ") . $claimid . xl(" was marked as billed only.") . "\n";
        }

        else if (isset($ar['bn_reopen'])) {
          $bill_info[] = xl("Claim ") . $claimid . xl(" has been re-opened.") . "\n";
        }

        else if (isset($ar['bn_x12'])) {
          $log = '';
          $segs = explode("~\n", gen_x12_837($patient_id, $encounter, $log));
          fwrite($hlog, $log);
          append_claim($segs);
          if (!updateClaim(false, $patient_id, $encounter, -1, 2, 2, $bat_filename)) {
            $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
          }

        }

        else if (isset($ar['bn_process_hcfa'])) {
          $log = '';
          $lines = gen_hcfa_1500($patient_id, $encounter, $log);
          fwrite($hlog, $log);
          $alines = explode("\014", $lines); // form feeds may separate pages
          foreach ($alines as $tmplines) {
            if ($claim_count++) $pdf->ezNewPage();
            $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
            $pdf->ezText($tmplines, 12, array('justification' => 'left', 'leading' => 12));
          }
          if (!updateClaim(false, $patient_id, $encounter, -1, 2, 2, $bat_filename)) {
            $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
          }
        }

        else {
          $bill_info[] = xl("Claim ") . $claimid . xl(" was queued successfully.") . "\n";
        }

      }

    } // end if this claim has billing

  } // end foreach

  if (isset($ar['bn_x12'])) {
    append_claim_close();
    fclose($hlog);
    send_batch();
    exit;
  }

  if (isset($ar['bn_process_hcfa'])) {
    fclose($hlog);
    // If a writable edi directory exists (and it should), write the pdf to it.
    $fh = @fopen("$webserver_root/edi/$bat_filename", 'a');
    if ($fh) {
      fwrite($fh, $pdf->ezOutput());
      fclose($fh);
    }
    // Send the PDF download.
    $pdf->ezStream(array('Content-Disposition' => $bat_filename));
    exit;
  }

  if (isset($ar['bn_external'])) {
    // Close external billing file.
    $be->close();
  }
}
?>
<html>
<head>
<?php if (function_exists(html_header_show)) html_header_show(); ?>

<link rel="stylesheet" href="<?echo $css_header;?>" type="text/css">

</head>
<body class="body_top">
<br><p><h3><?php xl('Billing queue results:','e'); ?></h3><a href="billing_report.php">back</a><ul>
<?php
foreach ($bill_info as $infoline) {
  echo nl2br($infoline);
}
?>
</ul></p>
</body>
</html>
