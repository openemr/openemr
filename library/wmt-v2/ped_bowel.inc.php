<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Diagnosis:</td>
        <td class="wmtBody"><input name="pad_bwl_dx_crhon" id="pad_bwl_dx_crhon" type="checkbox" value="1" <?php echo (($ad{'pad_bwl_dx_crhon'} == '1')?' checked ':''); ?> />&nbsp;Crohn's</td>
        <td class="wmtBody"><input name="pad_bwl_dx_ulcer" id="pad_bwl_dx_ulcer" type="checkbox" value="1" <?php echo (($ad{'pad_bwl_dx_ulcer'} == '1')?' checked ':''); ?> />&nbsp;Ulcerative Colitis</td>
				<td class="wmtBody">EGD:</td>
				<td><input name="pad_bwl_last_egd" id="pad_bwl_last_egd" class="wmtInput" type="text" value="<?php echo $ad{'pad_bwl_last_egd'}; ?>" /></td>
				<td colspan="2"><div style="float:right; margin-right: 10px;"><a class="css_button" tabindex="-1" onClick="toggleBowelExamNull();" href="javascript:;"><span>Clear Section</span></a></div></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtBody" colspan="2"><input name="pad_bwl_dx_ind" id="pad_bwl_dx_ind" type="checkbox" value="1" <?php echo (($ad{'pad_bwl_dx_ind'} == '1')?' checked ':''); ?> />&nbsp;Indeterminate Involvement:</td>
				<td class="wmtBody">UGI/SBFT:</td>
				<td><input name="pad_bwl_dx_ugi" id="pad_bwl_dx_ugi" class="wmtInput" type="text" value="<?php echo $ad{'pad_bwl_dx_ugi'}; ?>" /></td>
				<td class="wmtBody">Colon:</td>
				<td><input name="pad_bwl_dx_colon" id="pad_bwl_dx_colon" class="wmtInput" type="text" value="<?php echo $ad{'pad_bwl_dx_colon'}; ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td class="wmtBody">Last Eye Exam:</td>
				<td><input name="pad_bwl_last_eye" id="pad_bwl_last_eye" class="wmtInput" type="text" value="<?php echo $ad{'pad_bwl_last_eye'}; ?>" /></td>
				<td class="wmtBody">Last Remicade:</td>
				<td><input name="pad_bwl_last_remi" id="pad_bwl_last_remi" class="wmtInput" type="text" value="<?php echo $ad{'pad_bwl_last_remi'}; ?>" /></td>
				<td class="wmtBody">Prometheseus IBD:</td>
				<td><input name="pad_bwl_prom" id="pad_bwl_prom" class="wmtInput" type="text" value="<?php echo $ad{'pad_bwl_prom'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">Abdominal Pain:</td>
				<td class="wmtBody" colspan="6"><input name="pad_bwl_pain" id="pad_bwl_pain" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_pain'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">Stools:</td>
				<td class="wmtBody">Number:</td>
				<td class="wmtBody" colspan="5"><input name="pad_bwl_stl_num" id="pad_bwl_stl_num" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_stl_num'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">&nbsp;</td>
				<td class="wmtBody">Consistency:</td>
				<td class="wmtBody" colspan="5"><input name="pad_bwl_stl_con" id="pad_bwl_stl_con" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_stl_con'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">&nbsp;</td>
				<td class="wmtBody">Blood:</td>
				<td class="wmtBody" colspan="5"><input name="pad_bwl_stl_blood" id="pad_bwl_stl_blood" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_stl_blood'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">&nbsp;</td>
				<td class="wmtBody">Urgency:</td>
				<td class="wmtBody" colspan="5"><input name="pad_bwl_stl_urg" id="pad_bwl_stl_urg" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_stl_urg'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">Energy:</td>
				<td class="wmtBody" colspan="6"><input name="pad_bwl_energy" id="pad_bwl_energy" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_energy'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">Appetite/Diet/Weight:</td>
				<td class="wmtBody" colspan="6"><input name="pad_bwl_diet" id="pad_bwl_diet" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_diet'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">Joints:</td>
				<td class="wmtBody" colspan="6"><input name="pad_bwl_joint" id="pad_bwl_joint" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_joint'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">Fever:</td>
				<td class="wmtBody" colspan="6"><input name="pad_bwl_fev" id="pad_bwl_fev" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_fev'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">School:</td>
				<td class="wmtBody" colspan="6"><input name="pad_bwl_school" id="pad_bwl_school" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_school'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">Last Hgb:</td>
				<td class="wmtBody" colspan="6"><input name="pad_bwl_last_hgb" id="pad_bwl_last_hgb" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_last_hgb'}; ?>" /></td>
			</tr>
      <tr>
				<td class="wmtLabel">Other Therapy:</td>
				<td class="wmtBody" colspan="4"><input name="pad_bwl_oth" id="pad_bwl_oth" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bwl_oth'}; ?>" /></td>
				<td class="wmtBody">Last ESR:</td>
				<td><input name="pad_bwl_last_esr" id="pad_bwl_last_esr" class="wmtInput" type="text" value="<?php echo $ad{'pad_bwl_last_esr'}; ?>" /></td>
			</tr>
    </table>
