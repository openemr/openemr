<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Please mark yes or no:</td>
        <td><div style="width: 60%; float: right; padding-right: 12px;"><div style="float: left;" style="padding-right: 20px"><a class="css_button" tabindex="-1" onclick="return toggleNoProblem();" href="javascript:;"><span>Check All 'No Problems Indicated'</span></a></div>
        <div style="float: right;" style="padding-right: 1px;"><a class="css_button" tabindex="-1" onclick="return toggleROStoNull();" href="javascript:;"><span>Clear All</span></a></div></div></td>
      </tr>
      <tr>
        <td style="width: 50%; vertical-align: top;">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
            <tr>
              <td class="wmtLabel" colspan="2">Constitutional</td>
							<td class="wmtBody wmtR"><input name="ros_constitutional_hpi" id="ros_constitutional_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_constitutional_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'con','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_constitutional_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_constitutional_none" id="ros_constitutional_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_constitutional_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'con','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_constitutional_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'constitutional') continue;
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									$rs[$o['option_id'].'_nt'], 'constitutional');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Ear/Nose/Throat/Mouth</td>
							<td class="wmtBody wmtR"><input name="ros_ent_hpi" id="ros_ent_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_ent_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'ent','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_ent_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_ent_none" id="ros_ent_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_ent_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'ent','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_ent_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'ear/nose/throat/mouth') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'ent');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Respiratory</td>
							<td class="wmtBody wmtR"><input name="ros_respiratory_hpi" id="ros_respiratory_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_respiratory_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'rsp','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_respiratory_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_respiratory_none" id="ros_respiratory_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_respiratory_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'rsp','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_respiratory_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'respiratory') continue;
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									 $rs[$o['option_id'].'_nt'],'respiratory');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Genitourinary</td>
							<td class="wmtBody wmtR"><input name="ros_genito_hpi" id="ros_genito_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_genito_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'geni','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_genito_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_genito_none" id="ros_genito_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_genito_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'geni','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_genito_none">No Problems Indicated</label></td>
            </tr>
						<?php if($pat_sex == '') { ?>
						<tr>
							<td>&nbsp;</td>
							<td colspan="3" class="wmtBody" style="color: red;"><i>Patient gender is NOT specified, all options shown</i></td>
						</tr>	
						<?php
						}
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'genitourinary') continue;
							$tst = strtolower(substr($o['mapping'],0,1));
							if($tst == '' || (($tst == $pat_sex) && ($pat_sex != ''))) {
								GenerateROSLine($o['option_id'],$o['title'],
									$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'genito');
							}
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Neurologic</td>
							<td class="wmtBody wmtR"><input name="ros_neurologic_hpi" id="ros_neurologic_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_neurologic_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'neu','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_neurologic_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_neurologic_none" id="ros_neurologic_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_neurologic_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'neu','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_neurologic_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'neurologic') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'neurologic');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Endocrine</td>
							<td class="wmtBody wmtR"><input name="ros_endocrine_hpi" id="ros_endocrine_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_endocrine_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'end','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_endocrine_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_endocrine_none" id="ros_endocrine_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_endocrine_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'end','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_endocrine_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'endocrine') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'endocrine');
						}
						?>
						<?php if($pat_sex == 'm') { ?>
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<?php } ?>
          </table>
        </td>
        <td style="width: 50%; vertical-align: top;">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
            <tr>
              <td class="wmtLabel" colspan="2">Eyes</td>
							<td class="wmtBody wmtR"><input name="ros_eyes_hpi" id="ros_eyes_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_eyes_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'eye','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_eyes_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_eyes_none" id="ros_eyes_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_eyes_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'eye','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_eyes_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'eyes') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'],'eyes');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Cardiovascular</td>
							<td class="wmtBody wmtR"><input name="ros_cardio_hpi" id="ros_cardio_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_cardio_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'car','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_cardio_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_cardio_none" id="ros_cardio_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_cardio_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'car','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_cardio_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'cardiovascular') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'cardio');
						}
						?>
						<tr>
							<td>&nbsp;</td>	
						</tr>
            <tr>
              <td class="wmtLabel" colspan="2">Gastrointestinal</td>
							<td class="wmtBody wmtR"><input name="ros_gastro_hpi" id="ros_gastro_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_gastro_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'gas','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_gastro_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_gastro_none" id="ros_gastro_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_gastro_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'gas','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_gastro_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'gastrointestinal') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'gastro');
						}
						?>
						<tr>
							<td>&nbsp;</td>	
						</tr>
            <tr>
              <td class="wmtLabel" colspan="2">Musculoskeletal/Extremities</td>
							<td class="wmtBody wmtR"><input name="ros_muscle_hpi" id="ros_muscle_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_muscle_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'msc','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_muscle_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_muscle_none" id="ros_muscle_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_muscle_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'msc','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_muscle_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'musculoskeletal') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'muscle');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Skin/Hair/Nails</td>
							<td class="wmtBody wmtR"><input name="ros_skin_hpi" id="ros_skin_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_skin_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'skin','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_skin_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_skin_none" id="ros_skin_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_skin_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'skin','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_skin_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'skin/hair/nails') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'skin');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Psychiatric</td>
							<td class="wmtBody wmtR"><input name="ros_psychiatric_hpi" id="ros_psychiatric_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_psychiatric_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'psy','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_psychiatric_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_psychiatric_none" id="ros_psychiatric_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_psychiatric_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'psy','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_psychiatric_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'psychiatric') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']],$rs[$o['option_id'].'_nt'],'psychiatric');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Lymphatic</td>
							<td class="wmtBody wmtR"><input name="ros_lymphatic_hpi" id="ros_lymphatic_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_lymphatic_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'lym','<?php echo $client_id; ?>','<?php echo $hpi_override; ?>');" /></td>
							<td class="wmtBody"><label for="ros_lymphatic_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_lymphatic_none" id="ros_lymphatic_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_lymphatic_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'lym','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_lymphatic_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'lymphatic') continue;
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'],'lymphatic');
						}
						?>
						<?php if($pat_sex == 'f') { ?>
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td>&nbsp;</td></tr>
						<?php } ?>
          </table>
        </td>
      </tr>
			<tr>
				<td class="wmtLabel">Other Notes:</td>
			</tr>
			<tr>
				<td colspan="3"><textarea name="ros_nt" id="ros_nt" class="FullInput" rows="4"><?php echo htmlspecialchars($wmt_ros{'ros_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>
    </table>
		<div style="display: none;">
			<!-- This div is for all the 'DO NOT USE' keys to retain history -->
			<?php
				foreach($ros_unused as $o) {
					if(strpos(strtolower($o['notes']),'do not use') === false) continue;
					if(isset($rs[$o['option_id']])) {
						GenerateHiddenROS($o['option_id'], $rs[$o['option_id']]);
					}
				}
			?>
		</div>
<?php ?>
