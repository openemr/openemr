<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="2" class="LabelRed">ADHD Checklist Dated: <?php echo $ad{'adhd_link_date'}; ?><input name="adhd_link_date" id="adhd_link_date" type="hidden" tabindex="-1" value="<?php echo $ad{'adhd_link_date'}; ?>" /></td>
				<td><a class="css_button" tabindex="-1" onClick="toggleADHDNull();" href="javascript:;"><span>Clear Checklist</span></a></td>
			</tr>
			<tr><td colspan="3" class="wmtChapter">Past History</td>
      <tr>
        <td class="wmtBody">1.&nbsp;&nbsp;History of distractibility, short attention span, impulsivity or restlessness as a child</td>
        <td><select name="adhd_ph_1" id="adhd_ph_1" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ph_1'},'YesNo'); ?></select></td>
        <td><input name="adhd_ph_1_nt" id="adhd_ph_1_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ph_1_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">2.&nbsp;&nbsp;History of not living up to potential in school or work</td>
        <td><select name="adhd_ph_2" id="adhd_ph_2" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ph_2'},'YesNo'); ?></select></td>
        <td><input name="adhd_ph_2_nt" id="adhd_ph_2_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ph_2_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">3.&nbsp;&nbsp;History of frequent behavior problems in school (detention, suspension, fighting, etc.)</td>
        <td><select name="adhd_ph_3" id="adhd_ph_3" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ph_3'},'YesNo'); ?></select></td>
        <td><input name="adhd_ph_3_nt" id="adhd_ph_3_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ph_3_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">4.&nbsp;&nbsp;Substance abuse problems as a teenager or young adult</td>
        <td><select name="adhd_ph_4" id="adhd_ph_4" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ph_4'},'YesNo'); ?></select></td>
        <td><input name="adhd_ph_4_nt" id="adhd_ph_4_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ph_4_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">5.&nbsp;&nbsp;History of several or more driving accidents or infractions</td>
        <td><select name="adhd_ph_5" id="adhd_ph_5" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ph_5'},'YesNo'); ?></select></td>
        <td><input name="adhd_ph_5_nt" id="adhd_ph_5_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ph_5_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">6.&nbsp;&nbsp;Cigarette of nicotine habit (previous or current)</td>
        <td><select name="adhd_ph_6" id="adhd_ph_6" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ph_6'},'YesNo'); ?></select></td>
        <td><input name="adhd_ph_6_nt" id="adhd_ph_6_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ph_6_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">7.&nbsp;&nbsp;Family history of ADHD, learning problems</td>
        <td><select name="adhd_ph_7" id="adhd_ph_7" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ph_7'},'YesNo'); ?></select></td>
        <td><input name="adhd_ph_7_nt" id="adhd_ph_7_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ph_7_nt'}; ?>" /></td>
      </tr>
			<tr><td colspan="3"><div class="wmtDottedB"></div></td></tr>
			<tr><td colspan="3" class="wmtChapter">Current History - Inattention</td>
      <tr>
        <td class="wmtBody">1.&nbsp;&nbsp;Short attention span when attempting boring or monotonous tasks</td>
        <td><select name="adhd_ci_1" id="adhd_ci_1" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_1'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_1_nt" id="adhd_ci_1_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_1_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">2.&nbsp;&nbsp;Trouble listening or following instructions</td>
        <td><select name="adhd_ci_2" id="adhd_ci_2" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_2'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_2_nt" id="adhd_ci_2_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_2_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">3.&nbsp;&nbsp;Frequently forgetful or misplacing things</td>
        <td><select name="adhd_ci_3" id="adhd_ci_3" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_3'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_3_nt" id="adhd_ci_3_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_3_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">4.&nbsp;&nbsp;Trouble starting or finishing books or novels</td>
        <td><select name="adhd_ci_4" id="adhd_ci_4" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_4'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_4_nt" id="adhd_ci_4_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_4_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">5.&nbsp;&nbsp;Tendency to become easily bored</td>
        <td><select name="adhd_ci_5" id="adhd_ci_5" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_5'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_5_nt" id="adhd_ci_5_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_5_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">6.&nbsp;&nbsp;Chronic procrastination</td>
        <td><select name="adhd_ci_6" id="adhd_ci_6" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_6'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_6_nt" id="adhd_ci_6_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_6_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">7.&nbsp;&nbsp;Trouble remembering appointments or obligations</td>
        <td><select name="adhd_ci_7" id="adhd_ci_7" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_7'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_7_nt" id="adhd_ci_7_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_7_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">8.&nbsp;&nbsp;Impatient, low frustration tolerance</td>
        <td><select name="adhd_ci_8" id="adhd_ci_8" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_8'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_8_nt" id="adhd_ci_8_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_8_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">9.&nbsp;&nbsp;Trouble completing or fnishing tasks</td>
        <td><select name="adhd_ci_9" id="adhd_ci_9" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_9'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_9_nt" id="adhd_ci_9_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_9_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">10.&nbsp;&nbsp;Rush through paperwork or tasks, frequent careless mistakes</td>
        <td><select name="adhd_ci_10" id="adhd_ci_10" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_10'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_10_nt" id="adhd_ci_10_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_10_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">11.&nbsp;&nbsp;Trouble listening in conversation</td>
        <td><select name="adhd_ci_11" id="adhd_ci_11" class="wmtInput">
				<?php echo ListSel($ad{'adhd_ci_11'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_ci_11_nt" id="adhd_ci_11_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_ci_11_nt'}; ?>" /></td>
      </tr>
			<tr><td colspan="3"><div class="wmtDottedB"></div></td></tr>
			<tr><td colspan="3" class="wmtChapter">Current History - Restless / Impulsive</td>
      <tr>
        <td class="wmtBody">1.&nbsp;&nbsp;Restlessness (tapping pencil, bouncing leg, etc.)</td>
        <td><select name="adhd_cr_1" id="adhd_cr_1" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_1'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_1_nt" id="adhd_cr_1_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_1_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">2.&nbsp;&nbsp;Need to be in constant motion in order to think or relax</td>
        <td><select name="adhd_cr_2" id="adhd_cr_2" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_2'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_2_nt" id="adhd_cr_2_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_2_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">3.&nbsp;&nbsp;Trouble sitting still, or staying in one place for too long</td>
        <td><select name="adhd_cr_3" id="adhd_cr_3" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_3'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_3_nt" id="adhd_cr_3_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_3_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">4.&nbsp;&nbsp;An internal sense of nervousness/restlessness</td>
        <td><select name="adhd_cr_4" id="adhd_cr_4" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_4'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_4_nt" id="adhd_cr_4_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_4_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">5.&nbsp;&nbsp;Impulsive, act without thinking</td>
        <td><select name="adhd_cr_5" id="adhd_cr_5" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_5'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_5_nt" id="adhd_cr_5_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_5_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">6.&nbsp;&nbsp;Short fuse, quick to anger</td>
        <td><select name="adhd_cr_6" id="adhd_cr_6" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_6'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_6_nt" id="adhd_cr_6_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_6_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">7.&nbsp;&nbsp;Innappropriate comments, saying exactly what comes to mind</td>
        <td><select name="adhd_cr_7" id="adhd_cr_7" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_7'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_7_nt" id="adhd_cr_7_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_7_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">8.&nbsp;&nbsp;Difficulties falling asleep, turning off thoughts at night</td>
        <td><select name="adhd_cr_8" id="adhd_cr_8" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_8'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_8_nt" id="adhd_cr_8_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_8_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">9.&nbsp;&nbsp;Multiple, impulsive job/career changes</td>
        <td><select name="adhd_cr_9" id="adhd_cr_9" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_9'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_9_nt" id="adhd_cr_9_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_9_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">10.&nbsp;&nbsp;Preference for high stimulation or excitement</td>
        <td><select name="adhd_cr_10" id="adhd_cr_10" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_10'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_10_nt" id="adhd_cr_10_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_10_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">11.&nbsp;&nbsp;Argumentative, stubborn, "hard-headed"</td>
        <td><select name="adhd_cr_11" id="adhd_cr_11" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_11'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_11_nt" id="adhd_cr_11_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_11_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">12.&nbsp;&nbsp;Tendency toward addictions (food, alcohol, drugs, work)</td>
        <td><select name="adhd_cr_12" id="adhd_cr_12" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_12'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_12_nt" id="adhd_cr_12_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_12_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">13.&nbsp;&nbsp;Frequent traffic violations, reckless driving</td>
        <td><select name="adhd_cr_13" id="adhd_cr_13" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cr_13'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cr_13_nt" id="adhd_cr_13_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cr_13_nt'}; ?>" /></td>
      </tr>
			<tr><td colspan="3"><div class="wmtDottedB"></div></td></tr>
			<tr><td colspan="3" class="wmtChapter">Current History - Disorganization</td>
      <tr>
        <td class="wmtBody">1.&nbsp;&nbsp;Chronically late or usually in a hurry or rush</td>
        <td><select name="adhd_cd_1" id="adhd_cd_1" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cd_1'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cd_1_nt" id="adhd_cd_1_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cd_1_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">2.&nbsp;&nbsp;Easily overwhelmed by tasks of daily living</td>
        <td><select name="adhd_cd_2" id="adhd_cd_2" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cd_2'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cd_2_nt" id="adhd_cd_2_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cd_2_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">3.&nbsp;&nbsp;Poor financial management (late of unpaid bills, excessive debt)</td>
        <td><select name="adhd_cd_3" id="adhd_cd_3" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cd_3'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cd_3_nt" id="adhd_cd_3_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cd_3_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">4.&nbsp;&nbsp;Disorganized work/living area</td>
        <td><select name="adhd_cd_4" id="adhd_cd_4" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cd_4'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cd_4_nt" id="adhd_cd_4_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cd_4_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">5.&nbsp;&nbsp;Messy handwriting</td>
        <td><select name="adhd_cd_5" id="adhd_cd_5" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cd_5'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cd_5_nt" id="adhd_cd_5_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cd_5_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">6.&nbsp;&nbsp;Sense of underachievement or not living up to potential</td>
        <td><select name="adhd_cd_6" id="adhd_cd_6" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cd_6'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cd_6_nt" id="adhd_cd_6_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cd_6_nt'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtBody">7.&nbsp;&nbsp;Inconsistent work performance (deadlines, paperwork, lateness, etc.)</td>
        <td><select name="adhd_cd_7" id="adhd_cd_7" class="wmtInput">
				<?php echo ListSel($ad{'adhd_cd_7'},'Rare_Some_Often'); ?></select></td>
        <td><input name="adhd_cd_7_nt" id="adhd_cd_7_nt" class="wmtFullInput" type="text" value="<?php echo $ad{'adhd_cd_7_nt'}; ?>" /></td>
      </tr>
    </table>

