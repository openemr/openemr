<?php
if(!isset($frmdir)) $frmdir = 'nt';
if(!isset($field_name)) $field_name = 'nt';
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($button1)) $button1 = '';
if(!isset($button2)) $button2 = '';
if(!isset($dt[$field_prefix.$field_name])) $dt[$field_prefix.$field_name] = '';
$lbl = 'Notes';
if(isset($module['title'])) $lbl = $module['title'];
if(isset($module['notes']) && $module['notes'] != '') {
	if(strpos(strtoupper($module['notes']), 'NEGATIVE') === false) {
		$lbl = $module['notes'];
	}
}
if(!isset($use_snippets)) 
	$use_snippets = checkSettingMode('wmt::use_snippets','',$frmdir);
?>
<div class="form-row">
	<div class="form-group col-lg-12">
		<div>
			<label><?php xl($lbl, 'e'); ?></label>
			<?php include(FORM_BUTTONS . 'btn_snippets.inc.php'); ?>
			<?php include(FORM_BUTTONS . 'btn_clear_field.inc.php'); ?>
			<?php $button2 ? include($button2) : ''; ?>
			<?php $button1 ? include($button1) : ''; ?>
		</div>
		<textarea class="form-control" name="<?php echo $field_prefix.$field_name; ?>" id="<?php echo $field_prefix.$field_name; ?>" placeholder="Case Related Notes" rows="3"><?php echo htmlspecialchars($dt{$field_prefix.$field_name}, ENT_QUOTES); ?></textarea>
	</div>
</div>
