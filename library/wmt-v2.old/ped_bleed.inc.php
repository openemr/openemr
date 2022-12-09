<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Duration of Rectal Bleeding:</td>
        <td class="wmtBody" colspan="2"><select name="pad_bld_dur_num" id="pad_bld_dur_num" class="wmtInput"><?php ListSel($ad{'pad_bld_dur_num'},'RTO_Number'); ?></select>&nbsp;&nbsp;&nbsp;<select name="pad_bld_dur_frame" id="pad_bld_dur_frame" class="wmtInput"><?php ListSel($ad{'pad_bld_dur_frame'},'RTO_Frame'); ?></select></td>
				<td colspan="2">&nbsp;</td>
				<td colspan="4"><div style="float: right; margin-right: 10px;"><a class="css_button" tabindex="-1" onClick="toggleBleedExamNull();" href="javascript:;"><span>Clear Section</span></a></div></td>
      </tr>
      <tr>
        <td class="wmtLabel">Quality of Rectal Bleeding:</td>
        <td class="wmtBody"><input name="pad_bld_qual_red" id="pad_bld_qual_red" type="checkbox" value="1" <?php echo (($ad{'pad_bld_qual_red'} == '1')?' checked ':''); ?> />&nbsp;Bright Red Blood</td>
        <td class="wmtBody"><input name="pad_bld_qual_dark" id="pad_bld_qual_dark" type="checkbox" value="1" <?php echo (($ad{'pad_bld_qual_dark'} == '1')?' checked ':''); ?> />&nbsp;Dark/Black Stools</td>
        <td class="wmtBody" colspan="2"><input name="pad_bld_qual_mix" id="pad_bld_qual_mix" type="checkbox" value="1" <?php echo (($ad{'pad_bld_qual_mix'} == '1')?' checked ':''); ?> />&nbsp;Blood Mixed with Mucous</td>
        <td class="wmtBody" colspan="2"><input name="pad_bld_qual_tp" id="pad_bld_qual_tp" type="checkbox" value="1" <?php echo (($ad{'pad_bld_qual_tp'} == '1')?' checked ':''); ?> />&nbsp;Blood Noted on T. Paper</td>
        <td class="wmtBody"><input name="pad_bld_qual_oth" id="pad_bld_qual_oth" type="checkbox" value="1" <?php echo (($ad{'pad_bld_qual_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_bld_qual_nt" id="pad_bld_qual_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_qual_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Defecation:</td>
        <td class="wmtBody"><input name="pad_bld_def" id="pad_bld_def_pain" type="checkbox" value="1" onClick="TogglePair('pad_bld_def_pain','pad_bld_def_no');" <?php echo (($ad{'pad_bld_def'} == '1')?' checked ':''); ?> />&nbsp;Painful</td>
        <td class="wmtBody"><input name="pad_bld_def" id="pad_bld_def_no" type="checkbox" value="2" onClick="TogglePair('pad_bld_def_no','pad_bld_def_pain');" <?php echo (($ad{'pad_bld_def'} == '2')?' checked ':''); ?> />&nbsp;Non-Painful</td>
				<td colspan="5"><input name="pad_bld_def_nt" id="pad_bld_def_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_def_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Behavior:</td>
        <td class="wmtBody"><input name="pad_bld_beh_stiff" id="pad_bld_beh_stiff" type="checkbox" value="1" <?php echo (($ad{'pad_bld_beh_stiff'} == '1')?' checked ':''); ?> />&nbsp;Stiffening</td>
        <td class="wmtBody"><input name="pad_bld_beh_cry" id="pad_bld_beh_cry" type="checkbox" value="1" <?php echo (($ad{'pad_bld_beh_cry'} == '1')?' checked ':''); ?> />&nbsp;Crying</td>
        <td class="wmtBody"><input name="pad_bld_beh_strain" id="pad_bld_beh_strain" type="checkbox" value="1" <?php echo (($ad{'pad_bld_beh_strain'} == '1')?' checked ':''); ?> />&nbsp;Straining</td>
				<td colspan="4"><input name="pad_bld_beh_nt" id="pad_bld_beh_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_beh_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Quantity of Blood:</td>
        <td class="wmtBody"><input name="pad_bld_quant_strk" id="pad_bld_quant_strk" type="checkbox" value="1" <?php echo (($ad{'pad_bld_quant_strk'} == '1')?' checked ':''); ?> />&nbsp;Streaks</td>
        <td class="wmtBody"><input name="pad_bld_quant_lt" id="pad_bld_quant_lt" type="checkbox" value="1" <?php echo (($ad{'pad_bld_quant_lt'} == '1')?' checked ':''); ?> />&nbsp;&lt;&nbsp;1/2 tsp</td>
        <td class="wmtBody"><input name="pad_bld_quant_clot" id="pad_bld_quant_clot" type="checkbox" value="1" <?php echo (($ad{'pad_bld_quant_clot'} == '1')?' checked ':''); ?> />&nbsp;Clots of Blood</td>
        <td class="wmtBody"><input name="pad_bld_quant_gt" id="pad_bld_quant_gt" type="checkbox" value="1" <?php echo (($ad{'pad_bld_quant_gt'} == '1')?' checked ':''); ?> />&nbsp;&gt;&nbsp;1/2 tsp</td>
        <td class="wmtBody"><input name="pad_bld_quant_oth" id="pad_bld_quant_oth" type="checkbox" value="1" <?php echo (($ad{'pad_bld_quant_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_bld_quant_nt" id="pad_bld_quant_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_quant_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Frequency:</td>
        <td class="wmtBody"><select name="pad_bld_freq" id="pad_bld_freq" class="wmtInput"><?php ListSel($ad{'pad_bld_freq'},'PC1_Bleed_Freq'); ?></select></td>
				<td colspan="6"><input name="pad_bld_freq_nt" id="pad_bld_freq_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_freq_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtLabel">Consistency of Stool:</td>
        <td class="wmtBody"><input name="pad_bld_stl_hard" id="pad_bld_stl_hard" type="checkbox" value="1" <?php echo (($ad{'pad_bld_stl_hard'} == '1')?' checked ':''); ?> />&nbsp;Hard</td>
        <td class="wmtBody"><input name="pad_bld_stl_soft" id="pad_bld_stl_soft" type="checkbox" value="1" <?php echo (($ad{'pad_bld_stl_soft'} == '1')?' checked ':''); ?> />&nbsp;Soft</td>
        <td class="wmtBody"><input name="pad_bld_stl_liq" id="pad_bld_stl_liq" type="checkbox" value="1" <?php echo (($ad{'pad_bld_stl_liq'} == '1')?' checked ':''); ?> />&nbsp;Liquid</td>
        <td class="wmtBody"><input name="pad_bld_stl_muc" id="pad_bld_stl_muc" type="checkbox" value="1" <?php echo (($ad{'pad_bld_stl_muc'} == '1')?' checked ':''); ?> />&nbsp;Mucous</td>
        <td class="wmtBody"><input name="pad_bld_stl_oth" id="pad_bld_stl_oth" type="checkbox" value="1" <?php echo (($ad{'pad_bld_stl_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_bld_stl_nt" id="pad_bld_stl_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_stl_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Modifying Factors:</td>
        <td class="wmtBody">Improves:</td>
        <td class="wmtBody"><input name="pad_bld_imp_soft" id="pad_bld_imp_soft" type="checkbox" value="1" <?php echo (($ad{'pad_bld_imp_soft'} == '1')?' checked ':''); ?> />&nbsp;Soft Stools</td>
        <td class="wmtBody"><input name="pad_bld_imp_form" id="pad_bld_imp_form" type="checkbox" value="1" <?php echo (($ad{'pad_bld_imp_form'} == '1')?' checked ':''); ?> />&nbsp;Formula</td>
        <td class="wmtBody"><input name="pad_bld_imp_diet" id="pad_bld_imp_diet" type="checkbox" value="1" <?php echo (($ad{'pad_bld_imp_diet'} == '1')?' checked ':''); ?> />&nbsp;Diet</td>
        <td class="wmtBody"><input name="pad_bld_imp_oth" id="pad_bld_imp_oth" type="checkbox" value="1" <?php echo (($ad{'pad_bld_imp_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_bld_imp_nt" id="pad_bld_imp_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_imp_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody">Worsens:</td>
        <td class="wmtBody"><input name="pad_bld_wrs_eat" id="pad_bld_wrs_eat" type="checkbox" value="1" <?php echo (($ad{'pad_bld_wrs_eat'} == '1')?' checked ':''); ?> />&nbsp;Eating</td>
        <td class="wmtBody"><input name="pad_bld_wrs_stress" id="pad_bld_wrs_stress" type="checkbox" value="1" <?php echo (($ad{'pad_bld_wrs_stress'} == '1')?' checked ':''); ?> />&nbsp;Stress</td>
        <td class="wmtBody"><input name="pad_bld_wrs_food" id="pad_bld_wrs_food" type="checkbox" value="1" <?php echo (($ad{'pad_bld_wrs_food'} == '1')?' checked ':''); ?> />&nbsp;Foods</td>
        <td class="wmtBody"><input name="pad_bld_wrs_oth" id="pad_bld_wrs_oth" type="checkbox" value="1" <?php echo (($ad{'pad_bld_wrs_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_bld_wrs_nt" id="pad_bld_wrs_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_wrs_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Associated s/sx:</td>
        <td class="wmtBody"><input name="pad_bld_ass_abd" id="pad_bld_ass_abd" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_abd'} == '1')?' checked ':''); ?> />&nbsp;Abd. Pain</td>
        <td class="wmtBody"><input name="pad_bld_ass_naus" id="pad_bld_ass_naus" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_naus'} == '1')?' checked ':''); ?> />&nbsp;Nasuea</td>
        <td class="wmtBody"><input name="pad_bld_ass_epi" id="pad_bld_ass_epi" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_epi'} == '1')?' checked ':''); ?> />&nbsp;Epigastric Pain</td>
        <td class="wmtBody"><input name="pad_bld_ass_urg" id="pad_bld_ass_urg" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_urg'} == '1')?' checked ':''); ?> />&nbsp;Urgency</td>
        <td class="wmtBody"><input name="pad_bld_ass_loss" id="pad_bld_ass_loss" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_loss'} == '1')?' checked ':''); ?> />&nbsp;Weight Loss</td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody"><input name="pad_bld_ass_vom" id="pad_bld_ass_vom" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_vom'} == '1')?' checked ':''); ?> />&nbsp;Vomiting</td>
        <td class="wmtBody"><input name="pad_bld_ass_fev" id="pad_bld_ass_fev" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_fev'} == '1')?' checked ':''); ?> />&nbsp;Fever</td>
        <td class="wmtBody"><input name="pad_bld_ass_const" id="pad_bld_ass_const" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_const'} == '1')?' checked ':''); ?> />&nbsp;Constipation</td>
        <td class="wmtBody"><input name="pad_bld_ass_oth" id="pad_bld_ass_oth" type="checkbox" value="1" <?php echo (($ad{'pad_bld_ass_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_bld_ass_nt" id="pad_bld_ass_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_bld_ass_nt'}; ?>" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtBody"><input name="pad_bld_24hr" id="pad_bld_24hr_yes" type="checkbox" value="1" onClick="TogglePair('pad_bld_24hr_yes','pad_bld_24hr_no');" <?php echo (($ad{'pad_bld_24hr'} == '1')?' checked ':''); ?> />&nbsp;Yes</td>
        <td class="wmtBody"><input name="pad_bld_24hr" id="pad_bld_24hr_no" type="checkbox" value="2" onClick="TogglePair('pad_bld_24hr_no','pad_bld_24hr_yes');" <?php echo (($ad{'pad_bld_24hr'} == '2')?' checked ':''); ?> />&nbsp;No</td>
				<td class="wmtBody" colspan="5">First bowel movement after birth within first 24 hours.</td>
			</tr>
    </table>
