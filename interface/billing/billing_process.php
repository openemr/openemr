<?php
include_once("../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/billrep.inc");
include_once(dirname(__FILE__) . "/../../library/classes/WSClaim.class.php");

$EXPORT_INC = "$webserver_root/custom/BillingExport.php";
if (file_exists($EXPORT_INC)) {
  include_once($EXPORT_INC);
  $BILLING_EXPORT = true;
}

// This is a kludge to enter some parameters that are not in the X12
// Partners table, but should be:
$ISA07 = 'ZZ'; // ZZ = mutually defined, 01 = Duns, etc.
$ISA14 = '0';  // 1 = Acknowledgment requested, else 0
$ISA15 = 'T';  // T = testing, P = production
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

if (isset($_POST['bn_electronic_file']) && !empty($_POST['claims'])) {

	if (empty($_POST['claims'])) {
		$bill_info[] = xl("No claims were selected for inclusion.");
	}
	// $efile = array();

	$bat_type     = ''; // will be edi or hcfa
	$bat_sendid   = '';
	$bat_recvid   = '';
	$bat_content  = '';
	$bat_segcount = 2;
	$bat_time     = time();
	$bat_hhmm     = date('Hi' , $bat_time);
	$bat_yymmdd   = date('ymd', $bat_time);
	$bat_yyyymmdd = date('Ymd', $bat_time);
	// Minutes since 1/1/1970 00:00:00 GMT will be our interchange control number:
	$bat_icn = sprintf('%09.0f', $bat_time/60);

	foreach ($_POST['claims'] as $claim) {
		if (isset($claim['bill'])) {
			if (substr($claim['file'],strlen($claim['file']) -4) == '.pdf') {
			  $fname = substr($claim['file'],0,-4);
			}
			else {
			  $fname = $claim['file'];
			}

			$tmp = substr($fname, strrpos($fname, '.') + 1);
			if (!$bat_type) {
				$bat_type = $tmp;
			} else if ($bat_type != $tmp) {
				die("You cannot mix '$bat_type' and '$tmp' formats in the same batch!");
			}

			$fname = preg_replace("[/]","",$fname);
			$fname = preg_replace("[\.\.]","",$fname);
			$fname = preg_replace("[\\\\]","",$fname);
			$fname = $fconfig['claim_file_dir'] . $fname;

			if (file_exists($fname)) {
			//less than 500 is almost definitely an error
				if (filesize($fname) > 500) {
					$bill_info[] = xl("Added: ") . $fname . "\n";

					// $ta = array();
					// $ta["data"] = file_get_contents($fname);
					// $ta["size"] = filesize($fname);
					// $efile[] = $ta;

					if ($bat_type != 'edi') {
						$bat_content .= file_get_contents($fname);
						continue;
					}

					// If we get here, we are sending X12 837p data.  Strip off the ISA,
					// GS, ST, SE, GE, and IEA segments from the individual files and
					// send just one set of these for the whole batch.  We think this is
					// what the partners are happiest with, and Availity for one requires
					// (as of this writing) exactly one ISA/IEA pair per batch.
					$segs = explode('~', file_get_contents($fname));
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
									"GS*HC*$bat_sender*$bat_recvid*$bat_yyyymmdd*$bat_hhmm*1*X*004010X098A1~" .
									"ST*837*0001~";
							}
							continue;
						} else if (!$bat_content) {
							die("Error in $fname:<br>\nInput must begin with 'ISA'; " .
								"found '" . htmlentities($elems[0]) . "' instead");
						}
						if ($elems[0] == 'GS' || $elems[0] == 'ST') continue;
						if ($elems[0] == 'SE' || $elems[0] == 'GE' || $elems[0] == 'IEA') continue;
						if ($elems[0] == 'PER' && $PER06 && !$elems[5]) {
							$seg .= "*ED*$PER06";
						}
						$bat_content .= $seg . '~';
						$bat_segcount += 1;
					}

				}
				else {
					$bill_info[] = xl("May have an error: ") . $fname . "\n";
				}
			}
			else {
				$bill_info[] = xl("Not found: ") . $fname . "\n";
			}
		}

	}

	if ($bat_type == 'edi' && $bat_content) {
		$bat_content .= "SE*$bat_segcount*0001~GE*1*1~IEA*1*$bat_icn~";
	}

	// if (!empty($efile)) {
	if ($bat_content) {
		$db = $GLOBALS['adodb']['db'];
		$error = false;
		foreach ($_POST['claims'] as $claimid => $claim) {
			if (isset($claim['bill'])) {
				$tmpvars = split("-",$claimid);
				$pid = $tmpvars[0];
				$encounter = $tmpvars[1];
				if (!empty($encounter) && !empty($pid)) {
					$sql = "UPDATE billing set billed = 1 where encounter = '" . $encounter .
						"' and pid = '" . $pid . "' and activity != 0";
					$result = $db->execute($sql);
					if(!$result) {
						$error = true;
						$bill_info[] = xl("Marking claim "). $claimid . xl(" had a db error: ") . $db->ErrorMsg() . "\n";
					}

					//send claim to the web services code to sync to external system if enabled
					//remeber that in openemr it is only the encounter and patient id that make a group of
					//billing line items unique so they can be grouped or associated as 1 claim
					$ws = new WSClaim($pid, $encounter);
				}
			}
		}
		if (!$error) {
			$fname = date("Y-m-d", $bat_time) . "-billing_batch.txt";

			// If a writable edi directory exists, log the batch to it.
			// I guarantee you'll be glad we did this.  :-)
			$fh = @fopen("$webserver_root/edi/$fname", 'a');
			if ($fh) {
				fwrite($fh, $bat_content);
				fclose($fh);
			}

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=$fname");
			header("Content-Description: File Transfer");

			// $data = "";
			// $size = 0;
			// foreach ($efile as $file) {
			// $data .= $file['data'];
			// $size += $file['size'];
			// }
			// header("Content-Length: " . $size);
			// echo $data;

			header("Content-Length: " . strlen($bat_content));
			echo $bat_content;

			exit;
		}
	}

}
else {
	process_form($_POST);
}

function process_form($ar) {
	global $bill_info;

  if (isset($ar['bn_external'])) {
    // Open external billing file for output.
    $be = new BillingExport();
  }

	$db = $GLOBALS['adodb']['db'];

	if (empty($ar['claims'])) {
		$ar['claims'] = array();
	}
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

			$sql = "UPDATE billing set bill_date = NOW(), ";

			if (isset($ar['bn_hcfa_print'])) {
				$sql .= " bill_process = 5, target = 'hcfa', ";
			} else if (isset($ar['bn_hcfa'])) {
				$sql .= " bill_process = 1, target = 'hcfa', ";
			} else if (isset($ar['bn_ub92_print'])) {
				$sql .= " bill_process = 5, target = 'ub92', ";
			} else if (isset($ar['bn_ub92'])) {
				$sql .= " bill_process = 1, target = 'ub92', ";
			} else if (isset($ar['bn_x12'])) {
				$sql .= " bill_process = 1, target = '" . $target . "', x12_partner_id = '" . mysql_real_escape_string($claim_array['partner']) . "', ";
			} else if (isset($ar['bn_mark'])) {
				$sql .= " billed = 1, ";
				$mark_only = true;
      } else if (isset($ar['bn_external'])) {
        $sql .= " billed = 1, ";
      }

			$sql .= " payer_id = '$payer' where encounter = " . $encounter . " and pid = " . $patient_id;

			//echo $sql;
			$result = $db->Execute($sql); // Testing

			if(!$result) {
				$bill_info[] = xl("Claim "). $claimid . xl(" could not be queued due to error: ") . $db->ErrorMsg() . "\n";
			}
			else {
				// wtf is mark_as_billed? nothing sets it! -- Rod
				// if($ar['mark_as_billed'] == 1) {
				if($mark_only) {
					$bill_info[] = xl("Claim ") . $claimid . xl(" was marked as billed only.") . "\n";
				}
				else {
					$bill_info[] = xl("Claim ") . $claimid . xl(" was queued successfully.") . "\n";
				}
			}
			if ($mark_only) {
				//send claim to the web services code to sync to external system if enabled
				//remeber that in openemr it is only the encounter and patient id that make a group of
				//billing line items unique so they can be grouped or associated as 1 claim

				$ws = new WSClaim($patient_id,$encounter);
			}
		}

	}

  if (isset($ar['bn_external'])) {
    // Close external billing file.
    $be->close();
  }
}
?>
<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<br><p><h3><?xl('Billing queue results:','e')?></h3><a href="billing_report.php">back</a><ul>
<?
foreach ($bill_info as $infoline) {
	echo nl2br($infoline);
}
?>
</ul></p>
</body>
</html>
