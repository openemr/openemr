<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Duration of Vomiting:</td>
        <td class="wmtBody" colspan="2"><select name="pad_vom_dur_num" id="pad_vom_dur_num" class="wmtInput"><?php ListSel($ad{'pad_vom_dur_num'},'RTO_Number'); ?></select>&nbsp;&nbsp;&nbsp;<select name="pad_vom_dur_frame" id="pad_vom_dur_frame" class="wmtInput"><?php ListSel($ad{'pad_vom_dur_frame'},'RTO_Frame'); ?></select></td>
				<td colspan="3">&nbsp;</td>
				<td colspan="2"><div style="float: right; margin-right: 10px;"><a class="css_button" tabindex="-1" onClick="toggleVomitExamNull();" href="javascript:;"><span>Clear Section</span></a></div></td>
      </tr>
      <tr>
        <td class="wmtLabel">Quality of Vomiting:</td>
        <td class="wmtBody"><input name="pad_vom_qual_proj" id="pad_vom_qual_proj" type="checkbox" value="1" <?php echo (($ad{'pad_vom_qual_proj'} == '1')?' checked ':''); ?> />&nbsp;Projectile</td>
        <td class="wmtBody" colspan="2"><input name="pad_vom_qual_gag" id="pad_vom_qual_gag" type="checkbox" value="1" <?php echo (($ad{'pad_vom_qual_gag'} == '1')?' checked ':''); ?> />&nbsp;Gagging&nbsp;&amp;&nbsp;Forced</td>
        <td class="wmtBody"><input name="pad_vom_qual_un" id="pad_vom_qual_un" type="checkbox" value="1" <?php echo (($ad{'pad_vom_qual_un'} == '1')?' checked ':''); ?> />&nbsp;Uncomplicated</td>
        <td class="wmtBody"><input name="pad_vom_qual_oth" id="pad_vom_qual_oth" type="checkbox" value="1" <?php echo (($ad{'pad_vom_qual_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_vom_qual_nt" id="pad_vom_qual_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_qual_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Severity of Vomiting:</td>
        <td class="wmtBody" colspan="2"><input name="pad_vom_sev_night" id="pad_vom_sev_night" type="checkbox" value="1" <?php echo (($ad{'pad_vom_sev_night'} == '1')?' checked ':''); ?> />&nbsp;Night Time Awakening</td>
        <td class="wmtBody" colspan="2"><input name="pad_vom_sev_ill" id="pad_vom_sev_ill" type="checkbox" value="1" <?php echo (($ad{'pad_vom_sev_ill'} == '1')?' checked ':''); ?> />&nbsp;Frequent Respiratory Illness</td>
        <td class="wmtBody"><input name="pad_vom_sev_loss" id="pad_vom_sev_loss" type="checkbox" value="1" <?php echo (($ad{'pad_vom_sev_loss'} == '1')?' checked ':''); ?> />&nbsp;Weight Loss</td>
        <td class="wmtBody"><input name="pad_vom_sev_fuss" id="pad_vom_sev_fuss" type="checkbox" value="1" <?php echo (($ad{'pad_vom_sev_fuss'} == '1')?' checked ':''); ?> />&nbsp;Fussy/Irritable</td>
        <td class="wmtBody"><input name="pad_vom_sev_oth" id="pad_vom_sev_oth" type="checkbox" value="1" <?php echo (($ad{'pad_vom_sev_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_vom_sev_nt" id="pad_vom_sev_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_sev_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Quantity of Vomit:</td>
        <td class="wmtBody" colspan="2"><select name="pad_vom_quant_num" id="pad_vom_quant_num" class="wmtInput"><?php ListSel($ad{'pad_vom_quant_num'},'One_to_Ten'); ?></select>&nbsp;&nbsp;&nbsp;<select name="pad_vom_quant_meas" id="pad_vom_quant_meas" class="wmtInput"><?php ListSel($ad{'pad_vom_quant_meas'},'PC1_Vomit_Measure'); ?></select></td>
        <td class="wmtBody"><input name="pad_vom_quant_oth" id="pad_vom_quant_oth" type="checkbox" value="1" <?php echo (($ad{'pad_vom_quant_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_vom_quant_nt" id="pad_vom_quant_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_quant_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Character of Emesis:</td>
        <td class="wmtBody"><input name="pad_vom_char_form" id="pad_vom_char_form" type="checkbox" value="1" <?php echo (($ad{'pad_vom_char_form'} == '1')?' checked ':''); ?> />&nbsp;Formula</td>
        <td class="wmtBody"><input name="pad_vom_char_bile" id="pad_vom_char_bile" type="checkbox" value="1" <?php echo (($ad{'pad_vom_char_bile'} == '1')?' checked ':''); ?> />&nbsp;Bile</td>
        <td class="wmtBody"><input name="pad_vom_char_blood" id="pad_vom_char_blood" type="checkbox" value="1" <?php echo (($ad{'pad_vom_char_blood'} == '1')?' checked ':''); ?> />&nbsp;Blood</td>
        <td class="wmtBody">&nbsp;Mucous<input name="pad_vom_char_muc" id="pad_vom_char_muc" type="checkbox" value="1" <?php echo (($ad{'pad_vom_char_muc'} == '1')?' checked ':''); ?> /></td>
        <td class="wmtBody">&nbsp;Other<input name="pad_vom_char_oth" id="pad_vom_char_oth" type="checkbox" value="1" <?php echo (($ad{'pad_vom_char_oth'} == '1')?' checked ':''); ?> /></td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_vom_char_nt" id="pad_vom_char_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_char_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Frequency:</td>
        <td class="wmtBody"><select name="pad_vom_freq_frame" id="pad_vom_freq_frame" class="wmtInput"><?php ListSel($ad{'pad_vom_freq_frame'},'PC1_Vomit_Freq'); ?></select></td>
				<td colspan="6"><input name="pad_vom_freq_nt" id="pad_vom_freq_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_freq_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Timing:</td>
        <td class="wmtBody"><input name="pad_vom_time_day" id="pad_vom_time_day" type="checkbox" value="1" <?php echo (($ad{'pad_vom_time_day'} == '1')?' checked ':''); ?> />&nbsp;Weekdays</td>
        <td class="wmtBody"><input name="pad_vom_time_end" id="pad_vom_time_end" type="checkbox" value="1" <?php echo (($ad{'pad_vom_time_end'} == '1')?' checked ':''); ?> />&nbsp;Weekends</td>
        <td class="wmtBody"><input name="pad_vom_time_am" id="pad_vom_time_am" type="checkbox" value="1" <?php echo (($ad{'pad_vom_time_am'} == '1')?' checked ':''); ?> />&nbsp;A.M.</td>
        <td class="wmtBody"><input name="pad_vom_time_pm" id="pad_vom_time_pm" type="checkbox" value="1" <?php echo (($ad{'pad_vom_time_pm'} == '1')?' checked ':''); ?> />&nbsp;P.M.</td>
        <td class="wmtBody"><input name="pad_vom_time_aft" id="pad_vom_time_aft" type="checkbox" value="1" <?php echo (($ad{'pad_vom_time_aft'} == '1')?' checked ':''); ?> />&nbsp;After Meals</td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody" colspan="2"><input name="pad_vom_time_pri" id="pad_vom_time_pri" type="checkbox" value="1" <?php echo (($ad{'pad_vom_time_pri'} == '1')?' checked ':''); ?> />&nbsp;Prior to Bowel Movement</td>
        <td class="wmtBody" colspan="2"><input name="pad_vom_time_stress" id="pad_vom_time_stress" type="checkbox" value="1" <?php echo (($ad{'pad_vom_time_stress'} == '1')?' checked ':''); ?> />&nbsp;Stressful Periods</td>
        <td class="wmtBody"><input name="pad_vom_time_oth" id="pad_vom_time_oth" type="checkbox" value="1" <?php echo (($ad{'pad_vom_time_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_vom_time_nt" id="pad_vom_time_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_time_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Modifying Factors:</td>
        <td class="wmtBody">Improves:</td>
        <td class="wmtBody"><input name="pad_vom_imp_soft" id="pad_vom_imp_soft" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_soft'} == '1')?' checked ':''); ?> />&nbsp;Soft Stools</td>
        <td class="wmtBody"><input name="pad_vom_imp_ant" id="pad_vom_imp_ant" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_ant'} == '1')?' checked ':''); ?> />&nbsp;Antacids</td>
        <td class="wmtBody"><input name="pad_vom_imp_eat" id="pad_vom_imp_eat" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_eat'} == '1')?' checked ':''); ?> />&nbsp;Eating</td>
        <td class="wmtBody"><input name="pad_vom_imp_diet" id="pad_vom_imp_diet" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_diet'} == '1')?' checked ':''); ?> />&nbsp;Diet</td>
        <td class="wmtBody"><input name="pad_vom_imp_rice" id="pad_vom_imp_rice" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_rice'} == '1')?' checked ':''); ?> />&nbsp;Rice Cereal</td>
        <td class="wmtBody"><input name="pad_vom_imp_med" id="pad_vom_imp_med" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_med'} == '1')?' checked ':''); ?> />&nbsp;Other Meds</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
        <td class="wmtBody"><input name="pad_vom_imp_burp" id="pad_vom_imp_burp" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_burp'} == '1')?' checked ':''); ?> />&nbsp;Burping</td>
        <td class="wmtBody" colspan="2"><input name="pad_vom_imp_up" id="pad_vom_imp_up" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_up'} == '1')?' checked ':''); ?> />&nbsp;Sitting in an Upright Position</td>
        <td class="wmtBody"><input name="pad_vom_imp_oth" id="pad_vom_imp_oth" type="checkbox" value="1" <?php echo (($ad{'pad_vom_imp_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_vom_imp_nt" id="pad_vom_imp_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_imp_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody">Worsens:</td>
        <td class="wmtBody"><input name="pad_vom_wrs_eat" id="pad_vom_wrs_eat" type="checkbox" value="1" <?php echo (($ad{'pad_vom_wrs_eat'} == '1')?' checked ':''); ?> />&nbsp;Eating</td>
        <td class="wmtBody"><input name="pad_vom_wrs_stress" id="pad_vom_wrs_stress" type="checkbox" value="1" <?php echo (($ad{'pad_vom_wrs_stress'} == '1')?' checked ':''); ?> />&nbsp;Stress</td>
        <td class="wmtBody"><input name="pad_vom_wrs_food" id="pad_vom_wrs_food" type="checkbox" value="1" <?php echo (($ad{'pad_vom_wrs_food'} == '1')?' checked ':''); ?> />&nbsp;Foods</td>
        <td class="wmtBody"><input name="pad_vom_wrs_move" id="pad_vom_wrs_move" type="checkbox" value="1" <?php echo (($ad{'pad_vom_wrs_move'} == '1')?' checked ':''); ?> />&nbsp;Movement</td>
        <td class="wmtBody"><input name="pad_vom_wrs_oth" id="pad_vom_wrs_oth" type="checkbox" value="1" <?php echo (($ad{'pad_vom_wrs_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_vom_wrs_nt" id="pad_vom_wrs_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_wrs_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Associated s/sx:</td>
        <td class="wmtBody"><input name="pad_vom_ass_abd" id="pad_vom_ass_abd" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_abd'} == '1')?' checked ':''); ?> />&nbsp;Abd. Pain</td>
        <td class="wmtBody"><input name="pad_vom_ass_naus" id="pad_vom_ass_naus" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_naus'} == '1')?' checked ':''); ?> />&nbsp;Nausea</td>
        <td class="wmtBody"><input name="pad_vom_ass_epi" id="pad_vom_ass_epi" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_epi'} == '1')?' checked ':''); ?> />&nbsp;Epigastric Pain</td>
        <td class="wmtBody"><input name="pad_vom_ass_burn" id="pad_vom_ass_burn" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_burn'} == '1')?' checked ':''); ?> />&nbsp;Burning in Chest</td>
        <td class="wmtBody"><input name="pad_vom_ass_loss" id="pad_vom_ass_loss" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_loss'} == '1')?' checked ':''); ?> />&nbsp;Weight Loss</td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody"><input name="pad_vom_ass_dia" id="pad_vom_ass_dia" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_dia'} == '1')?' checked ':''); ?> />&nbsp;Diarrhea</td>
        <td class="wmtBody"><input name="pad_vom_ass_fev" id="pad_vom_ass_fev" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_fev'} == '1')?' checked ':''); ?> />&nbsp;Fever</td>
        <td class="wmtBody"><input name="pad_vom_ass_const" id="pad_vom_ass_const" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_const'} == '1')?' checked ':''); ?> />&nbsp;Constipation</td>
        <td class="wmtBody"><input name="pad_vom_ass_head" id="pad_vom_ass_head" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_head'} == '1')?' checked ':''); ?> />&nbsp;Headaches</td>
        <td class="wmtBody"><input name="pad_vom_ass_oth" id="pad_vom_ass_oth" type="checkbox" value="1" <?php echo (($ad{'pad_vom_ass_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_vom_ass_nt" id="pad_vom_ass_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_vom_ass_nt'}; ?>" /></td>
			</tr>
    </table>
