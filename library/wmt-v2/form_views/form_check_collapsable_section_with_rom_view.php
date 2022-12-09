<?php 

	$controller = $options['controller'];

	$sectionLabel = isset($sectionLabel) ? $sectionLabel : 'na';
	$prefix       = $controller::$definition->prefix;

	$parsedId = $controller::$definition->formatId($sectionLabel);

	$cbId   = 'cb_'  . $prefix . $parsedId;
	$tblId  = 'tbl_' . $parsedId;
	$tdId   = 'td_'  . $prefix . $parsedId;

	$subSecionId   = '';
	$btnClearId    = '';
	$formattedGcId = '';

	$sst         = $options['subSectionTitle'];
	$btnClearId  = $controller::$definition->getClearSectionBtnIdFormat($sst);
	$subSecionId = $controller::$definition->getSstIdFormat($options['subSectionTitleId']);
	$romId       = $controller::$definition->getRomIdFormat($options['subSectionTitleId']);

	$hasContId = 'tmp_' . $parsedId . '_has_cont';
	$disFalgId = $controller::$definition->getDisplayInputFlagId($options['subSectionTitleId']);

	// error_log('Your session view vars; ' . var_export($_SESSION[$prefix . 'display_flags'], true));
?>

<input type="hidden" name="<?php echo $subSecionId; ?>" id="<?php echo $subSecionId; ?>" value="<?php echo $sst; ?>" />

<input type="hidden" id="<?php echo $disFalgId; ?>" name="<?php echo $disFalgId; ?>" value="false">

<?php $defaultValue = $controller::$model->getDefaultValue($romId); ?>

<table style="width: 100%;">
	<tr>
		<td class="wmtBody wmtR fillWidth tdOverride" style="width: 100% !important;" colspan="10" id="<?php echo $tdId; ?>">
			<div class="labelAndClear">
				<label class="wmtLabel leftie;" style="float: left; margin: 4px 0; font-size: 12.6px;">
					<?php echo $sectionLabel; ?>
				</label>

				<p style="clear:both;">
					<div style="float:left; margin-bottom: 5px; margin-left: 5px;" class="css_button" id="<?php echo $btnClearId; ?>">
						<span>Clear</span>
					</div>
				</p>

				<table style="clear:left;">
					<tr>
						<td class="wmtBody wmtR">
							<input name="<?php echo $cbId; ?>" id="<?php echo $cbId; ?>" type="checkbox" value="1">
						</td>
						<td class="wmtBody fillWidth tdOverride" style="width: 100% !important;">

							<label class="wmtLabel leftie">Show Tests</label>
							<?php 

								if ($controller::$model->hasSstValues($subSecionId))
								{
									echo '<div class="hasContent" id="' . $hasContId . '"></div>';
								}

							?>
							<p style="clear: both;"></p>
						</td>
					</tr>
				</table>
			</div>
			<div class="romSection">
				<label class="romSectionLabel" for="<?php echo $romId; ?>">Range of Motion</label>
				<textarea id="<?php echo $romId; ?>" name="<?php echo $romId; ?>" class="wmtFullInput"><?php echo $defaultValue; ?></textarea>
			</div>
			<p style="clear: both;"></p>
		</td>
	</tr>
</table>

<table width="100%" border="0" cellpadding="3" id="<?php echo $tblId; ?>" style="display: none;">
	<tr>
		<td style="width: 100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"> 
				<?php FormWmt::view($viewName, $options); ?>

				<?php 

					if (isset($includeCommentSection) && $includeCommentSection) { 

						$formattedGcId = $controller::$definition->getGcIdFormat($options['subSectionTitleId']);

				?>

					<tr>
						<td colspan="3" cellpadding="27">

							<?php $defaultValue = $controller::$model->getDefaultValue($formattedGcId); ?>

							<label class="generalCommentsContLabel" for="<?php echo $formattedGcId; ?>">Note:</label>
							<br>
							<textarea class="wmtFullInput" id="<?php echo $formattedGcId; ?>" name="<?php echo $formattedGcId; ?>"><?php echo $defaultValue ; ?></textarea>
						</td>
					</tr>

				<?php } ?>
			</table>
		</td>
	</tr>
</table>

<script type="text/javascript">

	$(function () {	
		var taClicked = false;
		var clearPressed = false;

		var inView = false;
		var cb     = $('#<?php echo $cbId; ?>');
		var rom    = $('#<?php echo $romId; ?>');
		var gc     = $('#<?php echo $formattedGcId; ?>');
	
		var romClicked = false;

		var displayFlag = $('#<?php echo $disFalgId; ?>');

		<?php if ($controller::$model->sectionIsInView($subSecionId)) { ?>
			<?php echo $cbId; ?>_toggle();
		<?php } ?>

		cb.click(function () 
		{
			if (taClicked === true) 
			{
				taClicked = false;
				return;
			}
			else
			{
				taClicked = true;
			}

			<?php echo $cbId; ?>_toggle();
		});
		
		$('#<?php echo $tdId; ?>').click(function () {
			<?php echo $cbId; ?>_toggle();
		});

		rom.click(function () {
			romClicked = true;
			<?php echo $cbId; ?>_toggle();
		});

		$('#<?php echo $btnClearId; ?>').click(function () {
			taClicked  = true;
			rom.val('');
			gc.val('');
			$('#<?php echo $hasContId; ?>').addClass('hideHasContentIndicatior');
		});

		$('#<?php echo $controller::$definition->getClearFormBtnIdFormat(); ?>').click(function () {
			clearPressed = true;
			$('#<?php echo $btnClearId; ?>').trigger('click');
		});

		var timer;

		function <?php echo $cbId; ?>_toggle() {
			if (taClicked === true) 
			{
				taClicked = false;
				return;
			}

			if (romClicked)
			{
				timer = setInterval(resetTimer, 200);
				return;
			}

			if (clearPressed === true) {
				clearPressed = false;
				return;
			}

			$("#<?php echo $tblId; ?>").toggle();
			
			if (inView) {
				inView = false;
				displayFlag.val(false);
				cb.attr('checked', false);
			}
			else {
				inView = true;
				displayFlag.val(true);
				cb.attr('checked', true);
			}
		}

		function resetTimer() {
			romClicked = false;
			clearInterval(timer);
		}
	});

</script>
