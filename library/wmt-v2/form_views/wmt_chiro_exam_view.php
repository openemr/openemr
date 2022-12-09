<?php

	$chiroController = FormWmt::initForm('form_chiro_exam', $id, $frmn);

	$chiroController::$model->populateMcChoiceLabels();

	$summary = $chiroController::$model->getSummary();

?>

<style type="text/css">

	.sstLeftie
	{
		text-align: left;
		margin-top: 14px;
		margin-bottom: 4px;
	}

	.sst
	{
		font-family: Arial, Helvetica, sans-serif;
		font-size: 0.8em;
		font-weight: bold;
		text-align: left !important;
		text-transform: none;
		margin: 2px 5px;
		text-decoration: underline;
	}

	.leftRightSections
	{
		width: 48.7%;
		margin: 0.6%;
	}

	.leftRightSections label
	{
		font-family: Arial, Helvetica, sans-serif;
		font-size: 0.8em;
		font-weight: bold;
		text-align: left !important;
		text-transform: none;
		text-decoration: underline;
	}

	.normalSectionTable
	{
		margin: 0.6%;
	}

	.leftRightSections table
	{
		width: 100%;
	}

	.left
	{
		left: 0;
		float: left;
	}

	.right
	{
		right: 0;
		float: right;
	}

	.summaryDivider
	{
		width: 98%;
		margin: 0px auto;
	}

</style>

<?php if(count($summary) > 0) { ?>

<div class="wmtPrnMainContainer">

	<div class="wmtPrnCollapseBar">

		<span class="wmtPrnChapter">Orthopedic Exam</span>

	</div>

	<?php foreach ($summary as $ssKey => $ss) { ?>

		<div class="wmtPrnCollapseBox sstLeftie" style="border-collapse: collapse;">

			<span class="sst"><?php echo $ss['sst']; ?></span>

			<div></div>

			<?php if (isset($ss['rom'])) { ?>
			<span class="wmtPrnLabel wmtPrnT">&nbsp;&nbsp;ROM:</span>
			<span class="wmtPrnBody wmtPrnT">&nbsp;<?php echo htmlspecialchars($ss['rom'], ENT_QUOTES, '', FALSE); ?></span>
			<?php } ?>

			<?php if (isset($ss['left']) || isset($ss['right'])) { ?>

				<div class="leftRightSections left">

					<label><u>Left</u></label>

					<table border="0" cellspacing="0" cellpadding="4">
						
						<!--tr>
							<td class="wmtPrnLabel">Multiple Choice</td>
							<td class="wmtPrnLabel">Choice</td>
							<td class="wmtPrnLabel">Comments</td>
						</tr -->

						<?php 

							if (isset($ss['left']))
							{
								foreach ($ss['left'] as $leftColKey => $colArray) 
								{ 
									echo '<tr>';

									foreach ($colArray as $colKey => $colVal)
									{
										echo '<td class="wmtPrnLabel">';

										echo $colVal;

										echo '</td>';
									}

									echo '</tr>';
								} 
							}
							else
							{
								echo '<tr><td colspan="10">Nothing to display<td></tr>';
							}

						?>

					</table>

				</div>

				<div class="leftRightSections right" >

					<label><u>Right</u></label>

					<table border="0" cellspacing="0" cellpadding="4">
						
						<!--tr>
							<td class="wmtPrnLabel">Multiple Choice</td>
							<td class="wmtPrnLabel">Choice</td>
							<td class="wmtPrnLabel">Comments</td>
						</tr -->

						<?php 

							if (isset($ss['right']))
							{	
								foreach ($ss['right'] as $leftColKey => $colArray) 
								{ 
									echo '<tr>';

									foreach ($colArray as $colKey => $colVal)
									{
										echo '<td class="wmtPrnLabel">';

										echo $colVal;

										echo '</td>';
									}

									echo '</tr>';
								} 
							}
							else
							{
								echo '<tr><td colspan="50" class="wmtPrnLabel">Nothing to display<td/></tr>';
							}

						?>

					</table>

				</div>

			<?php } else { ?>

				<table class="normalSectionTable" border="0" cellspacing="0" cellpadding="4">
						
						<!-- tr>
							<td class="wmtPrnLabel">Multiple Choice</td>
							<td class="wmtPrnLabel">Choice</td>
							<td class="wmtPrnLabel">Comments</td>
						</tr -->

						<?php 

							foreach ($ss as $colKey => $colArray) 
							{
								if ($colKey == 'sst') continue;

								echo '<tr>';

								echo '<td class="wmtPrnLabel">';

								if (!empty($colArray['label']))
								{
									echo $colArray['label'];
								}

								echo '</td>';
								
								echo '<td class="wmtPrnLabel">';

								if (!empty($colArray['mc']))
								{
									echo $colArray['mc'];
								}

								echo '</td>';

								echo '<td class="wmtPrnLabel">';

								if (!empty($colArray['ac']))
								{
									echo $colArray['ac'];
								}

								echo '</td>';

								echo '</tr>';
							} 

						?>

					</table>

			<?php } ?>

		</div>

		<?php if (isset($ss['gc'])) { ?>
			
			<div class="summaryDivider"></div>

			<table border="0" cellspacing="0" cellpadding="4">
				<tr>
					<td class="wmtPrnLabel" style="border: none;">
						<label><u>General Comments:</u></label><br/>
						<?php echo $ss['gc']; ?>
					</td>
				</tr>
			</table>

		<?php } ?>

	<?php } ?>

<?php } ?>
</div>
