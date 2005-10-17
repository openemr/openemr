<?php
include_once("../globals.php");

include_once("$srcdir/patient.inc");
include_once("$srcdir/billrep.inc");
include_once(dirname(__FILE__) . "/../../library/classes/WSClaim.class.php");

$fconfig = $GLOBALS['oer_config']['freeb'];
$bill_info = array();

if (isset($_POST['bn_electronic_file']) && !empty($_POST['claims'])) {

	if (empty($_POST['claims'])) {
		$bill_info[] = "No claims were selected for inclusion.";

	}
	$efile = array();

	foreach ($_POST['claims'] as $claim) {
		if (isset($claim['bill'])) {
			if (substr($claim['file'],strlen($claim['file']) -4) == '.pdf') {
			  $fname = substr($claim['file'],0,-4);
			}
			else {
			  $fname = $claim['file'];
			}
			$fname = preg_replace("[/]","",$fname);
			$fname = preg_replace("[\.\.]","",$fname);
			$fname = preg_replace("[\\\\]","",$fname);
			$fname = $fconfig['claim_file_dir'] . $fname;
			if (file_exists($fname)) {
			//less than 500 is almost definitely an error
				if (filesize($fname) > 500) {
					$bill_info[] = "Added: " . $fname . "\n";
					$ta = array();
					$ta["data"] = file_get_contents($fname);
					$ta["size"] = filesize($fname);
					$efile[] = $ta;
				}
				else {
					$bill_info[] = "May have an error:" . $fname . "\n";
				}
			}
			else {
				$bill_info[] = "Not found:" . $fname . "\n";
			}
		}

	}
	if (!empty($efile)) {
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
						$bill_info[] = "Marking claim $claimid had a db error:" . $db->ErrorMsg() . "\n";
					}

					//send claim to the web services code to sync to external system if enabled
					//remeber that in openemr it is only the encounter and patient id that make a group of
					//billing line items unique so they can be grouped or associated as 1 claim
					$ws = new WSClaim($pid, $encounter);
				}
			}
		}
		if (!$error) {
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=". date("Y-m-d") . "-billing_batch.txt");
			header("Content-Description: File Transfer");

			$data = "";
			$size = 0;

			foreach ($efile as $file) {
				$data .= $file['data'];
				$size += $file['size'];
			}

			header("Content-Length: " . $size);
			echo $data;
			exit;
		}
	}

}
else {
	process_form($_POST);
}


function process_form($ar) {
	global $bill_info;

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
			$sql = "SELECT x.processing_format from x12_partners as x where x.id =" . $db->qstr($claim_array['partner']);
			$result = $db->Execute($sql);
			$target = "x12";
			if ($result && !$result->EOF) {
			  	$target = $result->fields['processing_format'];
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
			}

			$sql .= " payer_id = '$payer' where encounter = " . $encounter . " and pid = " . $patient_id;

			//echo $sql;
			$result = $db->Execute($sql);

			if(!$result) {
				$bill_info[] = "Claim $claimid could not be queued due to error:" . $db->ErrorMsg() . "\n";
			}
			else {
				// wtf is mark_as_billed? nothing sets it! -- Rod
				// if($ar['mark_as_billed'] == 1) {
				if($mark_only) {
					$bill_info[] = "Claim $claimid was marked as billed only.\n";
				}
				else {
					$bill_info[] = "Claim $claimid was queued successfully.\n";
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
}


?>
<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<br><p><h3>Billing queue results:</h3><a href="billing_report.php">back</a><ul>
<?
foreach ($bill_info as $infoline) {
	echo nl2br($infoline);
}
?>
</ul></p>
</body>
</html>
