<?php
$_GET['site'] = 'default';
$ignoreAuth = true;
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/wmt/wmtstandard.inc");
include_once("$srcdir/wmt/fyi.class.php");
set_time_limit(0);
global $pid, $id;
$log = fopen($webserver_root.'/sites/default/patches/ext_convert.log','w');

function AddKeyComment($key='', $nt='', $source=3) {
	global $pid, $id;
	if(!$id || !$pid) { 
		echo "<h>Fatal Error - Key Comment call with no form ID or PID</h><br>\n";
		echo "<br><b>Please report this to support</b><br><br>\n";
		exit;
	}
	if(!$key) return '';
	if($nt == '') return '';

	$sql = "INSERT INTO form_wmt_form_notes ".
		"(date, pid, user, groupname, authorized, activity, link_id, link_name, ".
		"link_field, note, input_source) VALUES (NOW(), ?, ?, ?, ?, 1, ?, ?, ?, ".
		"?, ?) ON DUPLICATE KEY UPDATE note=?";
	$parms = array($pid, 'admin', 'Default', 1, $id, 'form_ext_exam2', $key, 
			$nt, $source, $nt);
	sqlInsert($sql, $parms);
}

$flds = sqlListFields('form_ext_exam2');
$sql = "SELECT * FROM form_ext_exam2 AS e2 WHERE id >= ? AND id < ?";
$formID = 0;
while($formID < 40000) {
	$mres = sqlStatement($sql, array($formID,($formID + 2000)));
	echo "Processing  ID's From ($formID) to ";
	$formID = $formID + 2000;
	echo "[$formID]\n";
	while($mrow = sqlFetchArray($mres)) {
		$pid = $mrow{'pid'};
		$id = $mrow{'id'};
		echo "Processing PID: $pid Form ID: $id Dated (".$mrow{'form_dt'}.")\n";
		$txt = "Processing PID: $pid Form ID: $id Dated (".$mrow{'form_dt'}.")\n";
		fwrite($log, $txt);
		if(!$pid) {
			echo "Form ID: $id Has no PID ... Skipping\n";
			continue;
		}
		if(!$id) {
			echo "Form Has no ID ... Skipping\n";
			continue;
		}
	
		foreach($flds as $fld) {
			if(substr($fld, -3) == '_nt') continue;
			if(in_array($fld.'_nt', $flds)) {
				$nt  = trim($mrow{$fld . '_nt'});
				if($nt) {
					AddKeyComment($fld, $nt);
					$txt = "->  Added [$fld] ($nt)\n";
					fwrite($log, $txt);
				}
			}
		}
	}
}
?>
