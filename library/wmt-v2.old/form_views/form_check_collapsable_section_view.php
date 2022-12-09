<?php 

	$controller = $options['controller'];

	$sectionLabel = isset($sectionLabel) ? $sectionLabel : 'na';
	$prefix       = $controller::$definition->prefix;

	$parsedId = $controller::$definition->formatId($sectionLabel);

	$cbId   = 'cb_'  . $prefix . $parsedId;
	$atagId = 'a_'   . $prefix . $parsedId;
	$tblId  = 'tbl_' . $parsedId;

	// hasSstValues
	$isSst = !empty($options['subSectionTitle']) && !empty($options['subSectionTitle']);

	$subSecionId = '';

?>

<?php 

	if ($isSst)
	{
		$sst = $options['subSectionTitle'];
		$subSecionId = $controller::$definition->getSstIdFormat($options['subSectionTitleId']);
?>

	<input type="hidden" name="<?php echo $subSecionId; ?>" id="<?php echo $subSecionId; ?>" value="<?php echo $sst; ?>" />

<?php } ?>

<table>
	<tr>
		<td class="wmtBody wmtR">
			<input name="<?php echo $cbId; ?>" id="<?php echo $cbId; ?>" type="checkbox" value="1">
		</td>
		<td class="wmtBody fillWidth tdOverride" style="width: 100% !important;">
			<a href="" id="<?php echo $atagId; ?>" class="atSytled">
				<label for="<?php echo $cbId; ?>" class="wmtLabel leftie">
					<?php echo $sectionLabel; ?>
				</label>

				<?php 

					if ($isSst && $controller::$model->hasSstValues($subSecionId))
					{
						echo '<div class="hasContent"></div>';
					}

				?>
				<p style="clear: both;"></p>
			</a>
		</td>
	</tr>
</table>

<hr style="margin: 0; color: '#fff'"/>

<table width="100%" border="0" cellspacing="15" cellpadding="15" id="<?php echo $tblId; ?>" style="display: none;">
	<tr>
		<td style="width: 100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"> 
				<?php FormWmt::view($viewName, $options); ?>

				<?php 

					if (isset($includeCommentSection) && $includeCommentSection) { 

						$formattedGcId = $controller::$definition->getGcIdFormat($options['subSectionTitleId']);

				?>

					<tr>
						<td colspan="6" cellpadding="10">
							<div class="generalCommentsCont">

								<?php $defaultValue = $controller::$model->getDefaultValue($formattedGcId); ?>

								<label for="<?php echo $formattedGcId; ?>">General Comments:</label>
								<br>
								<textarea id="<?php echo $formattedGcId; ?>" name="<?php echo $formattedGcId; ?>" class="wmtFullInput"><?php echo $defaultValue ; ?></textarea>
							</div>
						</td>
					</tr>

				<?php } ?>
			</table>
		</td>
	</tr>
</table>

<script type="text/javascript">

	$(function () {
		$('#<?php echo $cbId; ?>').click(function () 
		{
			if ($(this + ":checked").length === 1)
			{
				$("#<?php echo $tblId; ?>").toggle();
				return;
			}

			$("#<?php echo $tblId; ?>").toggle();
		});

		$('#<?php echo $atagId; ?>').click(function (e) {
			e.preventDefault();
			$('#<?php echo $cbId; ?>').trigger('click');
		});
	});

</script>
