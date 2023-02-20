<?php
	if(!isset($portal_mode)) { $portal_mode = false; }
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="wmtLabel">Do you currently have any of the following problems?<br/>&nbsp;&nbsp;&nbsp;&nbsp;Please mark yes or no:</td>
        <td><a style="margin-left: 20px" class="css_button" tabindex="-1" onclick="return toggleROStoNull();" href="javascript:;"><span>Clear All</span></a><a class="css_button" style="margin-left: 80px" tabindex="-1" onclick="return toggleROStoNo()" href="javascript:;"><span>Set All to 'NO'</span></a></td>
      </tr>
      <tr>
        <td style="width: 50%">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
            <tr>
              <td class="wmtLabel" colspan="2">Constitutional</td>
            </tr>
            <tr>
              <td style="width: 6px">&nbsp;</td>
              <td class="wmtBody">Fever / Chills</td>
              <td style="width: 70px"><select name="ee1_rs_fev" id="ee1_rs_fev" class="Input">
                <?php ListSel($rs{'ee1_rs_fev'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_fev_nt" id="ee1_rs_fev_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_fev_nt'}; ?>" /></td>
              <td style="width: 6px">&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Weight Loss</td>
              <td><select name="ee1_rs_loss" id="ee1_rs_loss" class="Input">
                <?php ListSel($rs{'ee1_rs_loss'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_loss_nt" id="ee1_rs_loss_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_loss_nt'}?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Weight Gain</td>
              <td><select name="ee1_rs_gain" id="ee1_rs_gain" class="Input"> 
                <?php ListSel($rs{'ee1_rs_gain'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_gain_nt" id="ee1_rs_gain_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_gain_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Musculoskeletal</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Joint Pain</td>
              <td><select name="ee1_rs_jnt" id="ee1_rs_jnt" class="Input">
                <?php ListSel($rs{'ee1_rs_jnt'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_jnt_nt" id="ee1_rs_jnt_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_jnt_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Joint Stiffness</td>
              <td><select name="ee1_rs_stiff" id="ee1_rs_stiff" class="Input">
                <?php ListSel($rs{'ee1_rs_stiff'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_stiff_nt" id="ee1_rs_stiff_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_stiff_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Muscle Weakness</td>
              <td><select name="ee1_rs_wk" id="ee1_rs_wk" class="Input">
                <?php ListSel($rs{'ee1_rs_wk'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_wk_nt" id="ee1_rs_wk_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_wk_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Muscle Pain</td>
              <td><select name="ee1_rs_mpain" id="ee1_rs_mpain" class="Input">
                <?php ListSel($rs{'ee1_rs_mpain'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_mpain_nt" id="ee1_rs_mpain_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_mpain_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Polymyalgia Above Waist</td>
              <td><select name="ee1_rs_ply_up" id="ee1_rs_ply_up" class="Input">
                <?php ListSel($rs{'ee1_rs_ply_up'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_ply_up_nt" id="ee1_rs_ply_up_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_ply_up_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Polymyalgia Below Waist</td>
              <td><select name="ee1_rs_ply_dn" id="ee1_rs_ply_dn" class="Input">
                <?php ListSel($rs{'ee1_rs_ply_dn'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_ply_dn_nt" id="ee1_rs_ply_dn_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_ply_dn_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Allergic/Immunologic</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hay Fever</td>
              <td><select name="ee1_rs_hay" id="ee1_rs_hay" class="Input">
                <?php ListSel($rs{'ee1_rs_hay'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hay_nt" id="ee1_rs_hay_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hay_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Medications</td>
              <td><select name="ee1_rs_med" id="ee1_rs_med" class="Input">
                <?php ListSel($rs{'ee1_rs_med'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_med_nt" id="ee1_rs_med_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_med_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Skin</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Rash/Sores</td>
              <td><select name="ee1_rs_rash" id="ee1_rs_rash" class="Input">
                <?php ListSel($rs{'ee1_rs_rash'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_rash_nt" id="ee1_rs_rash_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_rash_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Mole Changes</td>
              <td><select name="ee1_rs_ml" id="ee1_rs_ml" class="Input">
                <?php ListSel($rs{'ee1_rs_ml'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_ml_nt" id="ee1_rs_ml_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_ml_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td class="wmtLabel" colspan="2">Breast</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Nipple Discharge</td>
              <td><select name="ee1_rs_nip" id="ee1_rs_nip" class="Input">
                <?php ListSel($rs{'ee1_rs_nip'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_nip_nt" id="ee1_rs_nip_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_nip_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Lumps</td>
              <td><select name="ee1_rs_lmp" id="ee1_rs_lmp" class="Input">
                <?php ListSel($rs{'ee1_rs_lmp'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_lmp_nt" id="ee1_rs_lmp_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_lmp_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Skin Changes</td>
              <td><select name="ee1_rs_skn" id="ee1_rs_skn" class="Input">
                <?php ListSel($rs{'ee1_rs_skn'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_skn_nt" id="ee1_rs_skn_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_skn_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr>
              <td class="wmtLabel" colspan="2">Neurologic</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Dizziness</td>
              <td><select name="ee1_rs_diz" id="ee1_rs_diz" class="Input">
                <?php ListSel($rs{'ee1_rs_diz'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_diz_nt" id="ee1_rs_diz_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_diz_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Seizures</td>
              <td><select name="ee1_rs_sz" id="ee1_rs_sz" class="Input">
                <?php ListSel($rs{'ee1_rs_sz'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_sz_nt" id="ee1_rs_sz_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_sz_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Numbness/Tingling</td>
              <td><select name="ee1_rs_numb" id="ee1_rs_numb" class="Input">
                <?php ListSel($rs{'ee1_rs_numb'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_numb_nt" id="ee1_rs_numb_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_numb_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Headaches</td>
              <td><select name="ee1_rs_head" id="ee1_rs_head" class="Input">
                <?php ListSel($rs{'ee1_rs_head'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_head_nt" id="ee1_rs_head_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_head_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Muscle Strength</td>
              <td><select name="ee1_rs_strength" id="ee1_rs_strength" class="Input">
                <?php ListSel($rs{'ee1_rs_strength'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_strength_nt" id="ee1_rs_strength_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_strength_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Tremors</td>
              <td><select name="ee1_rs_tremor" id="ee1_rs_tremor" class="Input">
                <?php ListSel($rs{'ee1_rs_tremor'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_tremor_nt" id="ee1_rs_tremor_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_tremor_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Dysarthria</td>
              <td><select name="ee1_rs_dysarthria" id="ee1_rs_dysarthria" class="Input">
                <?php ListSel($rs{'ee1_rs_dysarthria'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_dysarthria_nt" id="ee1_rs_dysarthria_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_dysarthria_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Endocrine</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hair Loss</td>
              <td><select name="ee1_rs_hair" id="ee1_rs_hair" class="Input">
                <?php ListSel($rs{'ee1_rs_hair'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hair_nt" id="ee1_rs_hair_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hair_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Acne</td>
              <td><select name="ee1_rs_acne" id="ee1_rs_acne" class="Input">
                <?php ListSel($rs{'ee1_rs_acne'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_acne_nt" id="ee1_rs_acne_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_acne_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Heat/Cold Intolerance</td>
              <td><select name="ee1_rs_hotcold" id="ee1_rs_hotcold" class="Input">
                <?php ListSel($rs{'ee1_rs_hotcold'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hotcold_nt" id="ee1_rs_hotcold_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hotcold_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id == 'capeneuro') { ?>
            	<tr>
              	<td>&nbsp;</td>
              	<td class="wmtBody">Hot Flashes</td>
              	<td><select name="ee1_rs_hot" id="ee1_rs_hot" class="Input">
                	<?php ListSel($rs{'ee1_rs_hot'},'Yes_No'); ?>
              	</select></td>
              	<td><input name="ee1_rs_hot_nt" id="ee1_rs_hot_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hot_nt'}; ?>" /></td>
              	<td>&nbsp;</td>
            	</tr>
						<?php } ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Diabetes</td>
              <td><select name="ee1_rs_diabetes" id="ee1_rs_diabetes" class="Input">
                <?php ListSel($rs{'ee1_rs_diabetes'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_diabetes_nt" id="ee1_rs_diabetes_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_diabetes_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Thyroid Problems</td>
              <td><select name="ee1_rs_thyroid" id="ee1_rs_thyroid" class="Input">
                <?php ListSel($rs{'ee1_rs_thyroid'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_thyroid_nt" id="ee1_rs_thyroid_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_thyroid_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Fatigue</td>
              <td><select name="ee1_rs_tired" id="ee1_rs_tired" class="Input">
                <?php ListSel($rs{'ee1_rs_tired'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_tired_nt" id="ee1_rs_tired_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_tired_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Change in Voice</td>
              <td><select name="ee1_rs_voice" id="ee1_rs_voice" class="Input">
                <?php ListSel($rs{'ee1_rs_voice'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_voice_nt" id="ee1_rs_voice_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_voice_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Dysphagia</td>
              <td><select name="ee1_rs_dysphagia" id="ee1_rs_dysphagia" class="Input">
                <?php ListSel($rs{'ee1_rs_dysphagia'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_dysphagia_nt" id="ee1_rs_dysphagia_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_dysphagia_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Odynophagia</td>
              <td><select name="ee1_rs_odyno" id="ee1_rs_odyno" class="Input">
                <?php ListSel($rs{'ee1_rs_odyno'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_odyno_nt" id="ee1_rs_odyno_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_odyno_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Polyuria</td>
              <td><select name="ee1_rs_polyuria" id="ee1_rs_polyuria" class="Input">
                <?php ListSel($rs{'ee1_rs_polyuria'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_polyuria_nt" id="ee1_rs_polyuria_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_polyuria_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Polydipsia</td>
              <td><select name="ee1_rs_polydipsia" id="ee1_rs_polydipsia" class="Input">
                <?php ListSel($rs{'ee1_rs_polydipsia'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_polydipsia_nt" id="ee1_rs_polydipsia_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_polydipsia_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Nightmares</td>
              <td><select name="ee1_rs_nightmare" id="ee1_rs_nightmare" class="Input">
                <?php ListSel($rs{'ee1_rs_nightmare'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_nightmare_nt" id="ee1_rs_nightmare_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_nightmare_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Night Sweats</td>
              <td><select name="ee1_rs_nightswt" id="ee1_rs_nightswt" class="Input">
                <?php ListSel($rs{'ee1_rs_nightswt'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_nightswt_nt" id="ee1_rs_nightswt_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_nightswt_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Brittle Nails</td>
              <td><select name="ee1_rs_brittle" id="ee1_rs_brittle" class="Input">
                <?php ListSel($rs{'ee1_rs_brittle'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_brittle_nt" id="ee1_rs_brittle_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_brittle_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Excessive Sweating</td>
              <td><select name="ee1_rs_sweat" id="ee1_rs_sweat" class="Input">
                <?php ListSel($rs{'ee1_rs_sweat'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_sweat_nt" id="ee1_rs_sweat_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_sweat_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Change in Neck Size</td>
              <td><select name="ee1_rs_neck" id="ee1_rs_neck" class="Input">
                <?php ListSel($rs{'ee1_rs_neck'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_neck_nt" id="ee1_rs_neck_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_neck_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($pat_sex == 'f') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Irregular Menses</td>
              <td><select name="ee1_rs_menses" id="ee1_rs_menses" class="Input">
                <?php ListSel($rs{'ee1_rs_menses'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_menses_nt" id="ee1_rs_menses_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_menses_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hirsutism</td>
              <td><select name="ee1_rs_hirs" id="ee1_rs_hirs" class="Input">
                <?php ListSel($rs{'ee1_rs_hirs'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hirs_nt" id="ee1_rs_hirs_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hirs_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Excessive Thrist</td>
              <td><select name="ee1_rs_thirst" id="ee1_rs_thirst" class="Input">
                <?php ListSel($rs{'ee1_rs_thirst'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_thirst_nt" id="ee1_rs_thirst_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_thirst_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Gastrointestinal</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Nausea</td>
              <td><select name="ee1_rs_naus" id="ee1_rs_naus" class="Input">
                <?php ListSel($rs{'ee1_rs_naus'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_naus_nt" id="ee1_rs_naus_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_naus_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Vomiting</td>
              <td><select name="ee1_rs_vomit" id="ee1_rs_vomit" class="Input">
                <?php ListSel($rs{'ee1_rs_vomit'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_vomit_nt" id="ee1_rs_vomit_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_vomit_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Reflux</td>
              <td><select name="ee1_rs_ref" id="ee1_rs_ref" class="Input">
                <?php ListSel($rs{'ee1_rs_ref'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_ref_nt" id="ee1_rs_ref_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_ref_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Abdominal Pain</td>
              <td><select name="ee1_rs_abd_p" id="ee1_rs_abd_p" class="Input">
                <?php ListSel($rs{'ee1_rs_abd_p'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_abd_p_nt" id="ee1_rs_abd_p_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_abd_p_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Anal Pain</td>
              <td><select name="ee1_rs_anal_p" id="ee1_rs_anal_p" class="Input">
                <?php ListSel($rs{'ee1_rs_anal_p'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_anal_p_nt" id="ee1_rs_anal_p_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_anal_p_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Jaundice</td>
              <td><select name="ee1_rs_jaun" id="ee1_rs_jaun" class="Input">
                <?php ListSel($rs{'ee1_rs_jaun'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_jaun_nt" id="ee1_rs_jaun_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_jaun_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Change Bowel Habits</td>
              <td><select name="ee1_rs_bow" id="ee1_rs_bow" class="Input">
                <?php ListSel($rs{'ee1_rs_bow'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_bow_nt" id="ee1_rs_bow_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_bow_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Diarrhea</td>
              <td><select name="ee1_rs_dia" id="ee1_rs_dia" class="Input">
                <?php ListSel($rs{'ee1_rs_dia'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_dia_nt" id="ee1_rs_dia_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_dia_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Constipation</td>
              <td><select name="ee1_rs_const" id="ee1_rs_const" class="Input">
                <?php ListSel($rs{'ee1_rs_const'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_const_nt" id="ee1_rs_const_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_const_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Melena</td>
              <td><select name="ee1_rs_melena" id="ee1_rs_melena" class="Input">
                <?php ListSel($rs{'ee1_rs_melena'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_melena_nt" id="ee1_rs_melena_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_const_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hematemesis</td>
              <td><select name="ee1_rs_hematemesis" id="ee1_rs_hematemesis" class="Input">
                <?php ListSel($rs{'ee1_rs_hematemesis'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hematemesis_nt" id="ee1_rs_hematemesis_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hematemesis_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hematochezia</td>
              <td><select name="ee1_rs_hematochezia" id="ee1_rs_hematochezia" class="Input">
                <?php ListSel($rs{'ee1_rs_hematochezia'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hematochezia_nt" id="ee1_rs_hematochezia_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hematochezia_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
					<?php if($client_id == 'capeneuro') { ?>
						<tr><td>&nbsp;</td></tr>
					<?php } ?>
					<?php if($pat_sex == 'f') { ?>
					<?php if($client_id != 'capeneuro') { ?>
					<?php }
					} ?>
          </table>
        </td>
        <td style="width: 50%; vertical-align: top;">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
            <tr>
              <td class="wmtLabel" colspan="2">Cardiovascular</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Chest Pain</td>
              <td><select name="ee1_rs_cpain" id="ee1_rs_cpain" class="Input">
                <?php ListSel($rs{'ee1_rs_cpain'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_cpain_nt" id="ee1_rs_cpain_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_cpain_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Difficulty Breathing</td>
              <td><select name="ee1_rs_breathe" id="ee1_rs_breathe" class="Input">
                <?php ListSel($rs{'ee1_rs_breathe'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_breathe_nt" id="ee1_rs_breathe_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_breathe_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Swelling</td>
              <td><select name="ee1_rs_swell" id="ee1_rs_swell" class="Input">
                <?php ListSel($rs{'ee1_rs_swell'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_swell_nt" id="ee1_rs_swell_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_swell_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Palpitations</td>
              <td><select name="ee1_rs_palp" id="ee1_rs_palp" class="Input">
                <?php ListSel($rs{'ee1_rs_palp'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_palp_nt" id="ee1_rs_palp_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_palp_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Jaw Pain</td>
              <td><select name="ee1_rs_jaw" id="ee1_rs_jaw" class="Input">
                <?php ListSel($rs{'ee1_rs_jaw'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_jaw_nt" id="ee1_rs_jaw_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_jaw_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Arm Pain</td>
              <td><select name="ee1_rs_arm" id="ee1_rs_arm" class="Input">
                <?php ListSel($rs{'ee1_rs_arm'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_arm_nt" id="ee1_rs_arm_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_arm_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Back Pain</td>
              <td><select name="ee1_rs_back" id="ee1_rs_back" class="Input">
                <?php ListSel($rs{'ee1_rs_back'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_back_nt" id="ee1_rs_back_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_back_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Acute Nitroglycerin Use</td>
              <td><select name="ee1_rs_acute" id="ee1_rs_acute" class="Input">
                <?php ListSel($rs{'ee1_rs_acute'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_acute_nt" id="ee1_rs_acute_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_acute_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Respiratory</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Wheezing</td>
              <td><select name="ee1_rs_whz" id="ee1_rs_whz" class="Input">
                <?php ListSel($rs{'ee1_rs_whz'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_whz_nt" id="ee1_rs_whz_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_whz_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Shortness of Breath</td>
              <td><select name="ee1_rs_shrt" id="ee1_rs_shrt" class="Input">
                <?php ListSel($rs{'ee1_rs_shrt'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_shrt_nt" id="ee1_rs_shrt_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_shrt_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Cough</td>
              <td><select name="ee1_rs_cgh" id="ee1_rs_cgh" class="Input">
                <?php ListSel($rs{'ee1_rs_cgh'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_cgh_nt" id="ee1_rs_cgh_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_cgh_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Sleep Apnea</td>
              <td><select name="ee1_rs_slp" id="ee1_rs_slp" class="Input">
                <?php ListSel($rs{'ee1_rs_slp'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_slp_nt" id="ee1_rs_slp_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_slp_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Sputum</td>
              <td><select name="ee1_rs_spu" id="ee1_rs_spu" class="Input">
                <?php ListSel($rs{'ee1_rs_spu'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_spu_nt" id="ee1_rs_spu_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_spu_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Dyspnea on Exertion</td>
              <td><select name="ee1_rs_dys" id="ee1_rs_dys" class="Input">
                <?php ListSel($rs{'ee1_rs_dys'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_dys_nt" id="ee1_rs_dys_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_dys_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hemoptysis</td>
              <td><select name="ee1_rs_hemoptysis" id="ee1_rs_hemoptysis" class="Input">
                <?php ListSel($rs{'ee1_rs_hemoptysis'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hemoptysis_nt" id="ee1_rs_hemoptysis_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hemoptysis_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Snoring</td>
              <td><select name="ee1_rs_snore" id="ee1_rs_snore" class="Input">
                <?php ListSel($rs{'ee1_rs_snore'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_snore_nt" id="ee1_rs_snore_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_snore_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Eyes</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Blurred Vision</td>
              <td><select name="ee1_rs_blr" id="ee1_rs_blr" class="Input">
                <?php ListSel($rs{'ee1_rs_blr'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_blr_nt" id="ee1_rs_blr_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_blr_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Double Vision</td>
              <td><select name="ee1_rs_dbl" id="ee1_rs_dbl" class="Input">
                <?php ListSel($rs{'ee1_rs_dbl'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_dbl_nt" id="ee1_rs_dbl_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_dbl_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Vision Changes</td>
              <td><select name="ee1_rs_vis" id="ee1_rs_vis" class="Input">
                <?php ListSel($rs{'ee1_rs_vis'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_vis_nt" id="ee1_rs_vis_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_vis_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Loss of Vision</td>
              <td><select name="ee1_rs_vloss" id="ee1_rs_vloss" class="Input">
                <?php ListSel($rs{'ee1_rs_vloss'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_vloss_nt" id="ee1_rs_vloss_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_vloss_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Blind</td>
              <td><select name="ee1_rs_blind" id="ee1_rs_blind" class="Input">
                <?php ListSel($rs{'ee1_rs_blind'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_blind_nt" id="ee1_rs_blind_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_blind_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Macular Degeneration</td>
              <td><select name="ee1_rs_mac" id="ee1_rs_mac" class="Input">
                <?php ListSel($rs{'ee1_rs_mac'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_mac_nt" id="ee1_rs_mac_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_mac_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Pain</td>
              <td><select name="ee1_rs_vpain" id="ee1_rs_vpain" class="Input">
                <?php ListSel($rs{'ee1_rs_vpain'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_vpain_nt" id="ee1_rs_vpain_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_vpain_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Dry</td>
              <td><select name="ee1_rs_dry" id="ee1_rs_dry" class="Input">
                <?php ListSel($rs{'ee1_rs_dry'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_dry_nt" id="ee1_rs_dry_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_dry_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Ear/Nose/Throat/Mouth</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Sore Throat</td>
              <td><select name="ee1_rs_sore" id="ee1_rs_sore" class="Input">
                <?php ListSel($rs{'ee1_rs_sore'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_sore_nt" id="ee1_rs_sore_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_sore_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Sinus Problems</td>
              <td><select name="ee1_rs_sin" id="ee1_rs_sin" class="Input">
                <?php ListSel($rs{'ee1_rs_sin'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_sin_nt" id="ee1_rs_sin_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_sin_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hearing Problems</td>
              <td><select name="ee1_rs_hear" id="ee1_rs_hear" class="Input">
                <?php ListSel($rs{'ee1_rs_hear'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hear_nt" id="ee1_rs_hear_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hear_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Tinnitus</td>
              <td><select name="ee1_rs_tin" id="ee1_rs_tin" class="Input">
                <?php ListSel($rs{'ee1_rs_tin'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_tin_nt" id="ee1_rs_tin_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_tin_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hot Flashes</td>
              <td><select name="ee1_rs_hot" id="ee1_rs_hot" class="Input">
                <?php ListSel($rs{'ee1_rs_hot'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hot_nt" id="ee1_rs_hot_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hot_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Swollen Lymph Nodes</td>
              <td><select name="ee1_rs_lymph" id="ee1_rs_lymph" class="Input">
                <?php ListSel($rs{'ee1_rs_lymph'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_lymph_nt" id="ee1_rs_lymph_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_lymph_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Mass</td>
              <td><select name="ee1_rs_mass" id="ee1_rs_mass" class="Input">
                <?php ListSel($rs{'ee1_rs_mass'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_mass_nt" id="ee1_rs_mass_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_mass_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Ear Pain</td>
              <td><select name="ee1_rs_epain" id="ee1_rs_epain" class="Input">
                <?php ListSel($rs{'ee1_rs_epain'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_epain_nt" id="ee1_rs_epain_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_epain_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id == 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Chronic Nose Bleeds</td>
              <td><select name="ee1_rs_nose" id="ee1_rs_nose" class="Input">
                <?php ListSel($rs{'ee1_rs_nose'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_nose_nt" id="ee1_rs_nose_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_nose_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Lymphatic</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Swollen Glands</td>
              <td><select name="ee1_rs_swl" id="ee1_rs_swl" class="Input">
                <?php ListSel($rs{'ee1_rs_swl'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_swl_nt" id="ee1_rs_swl_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_swl_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Frequent Bruising</td>
              <td><select name="ee1_rs_brse" id="ee1_rs_brse" class="Input">
                <?php ListSel($rs{'ee1_rs_brse'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_brse_nt" id="ee1_rs_brse_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_brse_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Chronic Nose Bleeds</td>
              <td><select name="ee1_rs_nose" id="ee1_rs_nose" class="Input">
                <?php ListSel($rs{'ee1_rs_nose'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_nose_nt" id="ee1_rs_nose_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_nose_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Sickle Cell/Trait</td>
              <td><select name="ee1_rs_trait" id="ee1_rs_trait" class="Input">
                <?php ListSel($rs{'ee1_rs_trait'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_trait_nt" id="ee1_rs_trait_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_trait_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Psychiatric</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Depressed Mood/Crying</td>
              <td><select name="ee1_rs_dep" id="ee1_rs_dep" class="Input">
                <?php ListSel($rs{'ee1_rs_dep'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_dep_nt" id="ee1_rs_dep_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_dep_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Anxiety</td>
              <td><select name="ee1_rs_anx" id="ee1_rs_anx" class="Input">
                <?php ListSel($rs{'ee1_rs_anx'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_anx_nt" id="ee1_rs_anx_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_anx_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Thoughts of Suicide</td>
              <td><select name="ee1_rs_sui" id="ee1_rs_sui" class="Input">
                <?php ListSel($rs{'ee1_rs_sui'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_sui_nt" id="ee1_rs_sui_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_sui_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Thoughts of Homicide</td>
              <td><select name="ee1_rs_hom" id="ee1_rs_hom" class="Input">
                <?php ListSel($rs{'ee1_rs_hom'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hom_nt" id="ee1_rs_hom_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hom_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Genitourinary</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Incontinence</td>
              <td><select name="ee1_rs_leak" id="ee1_rs_leak" class="Input">
                <?php ListSel($rs{'ee1_rs_leak'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_leak_nt" id="ee1_rs_leak_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_leak_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Urine Retention</td>
              <td><select name="ee1_rs_ret" id="ee1_rs_ret" class="Input">
                <?php ListSel($rs{'ee1_rs_ret'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_ret_nt" id="ee1_rs_ret_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_ret_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Burning w/Urination</td>
              <td><select name="ee1_rs_urine_burn" id="ee1_rs_urine_burn" class="Input">
                <?php ListSel($rs{'ee1_rs_urine_burn'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_urine_burn_nt" id="ee1_rs_urine_burn_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_urine_burn_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
					<?php
					if($pat_sex == 'f') {
						if($client_id != 'capeneuro') { 
					?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Vaginal Discharge</td>
              <td><select name="ee1_rs_vag" id="ee1_rs_vag" class="Input">
                <?php ListSel($rs{'ee1_rs_vag'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_vag_nt" id="ee1_rs_vag_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_vag_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Abnormal Bleeding</td>
              <td><select name="ee1_rs_bleed" id="ee1_rs_bleed" class="Input">
                <?php ListSel($rs{'ee1_rs_bleed'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_bleed_nt" id="ee1_rs_bleed_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_bleed_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Painful Periods</td>
              <td><select name="ee1_rs_pp" id="ee1_rs_pp" class="Input">
                <?php ListSel($rs{'ee1_rs_pp'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_pp_nt" id="ee1_rs_pp_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_pp_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Painful Intercourse</td>
              <td><select name="ee1_rs_sex" id="ee1_rs_sex" class="Input">
                <?php ListSel($rs{'ee1_rs_sex'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_sex_nt" id="ee1_rs_sex_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_sex_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Fibroids</td>
              <td><select name="ee1_rs_fib" id="ee1_rs_fib" class="Input">
                <?php ListSel($rs{'ee1_rs_fib'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_fib_nt" id="ee1_rs_fib_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_fib_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Infertility</td>
              <td><select name="ee1_rs_inf" id="ee1_rs_inf" class="Input">
                <?php ListSel($rs{'ee1_rs_inf'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_inf_nt" id="ee1_rs_inf_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_inf_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
					<?php }
							} ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Urgency</td>
              <td><select name="ee1_rs_urg" id="ee1_rs_urg" class="Input">
                <?php ListSel($rs{'ee1_rs_urg'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_urg_nt" id="ee1_rs_urg_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_urg_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Frequent Urination</td>
              <td><select name="ee1_rs_urine_freq" id="ee1_rs_urine_freq" class="Input">
                <?php ListSel($rs{'ee1_rs_urine_freq'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_urine_freq_nt" id="ee1_rs_urine_freq_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_urine_freq_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php if($client_id != 'capeneuro') { ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Hematuria</td>
              <td><select name="ee1_rs_hematuria" id="ee1_rs_hematuria" class="Input">
                <?php ListSel($rs{'ee1_rs_hematuria'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_hematuria_nt" id="ee1_rs_hematuria_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_hematuria_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Nocturia</td>
              <td><select name="ee1_rs_nocturia" id="ee1_rs_nocturia" class="Input">
                <?php ListSel($rs{'ee1_rs_nocturia'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_nocturia_nt" id="ee1_rs_nocturia_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_nocturia_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
						<?php } ?>
					<?php if($pat_sex == 'f') { 
						if($client_id != 'capeneuro') {
					?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Low Sexual Desire</td>
              <td><select name="ee1_rs_low" id="ee1_rs_low" class="Input">
                <?php ListSel($rs{'ee1_rs_low'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_low_nt" id="ee1_rs_low_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_low_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
					<?php } 
						} ?>
					<?php
					if($pat_sex == 'm') {
						if($client_id != 'capeneuro') {
					?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Erectile Dysfunction</td>
              <td><select name="ee1_rs_ed" id="ee1_rs_ed" class="Input">
                <?php ListSel($rs{'ee1_rs_ed'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_ed_nt" id="ee1_rs_ed_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_ed_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Decreased Libido</td>
              <td><select name="ee1_rs_libido" id="ee1_rs_libido" class="Input">
                <?php ListSel($rs{'ee1_rs_libido'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_libido_nt" id="ee1_rs_libido_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_libido_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
					<?php }
						} ?>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Weak Stream</td>
              <td><select name="ee1_rs_weaks" id="ee1_rs_weaks" class="Input">
                <?php ListSel($rs{'ee1_rs_weaks'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_weaks_nt" id="ee1_rs_weaks_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_weaks_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td class="wmtBody">Dribbling</td>
              <td><select name="ee1_rs_drib" id="ee1_rs_drib" class="Input">
                <?php ListSel($rs{'ee1_rs_drib'},'Yes_No'); ?>
              </select></td>
              <td><input name="ee1_rs_drib_nt" id="ee1_rs_drib_nt" class="FullInput" type="text" value="<?php echo $rs{'ee1_rs_drib_nt'}; ?>" /></td>
              <td>&nbsp;</td>
            </tr>
          </table>
        </td>
      </tr>
			<tr>
				<td class="wmtLabel">Other Notes:</td>
			</tr>
			<tr>
				<td colspan="3" style="<?php echo (($portal_mode)?'padding: 6px;':''); ?>"><textarea name="ee1_rs_nt" id="ee1_rs_nt" class="FullInput" rows="4"><?php echo $rs{'ee1_rs_nt'}; ?></textarea></td>
			</tr>
    </table>
