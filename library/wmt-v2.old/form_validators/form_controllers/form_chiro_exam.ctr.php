<?php

	require_once FORM_CLASSES . 'FormController.class.php';

	class form_chiro_exam_Controller extends FormController
	{
		public static function showView($name)
		{
			self::showMainView($name);
		}
	}
