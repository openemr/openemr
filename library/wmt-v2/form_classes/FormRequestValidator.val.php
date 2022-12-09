<?php

	class FormRequestValidator
	{
		public $prefix = '';

		private $validTypes = array(
			'int',
			'float',
			'string',
			'email',
			'url',
			'ip',
			'regex'
		);

		public static function isInt($unkVal)
		{
			if (!is_scalar($unkVal) ||
			     is_null($unkVal)   ||
			     is_bool($unkVal))
			{
				return false;
			}

			return (bool) preg_match('/^[0-9]+$/', $unkVal);
		}

		public static function isFloat($unkVal, $bolReturnResultsArray = false)
		{
			if (!is_scalar($unkVal) ||
			     is_null($unkVal)   ||
			     is_bool($unkVal))
			{
				return false;
			}

			$resPregMatch = preg_match('/^([0-9]+)(\.[0-9]+)?$/', $unkVal, $arrMatches);

			if ($bolReturnResultsArray)
			{
				return $arrMatches;
			}

			return (bool) $resPregMatch;
		}

		public function isString($string)
		{
			return is_string($string) && strlen($string) > 0; 
		}

		public function isEmail($email)
		{
			return filter_var($email, FILTER_VALIDATE_EMAIL); 
		}

		public function isUrl($url)
		{
			return filter_var($url, FILTER_VALIDATE_URL); 
		}

		public function isIp($ip)
		{
			return filter_var($ip, FILTER_VALIDATE_IP); 
		}

		public function typeExists($type)
		{
			return in_array($type, $this->validTypes);
		}

		public function isCustomRegex($vpostVaral, $regex)
		{
			return filter_var(
				$val, 
				FILTER_VALIDATE_REGEXP, 
				array('options' => array('regexp' => $regex))
			);
		}

		public function isTargetPrefixKey($needle)
		{
			return preg_match('/^' . $this->prefix . '/', $needle);
		}

		public function isAnswerComment($search)
		{
			return preg_match('/_ac$/', $search);
		}

		public function isMultiChoice($search)
		{
			return preg_match('/_mc$/', $search);
		}

		public function isSubSectionTitle($search)
		{
			return preg_match('/_sst$/', $search);
		}

		public function isGeneralComment($search)
		{
			return preg_match('/_gc$/', $search);
		}

		public function isRangeOfMotion($search)
		{
			return preg_match('/_rom$/', $search);
		}

		public function isDisplayFlag($search)
		{
			return preg_match('/_df$/', $search);
		}

		public function validateKeyPair(
			$type, 
			$value, 
			&$errorMessages
		)
		{
			if (!$this->typeExists($type))
			{
				$message = "The given type was not a valid type - Given: type";
				$errorMessages[] = $message;
				error_log(__FILE__ . ':' . __LINE__ . ' - ' . $message);
				return;
			}

			$methodName = 'is' . ucfirst($type);

			if (!$this->$methodName($value))
			{
				$message = "The supplied value was not a valid $type. \nGiven: $value\n";
				$errorMessages[] = $message;
				error_log(__FILE__ . ':' . __LINE__ . ' - ' . $message);
				return;
			}

			return true;
		}
	}
