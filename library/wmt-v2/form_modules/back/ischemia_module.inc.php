<?php 
if(!isset($field_prefix)) $field_prefix='';
?>
<!--
<input name="tmp_isch_image_div" id="tmp_isch_image_div" class="wmtL" type="checkbox" value="1" <?php // echo $dt{'tmp_isch_image_div'} == '1' ? ' checked ' : ''; ?> onClick="ToggleDivDisplay('isch_image_div','tmp_isch_image_div');" style="padding-left: 8px;"/>
<span class="bkkLabel"><label for="tmp_isch_image_div">Ischemia</label></span>
<div id="isch_image_div">
	<fieldset style="border: solid 1px gray; padding: 6px;"><legend class="bkkLabel">&nbsp;Text Builder&nbsp;</legend>
-->
		<table width="100%" border="0" cellspacing="0" cellpadding="4" style="table-layout: fixed; ">
			<tr>
				<td style="width: 20%;"><select name="tmp_isch_chc_1" id="tmp_isch_chc_1" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_isch_chc_1', 'ischemia_chc1', 'ischemia_nt');" >
					<?php SelMultiWithDesc('','nuc_isch_group_1'); ?>
				</select></td>
				<td style="width: 20%;"><select name="tmp_isch_chc_2" id="tmp_isch_chc_2" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_isch_chc_2', 'ischemia_chc2', 'ischemia_nt');" >
					<?php SelMultiWithDesc('','nuc_isch_group_2'); ?>
				</select></td>
				<td style="width: 20%;"><select name="tmp_isch_chc_3" id="tmp_isch_chc_3" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_isch_chc_3', 'ischemia_chc3', 'ischemia_nt');" >
					<?php SelMultiWithDesc('','nuc_isch_group_3'); ?>
				</select></td>
				<td style="width: 40%;"><select name="tmp_isch_chc_4" id="tmp_isch_chc_4" class="bkkFullInput" onchange="UpdateSelTextArea('tmp_isch_chc_4', 'ischemia_chc4', 'ischemia_nt');" >
					<?php SelMultiWithDesc('','nuc_isch_group_4'); ?>
				</select></td>
			</tr>
			<tr>
				<td colspan="4"><textarea name="ischemia_nt" id="ischemia_nt" class="bkkFullInput" rows="4"><?php echo $dt{'ischemia_nt'}; ?></textarea></td>
			</tr>
		</table>
	<!--
	</fieldset>
	<input name="ischemia_chc1" id="ischemia_chc1" value="<?php // echo $dt{'ischemia_chc1'}; ?>" type="hidden" tabindex="-1" />
	<input name="ischemia_chc2" id="ischemia_chc2" value="<?php // echo $dt{'ischemia_chc2'}; ?>" type="hidden" tabindex="-1" />
	<input name="ischemia_chc3" id="ischemia_chc3" value="<?php // echo $dt{'ischemia_chc3'}; ?>" type="hidden" tabindex="-1" />
	<input name="ischemia_chc4" id="ischemia_chc4" value="<?php // echo $dt{'ischemia_chc4'}; ?>" type="hidden" tabindex="-1" />
</div>
-->
