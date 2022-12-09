<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Duration:</td>
        <td class="wmtBody" colspan="2"><select name="pad_const_dur_num" id="pad_const_dur_num" class="wmtInput"><?php ListSel($ad{'pad_const_dur_num'},'RTO_Number'); ?></select>&nbsp;&nbsp;&nbsp;<select name="pad_const_dur_frame" id="pad_const_dur_frame" class="wmtInput"><?php ListSel($ad{'pad_const_dur_frame'},'RTO_Frame'); ?></select></td>
				<td colspan="3">&nbsp;</td>
				<td colspan="2"><div style="float: right; margin-right: 10px;"><a class="css_button" tabindex="-1" onClick="toggleConstipationExamNull();" href="javascript:;"><span>Clear Section</span></a></div></td>
      </tr>
      <tr>
        <td class="wmtLabel">Quality of BM:</td>
        <td class="wmtBody"><input name="pad_const_bm_hard" id="pad_const_bm_hard" type="checkbox" value="1" <?php echo (($ad{'pad_const_bm_hard'} == '1')?' checked ':''); ?> />&nbsp;Hard</td>
        <td class="wmtBody"><input name="pad_const_bm_soft" id="pad_const_bm_soft" type="checkbox" value="1" <?php echo (($ad{'pad_const_bm_soft'} == '1')?' checked ':''); ?> />&nbsp;Soft</td>
        <td class="wmtBody"><input name="pad_const_bm_blood" id="pad_const_bm_blood" type="checkbox" value="1" <?php echo (($ad{'pad_const_bm_blood'} == '1')?' checked ':''); ?> />&nbsp;Blood in Stool</td>
        <td class="wmtBody"><input name="pad_const_bm_pain" id="pad_const_bm_pain" type="checkbox" value="1" <?php echo (($ad{'pad_const_bm_pain'} == '1')?' checked ':''); ?> />&nbsp;Painful</td>
        <td class="wmtBody" colspan="3"><input name="pad_const_bm_soil" id="pad_const_bm_soil" type="checkbox" value="1" <?php echo (($ad{'pad_const_bm_soil'} == '1')?' checked ':''); ?> />&nbsp;Soiling Underwear</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtBody" colspan="2"><input name="pad_const_bm_ball" id="pad_const_bm_ball" type="checkbox" value="1" <?php echo (($ad{'pad_const_bm_ball'} == '1')?' checked ':''); ?> />&nbsp;Stool in Balls</td>
        <td class="wmtBody" colspan="2"><input name="pad_const_bm_loose" id="pad_const_bm_loose" type="checkbox" value="1" <?php echo (($ad{'pad_const_bm_loose'} == '1')?' checked ':''); ?> />&nbsp;Loose Watery (or Liquid)</td>
        <td class="wmtBody"><input name="pad_const_bm_oth" id="pad_const_bm_oth" type="checkbox" value="1" <?php echo (($ad{'pad_const_bm_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
      </tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_const_bm_nt" id="pad_const_bm_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_const_bm_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Behavior:</td>
        <td class="wmtBody"><input name="pad_const_beh_stiff" id="pad_const_beh_stiff" type="checkbox" value="1" <?php echo (($ad{'pad_const_beh_stiff'} == '1')?' checked ':''); ?> />&nbsp;Stiffening</td>
        <td class="wmtBody"><input name="pad_const_beh_cry" id="pad_const_beh_cry" type="checkbox" value="1" <?php echo (($ad{'pad_const_beh_cry'} == '1')?' checked ':''); ?> />&nbsp;Crying</td>
        <td class="wmtBody" colspan="2"><input name="pad_const_beh_hold" id="pad_const_beh_hold" type="checkbox" value="1" <?php echo (($ad{'pad_const_beh_hold'} == '1')?' checked ':''); ?> />&nbsp;Stool Witholding</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_const_beh_nt" id="pad_const_beh_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_const_beh_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Quantity of Stool:</td>
        <td class="wmtBody"><input name="pad_const_quant_sm" id="pad_const_quant_sm" type="checkbox" value="1" <?php echo (($ad{'pad_const_quant_sm'} == '1')?' checked ':''); ?> />&nbsp;Small</td>
        <td class="wmtBody"><input name="pad_const_quant_md" id="pad_const_quant_md" type="checkbox" value="1" <?php echo (($ad{'pad_const_quant_md'} == '1')?' checked ':''); ?> />&nbsp;Medium</td>
        <td class="wmtBody"><input name="pad_const_quant_lg" id="pad_const_quant_lg" type="checkbox" value="1" <?php echo (($ad{'pad_const_quant_lg'} == '1')?' checked ':''); ?> />&nbsp;Large</td>
        <td class="wmtBody"><input name="pad_const_quant_oth" id="pad_const_quant_oth" type="checkbox" value="1" <?php echo (($ad{'pad_const_quant_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_const_quant_nt" id="pad_const_quant_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_const_quant_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Frequency:</td>
        <td class="wmtBody" colspan="2"><input name="pad_const_freq" id="pad_const_freq" type="checkbox" value="1" <?php echo (($ad{'pad_const_freq'} == '1')?' checked ':''); ?> />&nbsp;1 stool q&nbsp;&nbsp;<select name="pad_const_freq_num" id="pad_const_freq_num" class="wmtInput"><?php ListSel($ad{'pad_const_freq_num'},'RTO_Number'); ?></select>&nbsp;&nbsp;days</td>
				<td colspan="5"><input name="pad_const_freq_nt" id="pad_const_freq_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_const_freq_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Modifying Factors:</td>
        <td class="wmtBody">Improves:</td>
        <td class="wmtBody"><input name="pad_const_imp_med" id="pad_const_imp_med" type="checkbox" value="1" <?php echo (($ad{'pad_const_imp_med'} == '1')?' checked ':''); ?> />&nbsp;Medications</td>
        <td class="wmtBody"><input name="pad_const_imp_beh" id="pad_const_imp_beh" type="checkbox" value="1" <?php echo (($ad{'pad_const_imp_beh'} == '1')?' checked ':''); ?> />&nbsp;Behavior Modification</td>
        <td class="wmtBody"><input name="pad_const_imp_diet" id="pad_const_imp_diet" type="checkbox" value="1" <?php echo (($ad{'pad_const_imp_diet'} == '1')?' checked ':''); ?> />&nbsp;Diet</td>
        <td class="wmtBody"><input name="pad_const_imp_oth" id="pad_const_imp_oth" type="checkbox" value="1" <?php echo (($ad{'pad_const_imp_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_const_imp_nt" id="pad_const_imp_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_const_imp_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody">Worsens:</td>
        <td class="wmtBody"><input name="pad_const_wrs_eat" id="pad_const_wrs_eat" type="checkbox" value="1" <?php echo (($ad{'pad_const_wrs_eat'} == '1')?' checked ':''); ?> />&nbsp;Eating</td>
        <td class="wmtBody"><input name="pad_const_wrs_stress" id="pad_const_wrs_stress" type="checkbox" value="1" <?php echo (($ad{'pad_const_wrs_stress'} == '1')?' checked ':''); ?> />&nbsp;Stress</td>
        <td class="wmtBody"><input name="pad_const_wrs_food" id="pad_const_wrs_food" type="checkbox" value="1" <?php echo (($ad{'pad_const_wrs_food'} == '1')?' checked ':''); ?> />&nbsp;Foods</td>
        <td class="wmtBody"><input name="pad_const_wrs_oth" id="pad_const_wrs_oth" type="checkbox" value="1" <?php echo (($ad{'pad_const_wrs_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_const_wrs_nt" id="pad_const_wrs_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_const_wrs_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Associated s/sx:</td>
        <td class="wmtBody"><input name="pad_const_ass_abd" id="pad_const_ass_abd" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_abd'} == '1')?' checked ':''); ?> />&nbsp;Abd. Pain</td>
        <td class="wmtBody"><input name="pad_const_ass_naus" id="pad_const_ass_naus" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_naus'} == '1')?' checked ':''); ?> />&nbsp;Nausea</td>
        <td class="wmtBody"><input name="pad_const_ass_epi" id="pad_const_ass_epi" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_epi'} == '1')?' checked ':''); ?> />&nbsp;Epigastric Pain</td>
        <td class="wmtBody"><input name="pad_const_ass_urg" id="pad_const_ass_urg" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_urg'} == '1')?' checked ':''); ?> />&nbsp;Urgency</td>
        <td class="wmtBody"><input name="pad_const_ass_wght" id="pad_const_ass_wght" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_wght'} == '1')?' checked ':''); ?> />&nbsp;Wt. Loss</td>
        <td class="wmtBody"><input name="pad_const_ass_vom" id="pad_const_ass_vom" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_vom'} == '1')?' checked ':''); ?> />&nbsp;Vomiting</td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody" colspan="3"><input name="pad_const_ass_acc" id="pad_const_ass_acc" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_acc'} == '1')?' checked ':''); ?> />&nbsp;Stool Accidents&nbsp;&nbsp;<select name="pad_const_ass_num" id="pad_const_ass_num" class="wmtInput"><?php ListSel($ad{'pad_const_ass_num'},'RTO_Number'); ?></select>&nbsp;&nbsp;<select name="pad_const_ass_freq" id="pad_const_ass_freq" class="wmtInput"><?php ListSel($ad{'pad_const_ass_freq'},'Stool_Frequency'); ?></select></td>
        <td class="wmtBody"><input name="pad_const_ass_fev" id="pad_const_ass_fev" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_fev'} == '1')?' checked ':''); ?> />&nbsp;Fever</td>
        <td class="wmtBody"><input name="pad_const_ass_oth" id="pad_const_ass_oth" type="checkbox" value="1" <?php echo (($ad{'pad_const_ass_oth'} == '1')?' checked ':''); ?> />&nbsp;Other</td>
			</tr>
			<tr>
				<td class="wmtLabel">&nbsp;</td>
				<td colspan="7"><input name="pad_const_ass_nt" id="pad_const_ass_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'pad_const_ass_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">&nbsp;</td>
        <td class="wmtBody"><input name="pad_const_24hr" id="pad_const_24hr_yes" type="checkbox" value="1" onClick="TogglePair('pad_const_24hr_yes','pad_const_24hr_no');" <?php echo (($ad{'pad_const_24hr'} == '1')?' checked ':''); ?> />&nbsp;Yes</td>
        <td class="wmtBody"><input name="pad_const_24hr" id="pad_const_24hr_no" type="checkbox" value="2" onClick="TogglePair('pad_const_24hr_no','pad_const_24hr_yes');" <?php echo (($ad{'pad_const_24hr'} == '2')?' checked ':''); ?> />&nbsp;No</td>
				<td class="wmtBody" colspan="5">First bowel movement after birth within first 24 hours.</td>
			</tr>
    </table>
