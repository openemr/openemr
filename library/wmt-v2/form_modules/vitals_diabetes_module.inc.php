<?php 
$close_v_table = FALSE;
include_once(FORM_MODULES.'vitals_basic_module.inc.php');
?>
		<tr>
			<td colspan="4"><b>Diabetes Related:</b></td>
		</tr>
		<tr>
			<td>TC:</td>
			<td><input name="vital_TC" id="vital_TC" class="wmtInput" type="text" style="width: 50px;" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->TC, ENT_QUOTES); ?>" /></td>
			<td>LDL:</td>
			<td><input name="vital_LDL" id="vital_LDL" class="wmtInput" type="text" style="width: 50px;" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->LDL, ENT_QUOTES); ?>" /></td>
			<td>HDL:</td>
			<td><input name="vital_HDL" id="vital_HDL" class="wmtInput" type="text" style="width: 50px;" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->HDL, ENT_QUOTES); ?>" /></td>
			<td>Trig:</td>
			<td><input name="vital_trig" id="vital_trig" class="wmtInput" type="text" style="width: 50px;" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->trig, ENT_QUOTES); ?>" /></td>
			<td>Micro:</td>
			<td><input name="vital_microalbumin" id="vital_microalbumin" class="wmtInput" type="text" style="width: 50px;" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->microalbumin, ENT_QUOTES); ?>" /></td>
			<td>BUN:</td>
			<td><input name="vital_BUN" id="vital_BUN" class="wmtInput" type="text" style="width: 50px;" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->BUN, ENT_QUOTES); ?>" /></td>
			<td>Creatine:</td>
			<td><input name="vital_cr" id="vital_cr" class="wmtInput" type="text" style="width: 50px;" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->cr, ENT_QUOTES); ?>" /></td>
		</tr>
		<tr>
			<td>Vitals Note:&nbsp;</td>
			<td colspan="14"><input name="vital_note" id="vital_note" class="wmtFullInput" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->note, ENT_QUOTES); ?>" /></td>
		</tr>
	</table>
</fieldset>
