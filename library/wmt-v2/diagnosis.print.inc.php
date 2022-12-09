<?php
// Count to see if any are actually attached to this encounter 
// rather than just checking the array
if(!isset($client_id)) $client_id = '';
if(!isset($diag)) $diag = array();
if(!isset($GLOBALS['wmt::link_diag_education'])) 
				$GLOBALS['wmt::link_diag_education'] = FALSE;
$suppress_plan = checkSettingMode('wmt::suppress_diag_plan','',$frmdir);
$use_sequence = checkSettingMode('wmt::diag_use_sequence','',$frmdir);
$suppress_class = '';
if($suppress_plan) $suppress_class = 'wmtBorder1B';
$chp_printed= false;
$_print = false;
if(!isset($dt['tmp_diag_window_mode'])) $dt['tmp_diag_window_mode'] = 'encounter';
if($dt['tmp_diag_window_mode'] == 'encounter') {
	foreach($diag as $prev) {
		if($prev['encounter'] == $encounter) $_print = true;
	}
}
if(!isset($use_sequence)) $use_sequence = true;
if($frmdir == 'dashboard') $use_sequence = false;
// Also Allow the print div and table to remain open
$close= true;
if(isset($leave_diag_div_open)) {
	if($leave_diag_div_open) $close= false;
}
if($_print) {
	$chp_printed= true;
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Diagnosis / Plan</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "		<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	echo "			<tr>\n";
	echo "				<td class='wmtPrnLabelBorderB' style='width: 40px;'>",(($use_sequence)?"Seq&nbsp;":""),"#</td>\n";
	echo "				<td class='wmtPrnLabelBorderLB' style='width: 100px;'>Diagnosis</td>\n";
	echo "				<td class='wmtPrnLabelBorderLB'>Title</td>\n";
	echo "			</tr>\n";
	$cnt = 1;
	foreach($diag as $prev) {
		// Skip any problems that loaded but are not related to this encounter
		if($dt['tmp_diag_window_mode'] == 'current') {
			if($prev['enddate']) continue;
		}
		if($frmdir == 'definable_fee' && !$prev['billing_id']) continue;
		if($dt['tmp_diag_window_mode'] == 'encounter') {
			if($prev['encounter'] != $encounter) continue;
		}
		if($pos = strpos($prev['diagnosis'],';')) {
			$remainder = trim(substr($prev['diagnosis'],($pos+1)));
			$prev['diagnosis'] = trim(substr($prev['diagnosis'],0,$pos));
		}
		$desc = GetDiagDescription($prev['diagnosis']);
		if($pos = strpos($prev['diagnosis'],':')) {
			$prev['diagnosis'] = trim(substr($prev['diagnosis'],($pos+1)));
		}
		$bdr_first_line = $bdr_second_line = $bdr_third_line = FALSE;
		if(!$prev['comments']) $bdr_first_line = TRUE;
		if($GLOBALS['wmt::link_diag_education']) {
			if($prev['print_dt'] != '' && $prev['print_dt'] != '0000-00-00') {
				if(!$prev['comments']) {
					$bdr_first_line = FALSE;
					$bdr_second_line = TRUE;
				}
			}
			if($prev['portal_dt'] != '' && $prev['portal_dt'] != '0000-00-00') {
				$prev['portal_url'] = substr($prev['portal_url'],25);
				$prev['portal_url'] = substr($prev['portal_url'],0,-42);
			
				if(!$prev['comments']) {
					$bdr_first_line = $bdr_second_line = FALSE;
					$bdr_third_line = TRUE;
				}
			}
		}
   	echo "			<tr>\n";
		echo "				<td class='wmtPrnLabel";
		echo $bdr_first_line ? ' wmtPrnBorder1B' : ''; 
		echo "'>";
		echo $use_sequence ? htmlspecialchars($prev['seq'],ENT_QUOTES,'',FALSE) : $cnt;
		echo "&nbsp).</td>\n";
		echo "				<td class='wmtPrnBody";
		echo $bdr_first_line ? ' wmtPrnBorder1B' : ''; 
		echo "'>",htmlspecialchars($prev['diagnosis'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		echo "				<td class='wmtPrnBody";
		echo $bdr_first_line ? ' wmtPrnBorder1B' : ''; 
		echo "' style='text-align: left;'>",htmlspecialchars($prev['title'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		echo "			</tr>\n";

		if($GLOBALS['wmt::link_diag_education']) {
			if($prev['print_dt'] != '' && $prev['print_dt'] != '0000-00-00') {
				echo "<tr>\n";
				echo "<td class='wmtPrnBody'>&nbsp;</td>\n";
				echo "<td class='wmtPrnBody";
				echo "' colspan='5'>";
				echo "Educational Materials From the Following Source Were Printed &amp; Given to Patient:";
				echo "</td></tr>\n";
				echo "<tr>\n";
				echo "<td colspan='2' class='wmtPrnBody";
				echo $bdr_second_line ? ' wmtPrnBorder1B' : ''; 
				echo "'>&nbsp;</td>\n";
				echo "<td class='wmtBody";
				echo $bdr_second_line ? ' wmtPrnBorder1B' : ''; 
				echo "' colspan='5'>";
				echo htmlspecialchars($prev['print_url'], ENT_QUOTES);
				echo "</td></tr>\n";
			}
			if($prev['portal_dt'] != '' && $prev['portal_dt'] != '0000-00-00') {
				echo "<tr>\n";
				echo "<td class='wmtPrnBody'>&nbsp;</td>\n";
				echo "<td class='wmtPrnBody";
				echo "' colspan='5'>";
				echo "The Following Link for Educational Materials Was Sent to the Patient Portal:";
				echo "</td></tr>\n";
				echo "<tr>\n";
				echo "<td colspan='2' class='wmtPrnBody";
				echo $bdr_third_line ? ' wmtPrnBorder1B' : ''; 
				echo "'>&nbsp;</td>\n";
				echo "<td class='wmtBody";
				echo $bdr_third_line ? ' wmtPrnBorder1B' : ''; 
				echo "' colspan='5'>";
				echo htmlspecialchars($prev['portal_url'], ENT_QUOTES);
				echo "</td></tr>\n";
			}
		}

		if($prev['comments']) {
			echo "			<tr>\n";
			echo "				<td class='wmtPrnLabel' style='border-bottom: solid 1px black'>Plan:</td>\n";
			echo "				<td colspan='2' class='wmtPrnBody' style='border-bottom: solid 1px black; text-align: left;'>",htmlspecialchars($prev['comments'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
			echo "</tr>\n";
		}
		$cnt++;
	}
	if($close) {
		echo "		</table>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
}
?>
