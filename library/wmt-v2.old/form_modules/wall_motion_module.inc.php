<?php 
if(!isset($field_prefix)) $field_prefix='';
?>
<!--
<input name="tmp_nuc_image_div" id="tmp_nuc_image_div" type="checkbox" value="1" <?php // echo $dt{'tmp_nug_image_div'} == '1' ? ' checked ' : ''; ?> onClick="ToggleDivDisplay('nuc_image_div','tmp_nuc_image_div');" style="padding-left: 8px;"/>
<span class="wmtLabel"><label for="tmp_nuc_image_div">Nuclear Imaging</label></span>
	<fieldset style="border: solid 1px gray; padding: 6px;"><legend class="wmtLabel">&nbsp;Text Builder&nbsp;</legend>
-->
		<table width="100%" border="0" cellspacing="0" cellpadding="4" style="table-layout: fixed;">
			<tr>
				<td style="width: 14%;"><select name="tmp_wall_chc_1" id="tmp_wall_chc_1" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_wall_chc_1', 'wall_motion_chc1', 'wall_motion_nt');" >
					<?php SelMultiWithDesc('','nuc_wall_group_1'); ?>
				</select></td>
				<td style="width: 12%;"><select name="tmp_wall_chc_2" id="tmp_wall_chc_2" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_wall_chc_2', 'wall_motion_chc2', 'wall_motion_nt');" >
					<?php SelMultiWithDesc('','nuc_wall_group_2'); ?>
				</select></td>
				<td style="width: 14%;"><select name="tmp_wall_chc_3" id="tmp_wall_chc_3" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_wall_chc_3', 'wall_motion_chc3', 'wall_motion_nt');" >
					<?php SelMultiWithDesc('','nuc_wall_group_3'); ?>
				</select></td>
				<td style="width: 30%;"><select name="tmp_wall_chc_4" id="tmp_wall_chc_4" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_wall_chc_4', 'wall_motion_chc1', 'wall_motion_nt');" >
					<?php SelMultiWithDesc('','nuc_wall_group_4'); ?>
				</select></td>
				<td style="width: 30%;"><select name="tmp_wall_chc_5" id="tmp_wall_chc_5" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_wall_chc_5', 'wall_motion_chc5', 'wall_motion_nt');" >
					<?php SelMultiWithDesc('','nuc_wall_group_5'); ?>
				</select></td>
			</tr>
			<tr>
				<td colspan="6"><textarea name="wall_motion_nt" id="wall_motion_nt" class="bkkFullInput" rows="4"><?php echo $dt{'wall_motion_nt'}; ?></textarea></td>
			</tr>
		</table>
<!--
	</fieldset>
	<input name="wall_motion_chc1" id="wall_motion_chc1" value="<?php // echo $dt{'wall_motion_chc1'}; ?>" type="hidden" tabindex="-1" />
	<input name="wall_motion_chc2" id="wall_motion_chc2" value="<?php // echo $dt{'wall_motion_chc2'}; ?>" type="hidden" tabindex="-1" />
	<input name="wall_motion_chc3" id="wall_motion_chc3" value="<?php // echo $dt{'wall_motion_chc3'}; ?>" type="hidden" tabindex="-1" />
	<input name="wall_motion_chc4" id="wall_motion_chc4" value="<?php // echo $dt{'wall_motion_chc4'}; ?>" type="hidden" tabindex="-1" />
	<input name="wall_motion_chc5" id="wall_motion_chc5" value="<?php // echo $dt{'wall_motion_chc5'}; ?>" type="hidden" tabindex="-1" />
-->
