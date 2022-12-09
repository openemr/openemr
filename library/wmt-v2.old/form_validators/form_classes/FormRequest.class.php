<?php

	abstract class FormRequest
	{
		private $fileName        = 'file_na';
		private $checkBoxPrefix  = 'cb_';
		protected $rules         = array();
		public $processedPost;
		public $validator;

		abstract public function run();

		public function init($fileName, $prefix)
		{
			$this->validator         =  FormWmt::validator($fileName);
			$this->validator->prefix =  $prefix;
		}

		protected function postProcessed()
		{
			return is_array($this->processedPost) || count($this->processedPost) > 0;
		}

		protected function processPost()
		{
			$this->processedPost = $this->extractArrayFromPost();
		}

		public function addDisplayFlagEntry($key, $val)
		{
			if ($val == 'true') $val = (bool) true;
			else if ($val == 'false') $val = false;

			$_SESSION[$this->validator->prefix . 'display_flags'][$key] = $val;
		}

		protected function autoSetRules($doProcessPost = false)
		{
			// if the prefix is na then there was an error somewhere
			if (empty($this->validator->prefix) || $this->validator->prefix == 'na_')
			{
				error_log(__FILE__ . ':' . __LINE__ . ' - $validator prefix was not a valid or not set. Got - ' . var_export($this->validator->prefix, true));
				return;
			}

			global $_POST;
$keySearch = '_differential_test_';
			foreach ($_POST as $postKey => $postVar)
			{
if (preg_match('/' . $keySearch . '_/', $postKey)) {
error_log('Found Your key: ' . var_export($postKey, true));
}
				if ($this->validator->isTargetPrefixKey($postKey))
				{
if (preg_match('/' . $keySearch . '_/', $postKey)) {
error_log('Got to main if with key  ' . var_export($postKey, true));
}

					if (strlen($postVar) == 0) continue;

					$type = 'int';

					if ($this->validator->isAnswerComment($postKey)   ||
					    $this->validator->isSubSectionTitle($postKey) ||
					    $this->validator->isGeneralComment($postKey)  ||
					    $this->validator->isRangeOfMotion($postKey))
					{
						$type = 'string';
					}
					else if ($this->validator->isMultiChoice($postKey))
					{
						$type = 'int';
					}

					$this->addRule($postKey, $type);
				}
			}

			if ($doProcessPost) $this->processPost();
		}

		public function processRequest()
		{
			if (!is_array($this->processedPost) || 
				count($this->processedPost) == 0)
			{
				$this->processPost();
			}

			$errorMessages = array();
			$finalResults  = array();

			foreach ($this->processedPost as $param)
			{
				// chiro_whatevername_like_this_mc=int:2
				$regex = '/^([A-Aa-z0-9_]+)=([A-Za-z]+:[\d\D\w\W\s\S]+)/';

				if (empty($param['validation']))
				{
					continue;
				}

				if (!preg_match_all($regex, $param['validation'], $paramOptions))
				{
					continue;
				}

				if (count($paramOptions) == 0) continue;

				$fieldName = $paramOptions[1][0];

				$keyPair = (string) $paramOptions[2][0];

				if (!preg_match(
						'/^([A-Za-z]+):([\d\D\w\W\s\S]+)/',
						$keyPair,
						$keyPairMatch
					)
				) 
				{
					continue;
				}

				$type  = strtolower($keyPairMatch[1]);
				$value = (string) $keyPairMatch[2];

				if (!$this->validator->validateKeyPair($type, $value, $errorMessages))
				{
					error_log(__FILE__ . ':' . __LINE__ . " - Var type was invalid for key: $fieldName");
					continue;
				}

				$finalResults[$fieldName] = $value;
			}

			if (count($errorMessages) > 0)
			{
				$finalResults['error_messages'] = $errorMessages;
			}

			return $finalResults;
		}

		public function addRule($key, $rules)
		{
			$this->rules[$key] = $rules;
		}

		private function extractArrayFromPost()
		{
			global $_POST;

			$targetKeyPairs = array();

			foreach ($_POST as $key => $var)
			{
				if (preg_match('/^tmp_/', $key))
				{
					continue;
				}

				if (substr($key, 0, 3) == $this->checkBoxPrefix)
				{
					unset($_POST[$key]);
					continue;
				}

				if ($this->validator->isTargetPrefixKey($key))
				{
					unset($_POST[$key]);

					if ($this->validator->isDisplayFlag($key))
					{
						$this->addDisplayFlagEntry($key, trim($var));
						continue;
					}

					$var = str_replace('&nbsp;', ' ', $var);
 
					$converted = strtr(
						$var, 
						array_flip(
							get_html_translation_table(
								HTML_ENTITIES, 
								ENT_QUOTES
							)
						)
					); 

					$var = trim($var, chr(0xC2).chr(0xA0));

					if (empty(html_entity_decode(trim($var)))) continue;

					$var = (string) $var;

					if (array_key_exists($key, $this->rules))
					{
						$targetKeyPairs[$key]['validation'] = trim($key) . '=' . $this->rules[$key] . ':' . trim($var);
					}

					$targetKeyPairs[$key]['value'] = trim($var);
				}
			}

			return $targetKeyPairs;
		}
	}
