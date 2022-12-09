<?php

	require_once FORM_CLASSES . 'FormCommand.class.php';

	class form_chiro_exam_Command extends FormCommand
	{
		// you have to call the parent constructor for this to work
		// and initialize the request object in the parent constructor
		public function __construct() 
		{
			$args = func_get_args();
			call_user_func_array(array($this, 'parent::__construct'), $args);
		}
	}