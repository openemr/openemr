<?php


	require_once LIBRARY . 'sql.inc';

	// MC = Multiple Choice
	// AC = Answer Comment
	class FormCommand
	{
		protected $definition;

		protected $defaultFields = 'date, pid, user, groupname, authorized, activity, link_id, link_name, ';

		protected $defaultFieldsValues = '?, ?, ?, ?, ?, ?, ?, ?, ';

		protected $request;
		protected $formId;
		protected $formName;

		private $tableName         = '';
		public $prefix             = '';
		private $childClassName    = '';
		public $multipleChoiceDefs = array();
		public $validator;
		
		protected function __construct()
		{
			$callerFileName = isset(debug_backtrace()[1]['file']) ? debug_backtrace()[1]['file'] : '';
			
			if (!preg_match('/form_commands\/([a-zA-Z0-9_]+).cmd.php$/', $callerFileName, $matches))
			{
				error_log(__FILE__ . ':' . __LINE__ . ' - $Caller file name wasnn\'t a valid file name. Got - ' . var_export($callerFileName, true) . "\nThis is gunna cause a lot of problems so start looking here if something doesn't workk right and getting this error");
				return;
			}
	
			$this->childClassName = $matches[1];
		}

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

		public function init($formId, $formName, &$definition)
		{
			$this->definition         =& $definition;
			$this->formId             =  $formId;
			$this->formName           =  $formName;
			$this->multipleChoiceDefs =  $definition->multipleChoiceDefs;
			$this->prefix             =  $definition->prefix;

			$this->request   = FormWmt::request($this->childClassName);
			$this->tableName = $definition->getMCProcessedTableName();

			$this->request->init($this->childClassName, $this->prefix);
			$this->request->run();

			$this->validator =& $this->request->validator;
		}

		private function getAllGroupedAnswers(&$keyPairs)
		{
			$multipChoiceAnswers = array();
			$answerNotes         = array();
			$subsectionTitles    = array();
			$generalComments     = array();
			$rangeOfMotions      = array();

			$currentSST = '';

			foreach ($keyPairs as $key => $value)
			{
				$isValidChoice = false;

				if ($this->validator->isSubSectionTitle($key))
				{
					$currentSST = $key;
				}
				else if ($this->validator->isAnswerComment($key))
				{
					$isValidChoice = true;

					$answerNotes[$key] = $value; 
				}
				else if ($this->validator->isGeneralComment($key))
				{
					$isValidChoice = true;

					$generalComments[$key] = $value;
				}
				else if ($this->validator->isRangeOfMotion($key))
				{
					$isValidChoice = true;

					$rangeOfMotions[$key] = $value;
				}
				else if ($this->validator->isMultiChoice($key))
				{
					$isValidChoice = true;

					$multipChoiceAnswers[$value][] = $key;
				}

				if (!empty($currentSST) && $isValidChoice)
				{
					if (isset($subsectionTitles[$currentSST]))
					{
						$subsectionTitles[$currentSST] .= $key . '|';
						continue;
					}
					
					$subsectionTitles[$currentSST] = $key . '|';
				}
			}

			foreach ($multipChoiceAnswers as $key => $values)
			{
				$multipChoiceAnswers[$key] = implode('|', $values);
			}

			$answers = array();

			if (count($answerNotes) > 0) $answers['answer_comments'] = $answerNotes;

			if (count($multipChoiceAnswers) > 0) $answers['multiple_choice_answers'] = $multipChoiceAnswers;

			if (count($subsectionTitles) > 0) $answers['sub_section_titles'] = $subsectionTitles;

			if (count($generalComments) > 0) $answers['general_comments'] = $generalComments;

			if (count($rangeOfMotions) > 0) $answers['range_of_motions'] = $rangeOfMotions;

			return $answers;
		}

		private function getInsertMCFields($answers)
		{
			$fields = array_values($this->multipleChoiceDefs);

			$processed = array();

			foreach ($fields as $key => $value)
			{
				if (array_key_exists($key, $answers))
				{
					$processed[$value] = strtolower($value);
					$processed[$value] = str_replace(' ', '_',  $processed[$value]);
					$processed[$value] = str_replace('\'', '_', $processed[$value]);
				}
			}

			return $processed;
		}

		private function getInsertValuesQuery($totalArrayKeys)
		{
			$sql = '';

			for ($i = 0; $i < $totalArrayKeys - 1; $i++) $sql .= '?, ';

			return $sql . '?';
		}

		private function getMCUpdateSql(
			$pid,
			$user,
			$userGroup,
			$authorized,
			&$groupedMcAnswers
		)
		{
			$multiChoiceKeys = array_keys($this->multipleChoiceDefs);
			$answerKeys      = array_keys($groupedMcAnswers);

			// if there is nothing in the answers array then nothing to do
			if (count($answerKeys) == 0)
			{
				return array();
			}

			foreach ($answerKeys as $key => $val)
			{
				if (!array_key_exists($val, $this->multipleChoiceDefs))
				{
					error_log(__FILE__ . ':' . __LINE__ . ' - $key was not a valid answer key. Got - ' . var_export($val, true) . ' - Valid answer keys: ' . var_export($this->multipleChoiceDefs));

					return array();
				}
			}

			$fieldsProcessed = $this->defaultFields;
			$sqlValues       = $this->defaultFieldsValues;

			$columns   = $this->getInsertMCFields($answerKeys);
			$tableName = $this->tableName;

			$fieldsProcessed .= implode(', ', $columns);
			$sqlValues       .= $this->getInsertValuesQuery(
				count($groupedMcAnswers)
			);

			$boundVals = array();

			$boundVals['date']       = date('Y-m-d H:i:s');
			$boundVals['pid']        = $pid;
			$boundVals['user']       = $user;
			$boundVals['groupname']  = $userGroup;
			$boundVals['authorized'] = $authorized;
			$boundVals['activity']   = 1;
			$boundVals['link_id']    = $this->formId;
			$boundVals['link_name']  = $this->formName;

			foreach ($this->multipleChoiceDefs as $choiceType => $choiceTitle)
			{
				// there is a bug where i am getting null. This is a quick hack 
				// to fix that and will come back through later and fix this
				if (empty($columns[$choiceTitle])) continue;

				if (!isset($columns[$choiceTitle]))
				{
					error_log(__FILE__ . ':' . __LINE__ . ' - Unable to get MC update sql. Column index doesn\'t exist. Current columns array - ' . var_export($columns, true) . "\nColumn: $choiceTitle");
					return array();
				}

				$valueIn = isset($groupedMcAnswers[$choiceType]) ? $groupedMcAnswers[$choiceType] : '';

				$boundVals[$columns[$choiceTitle]] = $valueIn;
			}

			$sql = "REPLACE INTO {$tableName} ($fieldsProcessed) VALUES ($sqlValues)";

			return array(
				'sql'       => $sql,
				'boundVals' => $boundVals
			);
		}

		private function getSSTUpdateSql(
			$pid,
			$user,
			$userGroup,
			$authorized,
			&$groupedSSTAnswers
		)
		{
			$sstKeys      = array_keys($this->definition->subSectionTitles);
			$groupSstKeys = array_keys($groupedSSTAnswers);

			// if there is nothing in the answers array then nothing to do
			if (count($groupSstKeys) == 0)
			{
				return array();
			}

			// check to make sure the given sub section title exists in the 
			// definition file if given. Note this is 100% optional but the
			// sub section title functionality will only work if this array
			// is previously defined in the form_definitions 
			foreach ($groupSstKeys as $key => $val)
			{
				if (!in_array(substr($val, 0, -4), $sstKeys))
				{
					error_log(__FILE__ . ':' . __LINE__ . ' - $key was not a valid sst key. Got - ' . var_export(substr($val, 0, -4), true) . ' - Valid answer keys: ' . var_export($sstKeys, true));

					return array();
				}
			}

			$columns   = $this->defaultFields . 'sub_section_title, answerKeys, sort';
			$sqlValues = $this->defaultFieldsValues . '?, ?, ?';

			$boundVals = array();

			$table = $this->definition->getSTTProcessedTableName();
			$sql   = "REPLACE INTO $table ($columns) VALUES ";
			$date  = date('Y-m-d H:i:s');

			$i = 0;

			foreach ($groupedSSTAnswers as $key => $answerKeys)
			{
				if ($i == 0) $i++;
				else $sql .= ', ';

				$sql .= "($sqlValues)";

				$boundVals[] = $date;
				$boundVals[] = $pid;
				$boundVals[] = $user;
				$boundVals[] = $userGroup;
				$boundVals[] = $authorized;
				$boundVals[] = 1;
				$boundVals[] = $this->formId;
				$boundVals[] = $this->formName;
				$boundVals[] = $key;
				$boundVals[] = $answerKeys;
				$boundVals[] = $this->definition->getSstIndexPos($key);
			}

			return array(
				'sql'       => $sql,
				'boundVals' => $boundVals
			);
		}

		private function getAnswerCommentsSql( 
			$pid,
			$user,
			$userGroup,
			$authorized,
			&$answerComments
		)
		{
			$columns   = $this->defaultFields;
			$columns  .= 'link_field, comment';
			
			$sqlValues = explode(', ', $columns);
			$sqlValues = $this->getInsertValuesQuery(count($sqlValues));
			$table     = $this->definition->getACProcessedTableName();
			$sql       = "REPLACE INTO $table ($columns) VALUES ";
			$date      = date('Y-m-d H:i:s');
			$boundVals = array();

			$i = 0;

			foreach ($answerComments as $key => $comment)
			{
				if ($i == 0) $i++;
				else $sql .= ', ';

				$sql .= "($sqlValues)";

				$boundVals[] = $date;
				$boundVals[] = $pid;
				$boundVals[] = $user;
				$boundVals[] = $userGroup;
				$boundVals[] = $authorized;
				$boundVals[] = 1;
				$boundVals[] = $this->formId;
				$boundVals[] = $this->formName;
				$boundVals[] = $key;
				$boundVals[] = $comment;
			}

			return array(
				'sql'       => $sql,
				'boundVals' => $boundVals
			);
		}

		private function getGeneralCommentsSql( 
			$pid,
			$user,
			$userGroup,
			$authorized,
			&$generalComments
		)
		{
			$columns   = $this->defaultFields;
			$columns  .= 'comment_id, comment';
			
			$sqlValues = explode(', ', $columns);
			$sqlValues = $this->getInsertValuesQuery(count($sqlValues));
			$table     = $this->definition->getGCProcessedTableName();
			$sql       = "REPLACE INTO $table ($columns) VALUES ";
			$date      = date('Y-m-d H:i:s');
			$boundVals = array();

			$i = 0;

			foreach ($generalComments as $id => $comment)
			{
				if ($i == 0) $i++;
				else $sql .= ', ';

				$sql .= "($sqlValues)";

				$boundVals[] = $date;
				$boundVals[] = $pid;
				$boundVals[] = $user;
				$boundVals[] = $userGroup;
				$boundVals[] = $authorized;
				$boundVals[] = 1;
				$boundVals[] = $this->formId;
				$boundVals[] = $this->formName;
				$boundVals[] = $id;
				$boundVals[] = $comment;
			}

			return array(
				'sql'       => $sql,
				'boundVals' => $boundVals
			);
		}

		private function getRangeOfMotionsSql( 
			$pid,
			$user,
			$userGroup,
			$authorized,
			&$rangeOfMotions
		)
		{
			$columns   = $this->defaultFields;
			$columns  .= 'rom_id, rom';
			
			$sqlValues = explode(', ', $columns);
			$sqlValues = $this->getInsertValuesQuery(count($sqlValues));
			$table     = $this->definition->getROMProcessedTableName();
			$sql       = "REPLACE INTO $table ($columns) VALUES ";
			$date      = date('Y-m-d H:i:s');
			$boundVals = array();

			$i = 0;

			foreach ($rangeOfMotions as $id => $rom)
			{
				if ($i == 0) $i++;
				else $sql .= ', ';

				$sql .= "($sqlValues)";

				$boundVals[] = $date;
				$boundVals[] = $pid;
				$boundVals[] = $user;
				$boundVals[] = $userGroup;
				$boundVals[] = $authorized;
				$boundVals[] = 1;
				$boundVals[] = $this->formId;
				$boundVals[] = $this->formName;
				$boundVals[] = $id;
				$boundVals[] = $rom;
			}

			return array(
				'sql'       => $sql,
				'boundVals' => $boundVals
			);
		}

		private function deleteAllMc()
		{
			$sql = "DELETE FROM " . $this->definition->getMCProcessedTableName() . ' WHERE link_id = ? AND link_name = ? LIMIT 1';

			sqlStatement($sql, array($this->formId, $this->formName));
		}

		private function deleteAllAC()
		{
			$sql = "DELETE FROM " . $this->definition->getACProcessedTableName() . ' WHERE link_id = ? AND link_name = ?';

			sqlStatement($sql, array($this->formId, $this->formName));
		}

		private function deleteAllROM()
		{
			$sql = "DELETE FROM " . $this->definition->getROMProcessedTableName() . ' WHERE link_id = ? AND link_name = ?';

			sqlStatement($sql, array($this->formId, $this->formName));
		}

		private function deleteAllGC()
		{
			$sql = "DELETE FROM " . $this->definition->getGCProcessedTableName() . ' WHERE link_id = ? AND link_name = ?';

			sqlStatement($sql, array($this->formId, $this->formName));
		}

		private function deleteAllSST()
		{
			$sql = "DELETE FROM " . $this->definition->getSTTProcessedTableName() . ' WHERE link_id = ? AND link_name = ?';

			sqlStatement($sql, array($this->formId, $this->formName));
		}

		private function getALlMCAnswers()
		{
			$desiredColumns = implode(', ', $this->multipleChoiceDefs);
			$sql = "SELECT * FROM " . $this->definition->getMCProcessedTableName() . ' WHERE link_id = ? AND link_name = ?';

			return sqlQuery(
				$sql,
				array($this->formId, $this->formName)
			);
		}

		private function getALlACComments()
		{
			$sql = 'SELECT link_field, comment FROM ' . $this->definition->getACProcessedTableName() . ' WHERE link_id = ? AND link_name = ?';

			$results = sqlStatementNoLog(
				$sql,
				array($this->formId, $this->formName)
			);

			$comments = array();

			while($res = sqlFetchArray($results))
			{
				$comments[$res{'link_field'}] = $res{'comment'};
			}

			return $comments;
		}

		private function getAllROMs()
		{
			$sql = 'SELECT rom_id, rom FROM ' . $this->definition->getROMProcessedTableName() . ' WHERE link_id = ? AND link_name = ?';
  
			$results = sqlStatementNoLog(
				$sql,
				array($this->formId, $this->formName)
			);

			$roms = array();

			while($res = sqlFetchArray($results))
			{
				$roms[$res{'rom_id'}] = $res{'rom'};
			}

			return $roms;
		}

		private function getALlGeneralComments()
		{
			$sql = 'SELECT comment_id, comment FROM ' . $this->definition->getGCProcessedTableName() . ' WHERE link_id = ? AND link_name = ?';
  
			$results = sqlStatementNoLog(
				$sql,
				array($this->formId, $this->formName)
			);

			$comments = array();

			while($res = sqlFetchArray($results))
			{
				$comments[$res{'comment_id'}] = $res{'comment'};
			}

			return $comments;
		}

		private function getALlSubSectionTitles()
		{
			$sql = 'SELECT sub_section_title, answerKeys FROM ' . $this->definition->getSTTProcessedTableName() . 
			' WHERE link_id = ? AND link_name = ? ORDER BY sort ASC';
  
			$results = sqlStatement(
				$sql,
				array($this->formId, $this->formName)
			);

			$ssts = array();

			while($res = sqlFetchArray($results))
			{
				$ssts[$res{'sub_section_title'}] = $res{'answerKeys'};
			}

			return $ssts;
		}

		public function create()
		{
			$processedRequest = $this->request->processRequest();
			$groupedAnswers   = $this->getAllGroupedAnswers($processedRequest);

			$pid        = $_SESSION['pid'];
			$user       = $_SESSION['authUser'];
			$userGroup  = $_SESSION['authGroup'];
			$authorized = $_SESSION['userauthorized'];

			if (isset($groupedAnswers['multiple_choice_answers']))
			{
				$sqlArray = $this->getMCUpdateSql(
					$pid,
					$user,
					$userGroup,
					$authorized,
					$groupedAnswers['multiple_choice_answers']
				);

				if (isset($sqlArray['sql']) && isset($sqlArray['boundVals']))
				{
					sqlStatement($sqlArray{'sql'}, $sqlArray{'boundVals'});
				}
			}
			else
			{
				$this->deleteAllMc();
			}

			if (isset($groupedAnswers['answer_comments']))
			{
				$sqlArray = $this->getAnswerCommentsSql(
					$pid,
					$user,
					$userGroup,
					$authorized,
					$groupedAnswers['answer_comments']
				);

				if (isset($sqlArray['sql']) && isset($sqlArray['boundVals']))
				{
					$this->deleteAllAC();

					sqlStatement($sqlArray{'sql'}, $sqlArray{'boundVals'});
				}
			}
			else
			{
				$this->deleteAllAC();
			}

			if (isset($groupedAnswers['sub_section_titles']))
			{	
				$sqlArray = $this->getSSTUpdateSql(
					$pid,
					$user,
					$userGroup,
					$authorized,
					$groupedAnswers['sub_section_titles']
				);

				if (isset($sqlArray['sql']) && isset($sqlArray['boundVals']))
				{
					$this->deleteAllSST();

					sqlStatement($sqlArray{'sql'}, $sqlArray{'boundVals'});
				}
			}
			else
			{
				$this->deleteAllSST();
			}

			if (isset($groupedAnswers['general_comments']))
			{	
				$sqlArray = $this->getGeneralCommentsSql(
					$pid,
					$user,
					$userGroup,
					$authorized,
					$groupedAnswers['general_comments']
				);
				
				if (isset($sqlArray['sql']) && isset($sqlArray['boundVals']))
				{
					$this->deleteAllGC();

					sqlStatement($sqlArray{'sql'}, $sqlArray{'boundVals'});
				}
			}
			else
			{
				$this->deleteAllGC();
			}

			if (isset($groupedAnswers['range_of_motions']))
			{	
				$sqlArray = $this->getRangeOfMotionsSql(
					$pid,
					$user,
					$userGroup,
					$authorized,
					$groupedAnswers['range_of_motions']
				);

				if (isset($sqlArray['sql']) && isset($sqlArray['boundVals']))
				{
					$this->deleteAllROM();

					sqlStatement($sqlArray{'sql'}, $sqlArray{'boundVals'});
				}
			}
			else
			{
				$this->deleteAllROM();
			}

			return true;
		}

		public function read()
		{
			$ssts       = array();
			$mcAnswers  = array();
			$acComments = array();
			$gcComments = array();
			$roms       = array();

			if ($this->formId > 0 && !empty($this->formName))
			{
				$ssts       = $this->getALlSubSectionTitles();
				$mcAnswers  = $this->getALlMCAnswers();
				$acComments = $this->getALlACComments();
				$gcComments = $this->getALlGeneralComments();
				$roms       = $this->getAllROMs();
			}

			return array(
				'subSectionTitles' => $ssts,
				'answers_mc'       => $mcAnswers,
				'answers_ac'       => $acComments,
				'answers_gc'       => $gcComments,
				'answers_rom'      => $roms
			);
		}

		public function update()
		{
			return $this->create();
		}

		public function delete()
		{
			$sql = 'DELETE FROM ' . $this->$definition->getACProcessedTableName() .
			' WHERE link_name = ? AND link_field = ?';

			return sqlStatement(
				$sql, 
				array($this->formId, $this->formName)
			);
		}
	}
