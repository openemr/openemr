<?
include_once("../globals.php");

include_once("$srcdir/patient.inc");
include_once("$srcdir/billrep.inc");
include_once(dirname(__FILE__) . "/../../library/classes/OFX.class.php");
include_once(dirname(__FILE__) . "/../../library/classes/X12Partner.class.php");

$alertmsg = '';

if ($_POST['mode'] == 'export') {
	$sdate = $_POST['from_date'];
	$edate = $_POST['to_date'];

	$sql = "SELECT billing.*, concat(pd.fname,' ', pd.lname) as name from billing join patient_data as pd on pd.pid = billing.pid where billed = '1' and"
	. "(process_date > '" . mysql_real_escape_string($sdate) . "' or DATE_FORMAT( process_date, '%Y-%m-%d' ) = '" . mysql_real_escape_string($sdate) ."') "
	. "and (process_date < '" . mysql_real_escape_string($edate) . "'or DATE_FORMAT( process_date, '%Y-%m-%d' ) = '" . mysql_real_escape_string($edate) ."') "
	. " order by pid,encounter";
	$db = get_db();
	$results = $db->Execute($sql);
	$billings = array();
	if ($results->RecordCount() == 0) {
		echo "No Bills Found to Include in OFX Export<br>";
	}
	else {
		while(!$results->EOF) {
			$billings[] = $results->fields;
			$results->MoveNext();
		}
		$ofx = new OFX($billings);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename=openemr_ofx.ofx");
		header("Content-Type: text/xml");
		echo $ofx->get_OFX();
		exit;
	}
}

if ($_POST['mode'] == 'process') {
	if (exec("ps x | grep 'process_bills[.]php'")) {
		$alertmsg = 'Request ignored - claims processing is already running!';
	}
	else {
		exec("cd $webserver_root/library/freeb;" .
			"php -q process_bills.php bill > process_bills.log 2>&1 &");
		$alertmsg = 'Batch processing initiated; this may take a while.';
	}
}

//global variables:
if (!isset($_POST["mode"])) {
	if (!isset($_POST["from_date"])) {
		$from_date=date("Y-m-d");
	} else {
		$from_date = $_POST["from_date"];
	}
	if (!isset($_POST["to_date"])) {
		$to_date = date("Y-m-d");
	} else {
		$to_date = $_POST["to_date"];
	}
	if (!isset($_POST["code_type"])) {
		$code_type="all";
	} else {
		$code_type = $_POST["code_type"];
	}
	if (!isset($_POST["unbilled"])) {
		$unbilled = "on";
	} else {
		$unbilled = $_POST["unbilled"];
	}
	if (!isset($_POST["authorized"])) {
		$my_authorized = "on";
	} else {
		$my_authorized = $_POST["authorized"];
	}
} else {
	$from_date = $_POST["from_date"];
	$to_date = $_POST["to_date"];
	$code_type = $_POST["code_type"];
	$unbilled = $_POST["unbilled"];
	$my_authorized = $_POST["authorized"];
}

$ofrom_date = $from_date;
$oto_date = $to_date;
$ocode_type = $code_type;
$ounbilled = $unbilled;
$oauthorized = $my_authorized;

?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script>

function select_all() {
	for($i=0;$i < document.update_form.length;$i++) {
		$name = document.update_form[$i].name;
		if ($name.substring(0,7) == "claims[" && $name.substring($name.length -6) == "[bill]") {
			document.update_form[$i].checked = true;
		}
	}
	set_button_states();
}

function set_button_states() {
	var f = document.update_form;
	var count0 = 0;
	var count1 = 0;
	var count2 = 0;
	for($i = 0; $i < f.length; ++$i) {
		$name = f[$i].name;
		if ($name.substring(0, 7) == "claims[" && $name.substring($name.length -6) == "[bill]" && f[$i].checked == true) {
			if      (f[$i].value == '0') ++count0;
			else if (f[$i].value == '1' || f[$i].value == '5') ++count1;
			else ++count2;
		}
	}

	var can_generate = (count0 > 0 || count1 > 0 || count2 > 0);
	var can_mark     = (count1 == 0 && (count0 > 0 || count2 > 0));
	var can_bill     = (count0 == 0 && count1 == 0 && count2 > 0);

	f.bn_hcfa_print.disabled      = !can_generate;
	f.bn_hcfa.disabled            = !can_generate;
	f.bn_x12.disabled             = !can_generate;
	f.bn_mark.disabled            = !can_mark;
	f.bn_electronic_file.disabled = !can_bill;
}

</script>
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<p style='margin-top:5px;margin-bottom:5px;margin-left:5px'>
<?
if ($userauthorized) {
?>
<a href="../main/main.php" target=Main><font class=title>Billing Report</font><font class=more> <?echo $tback;?></font></a>
<?} else {?>
<a href="../main/onotes/office_comments.php" target=Main><font class=title>Billing Report</font><font class=more><?echo $tback;?></font></a>
<?
}
?>
</p>

<form name=the_form method=post action=billing_report.php>
<input type=hidden name=mode value="change">
<table width=100% border="1" cellspacing="0" cellpadding="0">
	<tr>
		<td nowrap>&nbsp;<span class=text>From: </span><input type=entry name=from_date size=11 value="<?echo $from_date;?>"></td>
		<td nowrap>&nbsp;<span class=text>To: </span><input type=entry name=to_date size=11 value="<?echo $to_date;?>">
			<input type="hidden" name="code_type" value="%"></td>
		<td nowrap><input type=checkbox name=unbilled <?if ($unbilled == "on") {echo "checked";};?>><span class=text>Show Unbilled Only</span></td>
		<td nowrap><input type=checkbox name=authorized <?if ($my_authorized == "on") {echo "checked";};?>><span class=text>Show Authorized Only</span></td>
		<td align='right' width='10%' nowrap>
			&nbsp;<span class=text><a href="javascript:document.the_form.mode.value='change';document.the_form.submit()" class=link_submit>[Change View]</a>
			or
			<a href="javascript:document.the_form.mode.value='export';document.the_form.submit()" class=link_submit>[Export OFX]</a></span>&nbsp;
		</td>
	</tr>
	<tr>
		<td nowrap>&nbsp;<a href="print_billing_report.php?<?print "from_date=".urlencode($ofrom_date)."&to_date=".urlencode($oto_date)."&code_type=".urlencode($ocode_type)."&unbilled=".urlencode($ounbilled)."&authorized=".urlencode($oauthorized);?>" class=link_submit target=new>[View Printable Report]</a></td>
		<td nowrap>
<?php
	print '&nbsp;';
	$acct_config = $GLOBALS['oer_config']['ws_accounting'];
	if($acct_config['enabled'] == true) {
		print '<span class=text><a href="javascript:void window.open(\''.$acct_config['url_path'].'\')">[Accounting System]</a></span>';
	}
?>
		</td>
		<td colspan='2' nowrap>
			&nbsp;
			<a href="javascript:document.the_form.mode.value='process';document.the_form.submit()" class="link_submit"
				 title="Process all queued bills to create electronic data (and print if requested)">[Start Batch Processing]</a>
			&nbsp; <a href='../../library/freeb/process_bills.log' target='_blank' class='link_submit'
				title='See messages from the last batch processing run'>[view log]</a></span>
		</td>
		<td align='right' nowrap>
			<a href="javascript:select_all()" class="link_submit">[Select All]</a>&nbsp;
		</td>
	</tr>
</table>
</form>

<form name=update_form method=post action=billing_process.php>

<center>
<input type="submit" name="bn_hcfa_print" value="Generate HCFA &amp; Print" title="Queue for HCFA batch processing and printing">
<input type="submit" name="bn_hcfa" value="Generate HCFA" title="Queue for HCFA batch processing">
<input type="submit" name="bn_x12" value="Generate X12" title="Queue for X12 batch processing">
<input type="submit" name="bn_mark" value="Mark Bills as Cleared" title="Post to accounting and mark as billed">
<input type="submit" name="bn_electronic_file" value="Generate Electronic Batch &amp; Clear" title="Download billing file, post to accounting and mark as billed">
</center>

<input type=hidden name=mode value="bill">
<input type=hidden name=authorized value="<?echo $my_authorized;?>">
<input type=hidden name=unbilled value="<?echo $unbilled;?>">
<input type=hidden name=code_type value="%">
<input type=hidden name=to_date value="<?echo $to_date;?>">
<input type=hidden name=from_date value="<?echo $from_date;?>">
<?
if ($my_authorized == "on" ) {
	$my_authorized = "1";
} else {
	$my_authorized = "%";
}

if ($unbilled == "on") {
	$unbilled = "0";
} else {
	$unbilled = "%";
}
?>
<input type=hidden name=bill_list value="<?
$list = getBillsListBetween($from_date,$to_date,$my_authorized,$unbilled,"%");
print $list;
?>">
<!-- new form for uploading -->
<?php
if (!isset($_POST["mode"])) {
	if (!isset($_POST["from_date"])) {
		$from_date=date("Y-m-d");
	} else {
		$from_date = $_POST["from_date"];
	}
	if (!isset($_POST["to_date"])) {
		$to_date = date("Y-m-d");
	} else {
		$to_date = $_POST["to_date"];
	}
	if (!isset($_POST["code_type"])) {
		$code_type="all";
	} else {
		$code_type = $_POST["code_type"];
	}
	if (!isset($_POST["unbilled"])) {
		$unbilled = "on";
	} else {
		$unbilled = $_POST["unbilled"];
	}
	if (!isset($_POST["authorized"])) {
		$my_authorized = "on";
	} else {
		$my_authorized = $_POST["authorized"];
	}
} else {
	$from_date = $_POST["from_date"];
	$to_date = $_POST["to_date"];
	$code_type = $_POST["code_type"];
	$unbilled = $_POST["unbilled"];
	$my_authorized = $_POST["authorized"];
}
if ($my_authorized == "on" ) {
	$my_authorized = "1";
} else {
	$my_authorized = "%";
}

if ($unbilled == "on") {
	$unbilled = "0";
} else {
	$unbilled = "%";
}

?>
<?
if (isset($_POST["mode"]) && $_POST["mode"] == "bill") {
	billCodesList($list);
}
?>

<p>
<table border="0" cellspacing="0" cellpadding="0" width="100%">

<?
if ($ret = getBillsBetween($from_date,$to_date,$my_authorized,$unbilled,"%")) {
	$loop = 0;
	$oldcode = "";
	$last_encounter_id = "";
	$lhtml = "";
	$rhtml = "";
	$lcount = 0;
	$rcount = 0;
	$bgcolor = "";
	$skipping = FALSE;

	foreach ($ret as $iter) {
		$name = getPatientData($iter['pid']);
		$this_encounter_id = $iter['pid'] . "-" . $iter['encounter'];

		if ($last_encounter_id != $this_encounter_id) {
			if ($lhtml) {
				while ($rcount < $lcount) {
					$rhtml .= "<tr bgcolor='$bgcolor'><td colspan='7'>&nbsp;</td></tr>";
					++$rcount;
				}
				echo "<tr bgcolor='$bgcolor'>\n<td rowspan='$rcount' valign='top'>\n$lhtml</td>$rhtml\n";
				echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n\n";
			}

			$lhtml = "";
			$rhtml = "";

			// If there are ANY unauthorized items in this encounter and this is
			// the normal case of viewing only authorized billing, then skip the
			// entire encounter.
			//
			$skipping = FALSE;
			if ($my_authorized == '1') {
				$res = sqlQuery("select count(*) as count from billing where " .
					"encounter = '" . $iter['encounter'] . "' and " .
					"pid='" . $iter['pid'] . "' and " .
					"activity = 1 and authorized = 0");
				if ($res['count'] > 0) {
					$skipping = TRUE;
					$last_encounter_id = $this_encounter_id;
					continue;
				}
			}

			++$encount;
			$bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
			echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n";
			$lcount = 3;
			$rcount = 0;
			$oldcode = "";

			$lhtml .= "&nbsp;<span class=bold>". $name['fname'] . "&nbsp;" . $name['lname'] . "</span><span class=small>&nbsp;(" . $iter['pid'] . "-" . $iter['encounter'] . ")</span>";
			$lhtml .= "&nbsp;&nbsp;&nbsp;<a class=\"link_submit\" href=\"" . $GLOBALS['webroot'] ."/interface/patient_file/encounter/patient_encounter.php?set_encounter=" . $iter['encounter'] . "&pid=" . $iter['pid'] . "\">[To&nbsp;Encounter]</a>";
			$lhtml .= "&nbsp;&nbsp;&nbsp;<a class=\"link_submit\" href=\"" . $GLOBALS['webroot'] ."/interface/patient_file/summary/demographics_full.php?&pid=" . $iter['pid'] . "\">[To&nbsp;Demographics]</a>";
			$lhtml .= "<br />\n";
			$lhtml .= "&nbsp;<span class=text>Bill: ";
			$lhtml .= "<select name='claims[" . $this_encounter_id . "][payer]' style='background-color:$bgcolor'>";
			$query = "SELECT id.provider as id, id.type, ic.x12_default_partner_id as ic_x12id, ic.name as provider FROM insurance_data as id, insurance_companies as ic WHERE ic.id = id.provider AND pid = '" . mysql_escape_string($iter['pid']) . "' order by type";

			$result = sqlStatement($query);
			$count = 0;
			$default_x12_partner = $iter['x12_partner_id'];

			while ($row = mysql_fetch_array($result)) {
				if (strlen($row['provider']) > 0) {
					if ($count == 0) {
						$lhtml .= "<option value=\"" .$row['id'] . "\" selected>" . $row['type'] . ": " . $row['provider']. "</option>";
						if (!is_numeric($default_x12_partner)) $default_x12_partner = $row['ic_x12id'];
					}
					else {
						$lhtml .= "<option value=\"" . $row['id'] . "\">" . $row['type'] . ": " . $row['provider']. "</option>";
					}
				}
				$count++;
			}
			$lhtml .= "<option value='-1'>Unassigned</option>\n";
			$lhtml .= "</select>&nbsp;&nbsp;\n";
			$lhtml .= "<select name='claims[" . $this_encounter_id . "][partner]' style='background-color:$bgcolor'>";
			$x = new X12Partner();
			$partners = $x->_utility_array($x->x12_partner_factory());
			foreach ($partners as $xid => $xname) {
				$lhtml .= '<option label="' . $xname . '" value="' . $xid .'"';
				if ($xid == $default_x12_partner) {
					$lhtml .= "selected";
				}
				$lhtml .= '>' . $xname . '</option>';
			}
			$lhtml .= "</select>";
			$lhtml .= "<br>\n&nbsp;Claim was initiated: "  . $iter['date'];
			if ($iter['billed'] == 1) {
				$lhtml .= "<br>\n&nbsp;Claim was billed: "  . $iter['bill_date'];
				++$lcount;
			}
			if ($iter['bill_process'] == 1) {
				$lhtml .= "<br>\n&nbsp;Claim is queued for processing";
				++$lcount;
			}
			if ($iter['bill_process'] == 5) {
				$lhtml .= "<br>\n&nbsp;Claim is queued for printing and processing";
				++$lcount;
			}
			if ($iter['bill_process'] == 2) {
				$lhtml .= "<br>\n&nbsp;Claim was processed: "  . $iter['process_date'];
				$lhtml .= '<br>' . "\n" . '&nbsp;Claim is in file: <a href="get_claim_file.php?key=' . $iter['process_file'] .'">'  . $iter['process_file'] . '</a> or ';
				$lhtml .= '<a href="get_claim_file.php?action=print&key=' . $iter['process_file'] .'">Print It</a> or ';
				$lhtml .= '<a target="_new" href="freebtest.php?format=' . $iter['target'] . '&billkey=' . $iter['pid'] . '-' . $iter['encounter'] . '">Run Test</a>';
				$lhtml .= '<input type="hidden" name="claims[' . $this_encounter_id . '][file]" value="' . $iter['process_file'] . '">';
				$lcount += 2;
			}
			if ($iter['bill_process'] == 3) {
				$lhtml .= "<br>\n&nbsp;Claim was processed: "  . $iter['process_date'] . " but there was an error: ". $iter['process_file'];
				++$lcount;
			}
		}

		if ($skipping) continue;

		++$rcount;
		if ($rhtml) {
			$rhtml .= "<tr bgcolor='$bgcolor'>\n";
		}
		$rhtml .= "<td width='50'>";
		if ($oldcode != $iter['code_type']) {
			$rhtml .= "<span class=text>" . $iter['code_type'] . ": </span>";
		}
		$oldcode = $iter['code_type'];
		$rhtml .= "</td>\n";
		$justify = "";
		if ($iter['code_type'] == "CPT4" || $iter['code_type'] == "HCPCS") {
			$js = split(":",$iter['justify']);
			$counter = 0;
			foreach ($js as $j) {
				if(!empty($j)) {
					if ($counter == 0) {
						$justify .= " (<b>$j</b>)";
					}
					else {
						$justify .= " ($j)";
					}
					$counter++;
				}
			}
		}

		$rhtml .= "<td><span class=text>" . $iter{"code"}. "</span>" . '<span style="font-size:8pt;">' . $justify . "</span></td>\n";
		$rhtml .= '<td align="right"><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
		if ($iter['fee'] > 0) {
			$rhtml .= '$' . $iter['fee'];
		}
		$rhtml .= "</span></td>\n";
		$rhtml .= '<td><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
		$rhtml .= getProviderName($iter['provider_id']);
		$rhtml .= "</span></td>\n";
		$rhtml .= '<td width=100>&nbsp;&nbsp;&nbsp;<span style="font-size:8pt;">' . date("Y-m-d",strtotime($iter{"date"})) . "</span></td>\n";
		if ($iter['authorized'] != 1) {
			$rhtml .= "<td><span class=alert>Note: This code was not entered by an authorized user. Only authorized codes may be uploaded to the Open Medical Billing Network for processing. If you wish to upload these codes, please select an authorized user here.</span></td>\n";
		}
		else {
			$rhtml .= "<td></td>\n";
		}
		if ($last_encounter_id != $this_encounter_id) {
			$rhtml .= "<td><input type='checkbox' value='" . $iter['bill_process'] . "$procstatus' name='claims[" . $this_encounter_id . "][bill]' onclick='set_button_states()'>&nbsp;</td>\n";
		}
		else {
			$rhtml .= "<td></td>\n";
		}
		$rhtml .= "</tr>\n";
		$last_encounter_id = $this_encounter_id;
	}

	if ($lhtml) {
		while ($rcount < $lcount) {
			$rhtml .= "<tr bgcolor='$bgcolor'><td colspan='7'>&nbsp;</td></tr>";
			++$rcount;
		}
		echo "<tr bgcolor='$bgcolor'>\n<td rowspan='$rcount' valign='top'>\n$lhtml</td>$rhtml\n";
		echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n";
	}
}

?>

</table>

</form>
<script>
set_button_states();

<?
	if ($alertmsg) {
		echo "alert('$alertmsg');\n";
	}
?>

</script>
</body>
</html>
