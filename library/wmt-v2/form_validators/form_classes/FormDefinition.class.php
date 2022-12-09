<?php 

	// SST = Sub Section Title
	// MC = MultipleChoice
	// AC = Answer Commet

//	trait FormDefinitionTrait
//	{
//		protected $fileName = 'unknown_name';
//		protected $prefix   = 'unknown_';
//		// the keys of this array cannot be a '0' if so this key will be left 
		// out of the multiple choice array and all answers with this key
//		protected $multipleChoiceDefs = array(
//			'1' => 'Negative',
//			'2' => 'Positive'
//		);
//	}

	abstract class FormDefinition
	{
		public $fileName = 'unknown_name';
		public $prefix   = 'unknown_';
		// the keys of this array cannot be a '0' if so this key will be left 
		// out of the multiple choice array and all answers with this key
		public $multipleChoiceDefs = array(
			'2' => 'Positive',
			'1' => 'Negative',
			'3' => 'Inconclusive'
		);
		public $subSectionTitles = array();

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

		public function getSstIndexPos($sst)
		{
			if (preg_match('/^([A-Za-z0-9_\-]+)_sst$/', $sst, $matches))
			{
				$sst = $matches[1];
			}

			$index = array_search($sst, array_keys($this->subSectionTitles));

			if ($index < 0)
			{
				return 0;
			}

			return $index;
		}

		public function formatId($value)
		{
			$parsedId = str_replace(' ', '_', $value);
			$parsedId = str_replace('-', '_', $parsedId);
			$parsedId = str_replace(',', '_', $parsedId);
			$parsedId = str_replace('\'', '_', $parsedId);
			$parsedId = str_replace('`', '_', $parsedId);
			$parsedId = str_replace('~', '_', $parsedId);
			$parsedId = str_replace('!', '_', $parsedId);
			$parsedId = str_replace('?', '_', $parsedId);
			$parsedId = str_replace('>', '_', $parsedId);
			$parsedId = str_replace('<', '_', $parsedId);
			$parsedId = str_replace('(', '_', $parsedId);
			$parsedId = str_replace(')', '_', $parsedId);
			$parsedId = str_replace('{', '_', $parsedId);
			$parsedId = str_replace('}', '_', $parsedId);
			$parsedId = str_replace('@', '_', $parsedId);
			$parsedId = str_replace('#', '_', $parsedId);
			$parsedId = str_replace('$', '_', $parsedId);
			$parsedId = str_replace('[', '_', $parsedId);
			$parsedId = str_replace(']', '_', $parsedId);
			$parsedId = str_replace('’', '_', $parsedId);
			$parsedId = str_replace('__', '_', $parsedId);

			return htmlspecialchars(strtolower($parsedId));
		}

		public function getDisplayInputFlagId($sstId)
		{
			if (preg_match('/^([A-Za-z0-9_\-]+)_sst$/', $sstId, $matches))
			{
				$sstId = $matches[1];	
			}

			if (preg_match('/^' . $this->prefix . '([A-Za-z_]+)/', $sstId, $matches))
			{
				return $this->prefix . $this->formatId($matches[1]) . '_df';
			}
			
			return $this->prefix . $this->formatId($sstId) . '_df';
		}

		// Multiple Choice
		public function getMcIdFormat($value)
		{
			if (preg_match('/^' . $this->prefix . '([A-Za-z_]+)/', $value, $matches))
			{
				return $this->prefix . $this->formatId($matches[1]) . '_mc';
			}
			
			return $this->prefix . $this->formatId($value) . '_mc';
		}

		// Answer Comments
		public function getAcIdFormat($value)
		{
			if (preg_match('/^' . $this->prefix . '([A-Za-z_]+)/', $value, $matches))
			{
				return $this->prefix . $this->formatId($matches[1]) . '_ac';
			}

			return $this->prefix . $this->formatId($value) . '_ac';
		}

		// Sub Section Titles
		public function getSstIdFormat($title)
		{
			if (preg_match('/^' . $this->prefix . '([A-Za-z_]+)/', $title, $matches))
			{
				return $this->prefix . $this->formatId($matches[1]) . '_sst';
			}

			return $this->prefix . $this->formatId($title) . '_sst';
		}

		// Range of Motion
		public function getRomIdFormat($title)
		{
			if (preg_match('/^' . $this->prefix . '([A-Za-z_]+)/', $title, $matches))
			{
				return $this->prefix . $this->formatId($matches[1]) . '_rom';
			}

			return $this->prefix . $this->formatId($title) . '_rom';
		}

		public function getClearSectionBtnIdFormat($title) 
		{
			$idFormat = '_btn_clear_section';

			if (preg_match('/^' . $this->prefix . '([A-Za-z_]+)/', $title, $matches))
			{
				return 'tmp_' . $this->prefix . $this->formatId($matches[1]) . $idFormat;
			}

			return 'tmp_' . $this->prefix . $this->formatId($title) . $idFormat;
		}

		public function getUndoClearSectionBtnIdFormat($title) 
		{
			$idFormat = '_btn_undo_clear_section';

			if (preg_match('/^' . $this->prefix . '([A-Za-z_]+)/', $title, $matches))
			{
				return 'tmp_' . $this->prefix . $this->formatId($matches[1]) . $idFormat;
			}

			return 'tmp_' . $this->prefix . $this->formatId($title) . $idFormat;
		}

		public function getClearFormBtnIdFormat() 
		{
			return 'tmp_' . $this->prefix . '_btn_clear_form';
		}

		public function getUndoClearFormBtnIdFormat() 
		{
			return 'tmp_' . $this->prefix . '_btn_clear_undo';
		}

		// Sub Section Titles
		public function getGcIdFormat($name)
		{
			// for what ever reason if the prefix ie 'chiro' as been passes
			// through to these methods then we just check and make sure that
			// we're not doubling up on the prefix
			if (preg_match('/^' . $this->prefix . '([A-Za-z_]+)/', $name, $matches))
			{
				return $this->prefix . $this->formatId($matches[1]) . '_gc';
			}

			return $this->prefix . $this->formatId($name) . '_gc';
		}

		public function getMCProcessedTableName()
		{
			if (!preg_match('/^form_(' . $this->prefix . ')[A-Za-z0-9_]|[]A-Za-z0-9]+_exam$/', $this->fileName, $matches))
			{
				error_log(__FILE__ . ':' . __LINE__ . ' - $this->fileName was not a valid tableNameFormat. Expected form_{prefix}_exam . Got - ' . var_export($this->tableName, true));
			}

			return 'form_wmt_' . $matches[1] . 'mc';
		}

		public function getACProcessedTableName()
		{
			if (!preg_match('/^form_(' . $this->prefix . ')[A-Za-z0-9_]|[]A-Za-z0-9]+_exam$/', $this->fileName, $matches))
			{
				error_log(__FILE__ . ':' . __LINE__ . ' - $this->fileName was not a valid tableNameFormat. Expected form_{prefix}_exam . Got - ' . var_export($this->fileName, true));
			}

			return 'form_wmt_' . $matches[1] . 'ac';
		}

		public function getSTTProcessedTableName()
		{
			if (!preg_match('/^form_(' . $this->prefix . ')[A-Za-z0-9_]|[]A-Za-z0-9]+_exam$/', $this->fileName, $matches))
			{
				error_log(__FILE__ . ':' . __LINE__ . ' - $this->fileName was not a valid tableNameFormat. Expected form_{prefix}_exam . Got - ' . var_export($this->fileName, true));
			}

			return 'form_wmt_' . $matches[1] . 'sst';
		}

		public function getGCProcessedTableName()
		{
			if (!preg_match('/^form_(' . $this->prefix . ')[A-Za-z0-9_]|[]A-Za-z0-9]+_exam$/', $this->fileName, $matches))
			{
				error_log(__FILE__ . ':' . __LINE__ . ' - $this->fileName was not a valid tableNameFormat. Expected form_{prefix}_exam . Got - ' . var_export($this->fileName, true));
			}

			return 'form_wmt_' . $matches[1] . 'gc';
		}

		public function getROMProcessedTableName()
		{
			if (!preg_match('/^form_(' . $this->prefix . ')[A-Za-z0-9_]|[]A-Za-z0-9]+_exam$/', $this->fileName, $matches))
			{
				error_log(__FILE__ . ':' . __LINE__ . ' - $this->fileName was not a valid tableNameFormat. Expected form_{prefix}_exam . Got - ' . var_export($this->fileName, true));
			}

			return 'form_wmt_' . $matches[1] . 'rom';
		}
	}
