<?php
set_time_limit(0);
require_once("xmlrpc.inc");
require_once(dirname(__FILE__) . "/../sql.inc");
require_once $GLOBALS['OE_SITE_DIR'] . "/config.php";
require_once(dirname(__FILE__) . "/../billing.inc");

if ($argv[1] != "bill") {
	echo "This script can only be accessed as a CLI program.\n";
	echo "To execute from the command line run 'php -q process_bills.php bill' .\n";
	exit;
}

/****
$db = $GLOBALS['adodb']['db'];
$sql = "SELECT * from billing WHERE bill_process = 1 or bill_process = 5 group by pid,encounter" ;
$results = $db->Execute($sql);
$billkeys = array();
if (!$results) {
  echo "There was an error with the database.\n";
  echo $db->ErrorMsg();
  exit;
}
if ($results->RecordCount() == 0) {
  echo "No bills queued for processing.\n";
  exit;
}
while (!$results->EOF) {
	$ta['key'] = $results->fields['pid'] . "-" . $results->fields['encounter'];
	$ta['bill_process'] = $results->fields['bill_process'];
	$ta['format'] = $results->fields['target'];
	$billkeys[] = $ta;
	$results->MoveNext();
}
****/

$sql = "SELECT * from claims WHERE " .
  "( bill_process = 1 or bill_process = 5) AND " .
  "status > 0 AND status < 4";
$res = sqlStatement($sql);

$billkeys = array();

while ($row = sqlFetchArray($res)) {
  $crow = sqlQuery("SELECT count(*) AS count FROM claims WHERE " .
    "patient_id = '" . $row['patient_id'] . "' AND " .
    "encounter_id = '" . $row['encounter_id'] . "' AND " .
    "version > '" . $row['version'] . "'");
  if ($crow['count']) continue;

  $ta = array();
  $ta['key'] = $row['patient_id'] . "-" . $row['encounter_id'];
  $ta['bill_process'] = $row['bill_process'];
  $ta['format'] = $row['target'];
  $billkeys[] = $ta;
}

if (empty($billkeys)) {
  echo "No bills queued for processing.\n";
  exit;
}

foreach ($billkeys as $billkey) {
	$tmp = explode("-", $billkey['key']);
	$patient_id = $tmp[0];
	$encounter = $tmp[1];
	$name = "FreeB.Bill.process";
	$format = $billkey['format'];

	if (empty($format)) {
		$format = $GLOBALS['oer_config']['freeb']['default_format'];
	}
	$file_type = "txt";
	if ($format == "hcfa" || $format == "ub92") {
		$file_type = "pdf";
	}
	else {
		$file_type = "edi";	
	}
	echo "Creating job for: " . $billkey['key'] . " as $format returning $file_type.\n";
	$args = array(new xmlrpcval($billkey['key'], XMLRPCSTRING),
		new xmlrpcval($format),new xmlrpcval($file_type));

	$f = new xmlrpcmsg($name,$args);
	$c = new xmlrpc_client("/RPC2", "localhost", 18081);
	$c->setDebug(0);
	$r = $c->send($f);
	if (!$r) die("send failed");
	$v = $r->value();

	if (!$r->faultCode()) {
		$presult = $v->scalarval();
		echo "Claim for PatientID: $patient_id, Encounter: $billkey[key] " .
			"processed successfully. Results are in file:\n " . basename("/" .
			$presult) . "\n";

    /****
		$sql = "UPDATE billing set process_date = now(), bill_process = 2, process_file = '" .
			basename("/" . $presult) . "' where encounter = $encounter AND pid = '" .
			$patient_id . "'";
		$results = $db->Execute($sql);
		if (!$results) {
			echo "There was an error with the database.\n";
			echo $db->ErrorMsg() . "\n";
		}
    ****/

    if (!updateClaim(false, $patient_id, $encounter, -1, -1, 2, basename("/" . $presult))) {
      echo "Internal error: failed to update claim $patient_id-$encounter\n";
    }

		else { // everything worked
			$fconfig = $GLOBALS['oer_config']['freeb'];
			$fbase = basename("/" . $presult);
			$fname = $fconfig['claim_file_dir'] . $fbase;
			// If we need to copy PDFs, do it.
			if ($file_type == 'pdf' && $fconfig['copy_pdfs_to']) {
				$ifh = fopen($fname, 'rb');
				$ofh = fopen($fconfig['copy_pdfs_to'] . $fbase, 'w');
				while ($ifh && $ofh && !feof($ifh)) {
					fwrite($ofh, fread($ifh, 8192));
				}
				fclose($ofh);
				fclose($ifh);
				// chmod($fconfig['copy_pdfs_to'] . $fbase, 0666);
			}
			if ($billkey['bill_process'] == 5) {
				//the bill was generated without an error now print it
				$estring = $fconfig['print_command'] . " -P " . $fconfig['printer_name'] .
					" " . $fconfig['printer_extras'] . " " . $fname;
				$rstring = exec(escapeshellcmd($estring));	
			}
		}
	}
	else {
		$presult =  "Code: " . $r->faultCode() . " Reason '" .$r->faultString()."'<BR>";
		echo "Claim for PatientID: $patient_id, Encounter: $billkey[key] failed due to: \n " .
			basename("/" . $presult) . "\n";

    /****
		$sql = "UPDATE billing set process_date = now(), bill_process = 3, process_file = '" .
			add_escape_custom(basename("/" . $presult)) .
			"' where encounter = $encounter AND pid = '" . $patient_id . "'";
		$results = $db->Execute($sql);
		if (!$results) {
			echo "There was an error with the database.\n";
			echo $db->ErrorMsg() . "\n";
		}
    ****/

    if (!updateClaim(false, $patient_id, $encounter, -1, -1, 3, basename("/" . $presult))) {
      echo "Internal error: failed to update claim $patient_id-$encounter\n";
    }

	}
}

echo "\n\n";
?>
