<?php
if(!isset($unlink_allow)) $unlink_allow = false;
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($formdir)) $formdir= '';
if(!isset($imm)) $imm = array();
if(!isset($pat_entries['fyi_imm_nt'])) 
					$pat_entries['fyi_imm_nt'] = $portal_data_layout;
if(!isset($dt['fyi_imm_nt'])) $dt['fyi_imm_nt'] = '';
if(isset($fyi->fyi_imm_nt)) $dt['fyi_imm_nt'] = $fyi->fyi_imm_nt;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'LabelCenterBorderB'); ?>" style="width: 95px">Date</td>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'LabelCenterBorderLB'); ?>">Immunization</td>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'LabelCenterBorderLB'); ?>">Notes</td>
<?php if(!$portal_mode) { ?>
	<?php if($unlink_allow) { ?>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'LabelCenterBorderLB'); ?>" style="width: 115px">&nbsp;</td>
	<?php } else { ?>
		<td class="<?php echo (($portal_mode)?'bkkLabel bkkC bkkBorder1B':'LabelCenterBorderLB'); ?>" style="width: 65px">&nbsp;</td>
	<?php } ?>
<?php } ?>
	</tr>
<?php
$bg = 'bkkLight';
$cnt=1;
if(count($imm) > 0) {
	foreach($imm as $prev) {
?>
	<tr class="<?php echo $portal_mode ? $bg : ''; ?>">
		<td class="<?php echo $portal_mode ? 'bkkBody' : 'BodyBorderB'; ?>"><input name="imm_id_<?php echo $cnt; ?>" id="imm_id_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $prev['id']; ?>" /><?php echo substr($prev['administered_date'],0,10); ?>&nbsp;</td>
		<td class="<?php echo $portal_mode ? 'bkkBody' : 'BodyBorderLB'; ?>"><span class="cvx_code" data-cvx="<?php echo $prev['cvx_code']; ?>" data-id="<?php echo $cnt; ?>"><?php echo ImmLook($prev['cvx_code'],'immunizations'); ?></span>&nbsp;</td>
		<td class="<?php echo $portal_mode ? 'bkkBody' : 'BodyBorderLB'; ?>">
		<?php if(!$portal_mode) { ?>
			<input name="imm_comments_<?php echo $cnt; ?>" id="imm_comments_<?php echo $cnt; ?>" type="text" class="FullInput" tabindex="-1" value="<?php echo $prev['note']; ?>" /></td>
		<?php
		} else {
			echo htmlspecialchars($prev['note'], ENT_QUOTES, '', FALSE);
		} ?>
		<?php if(!$portal_mode) { ?>
		<td class="<?php echo (($portal_mode)?'bkkBody':'BodyBorderLB'); ?>" style="padding 0px;"><div style="float: left; padding-left: 2px;"><a class="css_button_small" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','updateimm','imm_id_','Immunization');" href="javascript:;"><span>Update</span></a></div>
<?php if($unlink_allow) { ?>
			<div style="float: left; padding-left: 2px;"><a class="css_button_small" tabindex="-1" onClick="return SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>','unlinkimm','imm_id_','Immunization');" href="javascript:;"><span>Un-Link</span></a></div>
<?php } ?>
		</td>
		<?php } ?>
	</tr>
<?php 
		$cnt++;
		$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');
	}
} else {
?>
	<tr class="<?php echo $portal_mode ? $bg : ''; ?>">
		<td>&nbsp;</td>
		<td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel wmtBorder1L'; ?>">None on File</td>
		<td class="<?php echo $portal_mode ? '' : 'wmtBorder1L'; ?>">&nbsp;</td>
		<?php if(!$portal_mode) { ?>
		<td class="<?php echo $portal_mode ? '' : 'wmtBorder1L'; ?>">&nbsp;</td>
		<?php } ?>
	</tr>
<?php 
}
$cols = ($portal_mode ? 3 : 4);
if(checkSettingMode('wmt::fyi_imm_nt', '', $frmdir) || $portal_mode) {
?>
	<tr>
		<td class="<?php echo $portal_mode ? 'bkkLabel bkkBorder1T' : 'wmtLabel wmtBorder1T'; ?>" colspan="4">Notes:</td>
	</tr>
	<tr>
		<td colspan="4"><textarea name="fyi_imm_nt" id="fyi_imm_nt" rows="4" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>"><?php echo htmlspecialchars($dt['fyi_imm_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
<?php
}
?>
</table>
<?php
if($pat_entries_exist && !$portal_mode) {
	if($pat_entries['fyi_imm_nt']['content'] && (strpos($dt{$field_prefix.'fyi_imm_nt'},$pat_entries['fyi_imm_nt']['content']) === false)) {
?>

		<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
		<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" style="padding: 6px;" id="tmp_fyi_imm_nt" onclick="AcceptPortalData(this.id);" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>"><?php echo htmlspecialchars($pat_entries['fyi_imm_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php
	}
}
?>
