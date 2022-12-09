<?php 
include_once($GLOBALS['srcdir'] . '/classes/Document.class.php');
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($fyi->fyi_well_nt)) $fyi->fyi_well_nt = '';
if(!isset($dt['fyi_well_nt'])) $dt['fyi_well_nt'] = $fyi->fyi_well_nt;
$local_fields = array('fyi_well_nt' );
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
$wellness_modules = LoadList('well_'.$frmdir);
echo "</table>\n";

foreach($wellness_modules as $wmod) {
	$winc = $wmod['option_id'] . '_module.inc.php';
	if(is_file("./$winc")) {
		include("./$winc");
	} else if(is_file($GLOBALS['srcdir']."/wmt-v2/form_modules/".$winc)) {
		include($GLOBALS['srcdir']."/wmt-v2/form_modules/".$winc);
	}
}
?>
	
<span class="wmtNoteLabelDiv">Notes:</span>
<br>
<div class="wmtNoteInputDiv"><textarea name="fyi_well_nt" id="fyi_well_nt" rows="4" class="wmtFullInput" ><?php echo htmlspecialchars($dt['fyi_well_nt'], ENT_QUOTES); ?></textarea></div>

	<?php
	if($pat_entries_exist && !$portal_mode) {
		if($pat_entries['fyi_well_nt']['content'] && (strpos($dt['fyi_well_nt']['content'],$pat_entries['fyi_well_nt']['content']) === false)) {
	?>
	<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
	<div class="wmtPortalData wmtBorderHighlight wmtL" style="margin: 6px;" id="tmp_fyi_well_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_well_nt']['content'], ENT_QUOTES); ?></div>
<?php
	}
}
if($review = checkSettingMode('wmt::wellness_review','',$frmdir)) {
	$caller = 'wellness';
	$chk_title = 'Wellness';
	include($GLOBALS['srcdir'].'/wmt-v2/form_bricks/module_reviewed.inc.php');
}
?>
<script>
<?php include(FORM_JS_DIR . 'well_full.js.php'); ?>
</script>
