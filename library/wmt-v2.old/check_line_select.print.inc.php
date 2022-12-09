<?php
if(!isset($field_id)) $field_id = 'form';
if(!isset($dt{$field_id.'_chk'})) $dt{$field_id.'_chk'} = '';
if(!isset($dt{$field_id.'_chc'})) $dt{$field_id.'_chc'} = '';
if(!isset($dt{$field_id.'_nt'})) $dt{$field_id.'_nt'} = '';
if(!isset($label)) $label = 'The label for this input is not defined';
if(!isset($list)) $list = 'YesNo';
if(!isset($nt_label)) $nt_label = '';
if(!isset($suppress_nt)) $suppress_nt = FALSE;
$checked = $GLOBALS['webroot'].'/library/wmt-v2/16x16_checkbox_yes.png';
$unchecked = $GLOBALS['webroot'].'/library/wmt-v2/16x16_checkbox_no.png';
?>
<tr>
	<td style="width: 22px;"><img src="<?php echo $dt{$field_id.'_chk'} ? $checked : $unchecked; ?>"  border="0" alt="<?php echo $dt{$field_id.'_chk'} ? '[x]' : '[ ]'; ?>" /></td>
	<td class="wmtPrnBody"><?php echo htmlspecialchars($label, ENT_QUOTES, '', FALSE); ?></td>
	<td class="wmtPrnBody"><?php echo ListLook($dt{$field_id.'_chc'},$list); ?></td>
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
	<td colspan="2" class="wmtPrnBody"><?php echo htmlspecialchars($dt{$field_id.'_nt'}, ENT_QUOTES); ?></td>
</tr>
<?php } ?>
