<?php 
if(!isset($field_prefix)) $field_prefix='';
?>
<!--
<input name="tmp_isch_image_div" id="tmp_isch_image_div" class="wmtL" type="checkbox" value="1" <?php // echo $dt{'tmp_isch_image_div'} == '1' ? ' checked ' : ''; ?> onClick="ToggleDivDisplay('isch_image_div','tmp_isch_image_div');" style="padding-left: 8px;"/>
<span class="bkkLabel"><label for="tmp_isch_image_div">Ischemia</label></span>
<div id="isch_image_div">
	<fieldset style="border: solid 1px gray; padding: 6px;"><legend class="bkkLabel">&nbsp;Text Builder&nbsp;</legend>
-->
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="table-layout: fixed; ">
<tr>
	<td style="width: 50%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="4" style="table-layout: fixed; ">
		<tr>
			<td class="bkkLabel">Reviewed w/pt:</td>
			<td>
				&nbsp;
				<span id="ce_ecg" class="bkkBodyClickable" onclick="UpdateNarrativeTextArea('ce_ecg', 'ECG', 'ce_summary_nt', 'Reviewed with patient:');" >ECG</span>
				&nbsp;/&nbsp;
				<span id="ce_echo" class="bkkBodyClickable" onclick="UpdateNarrativeTextArea('ce_echo', 'Echo', 'ce_summary_nt', 'Reviewed with patient:');" >Echo</span>
				&nbsp;/&nbsp;
				<span id="ce_carotid" class="bkkBodyClickable" onclick="UpdateNarrativeTextArea('ce_carotid', 'Carotid', 'ce_summary_nt', 'Reviewed with patient:');" >Carotid</span>
				&nbsp;/&nbsp;
				<span id="ce_st_test" class="bkkBodyClickable" onclick="UpdateNarrativeTextArea('ce_st_test', 'Stress Test', 'ce_summary_nt', 'Reviewed with patient:');" >St Test</span>
				&nbsp;/&nbsp;
				<span id="ce_nuc" class="bkkBodyClickable" onclick="UpdateNarrativeTextArea('ce_nuc', 'Nuclear', 'ce_summary_nt', 'Reviewed with patient:');" >Nuc</span>
				<input type="hidden" value="<?php echo $dt{'ce_review'}; ?>" id="ce_review" name="ce_review" />	
		</tr>
		<tr>
			<td class="bkkLabel">HTN:</td>
			<td>
				&nbsp;
				<a href="javascript:;" id="htn_control" class="bkkBodyClickable" onclick="UpdateNarrativeTextArea('htn_control', 'Controlled', 'ce_summary_nt', 'HTN (per JNC 7 guidelines, target BP 130/80');" >Controlled</a>
				&nbsp;/&nbsp;
				<a href="javascript:;" id="htn_better" class="bkkBodyClickable" onclick="UpdateNarrativeTextArea('htn_better', 'Better', 'ce_summary_nt', 'HTN (per JNC 7 guidelines, target BP 130/80');" >Better</a>
				&nbsp;/&nbsp;
				<a href="javascript:;" id="htn_high" class="bkkBodyClickable" onclick="UpdateNarrativeTextArea('htn_high', 'BP High', 'ce_summary_nt', 'HTN (per JNC 7 guidelines, target BP 130/80');" >BP High</a>
			</td>
		</tr>
		</table>
	</td>
	<td style="width: 50%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="4" style="table-layout: fixed; ">
		<tr>
			<td><textarea name="ce_summary_nt" id="ce_summary_nt" class="bkkFullInput" rows="8"><?php echo $dt{'ce_summary_nt'}; ?></textarea></td>
		</tr>
		</table>
	</td>
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
