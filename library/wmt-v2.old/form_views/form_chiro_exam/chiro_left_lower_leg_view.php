<table class="formContent">
	<?php 

		$list = $controller::$model->getLowerLegListModel();

		foreach ($list as $listItem)
		{
			$controller::showMultiChoiceCommentInput(
				'left_' . $listItem['option_id'],
				$listItem['title'],
				$listItem['notes'],
				!empty($options['subSectionTitle']) ? $options['subSectionTitle'] : ''
			);
		}
	?>
</table>
