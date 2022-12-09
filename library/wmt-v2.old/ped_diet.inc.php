<?php 
if(!isset($hide_ped_diet_clear_button)) { $hide_ped_diet_clear_button = false; }
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Diet:</td>
        <td class="wmtBody"><input name="pad_diet_form" id="pad_diet_form" type="checkbox" value="1" <?php echo (($ad{'pad_diet_form'} == '1')?' checked ':''); ?> />&nbsp;Formula</td>
        <td class="wmtBody"><input name="pad_diet_fed" id="pad_diet_fed" type="checkbox" value="1" <?php echo (($ad{'pad_diet_fed'} == '1')?' checked ':''); ?> />&nbsp;Breast Fed</td>
        <td class="wmtBody"><input name="pad_diet_oth" id="pad_diet_oth" type="checkbox" value="1" <?php echo (($ad{'pad_diet_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
				<td>
			<?php if($hide_ped_diet_clear_button) { ?>
				&nbsp;
			<?php } else { ?>
				<div style="float: right; margin-right: 10px;"><a class="css_button" tabindex="-1" onClick="toggleDietExamNull();" href="javascript:;"><span>Clear Section</span></a></div>
			<?php } ?>
				</td>
      </tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_diet_type_nt" id="pad_diet_type_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_diet_type_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Feeding Tube:</td>
        <td class="wmtBody"><select name="pad_diet_tube" id="pad_diet_tube" class="wmtInput"><?php ListSel($ad{'pad_diet_tube'},'EE1_YesNo'); ?></select></select></td>
				<td class="wmtBody">Type:</td>
				<td><input name="pad_diet_ttype" id="pad_diet_ttype" class="wmtInput" type="text" value="<?php echo $ad{'pad_diet_ttype'}; ?>" /></td>
				<td class="wmtBody">Size:&nbsp;&nbsp;</td>
				<td><input name="pad_diet_tsize" id="pad_diet_tsize" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_diet_tsize'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtLabel">Specifics:</td>
        <td class="wmtBody">Formula Choice:</td>
				<td colspan="5"><input name="pad_diet_chc" id="pad_diet_chc" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_diet_chc'}; ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtBody">Amount:</td>
				<td colspan="2"><input name="pad_diet_amt" id="pad_diet_amt" class="wmtInput" type="text" value="<?php echo $ad{'pad_diet_amt'}; ?>" /></td>
        <td class="wmtBody">Rate of Feed:</td>
				<td><input name="pad_diet_rate" id="pad_diet_rate" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_diet_rate'}; ?>" /></td>
			</tr>
			<tr>
				<td class="wmtLabel" colspan="3">Special Requirements:</td>
			</tr>
				<td colspan="6"><textarea name="pad_diet_require" id="pad_diet_require" class="wmtFullInput" rows="4"><?php echo $ad{'pad_diet_require'}; ?></textarea></td>
			</tr>
			<tr>
				<td class="wmtLabel" colspan="3">Other Notes:</td>
			</tr>
			<tr>
				<td colspan="6"><textarea name="pad_diet_nt" id="pad_diet_nt" class="wmtFullInput" rows="3"><?php echo $ad{'pad_diet_nt'}; ?></textarea></td>
			</tr>
    </table>
