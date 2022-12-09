<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" colspan="2">General:</td>
      <td rowspan="3"><textarea name="ee1_ge_gen_nt" id="ee1_ge_gen_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_gen_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td style="width: 12px;">&nbsp;</td>
      <td class="wmtBody" style="width: 180px;"><input name="ee1_ge_gen_norm_exam" id="ee1_ge_gen_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_gen_norm_exam'] == 1)?' checked':''); ?> onChange="setGEGeneralNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_gen_norm_exam">Set Normal</label></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_gen_button_disp" id="tmp_ge_gen_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_gen_');"><span>Clear</span></a></div></td>
		</tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Neck:</td>
      <td rowspan="3"><textarea name="ee1_ge_nk_nt" id="ee1_ge_nk_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_nk_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_nk_norm_exam" id="ee1_ge_nk_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_nk_norm_exam'] == 1)?' checked':''); ?> onChange="setGENeckNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_nk_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_neck_button_disp" id="tmp_ge_neck_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>','ge_nk_');"><span>Clear</span></a></div></td>
		</tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Face/Scalp:</td>
      <td rowspan="3"><textarea name="ee1_ge_hd_nt" id="ee1_ge_hd_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_hd_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_hd_norm_exam" id="ee1_ge_hd_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_hd_norm_exam'] == 1)?' checked':''); ?> onChange="setGEHeadNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_hd_norm_exam">Set Normal</label></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_head_button_disp" id="tmp_ge_head_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_hd_');"><span>Clear</span></a></div></td>
    </tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Cranial Nerves:</td>
			<td rowspan="3"><textarea name="ee1_ge_neu_cn_nt" id="ee1_ge_neu_cn_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_neu_cn_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_neu_norm_exam" id="ee1_ge_neu_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_neu_norm_exam'] == 1)?' checked':''); ?> onChange="setGENeuroNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_neu_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_neuro_button_disp" id="tmp_ge_neuro_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_neu_');"><span>Clear</span></a></div></td>
		</tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Ears:</td>
      <td rowspan="3"><textarea name="ee1_ge_ear_nt" id="ee1_ge_ear_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_ear_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_ear_norm_exam" id="ee1_ge_ear_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_ear_norm_exam'] == 1)?' checked':''); ?> onChange="setGEEarsNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_ear_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_ears_button_disp" id="tmp_ge_ears_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_ear');"><span>Clear</span></a></div></td>
    </tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Tuning Fork:</td>
      <td rowspan="3"><textarea name="ee1_ge_lym_nt" id="ee1_ge_lym_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_lym_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_lym_norm_exam" id="ee1_ge_lym_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_lym_norm_exam'] == 1)?' checked':''); ?> onChange="setGELymphNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_lym_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_lymph_button_disp" id="tmp_ge_lymph_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_lym_');"><span>Clear</span></a></div></td>
		</tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Eyes:</td>
     	<td rowspan="3"><textarea name="ee1_ge_eye_nt" id="ee1_ge_eye_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_eye_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_eye_norm_exam" id="ee1_ge_eye_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_eye_norm_exam'] == 1)?' checked':''); ?> onChange="setGEEyesNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_eye_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_eyes_button_disp" id="tmp_ge_eyes_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_eye');"><span>Clear</span></a></div></td>
    </tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Nose:</td>
      <td rowspan="3"><textarea name="ee1_ge_nose_nt" id="ee1_ge_nose_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_nose_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_nose_norm_exam" id="ee1_ge_nose_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_nose_norm_exam'] == 1)?' checked':''); ?> onChange="setGENoseNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_nose_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_nose_button_disp" id="tmp_ge_nose_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_nose_');"><span>Clear</span></a></div></td>
		</tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Oral Cavity:</td>
      <td rowspan="3"><textarea name="ee1_ge_mouth_nt" id="ee1_ge_mouth_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_mouth_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_mouth_norm_exam" id="ee1_ge_mouth_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_mouth_norm_exam'] == 1)?' checked':''); ?> onChange="setGEMouthNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_mouth_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_mouth_button_disp" id="tmp_ge_mouth_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_mouth_');"><span>Clear</span></a></div></td>
		</tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Oropharynx:</td>
      <td rowspan="3"><textarea name="ee1_ge_thrt_nt" id="ee1_ge_thrt_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_thrt_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_thrt_norm_exam" id="ee1_ge_thrt_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_thrt_norm_exam'] == 1)?' checked':''); ?> onChange="setGEThroatNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_thrt_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_throat_button_disp" id="tmp_ge_throat_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_thrt_');"><span>Clear</span></a></div></td>
		</tr>
		<tr><td>&nbsp;</td></tr>

    <tr>
      <td class="wmtLabel" colspan="2">Nasopharynx:</td>
      <td rowspan="3"><textarea name="ee1_ge_thy_nt" id="ee1_ge_thy_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_thy_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_thy_norm_exam" id="ee1_ge_thy_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_thy_norm_exam'] == 1)?' checked':''); ?> onChange="setGEThyroidNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_thy_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_thyroid_button_disp" id="tmp_ge_thyroid_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_thy_');"><span>Clear</span></a></div></td>
		</tr>
		<tr><td>&nbsp;</td></tr>

		<tr>
      <td class="wmtLabel" colspan="2">Larynx/Hypopharynx:</td>
			<td rowspan="3"><textarea name="ee1_ge_ms_nt" id="ee1_ge_ms_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_ms_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
      <td class="wmtBody"><input name="ee1_ge_ms_norm_exam" id="ee1_ge_ms_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_ms_norm_exam'] == 1)?' checked':''); ?> onChange="setGEMuscNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_ms_norm_exam">Set Normal</label</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><div name="tmp_ge_musc_button_disp" id="tmp_ge_musc_button_disp" style="float: left;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_ms_');"><span>Clear</span></a></div></td>
		<tr>
	</table>
<?php ?>
