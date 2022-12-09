<?php
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="5" class="wmtLabelRed">Gynecological Exam Dated: <?php echo $gyn{'date'}; ?>
				<td><a class="css_button" tabindex="-1" onClick="toggleGynExamNull();" href="javascript:;"><span>Clear Gyn Exam</span></a></td>
				<td><a class="css_button" tabindex="-1" onClick="toggleGynExamNormal();" href="javascript:;"><span>Set Normal</span></a></td>
			</tr>
      <tr>
        <td class="wmtLabel">Ext. Gen:</td>
        <td class="wmtBody"><input name="gyn_ext_wnl" id="gyn_ext_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_ext_wnl'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_ext_wnl','gyn_ext_abn');" /><label for="gyn_ext_wnl">&nbsp;WNL</label></td>
        <td class="wmtBody"><input name="gyn_ext_abn" id="gyn_ext_abn" type="checkbox" value="1" <?php echo (($gyn{'gyn_ext_abn'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_ext_abn','gyn_ext_wnl');" /><label for="gyn_ext_abn">&nbsp;Abnormal</label></td>
        <td class="wmtBody">Other:</td>
        <td colspan="3"><input name="gyn_ext_comm" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_ext_comm'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtLabel">Urethral Meatus:</td>
        <td class="wmtBody"><input name="gyn_mea_wnl" id="gyn_mea_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_mea_wnl'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_mea_wnl','gyn_mea_abn');" /><label for="gyn_mea_wnl">&nbsp;WNL</label></td>
        <td class="wmtBody"><input name="gyn_mea_abn" id="gyn_mea_abn" type="checkbox" value="1" <?php echo (($gyn{'gyn_mea_abn'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_mea_abn','gyn_mea_wnl');" /><label for="gyn_mea_abn">&nbsp;Abnormal</label></td>
        <td class="wmtBody">Other:</td>
        <td colspan="3"><input name="gyn_mea_comm" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_mea_comm'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtLabel">Urethra:</td>
        <td class="wmtBody"><input name="gyn_ure_wnl" id="gyn_ure_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_ure_wnl'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_ure_wnl','gyn_ure_abn');" /><label for="gyn_ure_wnl">&nbsp;WNL</label></td>
        <td class="wmtBody"><input name="gyn_ure_abn" id="gyn_ure_abn" type="checkbox" value="1" <?php echo (($gyn{'gyn_ure_abn'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_ure_abn','gyn_ure_wnl');" /><label for="gyn_ure_abn">&nbsp;Abnormal</label></td>
        <td class="wmtBody">Other:</td>
        <td colspan="3"><input name="gyn_ure_comm" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_ure_comm'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtLabel">Bladder:</td>
        <td class="wmtBody"><input name="gyn_blad_wnl" id="gyn_blad_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_blad_wnl'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_blad_wnl','gyn_blad_abn');" /><label for="gyn_blad_wnl">&nbsp;WNL</label></td>
        <td class="wmtBody"><input name="gyn_blad_abn" id="gyn_blad_abn" type="checkbox" value="1" <?php echo (($gyn{'gyn_blad_abn'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_blad_abn','gyn_blad_wnl');" /><label for="gyn_blad_abn">&nbsp;Abnormal</label></td>
        <td class="wmtBody">Other:</td>
        <td colspan="3"><input name="gyn_blad_comm" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_blad_comm'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtLabel">Vagina:</td>
        <td class="wmtBody"><input name="gyn_vag_wnl" id="gyn_vag_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_vag_wnl'} == '1')?' checked ':''); ?> onchange="VerifyYesFirstCheck('gyn_vag_wnl','name','gyn_vag_');" /><label for="gyn_vag_wnl">&nbsp;WNL</label></td>
        <td class="wmtBody"><input name="gyn_vag_abn" id="gyn_vag_abn" type="checkbox" value="1" <?php echo (($gyn{'gyn_vag_abn'} == '1')?' checked ':''); ?> onchange="TogglePair('gyn_vag_abn','gyn_vag_wnl');" /><label for="gyn_vag_abn">&nbsp;Abnormal</label></td>
        <td class="wmtBody"><input name="gyn_vag_dc" id="gyn_vag_dc" type="checkbox" value="1" <?php echo (($gyn{'gyn_vag_dc'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_vag_dc','gyn_vag_wnl');"  /><label for="gyn_vag_dc">&nbsp;D/C</label></td>
        <td class="wmtBody"><input name="gyn_vag_atro" id="gyn_vag_atro" type="checkbox" value="1" <?php echo (($gyn{'gyn_vag_atro'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_vag_atro','gyn_vag_wnl');" /><label for="gyn_vag_atro">&nbsp;Atrophic&nbsp;&nbsp;</label>&nbsp;<select name="gyn_vag_atro_type" id="gyn_vag_atro_type" class="Input" onchange="afterAtrophic();">
				<?php echo ListSel($gyn{'gyn_vag_atro_type'},'WHC_1_to_3'); ?></select></td>
        <td class="wmtBody"><input name="gyn_vag_cys" id="gyn_vag_cys" type="checkbox" value="1" <?php echo (($gyn{'gyn_vag_cys'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_vag_cys','gyn_vag_wnl');" /><label for="gyn_vag_cys">&nbsp;Cystocele&nbsp;&nbsp;</label>&nbsp;<select name="gyn_vag_cys_type" id="gyn_vag_cys_type" class="Input" onchange="afterCystocele();">
				<?php echo ListSel($gyn{'gyn_vag_cys_type'},'WHC_1_to_3'); ?></select></td>
        <td class="wmtBody"><input name="gyn_vag_rec" id="gyn_vag_rec" type="checkbox" value="1" <?php echo (($gyn{'gyn_vag_rec'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_vag_rec','gyn_vag_wnl');" /><label for="gyn_vag_rec">&nbsp;Rectocele&nbsp;&nbsp;</label>&nbsp;<select name="gyn_vag_rec_type" id="gyn_vag_rec_type" class="Input" onchange="afterRectocele();">
				<?php echo ListSel($gyn{'gyn_vag_rec_type'},'WHC_1_to_3'); ?></select></td>
      </tr>
			<tr>
				<td class="wmtBody wmtR">Notes:&nbsp;&nbsp;</td>
				<td colspan="6"><input name="gyn_vag_nt" id="gyn_vag_nt" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_vag_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Cervix:</td>
        <td class="wmtBody"><input name="gyn_cer_wnl" id="gyn_cer_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_cer_wnl'} == '1')?' checked ':''); ?> onchange="VerifyYesFirstCheck('gyn_cer_wnl','name','gyn_cer');" /><label for="gyn_cer_wnl">&nbsp;WNL</label></td>
        <td class="wmtBody"><input name="gyn_cer_abs" id="gyn_cer_abs" type="checkbox" value="1" <?php echo (($gyn{'gyn_cer_abs'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_cer_abs','gyn_cer_wnl');" /><label for="gyn_cer_abs">&nbsp;Absent</label></td>
        <td class="wmtBody"><input name="gyn_cer_fri" id="gyn_cer_fri" type="checkbox" value="1" <?php echo (($gyn{'gyn_cer_fri'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_cer_fri','gyn_cer_wnl');" /><label for="gyn_cer_fri">&nbsp;Friable</label></td>
        <td class="wmtBody"><input name="gyn_cer_ant" id="gyn_cer_ant" type="checkbox" value="1" <?php echo (($gyn{'gyn_cer_ant'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_cer_ant','gyn_cer_wnl');" /><label for="gyn_cer_ant">&nbsp;Anteverted</label></td>
        <td class="wmtBody"><input name="gyn_cer_polyp" id="gyn_cer_polyp" type="checkbox" value="1" <?php echo (($gyn{'gyn_cer_polyp'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_cer_polyp','gyn_cer_wnl');" /><label for="gyn_cer_polyp">&nbsp;Polyp</label></td>
        <td class="wmtBody"><input name="gyn_cer_iud" id="gyn_cer_iud" type="checkbox" value="1" <?php echo (($gyn{'gyn_cer_iud'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_cer_iud','gyn_cer_wnl');" /><label for="gyn_cer_iud">&nbsp;IuD String</label></td>
      </tr>
			<tr>
				<td class="wmtBody wmtR">Notes:&nbsp;&nbsp;</td>
				<td colspan="6"><input name="gyn_cer_nt" id="gyn_cer_nt" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_cer_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Uterus:</td>
        <td class="wmtBody"><input name="gyn_ut_wnl" id="gyn_ut_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_ut_wnl'} == '1')?' checked ':''); ?> onchange="VerifyYesFirstCheck('gyn_ut_wnl','name','gyn_ut_');" /><label for="gyn_ut_wnl">&nbsp;WNL</label></td>
        <td class="wmtBody"><input name="gyn_ut_abs" id="gyn_ut_abs" type="checkbox" value="1" <?php echo (($gyn{'gyn_ut_abs'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ut_abs','gyn_ut_wnl');" /><label for="gyn_ut_abs">&nbsp;Absent</label></td>
        <td class="wmtBody"><input name="gyn_ut_retro" id="gyn_ut_retro" type="checkbox" value="1" <?php echo (($gyn{'gyn_ut_retro'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ut_retro','gyn_ut_wnl');" /><label for="gyn_ut_retro">&nbsp;Retroflexed</label></td>
        <td class="wmtBody"><input name="gyn_ut_tender" id="gyn_ut_tender" type="checkbox" value="1" <?php echo (($gyn{'gyn_ut_tender'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ut_tender','gyn_ut_wnl');" /><label for="gyn_ut_tender">&nbsp;Tender</label></td>
        <td class="wmtBody">Size:&nbsp;<input name="gyn_ut_size" class="Input" style="width: 70px" type="text" value="<?php echo $gyn{'gyn_ut_size'}; ?>" />&nbsp;Wks</td>
        <td class="wmtBody"><input name="gyn_ut_pro" id="gyn_ut_pro" type="checkbox" value="1" <?php echo (($gyn{'gyn_ut_pro'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ut_pro','gyn_ut_wnl');" /><label for="gyn_ut_pro">&nbsp;Prolapsed</label></td>
      </tr>
			<tr>
				<td class="wmtBody wmtR">Notes:&nbsp;&nbsp;</td>
				<td colspan="6"><input name="gyn_ut_nt" id="gyn_ut_nt" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_ut_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Adnexa:</td>
        <td class="wmtBody"><input name="gyn_ad_wnl" id="gyn_ad_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_ad_wnl'} == '1')?' checked ':''); ?> onchange="VerifyYesFirstCheck('gyn_ad_wnl','name','gyn_ad_');" /><label for="gyn_ad_wnl">&nbsp;WNL</label></td>
        <td class="wmtBody"><input name="gyn_ad_absent" id="gyn_ad_absent" type="checkbox" value="1" <?php echo (($gyn{'gyn_ad_absent'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ad_absent','gyn_ad_wnl');" /><label for="gyn_ad_absent">&nbsp;Absent</label></td>
        <td class="wmtBody"><input name="gyn_ad_tender" id="gyn_ad_tender" type="checkbox" value="1" <?php echo (($gyn{'gyn_ad_tender'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ad_tender','gyn_ad_wnl');" /><label for="gyn_ad_tender">&nbsp;Tender</label></td>
        <td class="wmtBody"><input name="gyn_ad_enl" id="gyn_ad_enl" type="checkbox" value="1" <?php echo (($gyn{'gyn_ad_enl'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ad_enl','gyn_ad_wnl');" /><label for="gyn_ad_enl">&nbsp;Enlarged</label></td>
        <td class="wmtBody"><input name="gyn_ad_firm" id="gyn_ad_firm" type="checkbox" value="1" <?php echo (($gyn{'gyn_ad_firm'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ad_firm','gyn_ad_wnl');" /><label for="gyn_ad_firm">&nbsp;Firm</label></td>
        <td class="wmtBody"><input name="gyn_ad_mass" id="gyn_ad_mass" type="checkbox" value="1" <?php echo (($gyn{'gyn_ad_mass'} == '1')? 'checked ':''); ?> onchange="TogglePair('gyn_ad_mass','gyn_ad_wnl');" /><label for="gyn_ad_mass">&nbsp;Mass</label></td>
      </tr>
			<tr>
				<td class="wmtBody wmtR">Notes:&nbsp;&nbsp;</td>
				<td colspan="6"><input name="gyn_ad_nt" id="gyn_ad_nt" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_ad_nt'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Rectal:</td>
        <td class="wmtBody"><input name="gyn_rec_wnl" id="gyn_rec_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_rec_wnl'} == '1')?' checked ':''); ?> ><label for="gyn_rec_wnl">&nbsp;WNL&nbsp;</label>&nbsp;&nbsp;&nbsp;&nbsp;<span class="wmtLabel">Other</span></td>
        <td colspan="5"><input name="gyn_rec_comm" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_rec_comm'}; ?>" /></td>
      </tr>
      <tr>
        <td class="wmtLabel">Anus/Perineum:</td>
        <td class="wmtBody"><input name="gyn_an_wnl" id="gyn_an_wnl" type="checkbox" value="1" <?php echo (($gyn{'gyn_an_wnl'} == '1')?' checked ':''); ?> /><label for="gyn_an_wnl">&nbsp;WNL&nbsp;</label>&nbsp;&nbsp;&nbsp;&nbsp;<span class="wmtLabel">Other</span></td>
        <td colspan="5"><input name="gyn_an_comm" class="wmtFullInput" type="text" value="<?php echo $gyn{'gyn_an_comm'}; ?>" /></td>
      </tr>
			<tr>
				<td class="wmtLabelT">Other Findings:</td>
				<td colspan="6"><textarea name="gyn_comment" id="gyn_comment" class="wmtFullInput" rows="4"><?php echo $gyn{'gyn_comment'}; ?></textarea></td>
			</tr>
    </table>
