<?php 
$close_v_table = FALSE;
include_once(FORM_MODULES.'vitals_basic_module.inc.php');
?>
		<tr><td colspan="14"><div style="width: 100%; margin: 6px; border-top: solid 1px gray;"></div></td></tr>
		<tr>
			<td colspan="2">Urine Dip:</td>
			<td>SG:</td>
			<td><input name="vital_specific_gravity" id="vital_specific_gravity" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->specific_gravity, ENT_QUOTES); ?>" /></td>
			<td class="wmtBody">Blood:</td>
			<td><input name="vital_blood" id="vital_blood" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->blood, ENT_QUOTES); ?>" /></td>
			<td>pH:</td>
			<td><input name="vital_ph" id="vital_ph" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->ph, ENT_QUOTES); ?>" /></td>
			<td>Glucose:</td>
			<td><input name="vital_glucose" id="vital_glucose" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->glucose, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /></td>
			<td>Bilirubin:</td>
			<td><input name="vital_bilirubin" id="vital_bilirubin" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->bilirubin, ENT_QUOTES); ?>" /></td>
			<td>Ketones:</td>
			<td><input name="vital_ketones" id="vital_ketones" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->ketones, ENT_QUOTES); ?>" /></td>
		</tr>
		<tr>
			<td colspan="2"><button name="dip_normal" type="button" onclick="setUrineDipNegative();" value="dip">Set Urine Dip Negative</button></td>
			<td>Protein:</td>
			<td><input name="vital_protein" id="vital_protein" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->protein, ENT_QUOTES); ?>" /></td>
			<td>Urobilinogen:</td>
			<td><input name="vital_urobilinogen" id="vital_urobilinogen" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->urobilinogen, ENT_QUOTES); ?>" /></td>
			<td>Nitrates:</td>
			<td><input name="vital_nitrite" id="vital_nitrite" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->nitrite, ENT_QUOTES); ?>" /></td>
			<td>Leukocytes:</td>
			<td><input name="vital_leukocytes" id="vital_leukocytes" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->leukocytes, ENT_QUOTES); ?>" /></td>
			<td>Hemoglobin:</td>
			<td><input name="vital_hemoglobin" id="vital_hemoglobin" class="wmtFullInput" type="text" style="width: 60px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->hemoglobin, ENT_QUOTES); ?>" /></td>
		</tr>
		<tr><td colspan="14"><div style="width: 100%; margin: 6px; border-top: solid 1px gray;"></div></td></tr>
		<tr>
			<td>Vitals Note:&nbsp;</td>
			<td colspan="13"><input name="vital_note" id="vital_note" class="wmtFullInput" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->note, ENT_QUOTES); ?>" /></td>
		</tr>
	</table>
</fieldset>
