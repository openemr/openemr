<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Please mark yes or no:</td>
        <td><div style="float: right;" style="padding-right: 20px"><a class="css_button" tabindex="-1" onclick="return toggleROStoNull();" href="javascript:;"><span>Clear All</span></a></div></td>
      </tr>
      <tr>
        <td style="width: 50%">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
            <tr>
              <td class="wmtLabel" colspan="2">Constitutional</td>
							<td class="wmtBody wmtR"><input name="ros_constitutional_hpi" id="ros_constitutional_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_constitutional_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'con','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_constitutional_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_constitutional_none" id="ros_constitutional_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_constitutional_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'con','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_constitutional_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'constitutional') { continue; }
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									$rs[$o['option_id'].'_nt'], 'constitutional');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>

            <tr>
              <td class="wmtLabel" colspan="2">Eyes</td>
							<td class="wmtBody wmtR"><input name="ros_eyes_hpi" id="ros_eyes_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_eyes_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'eye','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_eyes_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_eyes_none" id="ros_eyes_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_eyes_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'eye','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_eyes_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'eyes') { continue; }
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'],'eyes');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Ears</td>
							<td class="wmtBody wmtR"><input name="ros_ears_hpi" id="ros_ears_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_ears_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'ears','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_ear_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_ears_none" id="ros_ears_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_ears_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'ears','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_ears_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'ears') { continue; }
							GenerateROSLine($o['option_id'], $o['title'],
								$rs[$o['option_id']], $rs[$o['option_id'].'_nt'], 'ears');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Hearing</td>
							<td class="wmtBody wmtR"><input name="ros_hear_hpi" id="ros_hear_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_hear_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'hear','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_hear_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_hear_none" id="ros_hear_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_hear_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'hear','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_hear_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'hearing') { continue; }
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									 $rs[$o['option_id'].'_nt'],'hear');
						}
						?>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td></tr>
          </table>
        </td>
        <td style="width: 50%">
          <table width="100%" border="0" cellspacing="0" cellpadding="0"> 
            <tr>
              <td class="wmtLabel" colspan="2">Nose</td>
							<td class="wmtBody wmtR"><input name="ros_nose_hpi" id="ros_nose_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_nose_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'nose','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_nose_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_nose_none" id="ros_nose_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_nose_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'nose','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_nose_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'nose') { continue; }
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									 $rs[$o['option_id'].'_nt'],'nose');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Sinuses</td>
							<td class="wmtBody wmtR"><input name="ros_sinus_hpi" id="ros_sinus_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_sinus_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'sinus','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_sinus_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_sinus_none" id="ros_sinus_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_sinus_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'sinus','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_sinus_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'sinuses') { continue; }
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									 $rs[$o['option_id'].'_nt'],'sinus');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Throat</td>
							<td class="wmtBody wmtR"><input name="ros_throat_hpi" id="ros_throat_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_throat_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'throat','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_throat_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_throat_none" id="ros_throat_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_throat_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'throat','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_throat_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'throat') { continue; }
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									 $rs[$o['option_id'].'_nt'],'throat');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Neck</td>
							<td class="wmtBody wmtR"><input name="ros_neck_hpi" id="ros_neck_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_neck_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'neck','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_neck_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_neck_none" id="ros_neck_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_neck_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'neck','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_neck_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'neck') { continue; }
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									 $rs[$o['option_id'].'_nt'],'neck');
						}
						?>
            <tr><!-- For spacing only -->
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class="wmtLabel" colspan="2">Sleep</td>
							<td class="wmtBody wmtR"><input name="ros_sleep_hpi" id="ros_sleep_hpi" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_sleep_hpi'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'sleep','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_sleep_hpi">Refer to HPI</label></td>
            </tr>
            <tr>
							<td class="wmtBody wmtR"><input name="ros_sleep_none" id="ros_sleep_none" type="checkbox" value="1" <?php echo (($wmt_ros{'ros_sleep_none'} == '1')?' checked ':''); ?> onchange="toggleROSTypeToNull(this,'sleep','<?php echo $client_id; ?>');" /></td>
							<td class="wmtBody"><label for="ros_sleep_none">No Problems Indicated</label></td>
            </tr>
						<?php
						foreach($ros_options as $o) {
							if(strtolower($o['codes']) != 'sleep') { continue; }
							GenerateROSLine($o['option_id'],$o['title'],$rs[$o['option_id']],
									 $rs[$o['option_id'].'_nt'],'sleep');
						}
						?>
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

