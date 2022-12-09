<?php

	class FormView
	{
		public static $definition;
		public static $command;
		public static $model;

		public function __get($name)
		{
			if (!isset($this->$name))
			{
				$trace = debug_backtrace();

				trigger_error(
					'Undefined property via __get(): ' . $name .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_NOTICE
				);

				return;
			}

			return $this->$name;
		}

		public function __set($name, $value)
		{
			if (!isset($this->$name))
			{
				$trace = debug_backtrace();
				
				trigger_error(
					'Undefined property via __set(): ' . $name .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_NOTICE
				);
			}
			
			return $this->$name = $value;
		}

		public static function showCheckBoxCollapsableSection(
			$options, 
			$includeCommentSection = null 
		)
		{
			if ($includeCommentSection) $options['includeCommentSection'] = true;

			FormWmt::view(
				'form_check_collapsable_section',
				$options
			);
		}

		public static function showCheckBoxCollapsableSectionWithRoM(
				$options,
				$includeCommentSection = null
		)
		{
			if ($includeCommentSection) $options['includeCommentSection'] = true;

			FormWmt::view(
				'form_check_collapsable_section_with_rom',
				$options
			);
		}


		public static function showMultiChoiceCommentInput(
			$optionId, 
			$label,
			$notes = '',
			$sst = ''
		)
		{
			FormWmt::view(
				'form_multi_choice_comment',
				array(
					'sst'        => $sst,
					'definition' => self::$definition,
					'command'    => self::$command,
					'model'      => self::$model,
					'label'      => $label,
					'notes'      => $notes,
					'optionId'   => $optionId,
					'choices'    => self::$definition->multipleChoiceDefs
				)
			);
		}
	}
