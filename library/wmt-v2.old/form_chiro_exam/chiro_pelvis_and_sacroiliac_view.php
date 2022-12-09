<table class="formContent">
	<?php 

		$list = $controller::$model->getPelvisAndSacroiliacListModel();

		foreach ($list as $listItem)
		{
			$controller::showMultiChoiceCommentInput(
				$listItem['option_id'],
				$listItem['title'],
				$listItem['notes'],
				!empty($options['subSectionTitle']) ? $options['subSectionTitle'] : ''
			);
		}
	?>
</table>
