<?php 
$rows = checkSettingMode('wmt::hpi_rows','',$frmdir);
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
$local_fields = array('lifestyle_nt' => 'Lifestyle Notes',
	'born_nt' => 'Born and Raised',
	'sohist_nt' => 'Social History',
	'education_nt' => 'Education',
	'military_nt' => 'Military Service',
	'legal_nt' => 'Legal',
	'work_nt' => 'Work History',
	'abuse_nt' => 'Sexual &amp; Physical Abuse',
	'relationships_nt' => 'Relationships',
	'religion_nt' => 'Religion',
	'recreation_nt' => 'Recreation &amp; Hobbies',
	'exercise_nt' => 'Exercise',
	'music_nt' => 'Music',
	'social_nt' => 'Other Notes');
foreach($local_fields as $key => $title) {
	if(!isset($dt[$field_prefix.$key])) $dt[$field_prefix.$key] = '';
	if(!isset($pat_entries[$key])) $pat_entries[$key] = $portal_data_layout;
?>
	<div class="<?php echo (($portal_mode)?'bkkLabel':'wmtLabel'); ?>" style="float: left; padding-left: 8px; margin-top: 4px;"><?php echo $title; ?></div>
	<div style="float: right; padding-right: 12px; margin-top: 4px;"><a class="css_button" tabindex="-1" onClick="document.forms[0].elements['<?php echo $field_prefix.$key; ?>'].value = '';" href="javascript:;"><span>Clear</span></a></div>
	<div style="margin: 4px;">
		<textarea name="<?php echo $field_prefix.$key; ?>" id="<?php echo $field_prefix.$key; ?>" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" rows="4"><?php echo htmlspecialchars($dt{$field_prefix.$key}, ENT_QUOTES, '', FALSE); ?></textarea>
	</div>
<?php 
}
?>
