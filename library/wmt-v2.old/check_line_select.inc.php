<?php
if(!isset($field_id)) $field_id = 'form';
if(!isset($dt{$field_id.'_chk'})) $dt{$field_id.'_chk'} = '';
if(!isset($dt{$field_id.'_chc'})) $dt{$field_id.'_chc'} = '';
if(!isset($dt{$field_id.'_nt'})) $dt{$field_id.'_nt'} = '';
if(!isset($label)) $label = 'The label for this input is not defined';
if(!isset($list)) $list = 'YesNo';
if(!isset($nt_label)) $nt_label = '';
if(!isset($nt_type)) $nt_type = 'textarea';
if(!isset($nt_rows)) $nt_rows = 3;
if(!isset($suppress_nt)) $suppress_nt = FALSE;
?>
<tr>
	<td class="wmtCheckCell"><input name="<?php echo $field_id; ?>_chk" id="<?php echo $field_id; ?>_chk" type="checkbox" value="1" <?php echo $dt{$field_id.'_chk'} ? 'checked="checked" ' : ''; ?> />
	<td><label class="wmtBody" for="<?php echo $field_id; ?>_chk"><?php echo htmlspecialchars($label, ENT_QUOTES, '', FALSE); ?></label></td>
	<td><select name="<?php echo $field_id; ?>_chc" id="<?php echo $field_id; ?>_chc" class="wmtInput"><?php ListSel($dt{$field_id.'_chc'},$list); ?></select></td>
</tr>
<?php if($nt_label) { ?>
<tr>
	<td class='wmtBody'><?php echo htmlspecialchars($label, ENT_QUOTES, '', FALSE); ?></td>
</tr>
<tr>
<?php } ?>
<?php if(!$suppress_nt) {?>
<tr>
	<td>&nbsp;</td>
	<td colspan="2">
		<?php if($nt_type == 'textarea') { ?>
		<textarea name="<?php echo $field_id; ?>_nt" id="<?php echo $field_id; ?>_nt" class="wmtFullInput" rows="<?php echo $nt_rows; ?>"><?php echo htmlspecialchars($dt{$field_id.'_nt'}, ENT_QUOTES); ?></textarea>
		<?php } else { ?>
		<input name="<?php echo $field_id; ?>_nt" id="<?php echo $field_id; ?>_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{$field_id.'_nt'}, ENT_QUOTES); ?>" />
		<?php } ?>
	</td>
</tr>
<?php } ?>
