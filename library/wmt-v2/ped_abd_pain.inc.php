<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Duration of Pain:</td>
        <td class="wmtBody" colspan="2"><select name="pad_abd_dur_num" id="pad_abd_dur_num" class="wmtInput"><?php ListSel($ad{'pad_abd_dur_num'},'RTO_Number'); ?></select>&nbsp;&nbsp;&nbsp;<select name="pad_abd_dur_frame" id="pad_abd_dur_frame" class="wmtInput"><?php ListSel($ad{'pad_abd_dur_frame'},'RTO_Frame'); ?></select></td>
				<td colspan="3">&nbsp;</td>
				<td colspan="2"><div style="float: right; margin-right: 10px;"><a class="css_button" tabindex="-1" onClick="togglePainExamNull();" href="javascript:;"><span>Clear Section</span></a></div></td>
      </tr>
      <tr>
        <td class="wmtLabel">Location of Pain:</td>
        <td class="wmtBody"><input name="pad_abd_loc_epi" id="pad_abd_loc_epi" type="checkbox" value="1" <?php echo (($ad{'pad_abd_loc_epi'} == '1')?' checked ':''); ?> />&nbsp;Epigastric</td>
        <td class="wmtBody"><input name="pad_abd_loc_low" id="pad_abd_loc_low" type="checkbox" value="1" <?php echo (($ad{'pad_abd_loc_low'} == '1')?' checked ':''); ?> />&nbsp;Lower</td>
        <td class="wmtBody"><input name="pad_abd_loc_dif" id="pad_abd_loc_dif" type="checkbox" value="1" <?php echo (($ad{'pad_abd_loc_dif'} == '1')?' checked ':''); ?> />&nbsp;Diffuse</td>
        <td class="wmtBody"><input name="pad_abd_loc_peri" id="pad_abd_loc_peri" type="checkbox" value="1" <?php echo (($ad{'pad_abd_loc_peri'} == '1')?' checked ':''); ?> />&nbsp;Periumbilical</td>
        <td class="wmtBody"><input name="pad_abd_loc_oth" id="pad_abd_loc_oth" type="checkbox" value="1" <?php echo (($ad{'pad_abd_loc_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
      </tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_abd_loc_nt" id="pad_abd_loc_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_abd_loc_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Quality of Pain:</td>
        <td class="wmtBody"><input name="pad_abd_qual_cramp" id="pad_abd_qual_cramp" type="checkbox" value="1" <?php echo (($ad{'pad_abd_qual_cramp'} == '1')?' checked ':''); ?> />&nbsp;Crampy</td>
        <td class="wmtBody"><input name="pad_abd_qual_stab" id="pad_abd_qual_stab" type="checkbox" value="1" <?php echo (($ad{'pad_abd_qual_stab'} == '1')?' checked ':''); ?> />&nbsp;Stabbing</td>
        <td class="wmtBody"><input name="pad_abd_qual_burn" id="pad_abd_qual_burn" type="checkbox" value="1" <?php echo (($ad{'pad_abd_qual_burn'} == '1')?' checked ':''); ?> />&nbsp;Burning</td>
        <td class="wmtBody"><input name="pad_abd_qual_dull" id="pad_abd_qual_dull" type="checkbox" value="1" <?php echo (($ad{'pad_abd_qual_dull'} == '1')?' checked ':''); ?> />&nbsp;Dull/Achy</td>
        <td class="wmtBody"><input name="pad_abd_qual_oth" id="pad_abd_qual_oth" type="checkbox" value="1" <?php echo (($ad{'pad_abd_qual_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_abd_qual_nt" id="pad_abd_qual_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_abd_qual_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Severity of Pain:</td>
        <td class="wmtBody" colspan="2"><input name="pad_abd_sev_wake" id="pad_abd_sev_wake" type="checkbox" value="1" <?php echo (($ad{'pad_abd_sev_wake'} == '1')?' checked ':''); ?> />&nbsp;Night Time Awakening</td>
        <td class="wmtBody" colspan="2"><input name="pad_abd_sev_stop" id="pad_abd_sev_stop" type="checkbox" value="1" <?php echo (($ad{'pad_abd_sev_stop'} == '1')?' checked ':''); ?> />&nbsp;Stops Routine Momentarily</td>
        <td class="wmtBody"><input name="pad_abd_sev_cry" id="pad_abd_sev_cry" type="checkbox" value="1" <?php echo (($ad{'pad_abd_sev_cry'} == '1')?' checked ':''); ?> />&nbsp;Cries With Pain</td>
        <td class="wmtBody">Scale&nbsp;&nbsp;<select name="pad_abd_sev_scale" id="pad_abd_sev_scale" class="wmtInput"><?php ListSel($ad{'pad_abd_sev_scale'},'One_To_Ten'); ?></select></td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_abd_sev_nt" id="pad_abd_sev_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_abd_sev_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Timing:</td>
        <td class="wmtBody"><input name="pad_abd_time_day" id="pad_abd_time_day" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_day'} == '1')?' checked ':''); ?> />&nbsp;Weekdays</td>
        <td class="wmtBody"><input name="pad_abd_time_end" id="pad_abd_time_end" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_end'} == '1')?' checked ':''); ?> />&nbsp;Weekends</td>
        <td class="wmtBody"><input name="pad_abd_time_am" id="pad_abd_time_am" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_am'} == '1')?' checked ':''); ?> />&nbsp;A.M.</td>
        <td class="wmtBody"><input name="pad_abd_time_pm" id="pad_abd_time_pm" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_pm'} == '1')?' checked ':''); ?> />&nbsp;P.M.</td>
        <td class="wmtBody"><input name="pad_abd_time_bef" id="pad_abd_time_bef" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_bef'} == '1')?' checked ':''); ?> />&nbsp;Before Meals</td>
        <td class="wmtBody"><input name="pad_abd_time_aft" id="pad_abd_time_aft" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_aft'} == '1')?' checked ':''); ?> />&nbsp;After Meals</td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody" colspan="2"><input name="pad_abd_time_prior" id="pad_abd_time_prior" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_prior'} == '1')?' checked ':''); ?> />&nbsp;Prior to Bowel Movement</td>
        <td class="wmtBody" colspan="2"><input name="pad_abd_time_stress" id="pad_abd_time_stress" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_stress'} == '1')?' checked ':''); ?> />&nbsp;Stressful Periods</td>
        <td class="wmtBody"><input name="pad_abd_time_oth" id="pad_abd_time_oth" type="checkbox" value="1" <?php echo (($ad{'pad_abd_time_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_abd_time_nt" id="pad_abd_time_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_abd_time_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Modifying Factors:</td>
        <td class="wmtBody">Improves:</td>
        <td class="wmtBody"><input name="pad_abd_imp_ant" id="pad_abd_imp_ant" type="checkbox" value="1" <?php echo (($ad{'pad_abd_imp_ant'} == '1')?' checked ':''); ?> />&nbsp;Antacids</td>
        <td class="wmtBody"><input name="pad_abd_imp_eat" id="pad_abd_imp_eat" type="checkbox" value="1" <?php echo (($ad{'pad_abd_imp_eat'} == '1')?' checked ':''); ?> />&nbsp;Eating</td>
        <td class="wmtBody"><input name="pad_abd_imp_bow" id="pad_abd_imp_bow" type="checkbox" value="1" <?php echo (($ad{'pad_abd_imp_bow'} == '1')?' checked ':''); ?> />&nbsp;Bowel Movement</td>
        <td class="wmtBody"><input name="pad_abd_imp_med" id="pad_abd_imp_med" type="checkbox" value="1" <?php echo (($ad{'pad_abd_imp_med'} == '1')?' checked ':''); ?> />&nbsp;Other Meds</td>
        <td class="wmtBody"><input name="pad_abd_imp_rest" id="pad_abd_imp_rest" type="checkbox" value="1" <?php echo (($ad{'pad_abd_imp_rest'} == '1')?' checked ':''); ?> />&nbsp;Rest/Sleep</td>
        <td class="wmtBody"><input name="pad_abd_imp_oth" id="pad_abd_imp_oth" type="checkbox" value="1" <?php echo (($ad{'pad_abd_imp_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_abd_imp_nt" id="pad_abd_imp_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_abd_imp_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody">Worsens:</td>
        <td class="wmtBody"><input name="pad_abd_wrs_school" id="pad_abd_wrs_school" type="checkbox" value="1" <?php echo (($ad{'pad_abd_wrs_school'} == '1')?' checked ':''); ?> />&nbsp;School Days</td>
        <td class="wmtBody"><input name="pad_abd_wrs_eat" id="pad_abd_wrs_eat" type="checkbox" value="1" <?php echo (($ad{'pad_abd_wrs_eat'} == '1')?' checked ':''); ?> />&nbsp;Eating</td>
        <td class="wmtBody"><input name="pad_abd_wrs_lay" id="pad_abd_wrs_lay" type="checkbox" value="1" <?php echo (($ad{'pad_abd_wrs_lay'} == '1')?' checked ':''); ?> />&nbsp;Lying Down</td>
        <td class="wmtBody"><input name="pad_abd_wrs_stress" id="pad_abd_wrs_stress" type="checkbox" value="1" <?php echo (($ad{'pad_abd_wrs_stress'} == '1')?' checked ':''); ?> />&nbsp;Stress</td>
        <td class="wmtBody"><input name="pad_abd_wrs_oth" id="pad_abd_wrs_oth" type="checkbox" value="1" <?php echo (($ad{'pad_abd_wrs_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_abd_wrs_nt" id="pad_abd_wrs_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_abd_wrs_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Associated s/sx:</td>
        <td class="wmtBody"><input name="pad_abd_ass_vomit" id="pad_abd_ass_vomit" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_vomit'} == '1')?' checked ':''); ?> />&nbsp;Vomiting</td>
        <td class="wmtBody" colspan="2"><input name="pad_abd_ass_blood" id="pad_abd_ass_blood" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_blood'} == '1')?' checked ':''); ?> />&nbsp;Bloody or Bilious&nbsp;<input name="pad_abd_ass_blood_nt" id="pad_abd_ass_blood_nt" class="wmtInput" type="text" value="<?php echo $ad{'pad_abd_ass_blood_nt'}; ?>" /></td>
        <td class="wmtBody"><input name="pad_abd_ass_naus" id="pad_abd_ass_naus" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_naus'} == '1')?' checked ':''); ?> />&nbsp;Nasuea</td>
        <td class="wmtBody"><input name="pad_abd_ass_reflux" id="pad_abd_ass_reflux" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_reflux'} == '1')?' checked ':''); ?> />&nbsp;Reflux Symptoms</td>
        <td class="wmtBody"><input name="pad_abd_ass_dia" id="pad_abd_ass_dia" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_dia'} == '1')?' checked ':''); ?> />&nbsp;Diarrhea</td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody"><input name="pad_abd_ass_const" id="pad_abd_ass_const" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_const'} == '1')?' checked ':''); ?> />&nbsp;Constipation</td>
        <td class="wmtBody"><input name="pad_abd_ass_stool" id="pad_abd_ass_stool" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_stool'} == '1')?' checked ':''); ?> />&nbsp;Bloody Stools</td>
        <td class="wmtBody"><input name="pad_abd_ass_loss" id="pad_abd_ass_loss" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_loss'} == '1')?' checked ':''); ?> />&nbsp;Weight Loss</td>
        <td class="wmtBody"><input name="pad_abd_ass_gain" id="pad_abd_ass_gain" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_gain'} == '1')?' checked ':''); ?> />&nbsp;Weight Gain</td>
        <td class="wmtBody"><input name="pad_abd_ass_fev" id="pad_abd_ass_fev" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_fev'} == '1')?' checked ':''); ?> />&nbsp;Fever</td>
        <td class="wmtBody"><input name="pad_abd_ass_pal" id="pad_abd_ass_pal" type="checkbox" value="1" <?php echo (($ad{'pad_abd_ass_pal'} == '1')?' checked ':''); ?> />&nbsp;Pallor</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_abd_ass_nt" id="pad_abd_ass_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_abd_ass_nt'}; ?>" /></td>
			</tr>
    </table>
