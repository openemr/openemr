<table class="formContent">
	<?php 

		$list = $controller::$model->getShoulderJointListModel();

		foreach ($list as $listItem)
		{
			$controller::showMultiChoiceCommentInput(
				'right_' . $listItem['option_id'],
				$listItem['title'],
				$listItem['notes'],
				!empty($options['subSectionTitle']) ? $options['subSectionTitle'] : ''
			); 
		}
	?>
</table>
