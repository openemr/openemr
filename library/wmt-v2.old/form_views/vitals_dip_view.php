<?php 
$close_v_table = FALSE;
include_once(FORM_VIEWS.'vitals_dip_view.php');
?>
		<tr><td colspan="14"><div style="width: 100%; margin: 6px; border-top: solid 1px gray;"></div></td></tr>
		<tr>
			<td colspan="2">Urine Dip:</td>
			<td>SG:</td>
			<td><?php echo htmlspecialchars($vitals->specific_gravity, ENT_QUOTES); ?>&nbsp;</td>
			<td class="wmtBody">Blood:</td>
			<td><?php echo htmlspecialchars($vitals->blood, ENT_QUOTES); ?>&nbsp;</td>
			<td>pH:</td>
			<td><?php echo htmlspecialchars($vitals->ph, ENT_QUOTES); ?>&nbsp;</td>
			<td>Glucose:</td>
			<td><?php echo htmlspecialchars($vitals->glucose, ENT_QUOTES); ?>&nbsp;</td>
			<td>Bilirubin:</td>
			<td><?php echo htmlspecialchars($vitals->bilirubin, ENT_QUOTES); ?>&nbsp;</td>
			<td>Ketones:</td>
			<td><?php echo htmlspecialchars($vitals->ketones, ENT_QUOTES); ?>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
			<td>Protein:</td>
			<td><?php echo htmlspecialchars($vitals->protein, ENT_QUOTES); ?>&nbsp;</td>
			<td>Urobilinogen:</td>
			<td><?php echo htmlspecialchars($vitals->urobilinogen, ENT_QUOTES); ?>&nbsp;</td>
			<td>Nitrates:</td>
			<td><?php echo htmlspecialchars($vitals->nitrite, ENT_QUOTES); ?>&nbsp;</td>
			<td>Leukocytes:</td>
			<td><?php echo htmlspecialchars($vitals->leukocytes, ENT_QUOTES); ?>&nbsp;</td>
			<td>Hemoglobin:</td>
			<td><?php echo htmlspecialchars($vitals->hemoglobin, ENT_QUOTES); ?>&nbsp;</td>
		</tr>
<?php if($vitals->note) { ?>
		<tr><td colspan="14"><div style="width: 100%; margin: 6px; border-top: solid 1px gray;"></div></td></tr>
		<tr>
			<td>Vitals Note:&nbsp;</td>
			<td colspan="13"><?php echo htmlspecialchars($vitals->note, ENT_QUOTES); ?></td>
		</tr>
<?php } ?>
	</table>
</fieldset>
