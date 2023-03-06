<?php

namespace OpenEMR\OemrAd;

@include_once(__DIR__ . "/../interface/globals.php");
@require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");

@include_once(__DIR__ . "/mdEmailMessage.class.php");
@include_once(__DIR__ . "/mdFaxMessage.class.php");
@include_once(__DIR__ . "/mdPostalLetter.class.php");

@require_once($GLOBALS['fileroot']. '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
@require_once($GLOBALS['fileroot']. '/vendor/phpmailer/phpmailer/src/SMTP.php');
@require_once($GLOBALS['fileroot']. '/vendor/phpmailer/phpmailer/src/Exception.php');
@require_once($GLOBALS['srcdir']."/pnotes.inc");

@include_once(__DIR__ . "/../configs/reminder_settings.php");
@include_once(__DIR__ . "/mdZoomIntegration.class.php");
@include_once(__DIR__ . "/mdSmslib.class.php");
@include_once(__DIR__ . "/mdMessagesLib.class.php");
@include_once(__DIR__ . "/reminderUtils.php");

use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\FaxMessage;
use OpenEMR\OemrAd\PostalLetter;
use OpenEMR\OemrAd\ZoomIntegration;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\Smslib;

class Reminder {

	private $email;
	private $send_phone;
	private $sms;

	/*Constructor*/
	public function __construct() {
		//$this->email = new \wmt\Email(TRUE);
		
		// $smsManagerObj = new \extTwilio\Sms();

		// Set "sender" phone number
		// $this->send_phone = preg_replace('/[^0-9]/', '', $smsManagerObj->getDefaultFromNo());
		//$this->sms = @new \wmt\Nexmo($this->send_phone);
	}

	public static function getConfigVars() {
		$returnList = new \stdClass();
		$returnList->send_phone = preg_replace('/[^0-9]/', '', Smslib::getDefaultFromNo());

		return $returnList;
	}

	public static function isDataEmpty($data) {
		$isEmpty = false;

		if($data && isset($data['action_type']) && $data['action_type'] == "hubspot_sync") {
			$field_list = array('id', 'trigger_type');
			foreach ($data as $key => $value) {
				if(in_array($key, $field_list)) {
					if($key == "trigger_type") {
						if($value == 'time' && (empty($data['time_trigger_data']) || empty($data['data_set']))) {
							$isEmpty = true;
						} else if($value == 'event' && empty($data['event_trigger'])) {
							//$isEmpty = true;
						}
					} else {
						if(trim($value) == "") {
							$isEmpty = true;
						}
					}
				}
			}
		} else if($data && isset($data['action_type']) && $data['action_type'] == "idempiere_webservice") {
			$field_list = array('id', 'seq', 'trigger_type');
			foreach ($data as $key => $value) {
				if(in_array($key, $field_list)) {
					if($key == "trigger_type") {
						if($value == 'time' && (empty($data['time_trigger_data']) || empty($data['data_set']))) {
							$isEmpty = true;
						} else if($value == 'event' && empty($data['event_trigger'])) {
							$isEmpty = true;
						}
					} else {
						if(trim($value) == "") {
							$isEmpty = true;
						}
					}
				}
			}
		} else {
			$field_list = array('id', 'communication_type', 'notification_template', 'trigger_type');
			foreach ($data as $key => $value) {
				if(in_array($key, $field_list)) {
					if($key == "notification_template" && (empty($value) || $value == "free_text")) {
						$isEmpty = true;
					} else if($key == "trigger_type") {
						if($value == 'time' && (empty($data['time_trigger_data']) || empty($data['data_set']))) {
							$isEmpty = true;
						} else if($value == 'event' && empty($data['event_trigger'])) {
							$isEmpty = true;
						}
					} else {
						if(empty($value)) {
							$isEmpty = true;
						}
					}
				}
			}
		}

		return $isEmpty;
	}

	public function isActionEventDataEmpty($data) {
		$isEmpty = false;
		$field_list = array('id', 'seq', 'trigger_type', 'action_type', 'configuration_id');

		foreach ($data as $key => $value) {
			if(in_array($key, $field_list)) {
				if(empty($value)) {
					$isEmpty = true;
				}
			}
		}

		return $isEmpty;
	}

	public static function getActionEventConfiguration($id = '') {
		$result_list = array();
		$binds = array();

		$sql = "SELECT nc.* ";
		$sql .= "FROM `actionevent_configurations` nc ";

		if(!empty($id)) {
			$sql .= "WHERE nc.`id` = ? ";
			$binds[] = $id;
		}
		$sql .= "order by nc.`seq` ASC";

		$result = sqlStatementNoLog($sql, $binds);

		while ($result_data = sqlFetchArray($result)) {
			$result_list[] = $result_data;
		}
		return $result_list;
	}

	public static function deleteActionEventConfiguration($id = '') {
		$sql = "DELETE FROM `actionevent_configurations` ";

		if(!empty($id)) {
			$sql .= " WHERE `id` = '$id' ";
		}

		return sqlStatement($sql);
	}

	public static function isActionEventConfigurationExist($id = '') {
		$row = sqlQuery("SELECT * FROM `actionevent_configurations` WHERE id = ? ", array($id));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function saveActionEventConfiguration($data) {
		extract($data);
		$isEmpty = self::isActionEventDataEmpty($data);

		if($isEmpty === false) {
			//Write new record
			$sql = "INSERT INTO `actionevent_configurations` ( ";
			$sql .= "id, seq, trigger_type, action_type, active, configuration_id ) VALUES (?, ?, ?, ?, ?, ?) ";
				
			sqlInsert($sql, array(
				$id,
				$seq,
				$trigger_type,
				$action_type,
				$active,
				$configuration_id
			));

			return true;
		}

		return false;
	}

	public static function updateActionEventConfiguration($id, $data) {
		if(!empty($data) && !empty($id)) {
			$binds = array();
			$setColsList = array();

			foreach ($data as $ind => $item) {
				$setColsList[] = $ind." = ?";
				$binds[] = $item;
			}

			$setStr = implode(", ", $setColsList);
			$binds[] = $id;

			if(!empty($setStr)) {
				sqlStatementNoLog("UPDATE `actionevent_configurations` SET ".$setStr." WHERE id = ?", $binds);
			}
		}

		return true;
	}

	public static function getNotificationConfiguration($id = '', $type = '', $actionType = '') {
		$result_list = array();
		$binds = array();

		$sql = "SELECT nc.* ";
		$sql .= "FROM `notification_configurations` nc ";

		if(!empty($id)) {
			$sql .= "WHERE nc.`id` = ? ";
			$binds[] = $id;
		}

		if(!empty($type)) {
			$sql .= "WHERE nc.`trigger_type` = ? ";
			$binds[] = $type;
		}

		if(!empty($actionType)) {
			$sql .= "AND nc.`action_type` = ? ";
			$binds[] = $actionType;
		}

		$sql .= "order by nc.`date` ASC";

		$result = sqlStatementNoLog($sql, $binds);

		while ($result_data = sqlFetchArray($result)) {
			$result_list[] = $result_data;
		}
		return $result_list;
	}

	public function getTestNotificationConfiguration() {
		$result_list = array();
		$binds = array(1);

		$sql = "SELECT nc.* ";
		$sql .= "FROM `notification_configurations` nc ";
		$sql .= "where nc.test_mode = ? ";
		$sql .= "order by nc.`date` ASC";

		$result = sqlStatementNoLog($sql, $binds);

		while ($result_data = sqlFetchArray($result)) {
			$result_list[] = $result_data;
		}
		return $result_list;
	}

	public static function getTestModeConfigList() {
		$configs = self::getTestNotificationConfiguration();
		$config_ids = array();

		if(isset($configs)) {
			foreach ($configs as $ck => $cItem) {
				if(isset($cItem['test_mode']) && !empty($cItem['id']) && $cItem['test_mode'] == 1) {
					$config_ids[$cItem['id']] = $cItem;
				}
			}
		}

		return $config_ids;
	}

	public static function getActionConfiguration($id = '', $type = '') {
		$result_list = array();
		$binds = array();

		$sql = "SELECT ac.`id` as event_id, ac.`seq` as as_seq, ac.`seq`, ac.`trigger_type` as as_trigger_type, ac.`action_type`, ac.`configuration_id`, ac.`date` as ac_date, active ";
		$sql .= "FROM `actionevent_configurations` ac ";
		//$sql .= "LEFT JOIN `notification_configurations` nc ON nc.`id` = ac.`configuration_id` ";

		if(!empty($id) && !is_array($id)) {
			$sql .= "WHERE ac.`id` = ? ";
			$binds[] = $id;
		}

		if(!empty($id) && is_array($id)) {
			$ids = "'".implode("','", $id)."'";
			$sql .= "WHERE ac.`id` IN (".$ids.") ";
		}

		if(!empty($type)) {
			$sql .= "WHERE ac.`trigger_type` = ? ";
			$binds[] = $type;
		}

		$sql .= "order by ac.`date` ASC";

		$result = sqlStatementNoLog($sql, $binds);

		while ($result_data = sqlFetchArray($result)) {
			$config_ids = (self::isJson($result_data['configuration_id']) == true) ? json_decode($result_data['configuration_id']) : array($result_data['configuration_id']);
			$notify_config_data = array();

			foreach ($config_ids as $key => $id) {
				$notify_config = self::getNotificationConfiguration($id);
				if(!empty($notify_config)) {
					$notify_config_data[] = $notify_config[0];
				}
			}
			$result_data['config_data'] = $notify_config_data;
			$result_list[] = $result_data;
		}

		return $result_list;
	}

	public static function getActionConfigurationByParam($param = array()) {
		$result_list = array();
		$binds = array();

		$sql = "SELECT ac.`id` as event_id, ac.`seq` as as_seq, ac.`seq`, ac.`trigger_type` as as_trigger_type, ac.`action_type`, ac.`configuration_id`, ac.`date` as ac_date, active ";
		$sql .= "FROM `actionevent_configurations` ac ";
		//$sql .= "LEFT JOIN `notification_configurations` nc ON nc.`id` = ac.`configuration_id` ";

		$sqlWhere = array();
		foreach ($param as $pk => $pkValue) {
			if($pk == "id") {
				if(!empty($pkValue) && !is_array($pkValue)) {
					$sqlWhere[] = "ac.`id` = ?";
					$binds[] = $pkValue;
				}

				if(!empty($pkValue) && is_array($pkValue)) {
					$ids = "'".implode("','", $pkValue)."'";
					$sqlWhere[] = "ac.`id` IN (".$ids.")";
				}
			}

			if($pk == "type") {
				if(!empty($pkValue)) {
					$sqlWhere[] = "ac.`trigger_type` = ?";
					$binds[] = $pkValue;
				}
			}
		}

		if(!empty($sqlWhere)) {
			$sql .= "WHERE ". implode(" AND ", $sqlWhere) . " ";
		}

		$configid_param = "";

		if(isset($param['id']) && isset($param['config_id']) && !empty($param['id']) && !empty($param['config_id'])) {
			$configid_param = $param['config_id'];
		}

		$sql .= "order by ac.`date` ASC";

		$result = sqlStatementNoLog($sql, $binds);

		while ($result_data = sqlFetchArray($result)) {
			$config_ids = (self::isJson($result_data['configuration_id']) == true) ? json_decode($result_data['configuration_id']) : array($result_data['configuration_id']);
			$notify_config_data = array();

			foreach ($config_ids as $key => $id) {

				//Skip configuration
				if(isset($configid_param) && !empty($configid_param) && $configid_param != $id) {
					continue;
				}


				if(!empty($id)) {
					//Get Notification Configuration
					$notify_config = self::getNotificationConfiguration($id);
					if(!empty($notify_config)) {
						$notify_config_data[] = $notify_config[0];
					}
				}
			}

			//Addd Config to list
			$result_data['config_data'] = $notify_config_data;
			$result_list[] = $result_data;
		}

		return $result_list;
	}

	public static function deleteNotificationConfiguration($id = '') {
		$sql = "DELETE FROM `notification_configurations` ";

		if(!empty($id)) {
			$sql .= " WHERE `id` = '$id' ";
		}

		return sqlStatement($sql);
	}

	public static function isAlreadyInUse($id) {
		$row = sqlQuery("SELECT * FROM `actionevent_configurations` WHERE configuration_id = ? ", array($id));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	public static function isConfigurationExist($id = '') {
		$row = sqlQuery("SELECT * FROM `notification_configurations` WHERE id = ? ", array($id));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function saveNotificationConfiguration($data) {
		extract($data);

		//Write new record
		$sql = "INSERT INTO `notification_configurations` ( ";
		$sql .= "id, seq, communication_type, action_type, notification_template, sync_mode, data_set, data_query, pre_processing_data_set, trigger_type, api_config, batch_size, request_template, request_timeout, time_trigger_data, event_trigger, time_delay, test_mode, to_user ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$id,
			$seq,
			$communication_type,
			$action_type,
			$notification_template,
			$sync_mode,
			$data_set,
			$data_query,
			$pre_processing_data_set,
			$trigger_type,
			$api_config,
			$batch_size,
			$request_template,
			$request_timeout,
			$time_trigger_data,
			$event_trigger,
			$time_delay,
			$test_mode,
			$to_user
		));

		return true;
	}

	public static function updateNotificationConfiguration($id, $data) {
		if(!empty($data) && !empty($id)) {
			$binds = array();
			$setColsList = array();

			foreach ($data as $ind => $item) {
				$setColsList[] = $ind." = ?";
				$binds[] = $item;
			}

			$setStr = implode(", ", $setColsList);
			$binds[] = $id;

			if(!empty($setStr)) {
				sqlStatementNoLog("UPDATE `notification_configurations` SET ".$setStr." WHERE id = ?", $binds);
			}
		}

		return true;
	}

	public static function savePreparedData($data) {
		
		//Extract value to variable
		extract($data);

		//Write new record
		$sql = "INSERT INTO `notif_log` ( ";
		$sql .= "event_id, config_id, event_type, msg_type, template_id, message, tablename, uniqueid, pid, uid, user_type, sent, sent_time, trigger_time, time_delay, created_time ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$event_id,
			$config_id,
			$event_type,
			$msg_type,
			$template_id,
			$message,
			$tablename,
			$uniqueid,
			$pid,
			$uid,
			$user_type,
			$sent,
			$sent_time,
			$trigger_time,
			$time_delay,
			date('Y-m-d H:i:s')
		));

		return true;
	}

	public static function savePreparedIntMessageData($data) {
		
		//Extract value to variable
		extract($data);

		//Write new record
		$sql = "INSERT INTO `vh_internal_messaging_notif_log` ( ";
		$sql .= "event_id, config_id, event_type, msg_type, template_id, message, attachments, tablename, uniqueid, pid, uid, to_user, user_type, sent, sent_time, trigger_time, time_delay, created_time ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$event_id,
			$config_id,
			$event_type,
			$msg_type,
			$template_id,
			$message,
			$attachments,
			$tablename,
			$uniqueid,
			$pid,
			$uid,
			$to_user,
			$user_type,
			$sent,
			$sent_time,
			$trigger_time,
			$time_delay,
			date('Y-m-d H:i:s')
		));

		return true;
	}

	public static function savePreparedApiData($data) {
			
		//Extract value to variable
		extract($data);

		//Write new record
		$sql = "INSERT INTO `vh_api_notif_log` ( ";
		$sql .= "event_id, config_id, event_type, msg_type, template_id, message, tablename, uniqueid, pid, uid, user_type, sent, sent_time, trigger_time, time_delay, created_time ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$event_id,
			$config_id,
			$event_type,
			$msg_type,
			$template_id,
			$message,
			$tablename,
			$uniqueid,
			$pid,
			$uid,
			$user_type,
			$sent,
			$sent_time,
			$trigger_time,
			$time_delay,
			date('Y-m-d H:i:s')
		));

		return true;
	}

	public static function getDataForSend($ids = array(), $event_type = false, $limit = 100, $offset = 0) {
		$sql = "SELECT * FROM `notif_log` WHERE ";

		if(!empty($ids)) {
			$sql .= " `id` IN (".implode(",", $ids).") AND ";
		}

		if($event_type !== false) {
			$sql .= " event_type = '".$event_type."' AND ";
		}

		$sql .= " sent = 0 ORDER BY trigger_time";

		if(empty($ids)) {
			$sql .= " LIMIT ".$offset.", ".$limit."";
		}

		$resultItems = array();
		$result = sqlStatementNoLog($sql);

		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	public static function getIntMsgDataForSend($ids = array(), $event_type = false, $limit = 100, $offset = 0) {
		$sql = "SELECT mnl.*, mnl.to_user FROM `vh_internal_messaging_notif_log` as mnl LEFT JOIN `notification_configurations`as nc ON nc.id = mnl.config_id WHERE ";

		if(!empty($ids)) {
			$sql .= " mnl.`id` IN (".implode(",", $ids).") AND ";
		}

		if($event_type !== false) {
			$sql .= " mnl.event_type = '".$event_type."' AND ";
		}

		$sql .= " mnl.sent = 0 ORDER BY mnl.trigger_time ";

		if(empty($ids)) {
			$sql .= " LIMIT ".$offset.", ".$limit."";
		}

		$resultItems = array();
		$result = sqlStatementNoLog($sql);
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	public static function getApiDataForSend($ids = array(), $event_type = false, $limit = 100, $offset = 0) {
		$sql = "SELECT * FROM `vh_api_notif_log` WHERE ";

		if(!empty($ids)) {
			$sql .= " `id` IN (".implode(",", $ids).") AND ";
		}

		if($event_type !== false) {
			$sql .= " event_type = '".$event_type."' AND ";
		}

		$sql .= " sent = 0 ORDER BY trigger_time ";

		if(empty($ids)) {
			$sql .= " LIMIT ".$offset.", ".$limit."";
		}

		$resultItems = array();
		$result = sqlStatementNoLog($sql);

		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	//Check SpecificEvent Id Exists
	public static function isSpecificEventIdExists($eventid_param = '', $configid_param = '') {
		$return_data = array();

		if(!empty($eventid_param)) {
			$return_data['event_id'] = $eventid_param;
		}

		if(!empty($eventid_param) && !empty($configid_param)) {
			$return_data['config_id'] = $configid_param;
		}

		if(empty($return_data)) {
			return false;
		}

		return $return_data;
	}

	//Get Data For Send by Event Id
	public static function getSendItemIdByEvent($params = array(), $limit = 100, $offset = 0) {
		$tablename = (isset($params['tablename']) && !empty($params['tablename'])) ? $params['tablename'] : "";
		$eventid_param = (isset($params['event_id']) && !empty($params['event_id'])) ? $params['event_id'] : "";
		$configid_param = (isset($params['config_id']) && !empty($params['config_id']) && !empty($eventid_param)) ? $params['config_id'] : "";
		$resultItems = array();

		if(!empty($tablename)) {
			$sql = "SELECT id FROM `".$tablename."` WHERE ";
			foreach ($params as $pk => $pkValue) {
				if($pk == "id") {
					if(!empty($pkValue)) {
						$sql .= " `id` IN (".implode(",", $pkValue).") AND ";
					}
				}

				if($pk == "event_type") {
					if($pkValue !== false) {
						$sql .= " event_type = '".$pkValue."' AND ";
					}
				}
			}

			if(!empty($eventid_param)) {
				$sql .= " event_id = '".$eventid_param."' AND ";
			}

			if(!empty($configid_param)) {
				$sql .= " config_id = '".$configid_param."' AND ";
			}

			$sql .= " sent = 0 ORDER BY trigger_time LIMIT ".$offset.", ".$limit."";
			
			$result = sqlStatementNoLog($sql);
			while ($result_data = sqlFetchArray($result)) {
				//$resultItems[] = $result_data;
				if(!empty($result_data['id'])) {
					$resultItems[] = $result_data['id'];
				}
			}
		}

		return $resultItems;
	}

	//Run to perform action befor to send reminder
	public static function preProcessingQuery($ids = array(), $event_type = false, $ignoreConfigs = array()) {
		$exceptionList = array();

		$dataItems = self::getDataForSend($ids, $event_type);
		$event_ids = array();

		try {
			foreach ($dataItems as $key => $item) {
				if(!empty($item['id'])) {
					$event_ids[] = $item['id'];
				}

				if(isset($item['config_id']) && !empty($item['config_id'])) {

					//Ignore test mode items
					if(!empty($ignoreConfigs) && isset($ignoreConfigs[$item['config_id']])) {
						continue;
					}

					$configs = self::getNotificationConfiguration($item['config_id']);

					$replacevars = array(
					  '$nid' => isset($item['id']) ? $item['id'] : "",
					  '$event_id' => isset($item['event_id']) ? $item['event_id'] : "",
					  '$msg_type' => isset($item['msg_type']) ? $item['msg_type'] : "",
					  '$tablename' => isset($item['tablename']) ? $item['tablename'] : "",
					  '$uniqueid' => isset($item['uniqueid']) ? $item['uniqueid'] : ""
					);

					if(isset($configs) && count($configs) > 0) {
						foreach ($configs as $key => $config) {
							try {
								if($item['config_id'] == $config['id']) {
									if(!empty(trim($config['pre_processing_data_set']))) {
										$pre_processing = trim($config['pre_processing_data_set']);
										$final_pre_processing = strtr($pre_processing, $replacevars);

										//Execute Query
										sqlStatementNoLogExecute($final_pre_processing);
									}
								}
							} catch (\Exception $e) {
								$exceptionList[] = 'PreProcessing Query Error: ' . $e->getMessage();
							}
						}	
					}
				}
			}
		} catch (\Exception $e) {
			$exceptionList[] = 'PreProcessing Main Error: ' . $e->getMessage();
		}

		if(!empty($exceptionList)) {
			//throw new \Exception(json_encode($exceptionList));
		}

		return $event_ids;
	}

	public static function preProcessingIntMsgQuery($ids = array(), $event_type = false) {
		$exceptionList = array();

		try {
			$dataItems = self::getIntMsgDataForSend($ids, $event_type);
			$event_ids = array();

			foreach ($dataItems as $key => $item) {
				if(!empty($item['id'])) {
					$event_ids[] = $item['id'];
				}

				if(isset($item['config_id']) && !empty($item['config_id'])) {
					$configs = self::getNotificationConfiguration($item['config_id']);

					$replacevars = array(
					  '$nid' => isset($item['id']) ? $item['id'] : "",
					  '$event_id' => isset($item['event_id']) ? $item['event_id'] : "",
					  '$msg_type' => isset($item['msg_type']) ? $item['msg_type'] : "",
					  '$tablename' => isset($item['tablename']) ? $item['tablename'] : "",
					  '$uniqueid' => isset($item['uniqueid']) ? $item['uniqueid'] : ""
					);

					if(isset($configs) && count($configs) > 0) {
						foreach ($configs as $key => $config) {
							try {
								if($item['config_id'] == $config['id']) {
									if(!empty(trim($config['pre_processing_data_set']))) {
										$pre_processing = trim($config['pre_processing_data_set']);
										$final_pre_processing = strtr($pre_processing, $replacevars);

										//Execute Query
										sqlStatementNoLogExecute($final_pre_processing);
									}
								}
							} catch (\Exception $e) {
								$exceptionList[] = 'Internal Msg PreProcessing Query Error: ' . $e->getMessage();
							}
						}	
					}
				}
			}

		} catch (\Exception $e) {
			$exceptionList[] = 'Internal Msg PreProcessing Main Error: ' . $e->getMessage();
		}

		if(!empty($exceptionList)) {
			//throw new \Exception(json_encode($exceptionList));
		}

		return $event_ids;
	}

	//Run to perform action befor to send reminder
	public static function preProcessingApiQuery($ids = array(), $event_type = false) {
		$exceptionList = array();

		try {
			$dataItems = self::getApiDataForSend($ids, $event_type);
			$event_ids = array();

			foreach ($dataItems as $key => $item) {
				if(!empty($item['id'])) {
					$event_ids[] = $item['id'];
				}

				if(isset($item['config_id']) && !empty($item['config_id'])) {
					$configs = self::getNotificationConfiguration($item['config_id']);

					$replacevars = array(
					  '$nid' => isset($item['id']) ? $item['id'] : "",
					  '$event_id' => isset($item['event_id']) ? $item['event_id'] : "",
					  '$msg_type' => isset($item['msg_type']) ? $item['msg_type'] : "",
					  '$tablename' => isset($item['tablename']) ? $item['tablename'] : "",
					  '$uniqueid' => isset($item['uniqueid']) ? $item['uniqueid'] : ""
					);

					if(isset($configs) && count($configs) > 0) {
						foreach ($configs as $key => $config) {
							try {
								if($item['config_id'] == $config['id']) {
									if(!empty(trim($config['pre_processing_data_set']))) {
										$pre_processing = trim($config['pre_processing_data_set']);
										$final_pre_processing = strtr($pre_processing, $replacevars);

										//Execute Query
										sqlStatementNoLogExecute($final_pre_processing);
									}
								}
							} catch (\Exception $e) {
								$exceptionList[] = 'API PreProcessing Query Error: ' . $e->getMessage();
							}
						}	
					}
				}
			}
		} catch (\Exception $e) {
			$exceptionList[] = 'API PreProcessing Main Error: ' . $e->getMessage();
		}

		if(!empty($exceptionList)) {
			//throw new \Exception(json_encode($exceptionList));
		}

		return $event_ids;
	}

	public static function sendIntMsgNotificationByEvent($type = 0, $eventid_param = '', $configid_param = '') {
		$itemIdList = array();
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		$iIds = self::getSendItemIdByEvent(array(
			'tablename' => 'vh_internal_messaging_notif_log',
			'event_type' => $event_type,
			'event_id' => $eventid_param,
			'config_id' => $configid_param
		));

		if(!empty($iIds)) {
			return self::sendIntMsgNotification($type, $iIds);
		}

		return array('total_items' => 0, 'total_sent_item' => 0);
	}

	public function sendIntMsgNotification($type = 0, $itemIds = array()) {
		$exceptionList = array();
		$totalItem = 0;
		$totalsentItem = 0;
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		try {

			$notificationLog = array(
				'sent' => array(),
				'failed' => array()
			);

			//Run Pre processing query
			$ids = self::preProcessingIntMsgQuery($itemIds, $event_type);
			$dataItems = self::getIntMsgDataForSend($ids, $event_type);
			foreach ($dataItems as $key => $item) {
				try {
					if(!empty($item['trigger_time'])) {
						$trigger_time = $item['trigger_time'];

						//Unix time
						$current_unix_time = strtotime('now');
						$trigger_unix_time = strtotime($item['trigger_time']);

						if($item['sent'] == 0 && $trigger_unix_time <= $current_unix_time) {

							if(empty($item['template_id']) || empty($item['message'])) {
								continue;
							}
							
							if(!empty($item['time_delay']) && $item['time_delay'] != 0) {
								sleep($item['time_delay']);
							}

							if($item['msg_type'] == "email") {
								$itemStatus = self::sendEmailMsg($item);
							}

							if(isset($itemStatus) && $itemStatus === true) {
								$totalsentItem++;
								$notificationLog['sent'][] = $item;
							} else {
								$notificationLog['failed'][] = $item;
							}
							$totalItem++;
						}
					}
				} catch (\Exception $e) {
					$exceptionList[] = 'Internal Message Error: ' . $e->getMessage();
				}
			}
		} catch (\Exception $e) {
			$exceptionList[] = 'Internal Message Main Error: ' . $e->getMessage();
		}

		return array('total_items' => $totalItem, 'total_sent_item' => $totalsentItem, 'exceptionList' => $exceptionList);
	}

	public static function prepareApiRequestBody($item) {
		$bodyParams = array(
			"messageName" => "intermediate_message",
			"businessKey" => isset($item['id']) ? $item['id'] : 0,
			"processVariables" => array()
		);
		$processVariables = array();
		$skipField = array('request_body', 'request_responce');

		if(!empty($item)) {
			foreach ($item as $field => $value) {
				if(in_array($field, $skipField)) {
					continue;
				}

				$processVariables[$field] = array(
					"value" => $value,
					"type" => "String"
				);
			}
		}

		$bodyParams["processVariables"] = $processVariables;
		return $bodyParams;
	}

	public static function callApiRequest($item) {
		$responce = array();

		if(!empty($item)) {
			$request_url = "http://localhost:8080/engine-rest/message";

			$bodyParams = self::prepareApiRequestBody($item);
			$jsonBody = json_encode($bodyParams);

			$ch=curl_init($request_url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($jsonBody))             
			);

			$result = curl_exec($ch);
			curl_close($ch);

			$responce = array(
				'request_body' => $jsonBody,
				'request_responce' => $result
			);
		}

		return $responce;
	}

	public static function sendApiDataByEvent($type = 0, $eventid_param = '', $configid_param = '') {
		$itemIdList = array();
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		$iIds = self::getSendItemIdByEvent(array(
			'tablename' => 'vh_api_notif_log',
			'event_type' => $event_type,
			'event_id' => $eventid_param,
			'config_id' => $configid_param
		));

		if(!empty($iIds)) {
			return self::sendApiData($type, $iIds);
		}

		return array('total_items' => 0, 'total_sent_item' => 0);
	}

	public function sendApiData($type = 0, $itemIds = array()) {
		$exceptionList = array();
		$totalItem = 0;
		$totalsentItem = 0;
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		try {
				
			$notificationLog = array(
				'sent' => array(),
				'failed' => array()
			);

			//Run Pre processing query
			$ids = self::preProcessingApiQuery($itemIds, $event_type);

			$dataItems = self::getApiDataForSend($ids, $event_type);

			foreach ($dataItems as $key => $item) {
				try {
					if(!empty($item['trigger_time'])) {
						$trigger_time = $item['trigger_time'];

						//Unix time
						$current_unix_time = strtotime('now');
						$trigger_unix_time = strtotime($item['trigger_time']);

						if($item['sent'] == 0 && $trigger_unix_time <= $current_unix_time) {
							if(!empty($item['time_delay']) && $item['time_delay'] != 0) {
								sleep($item['time_delay']);
							}

							$itemStatus = self::callApiRequest($item);

							self::updatePreparedApiData($item['id'], array(
								'sent' => 1,
								'sent_time' => date('Y-m-d H:i:s'),
								'request_body' => isset($itemStatus['request_body']) ? $itemStatus['request_body'] : "",
								'request_responce' => isset($itemStatus['request_responce']) ? $itemStatus['request_responce'] : "",
							));

							if(isset($itemStatus) && !empty($itemStatus)) {
								$totalsentItem++;
								$notificationLog['sent'][] = $item;
							} else {
								$notificationLog['failed'][] = $item;
							}
							$totalItem++;
						}
					}
				} catch (\Exception $e) {
					$exceptionList[] = 'API Error: ' . $e->getMessage();
				}
			}

		} catch (\Exception $e) {
			$exceptionList[] = 'API Main Error: ' . $e->getMessage();
		}

		return array('total_items' => $totalItem, 'total_sent_item' => $totalsentItem, 'exceptionList' => $exceptionList);
	}

	public static function sendNotificationByEvent($type = 0, $eventid_param = '', $configid_param = '') {
		$itemIdList = array();
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		$iIds = self::getSendItemIdByEvent(array(
			'tablename' => 'notif_log',
			'event_type' => $event_type,
			'event_id' => $eventid_param,
			'config_id' => $configid_param
		));

		if(!empty($iIds)) {
			return self::sendNotification($type, $iIds);
		}

		return array('total_items' => 0, 'total_sent_item' => 0);
	}

	public function sendNotification($type = 0, $itemIds = array()) {
		$exceptionList = array();
		$totalItem = 0;
		$totalsentItem = 0;
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		try {

			$notificationLog = array(
				'sent' => array(),
				'failed' => array()
			);

			if($type === 0 || $type === 2) {
				//prepare trigger data for send reminder
				self::prepareNotificationData('event');
			}

			//Get TestMode Notifications
			$ignoreConfigs = self::getTestModeConfigList();
			$testModeItems = array();

			//Run Pre processing query
			$ids = self::preProcessingQuery($itemIds, $event_type, $ignoreConfigs);
			$dataItems = self::getDataForSend($ids, $event_type);

			foreach ($dataItems as $key => $item) {
				try {
					if(!empty($item['trigger_time'])) {
						$trigger_time = $item['trigger_time'];

						//Unix time
						$current_unix_time = strtotime('now');
						$trigger_unix_time = strtotime($item['trigger_time']);

						if($item['sent'] == 0 && $trigger_unix_time <= $current_unix_time) {

							if(empty($item['template_id']) || empty($item['message'])) {
								continue;
							}

							//Skip Test Mode
							if(!empty($ignoreConfigs) && isset($ignoreConfigs[$item['config_id']])) {
								$testModeItems = self::handleTestItems($item, $testModeItems, $ignoreConfigs);
								continue;
							}
							
							if(!empty($item['time_delay']) && $item['time_delay'] != 0) {
								sleep($item['time_delay']);
							}

							if($item['msg_type'] == "email") {
								$itemStatus = self::sendEmail($item);
							} else if($item['msg_type'] == "sms") {
								$itemStatus = self::sendSMS($item);
							} else if($item['msg_type'] == "fax") {
								$itemStatus = self::sendFAX($item);
							} else if($item['msg_type'] == "postalmethod") {
								$itemStatus = self::sendPostalLetter($item);
							} else if($item['msg_type'] == "internalmessage") {
								$itemStatus = self::sendInternalMessage($item);
							}

							if(isset($itemStatus) && $itemStatus === true) {
								$totalsentItem++;
								$notificationLog['sent'][] = $item;
							} else {
								$notificationLog['failed'][] = $item;
							}
							$totalItem++;
						}
					}
				} catch (\Exception $e) {
					$exceptionList[] = 'Messaging Error: ' . $e->getMessage();
				}
			}

			if($totalItem > 0 || count($testModeItems) > 0) {
				self::sendNotificationLog($notificationLog, $testModeItems);
			}

		} catch (\Exception $e) {
			$exceptionList[] = 'Main Messaging Error: ' . $e->getMessage();
		}

		return array('total_items' => $totalItem, 'total_sent_item' => $totalsentItem, 'exceptionList' => $exceptionList);
	}

	public static function sendNotificationLog($data, $testItems = array()) {
		ob_start();
		if(!empty($data['sent']) || !empty($data['failed'])) {
		$srno = 1;
		?>
			<span><b>Cron Reminder Log</b></span>
			<br/><br/>
			<table border="1" cellpadding="2" cellspacing="0">
				<tr>
					<th align="left">Sr. No</th>
					<th align="left">Event Id</th>
					<th align="left">Config Id</th>
					<th align="left">Template Id</th>
					<th align="left">Commnication Type</th>
					<th align="left">User PId</th>
					<th align="left">Trigger Time</th>
					<th align="left">Status</th>
				</tr>
				<?php
					foreach ($data['sent'] as $skey => $sentItem) {
						?>
						<tr>
							<th align="left"><?php echo $srno; ?></th>
							<th align="left"><?php echo $sentItem['event_id']; ?></th>
							<th align="left"><?php echo $sentItem['config_id']; ?></th>
							<th align="left"><?php echo $sentItem['template_id']; ?></th>
							<th align="left"><?php echo $sentItem['msg_type']; ?></th>
							<th align="left"><?php echo $sentItem['pid']; ?></th>
							<th align="left"><?php echo $sentItem['trigger_time']; ?></th>
							<th align="left">Sent</th>
						</tr>
						<?php
						$srno++;
					}
					foreach ($data['failed'] as $fkey => $failedItem) {
						?>
						<tr>
							<th align="left"><?php echo $srno; ?></th>
							<th align="left"><?php echo $failedItem['event_id']; ?></th>
							<th align="left"><?php echo $failedItem['config_id']; ?></th>
							<th align="left"><?php echo $failedItem['template_id']; ?></th>
							<th align="left"><?php echo $failedItem['msg_type']; ?></th>
							<th align="left"><?php echo $failedItem['pid']; ?></th>
							<th align="left"><?php echo $failedItem['trigger_time']; ?></th>
							<th align="left">Failed</th>
						</tr>
						<?php
						$srno++;
					}
				?>
			</table>
		<?php
		}		

		$html_str = ob_get_clean();

		$test_html_str = self::generateTestModeItemLog($testItems);
		if(isset($test_html_str) && !empty($test_html_str)) {
			$html_str .= "<br/><br/>" . $test_html_str;
		}

		if(!empty($html_str) && isset($GLOBALS['alert_log_recipient']) && !empty(trim($GLOBALS['alert_log_recipient']))) {

			$email_data = array(
				'patient' => "Admin",
				'from' => isset($GLOBALS['EMAIL_SEND_FROM']) ? $GLOBALS['EMAIL_SEND_FROM'] : 'PATIENT SUPPORT',
				'subject' => 'Cron - Reminder Log',
				'email' => trim($GLOBALS['alert_log_recipient']),
				'html' => $html_str,
				'text' => $html_str,
				'message_content' => $html_str
			);

			$emailObj = new \wmt\Email(TRUE);
			$emailObj->FromName = $GLOBALS['EMAIL_FROM_NAME'];

			// Send email
			$status = @$emailObj->TransmitEmail($email_data);
			//EmailMessage::setTimeZone();
		}
	}

	public static function handleTestItems($item = array(), $testModeItems = array(), $ignoreConfigs = array()) {
		if(isset($item['config_id'])) {
			if(!isset($testModeItems[$item['config_id']])) {
				$testModeItems[$item['config_id']] = array(
					'config' => $ignoreConfigs[$item['config_id']],
					'items' => array()
				);
			}

			if(isset($testModeItems[$item['config_id']]['items'])) {

				self::updatePreparedData($item['id'], array(
					'sent' => 4,
					'sent_time' => date('Y-m-d H:i:s')
				));

				$testModeItems[$item['config_id']]['items'][] = $item;
			}
		}

		return $testModeItems;
	}

	public static function generateTestModeItemLog($items = array()) {
		$colList = array(
			'id',
			'event_id',
			'config_id',
			'event_type',
			'msg_type',
			'template_id',
			'tablename',
			'uniqueid',
			'pid',
			'uid',
			'trigger_time'
		);

		ob_start();

		if(!empty($items) && count($items) > 0) {
			$cnt = 0;
			foreach ($items as $tck => $tcItem) {
				if(isset($tcItem['items'])) {

					if($cnt > 0) {
						?>
							<br/><br/>
						<?php
					}

					?>
					<span><b>Config Id: <?php echo $tcItem['config']['id']; ?></b></span><br/>
					<table border="1" cellpadding="2" cellspacing="0">
						<tr>
							<?php foreach ($colList as $ck => $col) { ?>
								<th align="left">
									<?php echo ucfirst(str_replace("_", " ", $col)); ?>
								</th>
							<?php } ?>
						</tr>
					<?php foreach ($tcItem['items'] as $tik => $tItem) { ?>
						<tr>
						<?php foreach ($colList as $ck => $col) { ?>
							<td align="left">
								<?php echo isset($tItem[$col]) ? $tItem[$col] : ""; ?>
							</td>
						<?php } ?>
						</tr>
					<?php } ?>
					</table>
					<?php
				}

				$cnt++;
			}
		}

		$html_str = ob_get_clean();

		return $html_str;
	}

	public static function sendInternalMessage($data) {

		if(!empty($data['pid'])) {
			$preparedParam = self::prepareRequestForMessage($data);
			
			$defaultValue = array(
				'pid' => $data['pid'],
				'note_type' => "Action Event",
				'note' => $data['message'],
				'assigned_to' => "admin"
			);

			$defaultValue = array_merge($defaultValue, $preparedParam);
			$varArrayList = array(
				"set_assign_pid" => $defaultValue['pid'],
				"set_note_type" => $defaultValue['note_type'],
				"note" => $defaultValue['note'],
				"assigned_to" => $defaultValue['assigned_to'],
			);

			if(isset($defaultValue['message_reference'])) {
				$varArrayList['message_reference'] = $defaultValue['message_reference'];
			}

			extract($varArrayList);

			if(!empty($set_assign_pid) && !empty($assigned_to) && !empty($set_note_type)) {
				
				//Add PNote
				$noteId = addPnote($set_assign_pid, $note, '1', '1', $set_note_type, $assigned_to, '', "New");

				if(isset($noteId) && !empty($noteId)) {
					if(isset($message_reference) && is_array($message_reference) && !empty($message_reference)) {

						foreach ($message_reference as $mrKey => $mrItem) {
							//Add Msg Reference
							self::saveMsgGprelation('104', $mrItem, '6', $noteId);
						}
					}
				}

				self::updatePreparedData($data['id'], array(
							'sent' => 1,
							'sent_time' => date('Y-m-d H:i:s')
						));

				return true;
			} else {
				self::updatePreparedData($data['id'], array(
					'sent' => 3
				));
			}
		}

		return false;
	}

	public static function sendPostalLetter($data) {
		$rStatus = false;

		if(!empty($data['pid'])) {
			$pat_data = @\wmt\Patient::getPidPatient($data['pid']);

			$env_mode = self::getEnvMode();
			if($env_mode == "test") {
				$testAddress = self::getTestModeValue("postal_letter");
				$fullAddress =  PostalLetter::generatePostalAddress($testAddress, "\n");
			} else {
				$fullAddress =  PostalLetter::generatePostalAddress(array(
				    'street' => $pat_data->street,
				    'street1' => "",
				    'city' => $pat_data->city,
				    'state' => $pat_data->state,
				    'postal_code' => $pat_data->postal_code,
				    'country' => $pat_data->country_code,
				), "\n");
			}
			
			if($fullAddress['address'] === false) {
				return false;
			}

			if($fullAddress['status'] == true) {
				$message_list = new \wmt\Options('Reminder_Postal_Letters');

				$from_reply_address = isset($GLOBALS['POSTAL_LETTER_REPlY_ADDRESS']) ? $GLOBALS['POSTAL_LETTER_REPlY_ADDRESS'] : "";
				$from_reply_address_json = isset($GLOBALS['POSTAL_LETTER_REPlY_ADDRESS_JSON']) ? $GLOBALS['POSTAL_LETTER_REPlY_ADDRESS_JSON'] : "";

				$from_address = isset($pat_data->format_name) ? $pat_data->format_name."\n" : '';
				$from_address .= trim($fullAddress['address']);
				$base_address = trim($fullAddress['address']);
				$from_address_json = isset($fullAddress['address_json']) ? $fullAddress['address_json'] : array();

				try {

					$pItem = array(
					'pid' => $data['pid'],
					'data' => array(
						'template' => $data['template_id'],
						'html' => $data['message'],
						'text' => $data['message'],
						'address' => $from_address,
						'address_json' => json_encode($from_address_json),
						'reply_address' => $from_reply_address,
						'reply_address_json' => $from_reply_address_json,
						'receiver_name' => $pat_data->format_name,
						'address_from_type' => "custom",
						'base_address' => $base_address,
						'request_data' => array(),
						'files' => array(),
					));

					$pData = PostalLetter::TransmitPostalLetter(
						array($pItem['data']), 
						array('pid' => $pItem['pid'], 'logMsg' => true, 'calculate_cost' => false)
					);
					
					if(is_array($pData) && count($pData) == 1) {
						$responce = $pData[0];
					} else {
						throw new \Exception("Something went wrong.");
					}
					
					/*
					// Prepare postal letter data
					$postal_letter_data = array();
					$postal_letter_data['dec'] = !empty($data['template_id']) ? $message_list->getItem($data['template_id']) : 'General Postal Letter';
					$postal_letter_data['html'] = $data['message'];
			        $postal_letter_data['text'] = $data['message'];
			        $postal_letter_data['address'] = $form_address;
			        $postal_letter_data['address_json'] = json_encode($form_address_json);
			        $postal_letter_data['reply_address'] = $form_reply_address;
			        $postal_letter_data['reply_address_json'] = $form_reply_address_json;
			        $postal_letter_data['receiver_name'] = $pat_data->format_name;
			        $postal_letter_data['base_address'] = $base_address;

			        //Attache Files
					$attchFiles = @PostalLetter::AddAttachmentToMsg($data['pid'], $postal_letter_data, array(), array());

					// Send postal letter
					$responce = @PostalLetter::Transmit($postal_letter_data);
					//EmailMessage::setTimeZone();
					*/
				} catch (Exception $e) {
					$status = $e->getMessage();
					$responData = array(
						'status' => false,
						'error' => $status
					);
					$rStatus = $status;
				}

				if(isset($responce) && isset($responce['status']) && $responce['status'] == true) {

					// $postal_letter_data['pid'] = $data['pid'];
					// $postal_letter_data['request'] = array(
					// 	'message' => $postal_letter_data['message_content'],
					// 	'pid' => $postal_letter_data['pid'],
					// 	'address' => $postal_letter_data['base_address'],
					// 	'address_json' => $postal_letter_data['address_json'],
					// 	'rec_name' => $postal_letter_data['receiver_name'], 
					// 	'reply_address' => $postal_letter_data['reply_address'],
					// 	'reply_address_json' => $postal_letter_data['reply_address_json'],
					// 	'address_from' => "patient",
					// 	'address_book' => "",
					// 	'insurance_companies' => ""
					// );

					// $responceData = @PostalLetter::logPostalLetterData($responce, $postal_letter_data);

					$msgId = "";
					if(is_array($responce['data']) && count($responce['data']) == 1) {
						$msgId = isset($responce['data'][0]['msgid']) ? $responce['data'][0]['msgid'] : "";
					}

					self::updatePreparedData($data['id'], array(
							'msg_id' => isset($msgId) ? $msgId : "",
							'sent' => 1,
							'sent_time' => date('Y-m-d H:i:s')
						));

					return true;
				} else {
					self::updatePreparedData($data['id'], array(
						'sent' => 3
					));
					$rStatus = $responce['error'];
				}

			} else {
				self::updatePreparedData($data['id'], array(
						'sent' => 2,
						'sent_time' => date('Y-m-d H:i:s')
					));
			}
		}

		return $rStatus;
	}

	public static function sendFAX($data) {
		$rStatus = false;

		if(!empty($data['pid'])) {

			$pat_data = @\wmt\Patient::getPidPatient($data['pid']);

			$env_mode = self::getEnvMode();
			if($env_mode == "test") {
				$pat_data->fax_number = self::getTestModeValue("fax");
			}
			
			if($pat_data->fax_number === false) {
				return false;
			}

			if(!empty($pat_data->fax_number)) {
				try {

					$fItem = array(
					'pid' => $data['pid'],
					'data' => array(
						'template' => $data['template_id'],
						'fax_number' => $pat_data->fax_number,
						'receiver_name' => $pat_data->format_name,
						'html' => $data['message'],
						'text' => $data['message'],
						'fax_from_type' => 'custom',
						'request_data' => array(),
						'files' => array(),
					));

					$fData = FaxMessage::TransmitFax(
						array($fItem['data']), 
						array('pid' => $fItem['pid'], 'logMsg' => true, 'calculate_cost' => false)
					);

					if(is_array($fData) && count($fData) == 1) {
						$responce = $fData[0];
					} else {
						throw new \Exception("Something went wrong.");
					}

					/*
					// Prepare fax data
					$fax_data = array(
						'fax_number' => $pat_data->fax_number,
						'html' => $data['message'],
						'text' => $data['message'],
						'receiver_name' => $pat_data->format_name,
						'message_content' => $data['message'],
					);

					//Attache Files
					$attchFiles = @FaxMessage::AddAttachmentToFax($data['pid'], $fax_data, array(), array());
					$fax_data['data'] = @FaxMessage::getFilesContent($fax_data);

					// Send fax
					$responce = @FaxMessage::Transmit($fax_data);
					//EmailMessage::setTimeZone();
					*/

				} catch (Exception $e) {
					$status = $e->getMessage();
					$responData = array(
						'status' => false,
						'error' => $status
					);
					$rStatus = $status;
				}

				if(isset($responce) && isset($responce['status']) && $responce['status'] == true){

						// $fax_data['pid'] = $data['pid'];
						// $fax_data['request'] = array(
						// 	'message' => $fax_data['message_content'],
						// 	'pid' => $fax_data['pid'],
						// 	'rec_name' => $fax_data['receiver_name'], 
						// 	'fax_number' => $fax_data['fax_number'],
						// 	'fax_from' => "patient",
						// 	'address_book' => "",
						// 	'insurance_companies' => ""
						// );

						// $responceData = @FaxMessage::logFaxData($responce, $fax_data, false);
					
						$msgId = "";
						if(is_array($responce['data']) && count($responce['data']) == 1) {
							$msgId = isset($responce['data'][0]['msgid']) ? $responce['data'][0]['msgid'] : "";
						}

						self::updatePreparedData($data['id'], array(
							'msg_id' => isset($msgId) ? $msgId : "",
							'sent' => 1,
							'sent_time' => date('Y-m-d H:i:s')
						));

						return true;
				} else {
					self::updatePreparedData($data['id'], array(
						'sent' => 3
					));
					$rStatus = $responce['error'];
				}
			} else {
				self::updatePreparedData($data['id'], array(
						'sent' => 2,
						'sent_time' => date('Y-m-d H:i:s')
					));
			}
		}

		return $rStatus;
	}

	public static function sendSMS($data) {
		$rStatus = false;

		$configList = self::getConfigVars();
		$smsObj = Smslib::getSmsObj($configList->send_phone);

		//$smsObj = @new \wmt\Nexmo($configList->send_phone);


		if(!empty($data['pid'])) {
			$pat_data = self::getPatientData($data['pid']);
			$pat_phone = isset($pat_data['phone_cell']) && !empty($pat_data['phone_cell']) ? preg_replace('/[^0-9]/', '', $pat_data['phone_cell']) : "";

			$isEnable = $pat_data['hipaa_allowsms'] != 'YES' || empty($pat_data['phone_cell']) ? true : false;
			
			if(!empty($pat_phone) && $isEnable === false) {
				$final_pat_phone = MessagesLib::getPhoneNumbers($pat_phone);
				$form_to_phone =  $final_pat_phone['msg_phone'];
				$form_message = $data['message'];

				$env_mode = self::getEnvMode();
				if($env_mode == "test") {
					$form_to_phone = self::getTestModeValue("phone");
				}
				
				if($form_to_phone === false) {
					return false;
				}

				if (!empty($form_message)) {
					$result = @$smsObj->smsTransmit($form_to_phone, $form_message, 'text');
					$msgId = $result['msgid'];
					$msgStatus = isset($result['msgStatus']) ? $result['msgStatus'] : 'MESSAGE_SENT';
					
					if (!empty($msgId)) {

						$raw_data = json_encode(EmailMessage::includeRequest(
							array(
								'pid' => $data['pid'], 
								'message_tlp' => $data['template_id'], 
								'phone' => $form_to_phone
							), 
							array(
									'pid',
									'message_tlp', 
									'phone'
							)
						));

						$datetime = strtotime('now');
						$msg_date = date('Y-m-d H:i:s', $datetime);
						$msgLogId = @$smsObj->logSMS('SMS_MESSAGE', $form_to_phone, $configList->send_phone, $data['pid'], $msgId, $msg_date, $msgStatus, $form_message, 'out', false, $raw_data);
						
						self::updatePreparedData($data['id'], array(
							'msg_id' => !empty($msgLogId) ? $msgLogId : "",
							'sent' => 1,
							'sent_time' => date('Y-m-d H:i:s')
						));

						return true;

					} else {
						self::updatePreparedData($data['id'], array(
							'sent' => 3
						));
						$rStatus = $result['error'];
					}
				}
			} else {
				self::updatePreparedData($data['id'], array(
					'sent' => 2,
					'sent_time' => date('Y-m-d H:i:s')
				));
			}
		}

		return $rStatus;
	}

	public static function sendEmail($data) {
		$rStatus = false;

		if(!empty($data['pid'])) {
			$pat_data = self::getPatientData($data['pid']);

			// preformat commonly used data elements
			$pat_name = ($pat_data['title'])? $pat_data['title'] : "";
			$pat_name .= ($pat_data['fname'])? $pat_data['fname'] : "";
			$pat_name .= ($pat_data['mname'])? substr($pat_data['mname'],0,1).". " : "";
			$pat_name .= ($pat_data['lname'])? $pat_data['lname'] : "";

			if(!empty($pat_data)) {
				$messaging_enabled = ($pat_data['hipaa_allowemail'] != 'YES' || (empty($pat_data['email']) && !$GLOBALS['wmt::use_email_direct']) || (empty($pat_data['email_direct']) && $GLOBALS['wmt::use_email_direct'])) ? true : false;

				$email_direct = $GLOBALS['wmt::use_email_direct'] ? $pat_data['email_direct'] : $pat_data['email'];

				$env_mode = self::getEnvMode();
				if($env_mode == "test") {
					$email_direct = self::getTestModeValue("email");
				}
				
				if($email_direct === false) {
					return 'Messaging disable or email is empty';
				}

				if($messaging_enabled === false) {
					try {

						$message_list = new \wmt\Options('Reminder_Email_Messages');
						
						$appid = @self::getDataAppId($data);
						$event_datetime = @self::getDataAppDate($data);
						$subject = @self::getSubject($data['pid'], $message_list, $data['template_id'], $event_datetime, $appid);

						$eItem = array(
						'pid' => $data['pid'],
						'data' => array(
							'from' => isset($GLOBALS['EMAIL_SEND_FROM']) ? $GLOBALS['EMAIL_SEND_FROM'] : 'PATIENT SUPPORT',
							'email' => $email_direct,
							'template' => $data['template_id'],
							'subject' => $subject,
							'patient' => $pat_name,
							'html' => $data['message'],
							'text' => $data['message'],
							'request_data' => array(),
							'files' => array(),
						));

						$eData = EmailMessage::TransmitEmail(
							array($eItem['data']), 
							array('pid' => $eItem['pid'], 'logMsg' => true)
						);

						if(is_array($eData) && count($eData) == 1) {
							$responce = $eData[0];
						} else {
							throw new \Exception("Something went wrong.");
						}

						// $email_data = array(
						// 	'patient' => $pat_name,
						// 	'from' => isset($GLOBALS['EMAIL_SEND_FROM']) ? $GLOBALS['EMAIL_SEND_FROM'] : 'PATIENT SUPPORT',
						// 	'subject' => $subject,
						// 	'email' => $email_direct,
						// 	'html' => $data['message'],
						// 	'text' => $data['message'],
						// 	'message_content' => $data['message']
						// );

						// $emailObj = new \wmt\Email(TRUE);
						// $emailObj->FromName = $GLOBALS['EMAIL_FROM_NAME'];

						// // Send email
						// $status = @$emailObj->TransmitEmail($email_data);
						// //EmailMessage::setTimeZone();

					} catch (Exception $e) {
						$status = $e->getMessage();
						$rStatus = $status;
					}

					//$isActive = EmailMessage::isActive($status);

					if(isset($responce) && isset($responce['status']) && $responce['status'] == true){

						// $email_data['pid'] = $data['pid'];
						// $email_data['request'] = array(
						// 	'message' => $email_data['message_content'],
						// 	'pid' => $email_data['pid'],
						// 	'email_id' => $email_data['email'], 
						// 	'subject' => $email_data['subject'],
						// 	'baseDocList' => array()
						// );

						// $msgLogId = EmailMessage::logEmailData($status, $email_data);

						$msgId = "";
						if(is_array($responce['data']) && count($responce['data']) == 1) {
							$msgId = isset($responce['data'][0]['msgid']) ? $responce['data'][0]['msgid'] : "";
						}

						self::updatePreparedData($data['id'], array(
							'msg_id' => !empty($msgId) ? $msgId : "",
							'sent' => 1,
							'sent_time' => date('Y-m-d H:i:s')
						));

						return true;
					} else {
						self::updatePreparedData($data['id'], array(
							'sent' => 3
						));
						$rStatus = $responce['error'];
					}

				} else {
					self::updatePreparedData($data['id'], array(
						'sent' => 2,
						'sent_time' => date('Y-m-d H:i:s')
					));
				}
			}
		}

		return $rStatus;
	}

	public static function sendEmailMsg($data) {
		$env_mode = self::getEnvMode();
		$email_direct = isset($data['to_user']) ? $data['to_user'] : "";
		$attachmentsList = isset($data['attachments']) ? unserialize($data['attachments']) : "";

		if($env_mode == "test") {
			$email_direct = self::getTestModeValue("email");
		}
		
		if(!empty($email_direct)) {
			try {

				$message_list = new \wmt\Options('Reminder_Email_Messages');
				//$subject = $message_list->getItem($data['template_id']);

				$appid = @self::getDataAppId($data);
				$event_datetime = @self::getDataAppDate($data);
				$subject = @self::getSubject($data['pid'], $message_list, $data['template_id'], $event_datetime, $appid);

				$email_data = array(
					'patient' => $email_direct,
					'from' => isset($GLOBALS['EMAIL_SEND_FROM']) ? $GLOBALS['EMAIL_SEND_FROM'] : 'PATIENT SUPPORT',
					'subject' => $subject,
					'email' => $email_direct,
					'html' => $data['message'],
					'text' => $data['message'],
					'message_content' => $data['message']
				);

				$emailObj = new \wmt\Email(TRUE);
				$emailObj->FromName = $GLOBALS['EMAIL_FROM_NAME'];

				foreach ($attachmentsList as $k => $attachmentItem) {
					$emailObj->AddAttachment($attachmentItem['path'], $attachmentItem['name']);
				}

				// Send email
				$status = @$emailObj->TransmitEmail($email_data);
				//EmailMessage::setTimeZone();

			} catch (Exception $e) {
				$status = $e->getMessage();
			}

			$isActive = EmailMessage::isActive($status);

			if($isActive === false) {

				self::updatePreparedIntMsgData($data['id'], array(
					'sent' => 1,
					'sent_time' => date('Y-m-d H:i:s')
				));

				return true;
			} else {
				self::updatePreparedIntMsgData($data['id'], array(
					'sent' => 3
				));
			}

		} else {
			self::updatePreparedData($data['id'], array(
				'sent' => 2,
				'sent_time' => date('Y-m-d H:i:s')
			));
		}

		return false;
	}

	/*get environment mode*/
	public static function getEnvMode() {
		global $GLOBALS_NTF;
		return isset($GLOBALS_NTF['mode']) ? $GLOBALS_NTF['mode'] : "test";
	}

	public static function getTestModeValue($type = false) {
		global $GLOBALS_NTF;

		if($type == "email" && isset($GLOBALS_NTF['email']) && !empty($GLOBALS_NTF['email'])) {
			return $GLOBALS_NTF['email'];
		} else if($type == "phone" && isset($GLOBALS_NTF['phone']) && !empty($GLOBALS_NTF['phone'])) {
			return $GLOBALS_NTF['phone'];
		} else if($type == "fax" && isset($GLOBALS_NTF['fax_number']) && !empty($GLOBALS_NTF['fax_number'])) {
			return $GLOBALS_NTF['fax_number'];
		} else if($type == "postal_letter" && isset($GLOBALS_NTF['postal_address']) && !empty($GLOBALS_NTF['postal_address']) && is_array($GLOBALS_NTF['postal_address'])) {
			return $GLOBALS_NTF['postal_address'];
		}

		return false;
	}

	public static function handleZoomMeetingCreation($data, $config) {
		foreach ($data as $key => $item) {
			if($item['tablename'] == "openemr_postcalendar_events" && !empty($item['uniqueid'])) {
				ZoomIntegration::handleZoomApptEvent($item['uniqueid'], '', array(), true, true);
			}
		}
	}

	public function prepareInternalNotificationData($prepareFor = 'both', $eventid_param = '', $configid_param = '') {
		$exceptionList = array();

		try {
			$configs = self::getActionConfigurationByParam(array(
				'id' => $eventid_param,
				'config_id' => $configid_param
			));
			$totalPreparedItem = 0;
			$preparedItemStatus = array();
			
			if(isset($configs)) {
				foreach ($configs as $key => $action_config) {
					foreach ($action_config['config_data'] as $key => $config_data) {
						try {
							$config = array_merge($action_config, $action_config['config_data'][$key]);
							$event_id = $config['event_id'];

							if(isset($config['configuration_id']) && !empty($config['configuration_id']) && $config['active'] == "0" && !empty($config['id'])) {
								if($config['action_type'] == "internal_messaging"){
									//Handle Internal Messaging Prepare
									try {
										$totalPreparedInternalMessagingItem = self::prepareInternalMessagingData($prepareFor, $config_data, $config);

										if(isset($totalPreparedInternalMessagingItem) && $totalPreparedInternalMessagingItem > 0) {
											$totalPreparedItem = $totalPreparedItem + $totalPreparedInternalMessagingItem;

											//Prepare Item Status
											$preparedItemStatus = self::prepareItemStatus($config, $preparedItemStatus);
										}
									} catch(\Exception $e) {
										$exceptionList[] = 'Internal Messaging Error: ' . $e->getMessage();
									}
								}
							}
						} catch(\Exception $e) {
							$exceptionList[] = 'Config Error: ' . $e->getMessage();
						}
					}
				}
			}
		} catch(\Exception $e) {
			//Exception
			$exceptionList[] = 'Main Error: ' . $e->getMessage();
		}

		return array('total_prepared_item' => $totalPreparedItem, 'prepared_item_status' => $preparedItemStatus, 'exceptionList' => $exceptionList);
	}

	public static function prepareNotificationData($prepareFor = 'both', $eventid_param = '', $configid_param = '') {
		$exceptionList = array();

		try {
			$configs = self::getActionConfigurationByParam(array(
				'id' => $eventid_param,
				'config_id' => $configid_param
			));
			$totalPreparedItem = 0;
			$preparedItemStatus = array();
			
			if(isset($configs)) {
				foreach ($configs as $key => $action_config) {
					foreach ($action_config['config_data'] as $key => $config_data) {
						try {
							$config = array_merge($action_config, $action_config['config_data'][$key]);
							$event_id = $config['event_id'];

							if(isset($config['configuration_id']) && !empty($config['configuration_id']) && $config['active'] == "0" && !empty($config['id'])) {
								if($config['action_type'] == "messaging") {
									try {
										//Handle Messaging Prepare
										if($config['as_trigger_type'] == "time" && ($prepareFor == 'both' || $prepareFor == 'time')) {
											if(isset($config['data_set']) && !empty($config['data_set'])) {
												$dataSetQtr = trim($config['data_set']);

												$resultItems = array();
												$result = sqlStatementNoLogExecute($dataSetQtr);
												if($result) {
													while ($result_data = sqlFetchArray($result)) {
														$resultItems[] = $result_data;

														$pc_id = isset($result_data['pid']) ? $result_data['pid'] : "";
														$tablename = isset($result_data['tablename']) ? $result_data['tablename'] : "";
														$uniqueid = isset($result_data['uniqueid']) ? $result_data['uniqueid'] : "";
														$qtr_trigger_time = isset($result_data['trigger_time']) ? $result_data['trigger_time'] : "";

														//Handle Zoom
														@self::handleZoomMeetingCreation(array($result_data), $config);

														$appid = false;
														if($tablename == "openemr_postcalendar_events") {
															$appid = $result_data['uniqueid'];
														}
														
														if(!empty($pc_id) && !empty($tablename) && !empty($uniqueid)) {
															$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
															$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";


															$isExists = self::isNotifyDataExists($event_id, $pc_id, $tablename, $uniqueid, '0', $config['communication_type'], $config['id']);

															if($isExists === false) {

																//Calculate Trigger Time
																$triggerTime = self::calTriggerTime($config);
																$current_unix_time = strtotime('now');

																if(!empty($qtr_trigger_time) && $current_unix_time < strtotime($qtr_trigger_time)) {
																	$triggerTime = date('Y-m-d H:i:s', strtotime($qtr_trigger_time));
																}

																$event_datetime = isset($triggerTime) ? $triggerTime : false;

																$messageData = self::getFullMessage($pc_id, $config['notification_template'], $event_datetime, $appid);

																$message_text = '';
																if($config['communication_type'] == 'sms' || $config['communication_type'] == 'internalmessage') {
																	$message_text = isset($messageData['content']) ? $messageData['content'] : "";
																} else {
																	$message_text = isset($messageData['content_html']) ? $messageData['content_html'] : "";
																}

																if(!empty($triggerTime)) {
																	$preparedData = array(
																		'event_id' => $event_id,
																		'config_id' => $config['id'],
																		'event_type' => '1',
																		'msg_type' => $config['communication_type'],
																		'template_id' => $config['notification_template'],
																		'message' => $message_text,
																		'tablename' => $tablename,
																		'uniqueid' => $uniqueid,
																		'pid' => $pc_id,
																		'uid' => $uid,
																		'user_type' => $user_type,
																		'sent' => '0',
																		'sent_time' => '',
																		'trigger_time' => $triggerTime,
																		'time_delay' => $config['time_delay']
																	);

																	self::savePreparedData($preparedData);
																	$totalPreparedItem++;

																	$preparedItemStatus = self::prepareItemStatus($config, $preparedItemStatus);
																}
															}
														}
													}
												}
											}
										} else if($config['as_trigger_type'] == "event" && ($prepareFor == 'both' || $prepareFor == 'event')) {
											$resultData = self::getEventTriggerData($event_id, $config['id']);
											
											//Handle Zoom
											@self::handleZoomMeetingCreation($resultData, $config);
											
											$updateStatus = self::prepareDataForUpdate($resultData, $config);
											
											if(isset($updateStatus) && $updateStatus > 0) {
												$totalPreparedItem = $totalPreparedItem + $updateStatus;
												
												//Prepare Item Status
												$preparedItemStatus = self::prepareItemStatus($config, $preparedItemStatus);
											}
										}
									} catch(\Exception $e) {
										$exceptionList[] = 'Messaging Error: ' . $e->getMessage();
									}
								} else if($config['action_type'] == "internal_messaging"){
									//Handle Internal Messaging Prepare
									/*
									try {
										$totalPreparedInternalMessagingItem = self::prepareInternalMessagingData($prepareFor, $config_data, $config);

										if(isset($totalPreparedInternalMessagingItem) && $totalPreparedInternalMessagingItem > 0) {
											$totalPreparedItem = $totalPreparedItem + $totalPreparedInternalMessagingItem;

											//Prepare Item Status
											$preparedItemStatus = self::prepareItemStatus($config, $preparedItemStatus);
										}
									} catch(\Exception $e) {
										$exceptionList[] = 'Internal Messaging Error: ' . $e->getMessage();
									}
									*/
								} else if($config['action_type'] == "api") {
									//Handle API Prepare
									try {
										$totalPreparedApiItems = self::prepareApiData($prepareFor, $config_data, $config);
										
										if(isset($totalPreparedApiItems) && $totalPreparedApiItems > 0) {
											$totalPreparedItem = $totalPreparedItem + $totalPreparedApiItems;

											//Prepare Item Status
											$preparedItemStatus = self::prepareItemStatus($config, $preparedItemStatus);
										}
									} catch(\Exception $e) {
										$exceptionList[] = 'API Error: ' . $e->getMessage();
									}
								}
							}
						} catch(\Exception $e) {
							$exceptionList[] = 'Config Error: ' . $e->getMessage();
						}
					}
				}
			}
		} catch(\Exception $e) {
			//Exception
			$exceptionList[] = 'Main Error: ' . $e->getMessage();
		}

		return array('total_prepared_item' => $totalPreparedItem, 'prepared_item_status' => $preparedItemStatus, 'exceptionList' => $exceptionList);
	}

	public static function prepareInternalMessagingData($prepareFor, $config_data, $config) {
		$totalPreparedItem = 0;
		$event_id = $config['event_id'];

		if($config['as_trigger_type'] == "time" && ($prepareFor == 'both' || $prepareFor == 'time')) {
			if(isset($config['data_set']) && !empty($config['data_set'])) {
				$dataSetQtr = trim($config['data_set']);
				$to_user = explode(",", trim($config['to_user']));

				$resultItems = array();
				$result = sqlStatementNoLogExecute($dataSetQtr);
				while ($result_data = sqlFetchArray($result)) {
					$resultItems[] = $result_data;
				}

				$pc_id = "";
				$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
				$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";


				$isExists = self::isNotifyIntMessagingDataExists($event_id,'0', $config['communication_type'], $config['id']);

				if($isExists === false) {
					//Calculate Trigger Time
					$triggerTime = self::calTriggerTime($config);
					$current_unix_time = strtotime('now');

					$event_datetime = isset($triggerTime) ? $triggerTime : false;

					$messageData =self::getFullMessage($pc_id, $config['notification_template'], $event_datetime, '');

					$message_text = '';
					if($config['communication_type'] == 'sms' || $config['communication_type'] == 'internalmessage') {
						$message_text = isset($messageData['content']) ? $messageData['content'] : "";
					} else {
						$message_text = isset($messageData['content_html']) ? $messageData['content_html'] : "";
					}

					if(!empty($triggerTime)) {
						$headerArray = !empty($resultItems) ? array_keys($resultItems[0]) : array();

						$dataHeaderArray = array();
						foreach ($headerArray as $key => $value) {
							$dataHeaderArray[$value] = $value;
						}

						$finalData = array_merge(array($dataHeaderArray),$resultItems);

						$files = self::saveSourceDataFile($finalData);
						$filesList = serialize(array($files));

						foreach ($to_user as $tui => $toUser) {
							$preparedData = array(
								'event_id' => $event_id,
								'config_id' => $config['id'],
								'event_type' => '1',
								'msg_type' => $config['communication_type'],
								'template_id' => $config['notification_template'],
								'message' => $message_text,
								'attachments' => $filesList,
								'tablename' => "",
								'uniqueid' => "",
								'pid' => "",
								'uid' => $uid,
								'to_user' => $toUser,
								'user_type' => $user_type,
								'sent' => '0',
								'sent_time' => '',
								'trigger_time' => $triggerTime,
								'time_delay' => $config['time_delay']
							);

							self::savePreparedIntMessageData($preparedData);
							$totalPreparedItem++;
						}
					}
				}
			}
		} else if($config['as_trigger_type'] == "event" && ($prepareFor == 'both' || $prepareFor == 'event')) {
			$resultData = self::getEventIntMsgTriggerData($event_id, $config['id']);

			$updateStatus = self::prepareDataForIntMsgUpdate($resultData, $config);
					
			if(isset($updateStatus) && $updateStatus > 0) {
				$totalPreparedItem = $totalPreparedItem + $updateStatus;
			}
		}

		return $totalPreparedItem;
	}

	public static function prepareApiData($prepareFor, $config_data, $config) {
		$totalPreparedItem = 0;
		$event_id = $config['event_id'];

		if($config['as_trigger_type'] == "time" && ($prepareFor == 'both' || $prepareFor == 'time')) {
			if(isset($config['data_set']) && !empty($config['data_set'])) {
				$dataSetQtr = trim($config['data_set']);

				$resultItems = array();
				$result = sqlStatementNoLogExecute($dataSetQtr);
				if($result) {
					while ($result_data = sqlFetchArray($result)) {
						$resultItems[] = $result_data;

						$pc_id = isset($result_data['pid']) ? $result_data['pid'] : "";
						$tablename = isset($result_data['tablename']) ? $result_data['tablename'] : "";
						$uniqueid = isset($result_data['uniqueid']) ? $result_data['uniqueid'] : "";
						$qtr_trigger_time = isset($result_data['trigger_time']) ? $result_data['trigger_time'] : "";

						$appid = false;
						if($tablename == "openemr_postcalendar_events") {
							$appid = $result_data['uniqueid'];
						}
						
						if(!empty($pc_id) && !empty($tablename) && !empty($uniqueid)) {
							$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
							$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";

							$isExists = self::isNotifyApiDataExists($event_id, $pc_id, $tablename, $uniqueid, '0', $config['communication_type'], $config['id']);

							if($isExists === false) {

								//Calculate Trigger Time
								$triggerTime = self::calTriggerTime($config);
								$current_unix_time = strtotime('now');

								if(!empty($qtr_trigger_time) && $current_unix_time < strtotime($qtr_trigger_time)) {
									$triggerTime = date('Y-m-d H:i:s', strtotime($qtr_trigger_time));
								}

								$event_datetime = isset($triggerTime) ? $triggerTime : false;

								$messageData = self::getFullMessage($pc_id, $config['notification_template'], $event_datetime, $appid);

								$message_text = '';
								if($config['communication_type'] == 'sms' || $config['communication_type'] == 'internalmessage') {
									$message_text = isset($messageData['content']) ? $messageData['content'] : "";
								} else {
									$message_text = isset($messageData['content_html']) ? $messageData['content_html'] : "";
								}

								if(!empty($triggerTime)) {
									$preparedData = array(
										'event_id' => $event_id,
										'config_id' => $config['id'],
										'event_type' => '1',
										'msg_type' => $config['communication_type'],
										'template_id' => $config['notification_template'],
										'message' => $message_text,
										'tablename' => $tablename,
										'uniqueid' => $uniqueid,
										'pid' => $pc_id,
										'uid' => $uid,
										'user_type' => $user_type,
										'sent' => '0',
										'sent_time' => '',
										'trigger_time' => $triggerTime,
										'time_delay' => $config['time_delay']
									);

									self::savePreparedApiData($preparedData);
									$totalPreparedItem++;
								}
							}
						}
					}
				}
			}
		} else if($config['as_trigger_type'] == "event" && ($prepareFor == 'both' || $prepareFor == 'event')) {
			$resultData = self::getApiEventTriggerData($event_id, $config['id']);	
			$updateStatus = self::prepareApiDataForUpdate($resultData, $config);
			
			if(isset($updateStatus) && $updateStatus > 0) {
				$totalPreparedItem = $totalPreparedItem + $updateStatus;
			}
		}

		return $totalPreparedItem;
	}

	public static function saveSourceDataFile($data = array(), $filename = 'data_set_results.csv') {
		/*Save File*/
		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
		$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_". $filename;

		// Open a file in write mode ('w')
		$fp = fopen($fullfilename, 'w');

		// Loop through file pointer and a line
		foreach ($data as $fields) {
		    fputcsv($fp, $fields);
		}
		  
		fclose($fp);

		return array(
        	'path' => $fullfilename,
        	'name' => $filename
        );
	}

	public static function checkIsAppointment($id = false) {
		// create empty record or retrieve
		if (!$id) return false;

		// retrieve data
		$query = "SELECT * FROM `openemr_postcalendar_events` WHERE `pc_eid` = ?";
		$binds = array($id);
		$data = sqlQueryNoLog($query, $binds);

		if ($data && $data['pc_eid']) {
			return true;
		} else {
			return false;
		}
	}

	public static function prepareDataForUpdate($data, $config) {
		$updateCount = 0;

		if(!empty($data)) {
			$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
			$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";

			foreach ($data as $key => $item) {
				$event_datetime = isset($item['trigger_time']) ? $item['trigger_time'] : false;
				$unix_trigger_time = strtotime($item['trigger_time']);
				$current_unix_time = strtotime('now');
				$uniqueid = strtotime($item['uniqueid']);

				$appid = false;
				$appStatus = true;
				if($item['tablename'] == "openemr_postcalendar_events") {
					$appid = $item['uniqueid'];
				}

				if($item['tablename'] == "openemr_postcalendar_events" && $appid !== false){
					$appStatus = self::checkIsAppointment($appid);
				}

				if($appStatus === true) {
	 				$messageData = self::getFullMessage($item['pid'], $config['notification_template'], $event_datetime, $appid);
					$message_text = '';
					if($config['communication_type'] == 'sms' || $config['communication_type'] == 'internalmessage') {
						$message_text = isset($messageData['content']) ? $messageData['content'] : "";
					} else {
						$message_text = isset($messageData['content_html']) ? $messageData['content_html'] : "";
					}
				} else {
					$message_text = '';
				}

				//Calculate Trigger Time
				//$triggerTime = self::calTriggerTime($config);

				$preparedData = array(
					'msg_type' => $config['communication_type'],
					'event_type' => '2',
					'template_id' => $config['notification_template'],
					'message' => $message_text,
					'uid' => $uid,
					'user_type' => $user_type,
					'time_delay' => $config['time_delay']
				);

				if($appStatus === false) {
					$preparedData['sent'] = '3';
				}

				if($item['sent'] == 0 && $unix_trigger_time < $current_unix_time) {
					//$preparedData['trigger_time'] = $triggerTime;
					//Update Data
					self::updatePreparedData($item['id'], $preparedData);
					$updateCount++;
				} else {
					//Update Data
					self::updatePreparedData($item['id'], $preparedData);
					$updateCount++;
				}
			}
		}

		return $updateCount;
	}

	public static function prepareDataForIntMsgUpdate($data, $config) {
		$updateCount = 0;

		if(!empty($data)) {
			$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
			$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";

			$dataSetQtr = trim($config['data_set']);

			$resultItems = array();
			$result = sqlStatementNoLog($dataSetQtr);
			while ($result_data = sqlFetchArray($result)) {
				$resultItems[] = $result_data;
			}

			$headerArray = array_keys($resultItems[0]);

			$dataHeaderArray = array();
			foreach ($headerArray as $key => $value) {
				$dataHeaderArray[$value] = $value;
			}

			$finalData = array_merge(array($dataHeaderArray),$resultItems);

			$files = self::saveSourceDataFile($finalData);
			$filesList = serialize(array($files));

			foreach ($data as $key => $item) {
				$event_datetime = isset($item['trigger_time']) ? $item['trigger_time'] : false;
				$unix_trigger_time = strtotime($item['trigger_time']);
				$current_unix_time = strtotime('now');

				
 				$messageData = self::getFullMessage('', $config['notification_template'], $event_datetime);
				$message_text = '';
				if($config['communication_type'] == 'sms' || $config['communication_type'] == 'internalmessage') {
					$message_text = isset($messageData['content']) ? $messageData['content'] : "";
				} else {
					$message_text = isset($messageData['content_html']) ? $messageData['content_html'] : "";
				}



				$preparedData = array(
					'msg_type' => $config['communication_type'],
					'event_type' => '2',
					'template_id' => $config['notification_template'],
					'message' => $message_text,
					'attachments' => $filesList,
					'uid' => $uid,
					'user_type' => $user_type,
					'time_delay' => $config['time_delay']
				);

				if($item['sent'] == 0 && $unix_trigger_time < $current_unix_time) {
					//Update Data
					self::updatePreparedIntMsgData($item['id'], $preparedData);
					$updateCount++;
				} else {
					//Update Data
					self::updatePreparedIntMsgData($item['id'], $preparedData);
					$updateCount++;
				}
			}
		}

		return $updateCount;
	}

	public static function prepareApiDataForUpdate($data, $config) {
		$updateCount = 0;

		if(!empty($data)) {
			$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
			$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";

			foreach ($data as $key => $item) {
				$event_datetime = isset($item['trigger_time']) ? $item['trigger_time'] : false;
				$unix_trigger_time = strtotime($item['trigger_time']);
				$current_unix_time = strtotime('now');
				$uniqueid = strtotime($item['uniqueid']);

				$appid = false;
				$appStatus = true;
				if($item['tablename'] == "openemr_postcalendar_events") {
					$appid = $item['uniqueid'];
				}

				if($item['tablename'] == "openemr_postcalendar_events" && $appid !== false){
					$appStatus = self::checkIsAppointment($appid);
				}

				if($appStatus === true) {
	 				$messageData = self::getFullMessage($item['pid'], $config['notification_template'], $event_datetime, $appid);
					$message_text = '';
					if($config['communication_type'] == 'sms' || $config['communication_type'] == 'internalmessage') {
						$message_text = isset($messageData['content']) ? $messageData['content'] : "";
					} else {
						$message_text = isset($messageData['content_html']) ? $messageData['content_html'] : "";
					}
				} else {
					$message_text = '';
				}

				//Calculate Trigger Time
				//$triggerTime = self::calTriggerTime($config);

				$preparedData = array(
					'msg_type' => $config['communication_type'],
					'event_type' => '2',
					'template_id' => $config['notification_template'],
					'message' => $message_text,
					'uid' => $uid,
					'user_type' => $user_type,
					'time_delay' => $config['time_delay']
				);

				if($appStatus === false) {
					$preparedData['sent'] = '3';
				}

				if($item['sent'] == 0 && $unix_trigger_time < $current_unix_time) {
					//$preparedData['trigger_time'] = $triggerTime;
					//Update Data
					self::updatePreparedApiData($item['id'], $preparedData);
					$updateCount++;
				} else {
					//Update Data
					self::updatePreparedApiData($item['id'], $preparedData);
					$updateCount++;
				}
			}
		}

		return $updateCount;
	}

	public static function updatePreparedData($id, $data) {
		if(!empty($data) && !empty($id)) {
			$binds = array();
			$setColsList = array();

			foreach ($data as $ind => $item) {
				$setColsList[] = $ind." = ?";
				$binds[] = $item;
			}

			$setStr = implode(", ", $setColsList);
			$binds[] = $id;

			if(!empty($setStr)) {
				sqlStatementNoLog("UPDATE `notif_log` SET ".$setStr." WHERE id = ?", $binds);
			}
		}
	}

	public static function updatePreparedIntMsgData($id, $data) {
		if(!empty($data) && !empty($id)) {
			$binds = array();
			$setColsList = array();

			foreach ($data as $ind => $item) {
				$setColsList[] = $ind." = ?";
				$binds[] = $item;
			}

			$setStr = implode(", ", $setColsList);
			$binds[] = $id;

			if(!empty($setStr)) {
				sqlStatementNoLog("UPDATE `vh_internal_messaging_notif_log` SET ".$setStr." WHERE id = ?", $binds);
			}
		}
	}

	public static function updatePreparedApiData($id, $data) {
		if(!empty($data) && !empty($id)) {
			$binds = array();
			$setColsList = array();

			foreach ($data as $ind => $item) {
				$setColsList[] = $ind." = ?";
				$binds[] = $item;
			}

			$setStr = implode(", ", $setColsList);
			$binds[] = $id;

			if(!empty($setStr)) {
				sqlStatementNoLog("UPDATE `vh_api_notif_log` SET ".$setStr." WHERE id = ?", $binds);
			}
		}
	}

	public function loadAppointment($id = false) {
		if (!$id) return false;

		$query = "SELECT *, concat(pc_eventDate, ' ', pc_startTime) as event_datetime FROM `openemr_postcalendar_events` WHERE `pc_eid` = ? ";
		$binds = array($id);

		$row = sqlQuery($query, $binds);
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function getEventTriggerData($event_id = '', $config_id = '') {
		$sql = "SELECT * FROM `notif_log` WHERE event_id = ? AND config_id = ? AND (template_id IS NULL OR template_id = '') ";

		$resultItems = array();
		$result = sqlStatementNoLog($sql, array($event_id, $config_id));
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	public static function getEventIntMsgTriggerData($event_id = '', $config_id = '') {
		$sql = "SELECT * FROM `vh_internal_messaging_notif_log` WHERE event_id = ? AND config_id = ? AND (template_id IS NULL OR template_id = '') ";

		$resultItems = array();
		$result = sqlStatementNoLog($sql, array($event_id, $config_id));
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	public static function getApiEventTriggerData($event_id = '', $config_id = '') {
		$sql = "SELECT * FROM `vh_api_notif_log` WHERE event_id = ? AND config_id = ? AND (template_id IS NULL OR template_id = '') ";

		$resultItems = array();
		$result = sqlStatementNoLog($sql, array($event_id, $config_id));
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	public static function isNotifyApiDataExists($event_id, $pid, $tablename, $uniqueid, $send_status, $communication_type, $config_id) {
		$row = sqlQuery("SELECT * FROM `vh_api_notif_log` WHERE event_id = ? AND config_id = ? AND pid = ? AND tablename = ? AND uniqueid = ? AND sent = ? AND msg_type = ? ", array($event_id, $config_id, $pid, $tablename, $uniqueid, $send_status, $communication_type));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function isNotifyIntMessagingDataExists($event_id, $send_status, $communication_type, $config_id) {
		$row = sqlQuery("SELECT * FROM `vh_internal_messaging_notif_log` WHERE event_id = ? AND config_id = ? AND sent = ? AND msg_type = ? ", array($event_id, $config_id, $send_status, $communication_type));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function isNotifyDataExists($event_id, $pid, $tablename, $uniqueid, $send_status, $communication_type, $config_id) {
		$row = sqlQuery("SELECT * FROM `notif_log` WHERE event_id = ? AND config_id = ? AND pid = ? AND tablename = ? AND uniqueid = ? AND sent = ? AND msg_type = ? ", array($event_id, $config_id, $pid, $tablename, $uniqueid, $send_status, $communication_type));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	/*Get Patient Data*/
	public static function getPatientData($pid, $given = "*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS"){
	    $sql = "select $given from patient_data where pid=? order by date DESC limit 0,1";
	    return sqlQuery($sql, array($pid));
	}

	public static function getFullMessage($pid, $template, $appt_date = false, $eid = false) {
		$pat_data = self::getPatientData($pid);

		$pc_aid = false;
		$pc_facility = false;
		
		if($eid !== false) {
			$appt = @new \wmt\Appt($eid);
			$pc_aid = $appt->pc_aid;
			$pc_facility = $appt->pc_facility;
		}

		// Get message template
		$template = \wmt\Template::Lookup($template, $pat_data['language']);

		// Fetch merge data
		$data = @new \wmt\Grab($pat_data['language']);
		@$data->loadData($pid, $pc_aid, $pc_facility, $eid, $appt_date, $eid);

		// Perform data merge
		$template->Merge($data->getData());
		$content = $template->text_merged;
		$content_html = $template->html_merged;

		return array('content' => $content, 'content_html' => $content_html);
	}

	public static function getSubject($pid, $message_list, $template = '', $appt_date = false, $eid = false) {
		$subject = '';
		if(!empty($message_list) && isset($message_list->list[$template])) {
			$subject = $message_list->list[$template]['notes'];
		}

		if(!empty($subject)) {
			$pat_data = self::getPatientData($pid);

			$pc_aid = false;
			$pc_facility = false;
			
			if($eid !== false) {
				$appt = @new \wmt\Appt($eid);
				$pc_aid = $appt->pc_aid;
				$pc_facility = $appt->pc_facility;
			}

			// Get message template
			$template = \wmt\Template::Lookup($template, $pat_data['language']);

			// Fetch merge data
			$data = @new \wmt\Grab($pat_data['language']);
			@$data->loadData($pid, $pc_aid, $pc_facility, $eid, $appt_date, $eid);

			// Perform data merge
			$subject = $template->MergeText($data->getData(), $subject);
		}

		return $subject;
	}

	public static function getDataAppId($data) {
		$appid = false;
		if(!empty($data) && isset($data['tablename']) && $data['tablename'] == "openemr_postcalendar_events") {
			$appid = $data['uniqueid'];
		}

		return $appid;
	}

	public static function getDataAppDate($data) {
		return !empty($data) && isset($data['trigger_time']) ? $data['trigger_time'] : false;	
	}

	public static function calTriggerTime($config) {
		$trigger_time = '';
		if(isset($config['trigger_type']) && isset($config['time_trigger_data'])) {
			$timeObj = json_decode($config['time_trigger_data'], true);

			if($timeObj['time_type'] == "one_time") {
				$trigger_time = self::generateOneTime($timeObj);
			} else if($timeObj['time_type'] == "every_minute") {
				$trigger_time = self::generateEveryMinuteTime($timeObj);
			} else if($timeObj['time_type'] == "daily") {
				$trigger_time = self::generateDailyTime($timeObj);
			} else if($timeObj['time_type'] == "weekly") {
				$trigger_time = self::generateWeeklyTime($timeObj);
			} else if($timeObj['time_type'] == "monthly") {
				$trigger_time = self::generateMonthlyTime($timeObj);
			}
		}

		return $trigger_time;
	}

	public static function generateOneTime($config) {
		$date_time = isset($config['one_time']['date_time']) ? $config['one_time']['date_time'] : "";

		$trigger_time = "";	
		if(!empty($date_time)) {
			$trigger_time = (strtotime("now") < strtotime($date_time)) ? $date_time : "";
		}

		return $trigger_time;
	}

	public static function generateEveryMinuteTime($config) {
		$minutes = isset($config['every_minute']['minutes']) ? $config['every_minute']['minutes'] : "";

		$trigger_time = "";	
		if(!empty($minutes)) {
			$trigger_time = !empty($minutes) ? date('Y-m-d H:i:s', strtotime('+'.$minutes.' minutes')) : "";
		}

		return $trigger_time;
	}

	public static function generateDailyTime($config) {
		$time = isset($config['daily']['time']) ? $config['daily']['time'] : "";
			
		$trigger_time = "";	
		if(!empty($time)) {
			$date_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d').' '.$time));
			
			if(strtotime("now") > strtotime($date_time)) {
				$date_time = date('Y-m-d H:i:s', strtotime($date_time.'+1 day'));
			}

			$trigger_time = isset($date_time) ? $date_time : "";
		}

		return $trigger_time;
	}

	public static function generateWeeklyTime($config) {
		$time = isset($config['weekly']['time']) ? $config['weekly']['time'] : "";
		$days = isset($config['weekly']['day']) ? $config['weekly']['day'] : "";
		$unixNow = strtotime('now');
		$currentDay = strtolower(date('l', $unixNow));

		$days_timestamp = array();

		$trigger_time = "";
		if(!empty($days) && !empty($time)) {
			$tmpUnix = date('Y-m-d', $unixNow);
			$tmpUnix = strtotime($tmpUnix.' '.$time);

			if(isset($days[$currentDay]) && !empty($days[$currentDay]) && $unixNow < $tmpUnix) {
				$days_timestamp = array($unixNow);
			} else {
				foreach ($days as $key => $day) {
					$days_timestamp[] = strtotime('next '.$key);
				}
			}

			$nextDay = date('Y-m-d', min($days_timestamp));
			$date_time = date('Y-m-d H:i:s', strtotime($nextDay.' '.$time));
			$trigger_time = $date_time;
		}

		return $trigger_time;
	}

	public static function generateMonthlyTime($config) {
		$time = isset($config['monthly']['time']) ? $config['monthly']['time'] : "";
		$day = isset($config['monthly']['day']) ? $config['monthly']['day'] : "";
		$months = isset($config['monthly']['month']) ? $config['monthly']['month'] : "";

		$months_timestamp = array();

		$trigger_time = "";
		if(!empty($day) && !empty($time) && !empty($months)) {
			foreach ($months as $key => $month) {
				$months_timestamp[] = strtotime(self::getNextUpcomingMonth($month, $day, $time));
			}

			$trigger_time = date('Y-m-d H:i:s', min($months_timestamp));
		}

		return $trigger_time;
	}

	public static function getNextUpcomingMonth($month = 'January', $day = 1, $time = '00.00') {
		$currentYearMonth  = date('Y-m', strtotime("now"));
		$currentDateTime  = $currentYearMonth.'-'.$day.' '.$time;
		$currentMonth  = date('m', strtotime("now"));

		$tempMonth = date('m', strtotime($month));
		$tempM = ($tempMonth-$currentMonth);

		$tmpUnixTimeStamp = strtotime("+".$tempM." month", strtotime($currentDateTime));

		$plusMonth = ($tempM >= 0 && strtotime("now") < $tmpUnixTimeStamp) ? $tempM : (12-abs($tempM));

		$final = date("Y-m-d H:i:s", strtotime("+".$plusMonth." month", strtotime($currentDateTime)));

		return $final;
	}

	/*Load Appointments*/
	public static function loadAppointments($ids = false) {
		if (!$ids) return false;

		$query = "SELECT *, concat(pc_eventDate, ' ', pc_startTime) as event_datetime FROM `openemr_postcalendar_events` WHERE `pc_eid` IN (".$ids.") ";
		$binds = array();

		$row = sqlStatementNoLog($query, $binds);
		$results = array();
							
		if(isset($row) && !empty($row)) {
			while ($resultData = sqlFetchArray($row)) {
				$results[] = $resultData;
			}
		}

		return $results;
	}

	/*Transmit Email*/
	public static function transmitEmail($data) {
		if(!empty($data['pid'])) {
			$pat_data = self::getPatientData($data['pid']);

			// preformat commonly used data elements
			$pat_name = ($pat_data['title'])? $pat_data['title'] : "";
			$pat_name .= ($pat_data['fname'])? $pat_data['fname'] : "";
			$pat_name .= ($pat_data['mname'])? substr($pat_data['mname'],0,1).". " : "";
			$pat_name .= ($pat_data['lname'])? $pat_data['lname'] : "";

			if(!empty($pat_data)) {
				$messaging_enabled = ($pat_data['hipaa_allowemail'] != 'YES' || (empty($pat_data['email']) && !$GLOBALS['wmt::use_email_direct']) || (empty($pat_data['email_direct']) && $GLOBALS['wmt::use_email_direct'])) ? true : false;

				$email_direct = $GLOBALS['wmt::use_email_direct'] ? $pat_data['email_direct'] : $pat_data['email'];

				$env_mode = self::getEnvMode();
				if($env_mode == "test") {
					$email_direct = self::getTestModeValue("email");
				}
				
				if($email_direct === false) {
					return false;
				}

				if($messaging_enabled === false) {
					try {

						$message_list = new \wmt\Options('Email_Messages');
						//$subject = $message_list->getItem($data['template_id']);

						$appid = isset($data['uniqueid']) && !empty($data['uniqueid']) ? $data['uniqueid'] : false;
						$event_datetime = isset($data['event_datetime']) && !empty($data['event_datetime']) ? $data['event_datetime'] : false;
						$subject = @self::getSubject($data['pid'], $message_list, $data['template_id'], $event_datetime, $appid);

						$eItem = array(
						'pid' => $data['pid'],
						'data' => array(
							'from' => isset($GLOBALS['EMAIL_SEND_FROM']) ? $GLOBALS['EMAIL_SEND_FROM'] : 'PATIENT SUPPORT',
							'email' => $email_direct,
							'template' => $data['template_id'],
							'subject' => $subject,
							'patient' => $pat_name,
							'html' => $data['message'],
							'text' => $data['message'],
							'request_data' => array(),
							'files' => array(),
						));

						$eData = EmailMessage::TransmitEmail(
							array($eItem['data']), 
							array('pid' => $eItem['pid'], 'logMsg' => true)
						);

						if(is_array($eData) && count($eData) == 1) {
							$responce = $eData[0];
						} else {
							throw new \Exception("Something went wrong.");
						}

						// $email_data = array(
						// 	'patient' => $pat_name,
						// 	'from' => isset($GLOBALS['EMAIL_SEND_FROM']) ? $GLOBALS['EMAIL_SEND_FROM'] : 'PATIENT SUPPORT',
						// 	'subject' => $subject,
						// 	'email' => $email_direct,
						// 	'html' => $data['message'],
						// 	'text' => $data['message'],
						// 	'message_content' => $data['message']
						// );

						// $emailObj = new \wmt\Email(TRUE);
						// $emailObj->FromName = $GLOBALS['EMAIL_FROM_NAME'];

						// // Send email
						// $status = @$emailObj->TransmitEmail($email_data);
						// //EmailMessage::setTimeZone();

					} catch (Exception $e) {
						$status = $e->getMessage();
					}

					//$isActive = EmailMessage::isActive($status);

					if(isset($responce) && isset($responce['status']) && $responce['status'] == true){

						// $email_data['pid'] = $data['pid'];
						// $email_data['request'] = array(
						// 	'message' => $email_data['message_content'],
						// 	'pid' => $email_data['pid'],
						// 	'email_id' => $email_data['email'], 
						// 	'subject' => $email_data['subject'],
						// 	'baseDocList' => array()
						// );

						// EmailMessage::logEmailData($status, $email_data);

						return true;
					}

				}
			}
		}

		return false;
	}

	/*Transmit SMS*/
	public static function transmitSMS($data) {
		$configList = self::getConfigVars();
		
		$smsObj = Smslib::getSmsObj($configList->send_phone);

		//$smsObj = @new \wmt\Nexmo($configList->send_phone);


		if(!empty($data['pid'])) {
			$pat_data = self::getPatientData($data['pid']);
			$pat_phone = isset($pat_data['phone_cell']) && !empty($pat_data['phone_cell']) ? preg_replace('/[^0-9]/', '', $pat_data['phone_cell']) : "";

			$isEnable = $pat_data['hipaa_allowsms'] != 'YES' || empty($pat_data['phone_cell']) ? true : false;
			
			if(!empty($pat_phone) && $isEnable === false) {
				$final_pat_phone = MessagesLib::getPhoneNumbers($pat_phone);
				$form_to_phone =  $final_pat_phone['msg_phone'];
				$form_message = $data['message'];

				$env_mode = self::getEnvMode();
				if($env_mode == "test") {
					$form_to_phone = self::getTestModeValue("phone");
				}
				
				if($form_to_phone === false) {
					return false;
				}

				if (!empty($form_message)) {
					$result = @$smsObj->smsTransmit($form_to_phone, $form_message, 'text');
					$msgId = $result['msgid'];
					$msgStatus = isset($result['msgStatus']) ? $result['msgStatus'] : 'MESSAGE_SENT';

					if (!empty($msgId)) {
						$raw_data = json_encode(EmailMessage::includeRequest(
							array(
								'pid' => $data['pid'], 
								'message_tlp' => $data['template_id'], 
								'phone' => $form_to_phone
							), 
							array(
									'pid',
									'message_tlp', 
									'phone'
							)
						));

						$datetime = strtotime('now');
						$msg_date = date('Y-m-d H:i:s', $datetime);
						@$smsObj->logSMS('SMS_MESSAGE', $form_to_phone, $configList->send_phone, $data['pid'], $msgId, $msg_date, $msgStatus, $form_message, 'out', false, $raw_data);
						return true;

					}
				}
			}
		}

		return false;
	}

	/*Transmit Fax*/
	public static function transmitFAX($data) {
		if(!empty($data['pid'])) {

			$pat_data = @\wmt\Patient::getPidPatient($data['pid']);

			$env_mode = self::getEnvMode();
			if($env_mode == "test") {
				$pat_data->fax_number = self::getTestModeValue("fax");
			}
			
			if($pat_data->fax_number === false) {
				return false;
			}

			if(!empty($pat_data->fax_number)) {
				try {

					$fItem = array(
					'pid' => $data['pid'],
					'data' => array(
						'template' => $data['template_id'],
						'fax_number' => $pat_data->fax_number,
						'receiver_name' => $pat_data->format_name,
						'html' => $data['message'],
						'text' => $data['message'],
						'fax_from_type' => 'custom',
						'request_data' => array(),
						'files' => array(),
					));

					$fData = FaxMessage::TransmitFax(
						array($fItem['data']), 
						array('pid' => $fItem['pid'], 'logMsg' => true, 'calculate_cost' => false)
					);

					if(is_array($fData) && count($fData) == 1) {
						$responce = $fData[0];
					} else {
						throw new \Exception("Something went wrong.");
					}

					/*
					// Prepare fax data
					$fax_data = array(
						'fax_number' => $pat_data->fax_number,
						'html' => $data['message'],
						'text' => $data['message'],
						'receiver_name' => $pat_data->format_name,
						'message_content' => $data['message'],
					);

					//Attache Files
					$attchFiles = @FaxMessage::AddAttachmentToFax($data['pid'], $fax_data, array(), array());
					$fax_data['data'] = @FaxMessage::getFilesContent($fax_data);

					// Send fax
					$responce = @FaxMessage::Transmit($fax_data);
					//EmailMessage::setTimeZone();
					*/

				} catch (Exception $e) {
					$status = $e->getMessage();
					$responData = array(
						'status' => false,
						'error' => $status
					);
				}

				if(isset($responce) && isset($responce['status']) && $responce['status'] == true){

						// $fax_data['pid'] = $data['pid'];
						// $fax_data['request'] = array(
						// 	'message' => $fax_data['message_content'],
						// 	'pid' => $fax_data['pid'],
						// 	'rec_name' => $fax_data['receiver_name'], 
						// 	'fax_number' => $fax_data['fax_number'],
						// 	'fax_from' => "patient",
						// 	'address_book' => "",
						// 	'insurance_companies' => ""
						// );

						//$responceData = @FaxMessage::logFaxData($responce, $fax_data, false);

						return true;
				}
			}
		}

		return false;
	}

	/*Transmit PostalLetter*/
	public static function transmitPostalLetter($data) {
		if(!empty($data['pid'])) {
			$pat_data = @\wmt\Patient::getPidPatient($data['pid']);

			$env_mode = self::getEnvMode();
			if($env_mode == "test") {
				$testAddress = self::getTestModeValue("postal_letter");
				$fullAddress =  PostalLetter::generatePostalAddress($testAddress, "\n");
			} else {
				$fullAddress =  PostalLetter::generatePostalAddress(array(
				    'street' => $pat_data->street,
				    'street1' => "",
				    'city' => $pat_data->city,
				    'state' => $pat_data->state,
				    'postal_code' => $pat_data->postal_code,
				    'country' => $pat_data->country_code,
				), "\n");
			}
			
			if($fullAddress['address'] === false) {
				return false;
			}

			if($fullAddress['status'] == true) {
				$message_list = new \wmt\Options('Postal_Letters');

				$from_reply_address = isset($GLOBALS['POSTAL_LETTER_REPlY_ADDRESS']) ? $GLOBALS['POSTAL_LETTER_REPlY_ADDRESS'] : "";
				$from_reply_address_json = isset($GLOBALS['POSTAL_LETTER_REPlY_ADDRESS_JSON']) ? $GLOBALS['POSTAL_LETTER_REPlY_ADDRESS_JSON'] : "";

				$from_address = isset($pat_data->format_name) ? $pat_data->format_name."\n" : '';
				$from_address .= trim($fullAddress['address']);
				$base_address = trim($fullAddress['address']);

				$from_address_json = isset($fullAddress['address_json']) ? $fullAddress['address_json'] : array();

				try {

					$pItem = array(
					'pid' => $data['pid'],
					'data' => array(
						'template' => $data['template_id'],
						'html' => $data['message'],
						'text' => $data['message'],
						'address' => $from_address,
						'address_json' => json_encode($from_address_json),
						'reply_address' => $from_reply_address,
						'reply_address_json' => $from_reply_address_json,
						'receiver_name' => $pat_data->format_name,
						'address_from_type' => "custom",
						'base_address' => $base_address,
						'request_data' => array(),
						'files' => array(),
					));

					$pData = PostalLetter::TransmitPostalLetter(
						array($pItem['data']), 
						array('pid' => $pItem['pid'], 'logMsg' => true, 'calculate_cost' => false)
					);
					
					if(is_array($pData) && count($pData) == 1) {
						$responce = $pData[0];
					} else {
						throw new \Exception("Something went wrong.");
					}
					
					/*
					// Prepare postal letter data
					$postal_letter_data = array();
					$postal_letter_data['dec'] = !empty($data['template_id']) ? $message_list->getItem($data['template_id']) : 'General Postal Letter';
					$postal_letter_data['html'] = $data['message'];
			        $postal_letter_data['text'] = $data['message'];
			        $postal_letter_data['address'] = $form_address;
			        $postal_letter_data['address_json'] = json_encode($form_address_json);
			        $postal_letter_data['reply_address'] = $form_reply_address;
			        $postal_letter_data['reply_address_json'] = $form_reply_address_json;
			        $postal_letter_data['receiver_name'] = $pat_data->format_name;
			        $postal_letter_data['base_address'] = $base_address;

			        //Attache Files
					$attchFiles = @PostalLetter::AddAttachmentToMsg($data['pid'], $postal_letter_data, array(), array());

					// Send postal letter
					$responce = @PostalLetter::Transmit($postal_letter_data);
					//EmailMessage::setTimeZone();
					*/

				} catch (Exception $e) {
					$status = $e->getMessage();
					$responData = array(
						'status' => false,
						'error' => $status
					);
				}

				if(isset($responce) && isset($responce['status']) && $responce['status'] == true) {

					// $postal_letter_data['pid'] = $data['pid'];
					// $postal_letter_data['request'] = array(
					// 	'message' => $postal_letter_data['message_content'],
					// 	'pid' => $postal_letter_data['pid'],
					// 	'address' => $postal_letter_data['base_address'],
					// 	'address_json' => $postal_letter_data['address_json'],
					// 	'rec_name' => $postal_letter_data['receiver_name'], 
					// 	'reply_address' => $postal_letter_data['reply_address'],
					// 	'reply_address_json' => $postal_letter_data['reply_address_json'],
					// 	'address_from' => "patient",
					// 	'address_book' => "",
					// 	'insurance_companies' => ""
					// );

					// $responceData = @PostalLetter::logPostalLetterData($responce, $postal_letter_data);

					return true;
				}

			}
		}

		return false;
	}

	/*Transmit InternalMessage*/
	public static function transmitInternalMessage($data) {
		if(!empty($data['pid'])) {
			$set_assign_pid = $data['pid'];
			$set_note_type = "Action Event";
			$note = $data['message'];
			$assigned_to = "admin";

			if(!empty($set_assign_pid) && !empty($assigned_to) && !empty($set_note_type)) {
				addPnote($set_assign_pid, $note, '1', '1', $set_note_type, $assigned_to, '', "New");

				return true;
			}
		}

		return false;
	}

	/*Handle and process notifications*/
	public static function handleApptReportNotifications($ids, $type = '', $template = '') {
		$responce = array(
			'sent_items' => 0,
			'failed_items' => 0
		);

		if(!empty($ids) && !empty($type) && !empty($template)) {
			$apptIds = implode(",", $ids);
			$apptsData =  self::loadAppointments($apptIds);

			if(!empty($apptsData)) {
				foreach ($apptsData as $key => $item) {
					$appid = $item['pc_eid'];
					$event_datetime = $item['event_datetime'];
					$pc_pid = $item['pc_pid'];

					$messageData = self::getFullMessage($pc_pid, $template, $event_datetime, $appid);

					$message_text = '';
					if($type == 'sms' || $type == 'internalmessage') {
						$message_text = isset($messageData['content']) ? $messageData['content'] : "";
					} else {
						$message_text = isset($messageData['content_html']) ? $messageData['content_html'] : "";
					}

					$tranmitData = array(
						'template_id' => $template,
						'pid' => $pc_pid,
						'msg_type' => $type,
						'uniqueid' => $appid,
						'message' => $message_text,
						'event_datetime' => $event_datetime
					);

					if($type == 'email') {
						$transmitStatus = self::transmitEmail($tranmitData);
					} else if($type == 'sms') {
						sleep(1);
						$transmitStatus = self::transmitSMS($tranmitData);
					} else if($type == 'fax') {
						$transmitStatus = self::transmitFAX($tranmitData);
					} else if($type == 'postalmethod') {
						$transmitStatus = self::transmitPostalLetter($tranmitData);
					} else if($type == 'internalmessage') {
						$transmitStatus = self::transmitInternalMessage($tranmitData);
					}

					if($transmitStatus === true) {
						$responce['sent_items']++;
					} else {
						$responce['failed_items']++;
					}
				}
			}
		}

		return $responce;
	}

	public static function getTemplateByID($type, $id) {
		$title = "";
		
		if($type == "email") {
			$message_list = new \wmt\Options('Reminder_Email_Messages');
			$title = $message_list->getItem($id);
		} else if($type == "sms") {
			$message_list = new \wmt\Options('Reminder_SMS_Messages');
			$title = $message_list->getItem($id);
		} else if($type == "fax") {
			$message_list = new \wmt\Options('Reminder_Fax_Messages');
			$title = $message_list->getItem($id);
		} else if($type == "postalmethod") {
			$message_list = new \wmt\Options('Reminder_Postal_Letters');
			$title = $message_list->getItem($id);
		} else if($type == "internalmessage") {
			$message_list = new \wmt\Options('Reminder_Internal_Messages');
			$title = $message_list->getItem($id);
		}

		return $title;
	}

	public static function fetchDataQueryResult($item = array()) {
		if(!empty($item)) {
			$dataQuery = (isset($item['data_query']) && $item['data_query'] != "") ? $item['data_query'] : "";

			$tablename = isset($item['tablename']) ? $item['tablename'] : "";
			$uniqueid = isset($item['uniqueid']) ? $item['uniqueid'] : "";

			$tmpIdArray = array();
			if(isset($item['uniqueid'])) {
				$tmpIdArray = explode(",",$item['uniqueid']);
			}

			$preparedIdArray = array();
			if(is_array($tmpIdArray)) {
				foreach ($tmpIdArray as $tik => $tId) {
					$pItemK = '@id'.($tik + 1);
					$pItemV = $tId;

					$preparedIdArray[$pItemK] = $pItemV;
				}
			}

			$rpvars = array(
			  '@tablename' => $tablename,
			  '@id' => isset($item['uniqueid']) ? $item['uniqueid'] : ""
			);

			$rpvars = array_merge($rpvars, $preparedIdArray);

			if(isset($dataQuery) && $dataQuery != "") {
				$fetchDataSql = strtr($dataQuery, $rpvars);
			} else {
				//$fetchDataSql = "SELECT * FROM `".$tablename."` as tb WHERE id = '".$uniqueid."'";
			}

			if(isset($fetchDataSql) && !empty($fetchDataSql)) {
				$resultItems = array();
				$result = sqlStatement($fetchDataSql);

				
				while ($result_data = sqlFetchArray($result)) {
					$resultItems[] = $result_data;
				}

				return $resultItems;
			}
		}

		return false;
	}

	public static function generateBodyParam($req, $variableList) {
		extract($variableList);

		$bodyRequest = eval("return ".$req . ";");

		return $bodyRequest;
	}

	public static function prepareRequestForMessage($data) {
		$finalParam = array();

		if(!empty($data)) {

			$configuration = self::getNotificationConfiguration($data['config_id']);

			if(!empty($configuration) && count($configuration) > 0) {
				$data_query = $configuration[0]['data_query'];
				$request_template = $configuration[0]['request_template'] ? base64_decode($configuration[0]['request_template']) : "";

				$tablename = isset($data['tablename']) ? $data['tablename'] : "";
				$uniqueid = isset($data['uniqueid']) ? $data['uniqueid'] : "";

				$dataQueryResult = self::fetchDataQueryResult(array(
					'data_query' => $data_query,
					'tablename' => $tablename,
					'uniqueid' => $uniqueid
				));

				$resultItem = array();
				if(!empty($dataQueryResult) && count($dataQueryResult) > 0) {
					$resultItem = $dataQueryResult[0];
				}
				
				if(!empty($request_template)) {
					$preparedParam = self::generateBodyParam($request_template, $resultItem);

					if(!empty($preparedParam)) {
						$finalParam = $preparedParam;
					}
				}
			}
		}

		return $finalParam;
	}

	public function replaceMsgTags($data, $message_text = '') {
		$finalParam = array();
		$finalMsgText = $message_text; 

		if(!empty($data) && !empty($message_text)) {

			if (!preg_match("/{{(.*?)}}/", $message_text)) {
				return $finalMsgText;
			}

			$configuration = self::getNotificationConfiguration($data['config_id']);

			if(!empty($configuration) && count($configuration) > 0) {
				$data_query = $configuration[0]['data_query'];
				$tablename = isset($data['tablename']) ? $data['tablename'] : "";
				$uniqueid = isset($data['uniqueid']) ? $data['uniqueid'] : "";

				$dataQueryResult = self::fetchDataQueryResult(array(
					'data_query' => $data_query,
					'tablename' => $tablename,
					'uniqueid' => $uniqueid
				));

				$resultItem = array();
				if(!empty($dataQueryResult) && count($dataQueryResult) > 0) {
					$resultItem = $dataQueryResult[0];
				}

				$replacevars = array();
				foreach ($resultItem as $iKey => $rValue) {
					$replacevars['{{'.$iKey.'}}'] = $rValue;
				}

				//ReplacedText
				$finalMsgText = strtr($message_text, $replacevars);
			}
		}

		return $finalMsgText;
	}

	public function saveMsgGprelation($type1 = '', $id1 = '', $type2 = '', $id2 = '') {
		if(!empty($type1) && !empty($type2)) {
			$bind = array($type1, $id1, $type2, $id2);
			sqlInsert("INSERT INTO `gprelations` ( type1, id1, type2, id2 ) VALUES (?, ?, ?, ?) ", $bind);
		}
	}

	public static function prepareItemStatus($config = array(), $data = array()) {
		$preparedData = isset($data['data']) ? $data['data'] : array(); 
		$totalData = isset($data['total_data']) ? $data['total_data'] : 0;

		$config_event_id = isset($config['event_id']) ? $config['event_id'] : "";
		$config_id = isset($config['id']) ? $config['id'] : "";

		if(!empty($config)) {
			if(!empty($config_event_id) && !empty($config_id)) {
				if(!isset($preparedData[$config_event_id])) {
					$preparedData[$config_event_id] = array();
				}

				if(isset($preparedData[$config_event_id]) && !isset($preparedData[$config_event_id][$config_id])) {
					$preparedData[$config_event_id][$config_id] = 0;
				}

				if(isset($preparedData[$config_event_id][$config_id])) {
					$preparedData[$config_event_id][$config_id]++;
					$totalData++;
				}
			}
		}

		if(!empty($preparedData) && is_array($data)) {
			$data['data'] = $preparedData;
			$data['total_data'] = $totalData;
		}

		return $data;
	}

	public static function prepareStatusMsg($data = array()) {
		$msg = "Prepared Data:";
		$totalItemCount = 0;
		if(!empty($data)) {
			foreach ($data as $dk => $dItem) {
				if(isset($dItem['prepared_item_status'])) {
					$dataItem = isset($dItem['prepared_item_status']['data']) ? $dItem['prepared_item_status']['data'] : array();
					$totalDataItem = isset($dItem['prepared_item_status']['total_data']) ? $dItem['prepared_item_status']['total_data'] : 0;

					$totalItemCount += $totalDataItem;

					foreach ($dataItem as $dek => $deItem) {
						$msg .= PHP_EOL."-" . $dek;
						foreach ($deItem as $dck => $dcItem) {
							$msg .= PHP_EOL."\t-" . $dck . ":\t". $dcItem;
						}
					}
				}
			}
		}


		$msg .= "\n-Total Items Prepared:\t". $totalItemCount;

		return $msg;
	}

	public static function writeLog($log = "", $fileInfo = true, $appendDate = true) {
		$filePath = $GLOBALS['fileroot'] . '/library/OemrAD/log/reminder_cron_log.txt';
		
		$appendStr = "";
		if($appendDate === true) {
			$appendStr .= "[".date("Y-m-d H:i:s")."] ";
		}

		if($fileInfo === true) {
			$directoryURI =basename($_SERVER['SCRIPT_NAME']);
			$appendStr .= "[".$directoryURI."] ";
		}

		if(!empty($appendStr)) {
			$log = $appendStr . $log;
		}

		if(!empty($log)) {
			file_put_contents($filePath, $log.PHP_EOL, FILE_APPEND);
		}
	}

	public static function writeSqlLog($log = "", $cron_id = '', $isError = 0) {
		$directoryURI =basename($_SERVER['SCRIPT_NAME']);

		if(!empty($log)) {
			logNotificationData($directoryURI, $cron_id, $log, $isError);
		}
	}

	public static function getLogFile() {
		$filePath = $GLOBALS['fileroot'] . '/library/OemrAD/log/reminder_cron_log.txt';
		//$string = shell_exec('exec tail -n50 ' . $filePath);
		$string = "";
		$file = file($filePath);
        $fileLineCount = (count($file) <= 100) ? 1 : (count($file) - 100);
        $readLines = max(0, $fileLineCount); //n being non-zero positive integer
		if($readLines > 0) {
			for ($i = $readLines; $i < count($file); $i++) {
				$string .= $file[$i];
				//$string .= nl2br("\n");
			}
		}

		return $string;
	}

	public static function prepareShellCmd($event_id = '', $config_id = '') {
		if(!empty($event_id)) {
			$argsParam = array();

			if(!empty($event_id)) {
				$argsParam[] = $event_id;
			}

			if(!empty($config_id)) {
				$argsParam[] = $config_id;
			}

			$argsStr = implode(" ", $argsParam);

			if(isset($argsStr) && !empty($argsStr) && $argsStr != "") {
				$scriptFilePath = $GLOBALS['fileroot'].'/library/OemrAD/crons/reminder/cron_prepare_notification.php';
				$cmd = "/usr/bin/php ".$scriptFilePath." ".$argsStr." > /dev/null 2>/dev/null &";
				self::writeLog($cmd);
				//return shell_exec($cmd);
			}
		}

		return false;
	}

	public static function prepareSendShellCmd($event_id = '', $config_id = '') {
		if(!empty($event_id)) {
			$argsParam = array();

			if(!empty($event_id)) {
				$argsParam[] = $event_id;
			}

			if(!empty($config_id)) {
				$argsParam[] = $config_id;
			}

			$argsStr = implode(" ", $argsParam);

			if(isset($argsStr) && !empty($argsStr) && $argsStr != "") {
				$scriptFilePath = $GLOBALS['fileroot'].'/library/OemrAD/crons/reminder/cron_notification.php';
				$cmd = "/usr/bin/php ".$scriptFilePath." both ".$argsStr." > /dev/null 2>/dev/null &";
				self::writeLog($cmd);
				//return shell_exec($cmd);
			}
		}

		return false;
	}
}
