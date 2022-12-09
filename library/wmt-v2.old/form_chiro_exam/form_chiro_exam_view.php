<style type="text/css">
	.formContent
	{
		width: 100% !important;
	}

	.formContent tr td {
		padding: 1.5px;
		margin: 1.5px;
	}

	.formContent tr td:first-child
	{
		width: 20%;
	}

	.halfSection
	{
		width: 48.01%;
		min-height: 200px;
		padding-top: 1.7%;
		padding-left: 1.7%;
		padding-right: 1.7%;
		padding-bottom: 0.2%;
		margin-top: 1%;
		margin-right: 1%;
		margin-left: 1%;
		float: left;
	}

	.leftSection
	{
		left: 0;
	}

	.rightSection
	{
		right: 0;
	}

	.tdoverride
	{
		padding: 0 !important;
	}

	.atSytled
	{
		display: block;
		width:100%;
		margin: 2px 0px;
	}

	.atSytled label
	{
		color: black;
	}

	.atSytled:hover label
	{
		color: #636363;
	}

	/* most of these blow are for the subsection styles */
	.leftie
	{
		float: left;
	}

	.wmtR:hover
	{
		cursor: pointer;
	}
	.labelAndClear
	{
		height: 100%;
		width: 20.5% !important;
		float: left;
	}
	.romSectionLabel
	{
		float: left;
		font-weight: bold;
		margin-bottom: 3px;
	}
	.romSection
	{
		width: 77.3%;
		height: 100%;
		margin-left: 5px;
		float: left;
	}
	.romSection textarea 
	{
		width: 100%; 
		height: 70px; 
		float: left;
	}
	.hideHasContentIndicatior
	{
		display: none;
	}

	.hasContent
	{
		width: 11px;
		height:11px;
		border-radius: 50%;
		background-color: green;
		float: left;
		margin: 3px 8px;
	}

	.generalCommentsCont
	{
		margin-left: 15px;
		margin-right: 15px;
    	margin-bottom: 10px;
		min-width: 97.1%;
    	max-width: 98%;
    	background-color: #EAEAEA;
    	height: 70px;
	}

	.generalCommentsContLabel
	{
		margin-left: 15px;
		text-transform: none;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 0.9em;
	}

	.wmtR:hover
	{
		cursor: pointer;
	}

</style>

<tr>
	<td colspan="20">
		<div style="float: right; margin: 3px 5px 3px 0px; " class="css_button wmtR" id="<?php echo $controller::$definition->getClearFormBtnIdFormat(); ?>">
			<span>Clear Orthopedic Exam</span>
		</div>
		<p style="clear: both;"></p>
	</td>
</tr>

<?php

	foreach ($controller::$definition->subSectionTitles as $fileName => $viewTitle)
	{
		$controller::showCheckBoxCollapsableSectionWithRom(
			array(
				'sectionLabel' => $viewTitle,
				'viewName'     => 'form_chiro_exam/' . $fileName,
				'options'      => array(
					'controller'        => $controller,
					'subSectionTitleId' => $fileName,
					'subSectionTitle'   => $viewTitle
				)
			),
			true
		);
	}

?>
