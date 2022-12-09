<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($rows)) $rows = 4;
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">1st Trimester:</td>
				<td style="width: 40%">&nbsp;</td>
				<td><div style="float: right; padding-right: 12px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>tri_1_material_nt');" href="javascript:;"><span>Clear</span></a></div></td>
			</tr>
			<tr>
				<?php if($rows == 1) { ?>
				<td colspan="3"><input name="<?php echo $field_prefix; ?>tri_1_material_nt" id="<?php echo $field_prefix; ?>tri_1_material_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'tri_1_material_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<?php } else { ?>
				<td colspan="3"><textarea name="<?php echo $field_prefix; ?>tri_1_material_nt" id="<?php echo $field_prefix; ?>tri_1_material_nt" class="wmtFullInput" rows="<?php echo $rows; ?>"><?php echo htmlspecialchars($dt{$field_prefix.'tri_1_material_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
				<?php } ?>
			</tr>
      <tr>
        <td class="wmtLabel">2nd Trimester:</td>
				<td>&nbsp;</td>
				<td><div style="float: right; padding-right: 12px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>tri_2_material_nt');" href="javascript:;"><span>Clear</span></a></div></td>
			</tr>
			<tr>
				<?php if($rows == 1) { ?>
				<td colspan="3"><input name="<?php echo $field_prefix; ?>tri_2_material_nt" id="<?php echo $field_prefix; ?>tri_2_material_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'tri_2_material_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<?php } else { ?>
				<td colspan="3"><textarea name="<?php echo $field_prefix; ?>tri_2_material_nt" id="<?php echo $field_prefix; ?>tri_2_material_nt" class="wmtFullInput" rows="<?php echo $rows; ?>"><?php echo htmlspecialchars($dt{$field_prefix.'tri_2_material_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
				<?php } ?>
			</tr>
      <tr>
        <td class="wmtLabel">3rd Trimester:</td>
				<td>&nbsp;</td>
				<td><div style="float: right; padding-right: 12px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>tri_3_material_nt');" href="javascript:;"><span>Clear</span></a></div></td>
			</tr>
			<tr>
				<?php if($rows == 1) { ?>
				<td colspan="3"><input name="<?php echo $field_prefix; ?>tri_3_material_nt" id="<?php echo $field_prefix; ?>tri_3_material_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'tri_3_material_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<?php } else { ?>
				<td colspan="3"><textarea name="<?php echo $field_prefix; ?>tri_3_material_nt" id="<?php echo $field_prefix; ?>tri_3_material_nt" class="wmtFullInput" rows="<?php echo $rows; ?>"><?php echo htmlspecialchars($dt{$field_prefix.'tri_3_material_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
				<?php } ?>
			</tr>
    </table>
<?php ?>
