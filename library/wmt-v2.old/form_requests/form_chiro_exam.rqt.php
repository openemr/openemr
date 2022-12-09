<?php

	require_once FORM_CLASSES . 'FormRequest.class.php';
	
	class form_chiro_exam_Request extends FormRequest
	{
		public function run()
		{
			$this->autoSetRules(true);
		}
	}