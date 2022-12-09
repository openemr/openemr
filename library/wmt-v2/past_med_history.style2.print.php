<?php
$_print= false;
if(!isset($fyi->fyi_pmh_nt)) $fyi->fyi_pmh_nt = '';
if(!isset($pmh)) $pmh = array();
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title= xl('Medical History','r');
if((count($pmh) > 0)) || $pmh_note != '') $_print= true;
if($_print) {
?>
<fieldset style='border: solid 1px black; padding: 0px; border-collapse: collapse;'><legend class='wmtPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3' style='border-collapse: collapse; margin-top: 4px;'>
<?php
	if(count($pmh) > 0) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabel wmtPrnC'>Issue</td>\n";
		echo "			<td class='wmtPrnLabel wmtPrnC'>Notes</td>\n";
		echo "		</tr>\n";
	}
	foreach($pmh as $prev) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnBody'>&nbsp;",htmlspecialchars(ListLook($prev['pmh_type'],'Medical_History_Problems'), ENT_QUOTES, '', FALSE),"</td>\n";
		echo "			<td class='wmtPrnBody'>&nbsp;",htmlspecialchars($prev['pmh_nt'], ENT_QUOTES, '', FALSE),"</td>\n";
		echo "		</tr>\n";
	}
	if($fyi->fyi_pmh_nt) {
		echo "		<tr>\n";
		echo "			<td colspan='2' class='wmtPrnLabel'>&nbsp;&nbsp;Other Notes:</td>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td colspan='2' class='wmtPrnBody'>",htmlspecialchars($fyi->fyi_pmh_nt, ENT_QUOTES, '', FALSE),"</td>\n";
		echo "		</tr>\n";
	}
	echo "	</table>\n";
	echo "</fieldset>\n";
	$pane_printed = true;
}
?>
