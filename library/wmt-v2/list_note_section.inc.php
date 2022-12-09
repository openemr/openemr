<?php
if(!isset($dt[$note_field])) $dt[$note_field] = '';
if($note_field) {
	if(!isset($fyi->$note_field)) $fyi->$note_field = '';
	if(!isset($pat_entries[$note_field])) 
			$pat_entries[$note_field] = $portal_data_layout;
	if((checkSettingMode('wmt::'.$note_field, '', $frmdir) != 'none') || $portal_mode) {
?>
<div style="padding: 6px;">
	<div class="<?php echo $portal_mode ? 'bkkL bkkLabel' : 'wmtL wmtLabel'; ?>">Notes:</div>
	<div class="wmtL wmtBody" style="white-space: pre-wrap;"><?php echo htmlspecialchars($fyi->$note_field, ENT_QUOTES, '', FALSE); ?></div>
	<div><textarea name="<?php echo $note_field; ?>" id="<?php echo $note_field; ?>" rows="4" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>"><?php echo htmlspecialchars($dt[$note_field], ENT_QUOTES, '', FALSE); ?></textarea></div>
<?php
		if($pat_entries_exist && !$portal_mode) {
			if($pat_entries[$note_field]['content'] && (strpos($dt{$field_prefix.$note_field},$pat_entries[$note_field]['content']) === false)) {
?>
			<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
			<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" style="padding: 6px;" id="tmp_<?php echo $field_prefix.$note_field; ?>" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries[$note_field]['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php
			}
		}
echo '</div>';
	}
}
?>
