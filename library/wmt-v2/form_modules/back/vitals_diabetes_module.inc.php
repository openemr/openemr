    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel" colspan="2">Vitals Taken:</td> 
        <td colspan="3"><input name="vital_date" id="vital_date" type="text" class="wmtLabelRed" readonly="readonly" value="<?php echo htmlspecialchars($vitals->date, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td colspan="3"><a class="css_button" tabindex="-1" onClick="get_vitals('diabetes');" href="javascript:;"><span>Find Other Vitals</span></a></td>
				<td colspan="3">&nbsp;</td>
				<td colspan="2"><div style="float: right; padding-right; 10px"><a class="css_button" tabindex="-1" onClick="ClearExam('<?php echo $client_id; ?>');" href="javascript:;"><span>Clear Exam</span></a></div></td>
      </tr>
			<tr>
				<td class="wmtBody">Height:</td>
				<td><input name="vital_height" id="vital_height" class="wmtInput" style="width: 60px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->height, ENT_QUOTES, '', FALSE); ?>" onchange="UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); TimeStamp('vital_date');" /></td>
				<td class="wmtBody">Weight:</td>
				<td><input name="vital_weight" id="vital_weight" class="wmtInput" style="width: 60px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->weight, ENT_QUOTES, '', FALSE); ?>" onchange="UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); TimeStamp('vital_date');" /></td>
				<td class="wmtBody">BMI:</td>
				<td><input name="vital_BMI" id="vital_BMI" class="wmtInput" style="width: 60px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->BMI, ENT_QUOTES, '', FALSE); ?>" onchange="OneDecimal('vital_BMI'); TimeStamp('vital_date');" /></td>
				<td colspan="2"><input name="vital_BMI_status" id="vital_BMI_status" class="wmtInput" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->BMI_status, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="wmtBody">Pulse:</td>
				<td><input name="vital_pulse" id="vital_pulse" class="wmtInput" style="width: 50px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->pulse, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td class="wmtBody">Resp:</td>
				<td><input name="vital_respiration" id="vital_respiration" class="wmtInput" style="width: 60px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->respiration, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td class="wmtBody">Temp:</td>
				<td><input name="vital_temperature" id="vital_temperature" class="wmtInput" style="width: 60px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->temperature, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td style="width: 1%">&nbsp;</td>
			</tr>
			<tr>
				<td class="wmtBody">Seated BP:</td>
				<td><input name="vital_bps" id="vital_bps" class="wmtInput" style="width: 30px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($dt{'vital_bps'}, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" />/<input name="vital_bpd" id="vital_bpd" class="wmtInput" style="width: 30px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($dt{'vital_bpd'}, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td class='wmtBody'>Prone BP:</td>
				<td><input name="vital_prone_bps" id="vital_prone_bps" class="wmtInput" style="width: 30px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" />/<input name="vital_prone_bpd" id="vital_prone_bpd" class="wmtInput" style="width: 30px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td class="wmtBody">Standing BP:</td>
				<td><input name="vital_standing_bps" id="vital_standing_bps" class="wmtInput" style="width: 30px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->standing_bps, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" />/<input name="vital_standing_bpd" id="vital_standing_bpd" class="wmtInput" style="width: 30px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vital->standing_bpd, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td class="wmtBody">Arm:</td>
				<td>
				<?php if($wrap_mode == 'never') { ?>
					<select name="vital_arm" id="vital_arm" class="wmtInput2" style="width: 60px;" onchange="TimeStamp('vital_date');" >
					<?php ListSel($vitals->arm,'Vital_Arm'); ?>
					</select></td>
				<?php } else { ?>
					<input name="vital_arm" id="vital_arm" class="wmtInput2" style="width: 60px;" readonly="readonly" value="<?php echo ListLook($vitals->arm,'Vital_Arm'); ?>" /></td>
				<?php } ?>
				<td class="wmtBody">O<sub>2</sub> Sat.:</td>
				<td><input name="vital_oxygen_saturation" id="vital_oxygen_saturation" class="wmtInput" style="width: 60px" type="text" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->oxygen_saturation, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td class="wmtBody">Finger Stick:</td>
				<td><input name="vital_diabetes_accucheck" id="vital_diabetes_accucheck" class="wmtFullInput" type="text" style="width: 60px" <?php echo (($wrap_mode != 'new')?' readonly ':''); ?> value="<?php echo htmlspecialchars($vitals->diabetes_accucheck, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				</td>
				<td class="wmtBody">HgbA1c:</td>
				<td><input name="vital_HgbA1c" id="vital_HgbA1c" class="wmtFullInput" type="text" style="width: 60px" readonly value="<?php echo htmlspecialchars($vitals->HgbA1c, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="wmtBody">TC:</td>
				<td><input name="vital_TC" id="vital_TC" class="wmtFullInput" type="text" style="width: 60px" readonly value="<?php echo htmlspecialchars($vital->TC, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="wmtBody">LDL:</td>
				<td><input name="vital_LDL" id="vital_LDL" class="wmtFullInput" type="text" style="width: 60px" readonly value="<?php echo htmlspecialchars($vitals->LDL, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="wmtBody">HDL:</td>
				<td><input name="vital_HDL" id="vital_HDL" class="wmtFullInput" type="text" style="width: 60px" readonly value="<?php echo htmlspecialchars($vitals->HDL, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="wmtBody">Trig:</td>
				<td><input name="vital_trig" id="vital_trig" class="wmtFullInput" type="text" style="width: 60px" readonly value="<?php echo htmlspecialchars($vitals->trig, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="wmtBody">Micro:</td>
				<td><input name="vital_microalbumin" id="vital_microalbumin" class="wmtFullInput" type="text" style="width: 60px" readonly value="<?php echo htmlspecialchars($vitals->microalbumin, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="wmtBody">BUN:</td>
				<td><input name="vital_BUN" id="vital_BUN" class="wmtFullInput" type="text" style="width: 60px" readonly value="<?php echo htmlspecialchars($vitals->BUN, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="wmtBody">Creatine:</td>
				<td><input name="vital_cr" id="vital_cr" class="wmtFullInput" type="text" style="width: 60px" readonly value="<?php echo htmlspecialchars($vitals->cr, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>
			<tr>
				<td class="wmtBody">Vitals Note:&nbsp;</td>
				<td colspan="11"><input name="vital_note" id="vital_note" class="wmtFullInput" type="text" readonly="readonly" value="<?php echo htmlspecialchars($vitals->note, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>
    </table>
