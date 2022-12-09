<?php

	require_once WMT2 . 'list_tools.inc';

	class FormModel
	{
		protected $command;
		protected $definition;

		private $currentAnswers      = array();
		private $currentListLabels   = array();

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
			}
			
			return $this->$name;
		}

		public function init($definition, $command)
		{	
			$this->definition =& $definition;
			$this->command    =& $command;

			$this->setCurrentAnswers();
		}

		public function setCurrentAnswers()
		{
			$this->currentAnswers = $this->command->read();
		}

		public function formHasValues()
		{
			if (!empty($_SESSION[$this->definition->prefix . 'display_flags']) && is_array($_SESSION[$this->definition->prefix . 'display_flags']))
			{
				foreach ($_SESSION[$this->definition->prefix . 'display_flags'] as $flag)
				{
					if ($flag == true || $flag == 'true') return true;
				}
			}

			return true;
		}

		public function sectionIsInView($sstId)
		{
			if (!preg_match('/^([A-Za-z0-9_\-]+)_sst$/', $sstId, $matches))
			{
				return false;
			}

			$key = $matches[1] . '_df';

			return !empty($_SESSION[$this->definition->prefix . 'display_flags'][$key]) &&
			($_SESSION[$this->definition->prefix . 'display_flags'][$key] == true ||
			 $_SESSION[$this->definition->prefix . 'display_flags'][$key] == 1);
		}

		public function examHasValue()
		{
			$return = false;

			foreach ($this->definition->subSectionTitles as $ssKey => $sst)
			{
				if ($this->hasSstValues($ssKey)) return true;
			}

			return false;
		}

		public function hasSstValues($subSecionTitleId)
		{
			if (!isset($this->currentAnswers['subSectionTitles']))
			{
				return false;
			}	

			if (!preg_match('/^([A-Za-z0-9\-_]+)_sst$/', $subSecionTitleId))
			{
				$subSecionTitleId .= '_sst';
			}

			return array_key_exists(
				$subSecionTitleId, 
				$this->currentAnswers['subSectionTitles']
			);

			if (!array_key_exists(
				$subSecionTitleId, 
				$this->currentAnswers['subSectionTitles']
			))
			{
				return false;
			}

			$searchVal = $this->currentAnswers['subSectionTitles'][$subSecionTitleId];

			if (!preg_match('/_mc/', $searchVal) || !preg_match('/_gc/', $searchVal ))
			{
				return false;
			}

			return true;
		}

		public function getDefaultValue($searchKey, $default = '')
		{
			$haystack = $this->currentAnswers;

			if (is_array($haystack['answers_rom']) && count($haystack['answers_rom']) > 0)
			{
				foreach ($haystack['answers_rom'] as $key => $rom)
				{
					if ($key == $searchKey) return $rom;
				}
			}

			foreach ($haystack as $groupKey => $answerGroup)
			{
				if ($groupKey == 'answers_mc')
				{
					if (!is_array($answerGroup)) return $default;

					foreach ($answerGroup as $column => $value)
					{
						if (preg_match("/$searchKey/", $value)) return $column;
					}
				}
				else
				{
					if (array_key_exists(trim($searchKey), $answerGroup))
					{
						return $answerGroup[$searchKey];
					}
				}
				
			}

			return $default;
		}

		public function getSummary()
		{
			$answers = $this->currentAnswers;

			if (isset($answers['subSectionTitles']))
			{
				return $this->getSubSectionSummary($answers);
			}
			else
			{
				// here is where i will write a different summary method to display
				// other different types of formatted arrays depending on the needs
				// of the forms that we'll insert in the future
			}
		}

		private function getSubSectionSummary(&$summaryArray, $doLeftRightSplit = false)
		{
			$finalSumary = array();

			$anmc  = isset($summaryArray['answers_mc']) && is_array($summaryArray['answers_mc']) ? $summaryArray['answers_mc'] : '';

			$anac  = isset($summaryArray['answers_ac']) && is_array($summaryArray['answers_ac']) ? $summaryArray['answers_ac'] : '';

			$angc  = isset($summaryArray['answers_gc']) && is_array($summaryArray['answers_gc']) ? $summaryArray['answers_gc'] : '';

			$anrom = isset($summaryArray['answers_rom']) && is_array($summaryArray['answers_rom']) ? $summaryArray['answers_rom'] : '';

			foreach ($summaryArray['subSectionTitles'] as $sst => $sstRes)
			{
				$finalSumary[$sst]['sst'] = $this->definition->subSectionTitles[substr($sst, 0, -4)];

				if (is_array($anmc))
				{
					$sstMcAnswers = explode('|', substr($sstRes, 0, -1));

					// first loop though each of the section titles array
					foreach ($sstMcAnswers as $answerKey)
					{
						$finalKey = substr($answerKey, 0, -3);
						$finalKey = substr($finalKey, strlen($this->definition->prefix));

						// loop through each of the predefined choices
						foreach ($this->definition->multipleChoiceDefs as $mcChoice)
						{
							// get the propper id format of the given choice 
							$formattedId = $this->definition->formatId($mcChoice);

							// if the given key is found in the current mc answers array
							// string then we return the value that is in the predefined
							// array that is corrispondant to the converted formatted id 
							if (preg_match('/' . $answerKey . '/', $anmc[$formattedId]))
							{
								if (preg_match('/^right_/', $finalKey) && $doLeftRightSplit)
								{
									$finalKey = substr($finalKey, 6);
									
									if (isset($this->currentListLabels[$finalKey]))
									{
										$finalSumary[$sst]['right'][$finalKey]['label'] = $this->currentListLabels[$finalKey];
									}

									$finalSumary[$sst]['right'][$finalKey]['mc'] = $mcChoice;
									continue;
								}
								else if (preg_match('/^left_/', $finalKey) && $doLeftRightSplit)
								{
									$finalKey = substr($finalKey, 5);

									if (isset($this->currentListLabels[$finalKey]))
									{
										$finalSumary[$sst]['left'][$finalKey]['label'] = $this->currentListLabels[$finalKey];
									}

									$finalSumary[$sst]['left'][$finalKey]['mc'] = $mcChoice;
									continue;
								}

								$labelKey = '';

								if (preg_match('/^left_([A-Za-z0-9_\-]+)/', $finalKey, $matches))
								{
									$labelKey = $matches[1];
								}
								else if (preg_match('/^right_([A-Za-z0-9_\-]+)/', $finalKey, $matches))
								{
									$labelKey = $matches[1];
								}
								else
								{
									$labelKey = $finalKey;
								}

								if (isset($this->currentListLabels[$labelKey]))
								{
									$finalSumary[$sst][$finalKey]['label'] = $this->currentListLabels[$labelKey];
								}

								$finalSumary[$sst][$finalKey]['mc'] = $mcChoice;
							}
						}
					}
				}

				if (is_array($anac))
				{
					foreach ($anac as $answerKey => $answer)
					{
						$finalKey = substr($answerKey, 0, -3);
						$finalKey = substr($finalKey, strlen($this->definition->prefix));

						if (preg_match('/' . $answerKey . '/', $sstRes))
						{
							if (preg_match('/^right_/', $finalKey) && $doLeftRightSplit)
							{
								$finalKey = substr($finalKey, 6);

								if (isset($this->currentListLabels[$finalKey]))
								{
									$finalSumary[$sst]['right'][$finalKey]['label'] = $this->currentListLabels[$finalKey];
								}

								$finalSumary[$sst]['right'][$finalKey]['ac'] = $answer;

								continue;
							}
							else if (preg_match('/^left_/', $finalKey) && $doLeftRightSplit)
							{
								$finalKey = substr($finalKey, 5);

								if (isset($this->currentListLabels[$finalKey]))
								{
									$finalSumary[$sst]['left'][$finalKey]['label'] = $this->currentListLabels[$finalKey];
								}

								$finalSumary[$sst]['left'][$finalKey]['ac'] = $answer;

								continue;
							}

							if (isset($this->currentListLabels[$finalKey]))
							{
								$finalSumary[$sst][$finalKey]['label'] = $this->currentListLabels[$finalKey];
							}

							$finalSumary[$sst][$finalKey]['ac'] = $answer;
						}
					}
				}

				if (is_array($angc))
				{
					foreach ($angc as $generalCommentKey => $comment)
					{
						$finalKey = substr($generalCommentKey, 0, -3);
						$finalKey = substr($finalKey, strlen($this->definition->prefix));

						if (preg_match('/' . $generalCommentKey . '/', $sstRes))
						{
							if (isset($this->currentListLabels[$finalKey]))
							{
								$finalSumary[$sst][$finalKey]['label'] = $this->currentListLabels[$finalKey];
							}

							$finalSumary[$sst]['gc'] = $comment;
						}
					}
				}

				if (is_array($anrom))
				{
					foreach ($anrom as $rangeOfMotionKey => $rom)
					{
						$finalKey = substr($rangeOfMotionKey, 0, -4);
						$finalKey = substr($finalKey, strlen($this->definition->prefix));

						if (preg_match('/' . $rangeOfMotionKey . '/', $sstRes))
						{
							if (isset($this->currentListLabels[$finalKey]))
							{
								$finalSumary[$sst][$finalKey]['label'] = $this->currentListLabels[$finalKey];
							}

							$finalSumary[$sst]['rom'] = $rom;
						}
					}
				}
			}

			return $finalSumary;
		}

		public function getModelListById($id, $orderBy)
		{
			$list = LoadList($id, 'active', $orderBy);

			foreach ($list as $listItem)
			{
				$this->currentListLabels[$listItem['option_id']] = $listItem['title'];
			}

			return $list;
		}
	}
