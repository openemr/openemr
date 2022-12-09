<?php 

	$processedMcId    = $definition->getMcIdFormat($optionId);
	$processedAcId    = $definition->getAcIdFormat($optionId);
	$processedNotesId = 'tmp_' . $definition->getAcIdFormat($optionId);
	$clearBtnId       = $definition->getClearSectionBtnIdFormat($sst);

?>

<input type="hidden" id="<?php echo $processedNotesId; ?>" value="<?php echo $notes; ?>" />

<tr>
	<td style="width: 6px;"></td>
	<td class="wmtBody" style="width: 20.5% !important;"><?php echo $label; ?></td>
	<td style="width: 52px;">					
		<select name="<?php echo $processedMcId; ?>" id="<?php echo $processedMcId; ?>" class="wmtInput">
			<option></option>

			<?php 

				$defaultValue = $model->getDefaultValue($processedMcId, 'false');

				if (isset($choices))
				{
					foreach ($choices as $index => $choice)
					{
						$bla = $definition->formatId($choice);

						$selected = '';

						if ($definition->formatId($choice) == $defaultValue)
						{
							$selected = 'selected';
						}

						echo "<option value=\"$index\" $selected>$choice</option>";
					}
				}
				else
				{
					echo "<option>No choice provided</option>";
				}

			?>
		</select>
	</td>
	<?php $defaultValue = $model->getDefaultValue($processedAcId); ?>
	<td>
		<input name="<?php echo $processedAcId; ?>" id="<?php echo $processedAcId; ?>" class="wmtFullInput" type="text" value="<?php echo $defaultValue; ?>">
	</td>
	<td style="width: 6px;"></td>
</tr>

<script>

$(function () {	

	var choices = [
		"<?php echo $choices[1]; ?>",
		"<?php echo $choices[2]; ?>"
	];

	var mcDd = $('#<?php echo $processedMcId; ?>');
	var ac   = $('#<?php echo $processedAcId; ?>');

	$('#<?php echo $clearBtnId; ?>').click(function () {
		mcDd.val($('#<?php echo $processedMcId; ?> option:first').val());
		ac.val('');
	});

	mcDd.change( function() {
		var notes = $('#<?php echo $processedNotesId; ?>').val();   

		var selectVal = $("#<?php echo $processedMcId; ?> option:selected").val();

		if (selectVal === '1' || selectVal === '2')
		{
			ac.val('');
			ac.val(titleCase(notes));
		}
		else
		{	
			ac.val('');
		}
	});

	function titleCase(string) { return string.charAt(0).toUpperCase() + string.slice(1); }
});
</script>
