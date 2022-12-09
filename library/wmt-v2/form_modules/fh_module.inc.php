<?php ?>
		<?php include($GLOBALS['srcdir'].'/wmt/family_history.inc.php'); ?>
  	<table width="100%" border="0" cellspacing="0" cellpadding="0"> 
		<tr>
				<td class="wmtLabel" colspan="3" style="border-top: solid 1px black;">Has anyone in your family ever been diagnosed with:</td>
      	<td style="border-top: solid 1px black;"><div style="float: right; padding-right: 20px;"><a href="javascript:;" tabindex="-1" class="css_button_small" onclick="toggleFamilyExtraNo();"><span>Set All to 'NO'</span></a></div></td>
			</tr>
			<tr>
			<?php if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
       	<td style="width: 52px"><select name="tmp_fh_rs_hd" id="tmp_fh_rs_hd" class="wmtInput">
      	<?php ListSel($dt{'tmp_fh_rs_hd'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Heart Disease</td>
			<?php } else { ?>
       	<td style="width: 52px"><select name="tmp_fh_rs_dia" id="tmp_fh_rs_dia" class="wmtInput">
      	<?php ListSel($dt{'tmp_fh_rs_dia'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Diabetes Mellitus</td>
			<?php } ?>
       	<td style="width: 52px"><select name="tmp_fh_rs_coronary" id="tmp_fh_rs_coronary" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_coronary'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Coronary Artery Disease</td>
			</tr>
			<tr>
       	<td><select name="tmp_fh_rs_htn" id="tmp_fh_rs_htn" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_htn'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">HTN</td>
       	<td><select name="tmp_fh_rs_hyper" id="tmp_fh_rs_hyper" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_hyper'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Hyperlipidemia</td>
			</tr>
			<tr>
			<?php if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
       	<td><select name="tmp_fh_rs_cardiac" id="tmp_fh_rs_cardiac" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_cardiac'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Sudden Cardiac Death</td>
       	<td><select name="tmp_fh_rs_vhd" id="tmp_fh_rs_vhd" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_vhd'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Valvular Heart Disease</td>
			<?php } else { ?>
       	<td><select name="tmp_fh_rs_thy" id="tmp_fh_rs_thy" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_thy'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Thyroid Cancer</td>
       	<td><select name="tmp_fh_rs_colon" id="tmp_fh_rs_colon" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_colon'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Colon Cancer</td>
			<?php } ?>
			</tr>
			<tr>
			<?php if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
       	<td><select name="tmp_fh_rs_arr" id="tmp_fh_rs_arr" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_arr'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Arrhythmia</td>
			<?php } else { ?>
       	<td><select name="tmp_fh_rs_lung" id="tmp_fh_rs_lung" class="wmtInput">
       	<?php ListSel($dt{'tmp_fh_rs_lung'},'Yes_No'); ?>
       	</select></td>
       	<td class="wmtBody">Lung Cancer</td>
			<?php } ?>
       	<td>&nbsp;</td>
       	<td>&nbsp;</td>
			</tr>
		</table>
