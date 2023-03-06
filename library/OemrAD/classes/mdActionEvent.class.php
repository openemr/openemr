<?php

namespace OpenEMR\OemrAd;

@include_once(__DIR__ . "/../interface/globals.php");
@include_once(__DIR__ . "/mdReminder.class.php");
@include_once(__DIR__ . "/mdEmailMessage.class.php");

use OpenEMR\OemrAd\Reminder;
use OpenEMR\OemrAd\EmailMessage;

/**
 * ActionEvent Class
 */
class ActionEvent {
    
    public function __construct() {
    }

    public static function prepareNotificationData($prepareFor = 'both', $eventid_param = '', $configid_param = '') {
		try {
			$configs = self::getActionConfigurationByParam(array(
				'id' => $eventid_param,
				'config_id' => $configid_param,
				'action_type' => 'action_reminder',
			));
			$totalPreparedItem = 0;
			$totalFailedItem = 0;
			$preparedItemStatus = array();

			if(isset($configs)) {
				foreach ($configs as $key => $action_config) {
					foreach ($action_config['config_data'] as $key => $config_data) {
						try {
							$config = array_merge($action_config, $action_config['config_data'][$key]);
							$event_id = $config['event_id'];
							$configuration_id = $config['id'];
							$tagmsg = strtoupper("(".$event_id.':'.$configuration_id.")");

							if(isset($config['configuration_id']) && !empty($config['configuration_id']) && $config['active'] == "0" && !empty($config['id'])) {
								if($config['action_type'] == "action_reminder") {

									if($config['as_trigger_type'] == "time" && ($prepareFor == 'both' || $prepareFor == 'time')) {

										if(!isset($config['data_set']) || empty($config['data_set'])) {
											throw new \Exception("DataSource value is empty");
											continue;
										}

										if(!isset($config['operation_type']) || empty($config['operation_type'])) {
											throw new \Exception("Operation type not found.");
											continue;
										}

										$resultItems = self::fetchDataSet($config['data_set']);

										foreach ($resultItems as $rik => $result_data) {
											try {
												$tablename = isset($result_data['tablename']) ? $result_data['tablename'] : "";
												$uniqueid = isset($result_data['uniqueid']) ? $result_data['uniqueid'] : "";
												$qtr_trigger_time = isset($result_data['trigger_time']) ? $result_data['trigger_time'] : "";

												if(empty($tablename) || empty($uniqueid)) {
													throw new \Exception("DataSource Result Item missing data");
													continue;
												}

												$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
												$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";

												$isExists = self::isNotifyDataExists($event_id, $tablename, $uniqueid, '0', $config['id']);

												if($isExists === false) {
													//Calculate Trigger Time
													$triggerTime = Reminder::calTriggerTime($config);
													$current_unix_time = strtotime('now');

													if(!empty($qtr_trigger_time) && $current_unix_time < strtotime($qtr_trigger_time)) {
														$triggerTime = date('Y-m-d H:i:s', strtotime($qtr_trigger_time));
													}

													$event_datetime = isset($triggerTime) ? $triggerTime : false;
													$pc_id = isset($result_data['pid']) ? $result_data['pid'] : false;
													$app_id = isset($result_data['app_id']) ? $result_data['app_id'] : false;

													$message_text = '';
													$to_send = '';
													$operation_type = isset($config['operation_type']) ? $config['operation_type'] : 0;

													if($operation_type == 2) {
														$message_text = self::getMsgContent($config['communication_type'], $config['notification_template'], $pc_id, $event_datetime, $app_id);

														$to_send = isset($config['to_send']) ? $config['to_send'] : '';

														$to_send = self::generateToSend($to_send, $result_data);

														if(empty($to_send)) {
															throw new \Exception("Empty 'to send' value for messaging. ");
															continue;
														}
													}

													if(!empty($triggerTime)) {
														$preparedData = array(
															'event_id' => $event_id,
															'config_id' => $config['id'],
															'msg_type' => $config['communication_type'],
															'template_id' => $config['notification_template'],
															'message' => $message_text,
															'to_send' => $to_send,
															'operation_type' => $operation_type,
															'event_type' => '1',
															'tablename' => $tablename,
															'uniqueid' => $uniqueid,
															'uid' => $uid,
															'user_type' => $user_type,
															'sent' => '0',
															'sent_time' => '',
															'trigger_time' => $triggerTime,
															'time_delay' => $config['time_delay']
														);
														
														self::savePreparedData($preparedData);
														$totalPreparedItem++;

														$preparedItemStatus = Reminder::prepareItemStatus($config, $preparedItemStatus);
													}
												}

											} catch(\Exception $e) {
												$totalFailedItem++;
												echo $tagmsg.': ' .$e->getMessage();
											}
										}


									} else if($config['as_trigger_type'] == "event" && ($prepareFor == 'both' || $prepareFor == 'event')) {
										//Todo List
										$resultData = self::getEventTriggerData($event_id, $config['id']);

										$updateStatus = self::prepareDataForUpdate($resultData, $config);

										if(isset($updateStatus) && $updateStatus > 0) {
											$totalPreparedItem = $totalPreparedItem + $updateStatus;
											
											//Prepare Item Status
											$preparedItemStatus = Reminder::prepareItemStatus($config, $preparedItemStatus);
										}
									}
								}
							}
						} catch(\Exception $e) {
							$totalFailedItem++;
							echo $tagmsg.': ' .$e->getMessage();
						}
					}
				}
			}
		} catch(\Exception $e) {
			$totalFailedItem++;
			echo 'Message: ' .$e->getMessage();
		}

		return array('total_prepared_item' => $totalPreparedItem, 'total_failed_item' => $totalFailedItem,'prepared_item_status' => $preparedItemStatus);
	}

	public static function getEventTriggerData($event_id = '', $config_id = '') {
		$sql = "SELECT * FROM `vh_action_reminder_log` WHERE event_id = ? AND config_id = ? AND operation_type = '2' AND (template_id IS NULL OR template_id = '') ";

		$resultItems = array();
		$result = sqlStatementNoLog($sql, array($event_id, $config_id));
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
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
				$pc_id = $item['pid'];

				$appid = false;
				$appStatus = true;

				$message_text = '';
				$to_send = '';
				$operation_type = isset($config['operation_type']) ? $config['operation_type'] : 0;
				
				if($operation_type == 2) {
					$message_text = self::getMsgContent($config['communication_type'], $config['notification_template'], $pc_id, $event_datetime, $app_id);

					$to_send = isset($config['to_send']) ? $config['to_send'] : '';
					$to_send = self::generateToSend($to_send, $result_data);
				}

				//Calculate Trigger Time
				//$triggerTime = self::calTriggerTime($config);

				$preparedData = array(
					'msg_type' => $config['communication_type'],
					'event_type' => '2',
					'template_id' => $config['notification_template'],
					'to_send' => $to_send,
					'operation_type' => $operation_type,
					'message' => $message_text,
					'uid' => $uid,
					'user_type' => $user_type,
					'time_delay' => $config['time_delay']
				);

				// if($appStatus === false) {
				// 	$preparedData['sent'] = '3';
				// }

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

	public static function generateToSend($toSendStr = '', $dItem = array()) {
		if(!empty($toSendStr)) {
			$rpvars = array();

			foreach ($dItem as $iField => $itemValue) {
				$rpvars['@'.$iField] = $itemValue;
			}

			$toSendStr = strtr($toSendStr, $rpvars);
		}
		return $toSendStr;
	}

	public static function actionReminderByEvent($type = 0, $eventid_param = '', $configid_param = '') {
		$itemIdList = array();
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		return self::actionReminder($type, $eventid_param, $configid_param);

		return array('total_items' => 0, 'total_sent_item' => 0);
	}

	public static function actionReminder($type = 0, $eventid_param = '', $configid_param = '') {
		$totalItem = 0;
		$totalsentItem = 0;
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		if($type === 0 || $type === 2) {
			//prepare trigger data for send reminder
			self::prepareNotificationData('event');
		}

		$dataItems = self::getSendItemIdByEvent(array(
			'tablename' => 'vh_action_reminder_log',
			'event_type' => $event_type,
			'event_id' => $eventid_param,
			'config_id' => $configid_param,
			'status' => false
		));

		//Run Pre processing query
		$dataItems = self::preProcessingQuery($dataItems, $event_type);
		$preparedDataForAction = array();

		//Get TestMode Notifications
		$ignoreConfigs = Reminder::getTestModeConfigList();
		$testModeItems = array();

		foreach ($dataItems as $key => $item) {
			$tagmsg = strtoupper("(".$item['event_id'].':'.$item['config_id'].")");
			try {
				if(empty($item['trigger_time'])) {
					continue;
				}

				$trigger_time = $item['trigger_time'];
				
				//Unix time
				$current_unix_time = strtotime('now');
				$trigger_unix_time = strtotime($item['trigger_time']);

				$cConfig = $item['config_item'] ? $item['config_item'] : array();

				$item['seq'] = isset($cConfig['seq']) ? $cConfig['seq'] : 0;
				$seq_key = "s".$item['seq'];
				$config_key = $item['event_id']."_".$item['config_id'];

				if($item['sent'] == 0 && $trigger_unix_time <= $current_unix_time) {
					$p_data_item = array();

					if(!isset($preparedDataForAction[$seq_key])) {
						$preparedDataForAction[$seq_key] = array();
					}

					if(!isset($preparedDataForAction[$seq_key][$config_key])) {
						$preparedDataForAction[$seq_key][$config_key] = array(
							'seq' => $item['seq'],
							'config' => $cConfig ? $cConfig : array(),
							'data_items' => array(),
							'error_data_items' => array()
						);
					}

					//If Config Empty
					if(!isset($item['config_item']) || empty($item['config_item'])) {
						throw new \Exception('Config Not Found.');
						continue;
					}

					//If Config not active
					if(!isset($item['config_item']['active']) || $item['config_item']['active'] === 1 ) {
						throw new \Exception('Config Not active.');
						continue;
					}

					if(!isset($item['config_item']['operation_type']) || !isset($item['config_item']['operation_type']) || !isset($item['operation_type'])) {
						throw new \Exception('Config Operation Type is empty.');
						continue;
					}

					if($item['operation_type'] !== $item['config_item']['operation_type']) {
						throw new \Exception('Something wrong with operation type.');
						continue;
					}

					if($item['operation_type'] == 2 && empty($item['config_item']['data_query'])) {
						$actionDataItems = array(array());
					} else {
						//Fetch Data For Sync	
						$actionData = self::fetchDataForAction($item);

						if($actionData === false) {
							throw new \Exception('Fetch data item error.');
						}

						$actionDataItems = (!empty($actionData) && is_array($actionData) && count($actionData) > 0) ? $actionData : array();

						if(empty($actionDataItems)) {
							throw new \Exception('Fetch data item error.');
						}
					}

					//foreach ($actionDataItems as $adk => $actionDataItem) {
						if(isset($item['seq'])) {
							$p_data_item = array(
								'event' => $item,
								'config' => isset($item['config_item']) ? $item['config_item'] : array(),
								'data_item' => $actionDataItems
							);

							if(isset($preparedDataForAction[$seq_key]) && isset($preparedDataForAction[$seq_key][$config_key])) {
								if(isset($preparedDataForAction[$seq_key][$config_key]['data_items']) && !empty($p_data_item)) {
									$preparedDataForAction[$seq_key][$config_key]['data_items'][] = $p_data_item;
								}
							}
						}
					//}
				}

			} catch(\Exception $e) {
				$p_data_item = array(
					'dStatus' => false,
					'dError' => $e->getMessage(),
					'event' => $item,
					'config' => isset($item['config_item']) ? $item['config_item'] : array(),
					'data_item' => array()
				);

				if(isset($preparedDataForAction[$seq_key]) && isset($preparedDataForAction[$seq_key][$config_key])) {
					if(isset($preparedDataForAction[$seq_key][$config_key]['error_data_items']) && !empty($p_data_item)) {
						$preparedDataForAction[$seq_key][$config_key]['error_data_items'][] = $p_data_item;
					}
				}
			}
		}

		//Sort By SEQ
		ksort($preparedDataForAction, SORT_NUMERIC);

		//Process Parepared Data For Sync
		foreach ($preparedDataForAction as $ccik => $configItem) {
			foreach ($configItem as $sk => $sItem) {
				$config = $sItem['config'] ? $sItem['config'] : array();
				$data_items = $sItem['data_items'] ? $sItem['data_items'] : array();
				$error_data_items = $sItem['error_data_items'] ? $sItem['error_data_items'] : array();
				
				foreach ($data_items as $edk => $dItem) {
					if(!empty($dItem) && isset($dItem['event']) && isset($dItem['event']['id'])) {
						$eventId = $dItem['event']['id'];
						$eventData = isset($dItem['event']) ? $dItem['event'] : array();
						$eOperationType = isset($eventData['operation_type']) ? $eventData['operation_type'] : 0;
						$eMsg = 'Something went wrong.';

						if($eOperationType != 2) {
							//Skip Test Mode
							if(!empty($ignoreConfigs) && isset($ignoreConfigs[$eventData['config_id']])) {
								$testModeItems = $this->handleTestItems($eventData, $testModeItems, $ignoreConfigs);
								$totalItem++;
							}
						}

						//Query End point
						if($eOperationType == 1) {
							$endResultData = self::handleQueryEndPoint($dItem);
						} else if($eOperationType == 2) {
							$endResultData = self::handleMessagingEndPoint($dItem);
						} else if($eOperationType == 3) {
							$endResultData = self::handleCodeEditorEndPoint($dItem);
						} else {
							$eMsg = 'Operation type not found.';
						}

						$updateData = array(
							'sent' => 2,
							'sent_time' => date('Y-m-d H:i:s'),
							'status' => $eMsg,
							'request_body' => '',
							'request_responce' => ''
						);

						if($endResultData) {
							$updateData = array(
								'sent' => (isset($endResultData['status']) && $endResultData['status'] === true) ? 1 : 2,
								'sent_time' => date('Y-m-d H:i:s'),
								'status' => isset($endResultData['error']) ? $endResultData['error'] : "success",
								'request_body' => isset($endResultData['req_body']) && !empty($endResultData['req_body']) ? json_encode($endResultData['req_body']) : "",
								'request_responce' => isset($endResultData['req_responce']) && !empty($endResultData['req_responce']) ? json_encode($endResultData['req_responce']) : ""
							);
						}

						//Update Data
						self::updatePreparedData(
							$eventId,
							$updateData
						);

						if(isset($updateData['sent']) && $updateData['sent'] == 1) {
							$totalsentItem += count(array($eventId));
						}
					}

					$totalItem++;
				}

				foreach ($error_data_items as $edk => $errorDataItem) {
					if(!empty($errorDataItem) && isset($errorDataItem['event']) && isset($errorDataItem['event']['id'])) {
						$eventId = $errorDataItem['event']['id'];
						$errorMsg = "";

						if(isset($errorDataItem['dStatus']) && isset($errorDataItem['dError']) && $errorDataItem['dStatus'] === false) {
							$errorMsg = $errorDataItem['dError'];
						}

						//Update error data
						self::updatePreparedData(
							array($eventId),
							array(
								'sent' => 2,
								'sent_time' => date('Y-m-d H:i:s'),
								'status' => $errorMsg,
								'request_body' => '',
								'request_responce' => ''
							)
						);
					}

					$totalItem++;
				}
			}
		}

		if(count($testModeItems) > 0) {
			Reminder::sendNotificationLog(array(), $testModeItems);
		}

		return array('total_items' => $totalItem, 'total_sent_item' => $totalsentItem);
	}

	public static function handleCodeEditorEndPoint($dataItem = array()) {
		$responceData = array(
			'status' => false,
			'req_body' => '',
			'req_responce' => ''
		);

		try {
			if(!empty($dataItem)) {
				$eventData = isset($dataItem['event']) ? $dataItem['event'] : array();
				$config = isset($dataItem['config']) ? $dataItem['config'] : array();
				$dItems = isset($dataItem['data_item']) ? $dataItem['data_item'] : array();
				$ope_action = isset($config['operation_action']) ? html_entity_decode(base64_decode($config['operation_action'])) : '';

				if(empty($ope_action)) {
					throw new \Exception('Operation action value is empty.');
				}

				if(!empty($config['time_delay']) && $config['time_delay'] != 0) {
					sleep($config['time_delay']);
				}

				$responceData = self::executeActionCode($ope_action, $dItems, $config, $eventData);

			}
		} catch (\Throwable $e) {
			$responceData = array(
				'status' => false,
				'error' => $e->getMessage(),
				'req_body' => '',
				'req_responce' => ''
			);
		}

		return $responceData;
	}

	public static function executeActionCode($ope_action, $dataItems = array(), $config = array(), $eventData = array()) {
		$responceData = array(
			'status' => false,
			'req_body' => '',
			'req_responce' => ''
		);

		try {
			if(!empty($ope_action)) {
				ob_start();
				eval("?> $ope_action <?php ");
				$pCode = ob_get_clean();

				$responceData = array(
					'status' => true,
					'req_body' => '',
					'req_responce' => (isset($pCode) && !empty($pCode)) ? trim($pCode) : ""
				);
			}
		} catch (\Throwable $e) {
			throw new \Exception($e->getMessage());
		}

		return $responceData;
	}

	public function handle15000Threshold1($items = array()) {
		$resultItems = array();

		if(!empty($items)) {
			$totalBalance = 0;

			foreach ($items as $ik => $item) {
				if(!isset($item['case_id']) || !isset($item['chart_number']) || empty($item['case_id']) || empty($item['chart_number'])) {
					continue;
				}

				// Delete Table records. 
				$deleteSet = sqlQuery("DELETE FROM vh_15000threshold_report WHERE DATE(reported_datetime) < DATE(now())", array());

				$dataSet1 = sqlQuery("SELECT count(vtr.id) as total_row from vh_15000threshold_report vtr where vtr.case_id = ? and vtr.pub_pid = ? and reported_datetime = ? ", array($item['case_id'], $item['chart_number'], $item['reported_date_time']));

				if(isset($dataSet1['total_row']) && $dataSet1['total_row'] > 0) {
					//UPDATE
					sqlQuery("UPDATE `vh_15000threshold_report` SET amount = ?, reported_datetime = ? WHERE pub_pid = ? and  case_id = ?", array($item['total'], $item['reported_date_time'], $item['chart_number'], $item['case_id']));
				} else {
					//INSERT
					sqlQuery("INSERT INTO `vh_15000threshold_report` (pub_pid, case_id, oemr_case_id, amount, reported_datetime) VALUES (?, ?, ?, ?, ?)",array($item['chart_number'], $item['case_id'], $item['oemr_case_id'], $item['total'], $item['reported_date_time']));
				}

				$caseBalance = isset($item['total']) ? $item['total'] : 0;

				if($caseBalance > 15000) {
					$dataSet2 = sqlQuery("SELECT count(vtr.case_id) as total_row from vh_15000threshold_data vtr where vtr.case_id = ? ", array($item['oemr_case_id']));

					if(isset($dataSet2['total_row']) && $dataSet2['total_row'] == 0) {
						sqlQuery("INSERT INTO `vh_15000threshold_data` (case_id, reported_datetime_whencrossed15k) VALUES (?, ?)", array($item['oemr_case_id'], $item['reported_date_time']));
					}
				}
			}
		}
	}

	public function handle15000Threshold($items = array()) {
		$resultItems = array();

		if(!empty($items)) {
			$totalBalance = 0;

			foreach ($items as $ik => $item) {
				if(!isset($item['case_id']) || !isset($item['chart_number']) || empty($item['case_id']) || empty($item['chart_number'])) {
					continue;
				}

				$dataSet1 = sqlQuery("SELECT count(vtr.id) as total_row from vh_15000threshold_report vtr where vtr.case_id = ? and vtr.pub_pid = ? ", array($item['case_id'], $item['chart_number']));

				if(isset($dataSet1['total_row']) && $dataSet1['total_row'] > 0) {
					//UPDATE
					sqlQuery("UPDATE `vh_15000threshold_report` SET amount = ?, reported_datetime = ? WHERE pub_pid = ? and  case_id = ?", array($item['total'], $item['reported_date_time'], $item['chart_number'], $item['case_id']));
				} else {
					//INSERT
					sqlQuery("INSERT INTO `vh_15000threshold_report` (pub_pid, case_id, oemr_case_id, amount, reported_datetime) VALUES (?, ?, ?, ?, ?)",array($item['chart_number'], $item['case_id'], $item['oemr_case_id'], $item['total'], $item['reported_date_time']));
				}

				$caseBalance = isset($item['total']) ? $item['total'] : 0;

				if($caseBalance > 15000) {
					$dataSet2 = sqlQuery("SELECT vtr.* from vh_15000threshold_report vtr where vtr.case_id = ? and vtr.pub_pid = ? and (vtr.reported_datetime_whencrossed15k = '' or vtr.reported_datetime_whencrossed15k IS NULL) ", array($item['case_id'], $item['chart_number']));

					if(isset($dataSet2) && !empty($dataSet2)) {
						if($dataSet2['reported_datetime_whencrossed15k'] == "") {
							sqlQuery("UPDATE `vh_15000threshold_report` SET reported_datetime_whencrossed15k = ? WHERE pub_pid = ? and  case_id = ?", array($item['reported_date_time'], $item['chart_number'], $item['case_id']));
						}
					}
				}
			}

			/*foreach ($items as $ik => $item) {
				if(!isset($item['case_id']) || !isset($item['chart_number']) || empty($item['case_id']) || empty($item['chart_number'])) {
					continue;
				}

				$caseBalance = isset($item['total']) ? $item['total'] : 0;
				$totalBalance += $caseBalance;

				if($caseBalance > 15000) {
					$dataSet1 = sqlQuery(
						"SELECT count(vtr.id) as total_row from vh_15000threshold_report vtr where vtr.case_id = ? and vtr.pub_pid = ? and DATE(vtr.date_time) = DATE('".$item['reported_date_time']."') ", 
						array($item['case_id'], $item['chart_number'])
					);

					if(isset($dataSet1['total_row']) && $dataSet1['total_row'] > 0) {
						//UPDATE
						$resultItems[] = array(
							'case_id' => $item['case_id'],
							'oemr_case_id' => $item['oemr_case_id'],
							'status' => 'Data item already exists.' 
						);
					} else {
						sqlQuery(
							"INSERT INTO `vh_15000threshold_report` (pub_pid, case_id, oemr_case_id, amount, date_time) VALUES (?, ?, ?, ?, ?)",
							array($item['chart_number'], $item['case_id'], $item['oemr_case_id'], $item['total'], $item['reported_date_time'])
						);

						$resultItems[] = array(
							'case_id' => $item['case_id'],
							'oemr_case_id' => $item['oemr_case_id'],
							'status' => 'Data item inserted.' 
						);
					}

					// Check data already exists.
					$dataSet2 = sqlQuery("SELECT count(vtd.case_id) as total_row from vh_15000threshold_data vtd where vtd.case_id = ?", array($item['case_id']));

					// Report date.
					if(isset($dataSet2['total_row']) && $dataSet2['total_row'] == 0) {
						sqlQuery("INSERT INTO `vh_15000threshold_data` (case_id, reported_datetime) VALUES (?, ?)", array($item['case_id'], $item['reported_date_time'])
						);
					}
				}
			}*/
		}

		if(!empty($resultItems)) {
			echo json_encode($resultItems);
		}
	}

	public static function handleMessagingEndPoint($dataItem = array()) {
		$responceData = array(
			'status' => false,
			'req_body' => '',
			'req_responce' => ''
		);

		try {
			if(!empty($dataItem)) {
				$eventData = isset($dataItem['event']) ? $dataItem['event'] : array();
				$config = isset($dataItem['config']) ? $dataItem['config'] : array();
				$dItems = isset($dataItem['data_item']) ? $dataItem['data_item'] : array();
				$to_send = isset($eventData['to_send']) ? $eventData['to_send'] : '';

				if(empty($to_send)) {
					throw new \Exception('Empty to send value.');
				}

				foreach ($dItems as $dik => $dItem) {
					if(!empty($config['time_delay']) && $config['time_delay'] != 0) {
						sleep($config['time_delay']);
					}

					$rpvars = array(
					  '@tablename' => isset($eventData['tablename']) ? $eventData['tablename'] : "",
					  '@id' => isset($eventData['uniqueid']) ? $eventData['uniqueid'] : "",
					  '@nt_id' => isset($eventData['id']) ? $eventData['id'] : ""
					);

					foreach ($dItem as $iField => $itemValue) {
						$rpvars['@'.$iField] = $itemValue;

						if($iField == "email_from" || $iField == "email_patient") {
							$eventData[$iField] = $itemValue;
						}
					}

					if($eventData['msg_type'] == "email") {
						$itemStatus = self::sendEmail($eventData);
						if($itemStatus !== true) {
							throw new \Exception($itemStatus);
						}
					} else if($eventData['msg_type'] == "sms") {
						//$itemStatus = self::sendSMS($eventData);
					} else if($eventData['msg_type'] == "fax") {
						//$itemStatus = self::sendFAX($eventData);
					} else if($eventData['msg_type'] == "postalmethod") {
						//$itemStatus = self::sendPostalLetter($eventData);
					} else if($eventData['msg_type'] == "internalmessage") {
						//$itemStatus = self::sendInternalMessage($eventData);
					}

					$responceData['status'] = true;
				}
			}
		} catch (\Throwable $e) {
			$responceData = array(
				'status' => false,
				'error' => $e->getMessage(),
				'req_body' => '',
				'req_responce' => ''
			);
		}

		return $responceData;
	}

	public static function handleQueryEndPoint($dataItem = array()) {
		$responceData = array(
			'status' => false,
			'req_body' => '',
			'req_responce' => ''
		);

		try {
			if(!empty($dataItem)) {
				$eventData = isset($dataItem['event']) ? $dataItem['event'] : array();
				$config = isset($dataItem['config']) ? $dataItem['config'] : array();
				$dItems = isset($dataItem['data_item']) ? $dataItem['data_item'] : array();
				$ope_action = isset($config['operation_action']) ? html_entity_decode(base64_decode($config['operation_action'])) : '';

				if(empty($ope_action)) {
					throw new \Exception('Operation action value is empty.');
				}

				foreach ($dItems as $dik => $dItem) {
					if(!empty($config['time_delay']) && $config['time_delay'] != 0) {
						sleep($config['time_delay']);
					}

					$rpvars = array(
					  '@tablename' => isset($eventData['tablename']) ? $eventData['tablename'] : "",
					  '@id' => isset($eventData['uniqueid']) ? $eventData['uniqueid'] : "",
					  '@nt_id' => isset($eventData['id']) ? $eventData['id'] : ""
					);

					foreach ($dItem as $iField => $itemValue) {
						$rpvars['@'.$iField] = $itemValue;
					}

					$fetchDataSql = strtr($ope_action, $rpvars);
					
					$resultItems = self::fetchDataSet($fetchDataSql, true);

					$responceData = array(
						'status' => true,
						'req_body' => $fetchDataSql,
						'req_responce' => ''
					);
					
				}
			}
		} catch (\Throwable $e) {
			$responceData = array(
				'status' => false,
				'error' => $e->getMessage(),
				'req_body' => isset($fetchDataSql) ? $fetchDataSql : '',
				'req_responce' => ''
			);
		}

		return $responceData;
	}

	public static function fetchDataForAction($item = array()) {
		try {
			if(!empty($item)) {
				$dataQuery = (isset($item['config_item']) && isset($item['config_item']['data_query']) && $item['config_item']['data_query'] != "") ? $item['config_item']['data_query'] : "";

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
				  '@id' => isset($item['uniqueid']) ? $item['uniqueid'] : "",
				  '@nt_id' => isset($item['id']) ? $item['id'] : "",
				  '@id_in' => !empty($tmpIdArray) ? "'" . implode("','",$tmpIdArray) . "'" : ''
				);

				$rpvars = array_merge($rpvars, $preparedIdArray);

				if(isset($dataQuery) && $dataQuery != "") {
					$fetchDataSql = strtr($dataQuery, $rpvars);
				} else {
					$fetchDataSql = "SELECT * FROM `".$tablename."` as tb WHERE id = '".$uniqueid."'";
				}

				$resultItems = self::fetchDataSet($fetchDataSql);

				return $resultItems;

			}
		} catch(\Exception $e) {
			throw new \Exception($e->getMessage());
		}

		return false;
	}

	public static function savePreparedData($data) {
		//Extract value to variable
		extract($data);

		//Write new record
		$sql = "INSERT INTO `vh_action_reminder_log` ( ";
		$sql .= "event_id, config_id, msg_type, template_id, message, to_send, operation_type, event_type, tablename, uniqueid, uid, user_type, sent, sent_time, trigger_time, time_delay, created_time ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$event_id,
			$config_id,
			$msg_type,
			$template_id,
			$message,
			$to_send,
			$operation_type,
			$event_type,
			$tablename,
			$uniqueid,
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

	public static function getMsgContent($communication_type = '', $template = '', $pc_id = false, $appt_date = false, $eid = false) {
		$messageData = self::getFullMessage($pc_id, $template, $appt_date, $eid);

		$message_text = '';
		if($communication_type == 'sms' || $communication_type == 'internalmessage') {
			$message_text = isset($messageData['content']) ? $messageData['content'] : "";
		} else {
			$message_text = isset($messageData['content_html']) ? $messageData['content_html'] : "";
		}

		return $message_text;
	}

	public static function getFullMessage($pid = false, $template, $appt_date = false, $eid = false) {
		$pat_language = 'en';

		if($pid !== false) {
			$pat_data = self::getPatientData($pid);
			$pat_language = $pat_data['language'];
		}

		$pc_aid = false;
		$pc_facility = false;
		
		if($eid !== false) {
			$appt = @new \wmt\Appt($eid);
			$pc_aid = $appt->pc_aid;
			$pc_facility = $appt->pc_facility;
		}

		// Get message template
		$template = \wmt\Template::Lookup($template, $pat_language);

		// Fetch merge data
		$data = @new \wmt\Grab($pat_language);
		@$data->loadData($pid, $pc_aid, $pc_facility, $eid, $appt_date, $eid);

		// Perform data merge
		$template->Merge($data->getData());
		$content = $template->text_merged;
		$content_html = $template->html_merged;

		return array('content' => $content, 'content_html' => $content_html);
	}

	public function rpStrVariable($dataStr = '', $params = array()) {
		return strtr($dataStr, $params);
	}

	public static function fetchDataSet($dataSet = '', $sqlExecute = false) {
		$resultItems = array();
		try {
			if(!empty($dataSet)) {
				$isDataSetJson = self::isJson($dataSet);
				$dataSetSource = $dataSet;
				if($isDataSetJson) $dataSetSource = json_decode($dataSetSource);

				if(gettype($dataSetSource) === "string") {
					if($sqlExecute === true) {
						sqlStatementNoLogExecute($dataSetSource);
						return true;
					} 

					$dataSetResult = sqlStatementNoLogExecute($dataSetSource);
					while ($result_data = sqlFetchArray($dataSetResult)) {
						$resultItems[] = $result_data;
					}
				} else if(gettype($dataSetSource) === "object") {
					if(!isset($dataSetSource->db_type) || !isset($dataSetSource->host) || !isset($dataSetSource->port) || !isset($dataSetSource->database) || !isset($dataSetSource->user) || !isset($dataSetSource->query)) {
						throw new \Exception("Query details not valid");
					}

					if(empty($dataSetSource->db_type) || empty($dataSetSource->host) || empty($dataSetSource->port) || empty($dataSetSource->database) || empty($dataSetSource->user) || empty($dataSetSource->query)) {
						throw new \Exception("Query details not valid");
					}

					if($dataSetSource->db_type == "mysqli") {
						$dbconn = ADONewConnection("mysqli");
						$dbconn->Connect($dataSetSource->host, $dataSetSource->user, $dataSetSource->password, $dataSetSource->database);
						if($sqlExecute === true) {
							$dbconn->Query($dataSetSource->query);
							$dbconn->Disconnect();
							return true;
						}

						$dbconn->setFetchMode(ADODB_FETCH_ASSOC);
						$dataSetResult = $dbconn->Execute($dataSetSource->query);
						$resultItems = $dataSetResult->GetAll();
						$dbconn->Disconnect();
					} else if($dataSetSource->db_type == "pgsql") {
						$dbconn = ADONewConnection("pgsql");
						$dbconn->Connect($dataSetSource->host, $dataSetSource->user, $dataSetSource->password, $dataSetSource->database);

						if($sqlExecute === true) {
							$dbconn->Query($dataSetSource->query);
							$dbconn->Disconnect();
							return true;
						}


						$dbconn->setFetchMode(ADODB_FETCH_ASSOC);
						$dataSetResult = $dbconn->Execute($dataSetSource->query);
						$resultItems = $dataSetResult->GetAll();
						$dbconn->Disconnect();
					} else {
						throw new \Exception("Query Type is invalid");
					}
				}
			} else {
				throw new \Exception("DataSource value is empty");
			}
		} catch(\Throwable $e) {
			throw new \Exception($e->getMessage());
		}

		return $resultItems;
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

			if($pk == "action_type") {
				if(!empty($pkValue)) {
					$sqlWhere[] = "ac.`action_type` = ?";
					$binds[] = $pkValue;
				}
			}
		}

		if(!empty($sqlWhere)) {
			$sql .= "WHERE ac.`active` = 0 AND " . implode(" AND ", $sqlWhere) . " ";
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

	public static function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
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

	public static function isNotifyDataExists($event_id, $tablename, $uniqueid, $send_status, $config_id) {
		$row = sqlQuery("SELECT * FROM `vh_action_reminder_log` WHERE event_id = ? AND config_id = ? AND tablename = ? AND uniqueid = ? AND sent = ? ", array($event_id, $config_id, $tablename, $uniqueid, $send_status));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function preProcessingQuery($dataItems = array(), $event_type = false) {
		// $dataItems = self::getDataForProcess($ids, $event_type);

		foreach ($dataItems as $key => $item) {
			if(isset($item['config_id']) && !empty($item['config_id'])) {
				if(isset($item['config_item'])) {
					$config = $item['config_item'];
				}

				$replacevars = array(
				  '$nid' => isset($item['id']) ? $item['id'] : "",
				  '$event_id' => isset($item['event_id']) ? $item['event_id'] : "",
				  '$tablename' => isset($item['tablename']) ? $item['tablename'] : "",
				  '$uniqueid' => isset($item['uniqueid']) ? $item['uniqueid'] : ""
				);

				if(isset($config)) {
					if($item['config_id'] == $config['id']) {
						if($config['active'] == 0) {
							if(!empty(trim($config['pre_processing_data_set']))) {
								$pre_processing = trim($config['pre_processing_data_set']);
								$final_pre_processing = strtr($pre_processing, $replacevars);

								//Execute Query
								sqlStatementNoLog($final_pre_processing);
							}
						}
					}
				}
			}
		}

		return $dataItems;
	}

	public function preProcessingQuery1($ids = array(), $event_type = false) {
		$dataItems = self::getDataForProcess($ids, $event_type);
		$event_ids = array();
		$configList = array();

		foreach ($dataItems as $key => $item) {
			if(!empty($item['id'])) {
				$event_ids[] = $item['id'];
			}

			if(isset($item['config_id']) && !empty($item['config_id'])) {
				if(!isset($configList[$item['config_id']])) {
					$configs = self::getConfiguration($item['config_id'], '', '', $item['event_id']);
					if(isset($configs) && count($configs) > 0) {
						foreach ($configs as $configKey => $config) {
							$configList[$config['id']] = $config;
						}
					}
				}

				if(isset($configList[$item['config_id']])) {
					$config = $configList[$item['config_id']];
				}

				$replacevars = array(
				  '$nid' => isset($item['id']) ? $item['id'] : "",
				  '$event_id' => isset($item['event_id']) ? $item['event_id'] : "",
				  '$tablename' => isset($item['tablename']) ? $item['tablename'] : "",
				  '$uniqueid' => isset($item['uniqueid']) ? $item['uniqueid'] : ""
				);

				if(isset($config)) {
					if($item['config_id'] == $config['id']) {
						$dataItems[$key]['config_item'] = $config;
						
						if($config['active'] === 0) {
							if(!empty(trim($config['pre_processing_data_set']))) {
								$pre_processing = trim($config['pre_processing_data_set']);
								$final_pre_processing = strtr($pre_processing, $replacevars);

								//Execute Query
								sqlStatementNoLog($final_pre_processing);
							}
						}
					}
				}
			}
		}

		//return $event_ids;
		return $dataItems;
	}

	public static function getDataForProcess($ids = array(), $event_type = false, $limit = 100, $offset = 0) {
		$sql = "SELECT * FROM `vh_action_reminder_log` WHERE ";

		if(!empty($ids)) {
			$sql .= " `id` IN (".implode(",", $ids).") AND ";
		}

		if($event_type !== false) {
			$sql .= " event_type = '".$event_type."' AND ";
		}

		$sql .= " sent = 0 ORDER BY seq LIMIT ".$offset.", ".$limit."";

		$resultItems = array();
		$result = sqlStatementNoLog($sql);
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	//Get Data For Send by Event Id
	public static function getSendItemIdByEvent($params = array(), $limit = 100, $offset = 0) {
		$tablename = (isset($params['tablename']) && !empty($params['tablename'])) ? $params['tablename'] : "";
		$eventid_param = (isset($params['event_id']) && !empty($params['event_id'])) ? $params['event_id'] : "";
		$configid_param = (isset($params['config_id']) && !empty($params['config_id']) && !empty($eventid_param)) ? $params['config_id'] : "";
		$update_status = (isset($params['status'])) ? $params['status'] : false;
		$resultItems = array();
		$idList = [];
		$configList = [];

		$configs = self::getActionConfigurationByParam(array(
				'id' => $eventid_param,
				'config_id' => $configid_param,
				'action_type' => 'action_reminder',
			));

		foreach ($configs as $key => $action_config) {
			foreach ($action_config['config_data'] as $key => $config_data) {
				$config = array_merge($action_config, $action_config['config_data'][$key]);
				$configList[$config['event_id'] .'~'. $config['id']] = $config;
			}
		}

		if(!empty($tablename)) {
			$sql = "SELECT * FROM `".$tablename."` WHERE ";
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
				if(!empty($result_data['id'])) {
					if(isset($configList) && count($configList) > 0) {
						if(isset($configList[$result_data['event_id'].'~'.$result_data['config_id']])) {
							$result_data['config_item'] = $configList[$result_data['event_id'].'~'.$result_data['config_id']];
						}
					}

					$resultItems[] = $result_data;
					$idList[] = $result_data['id'];
				}
			}
		}

		if($update_status === true) {
			if(!empty($idList)) {
				sqlStatementNoLog("UPDATE `vh_action_reminder_log` SET sent = -1 WHERE id IN ('". implode("','", $idList) ."') ", array());
			}
		}

		return $resultItems;
	}

	public static function isAssoc(array $arr){
	    if (array() === $arr) return false;
	    return array_keys($arr) !== range(0, count($arr) - 1);
	}

	public static function prepareFieldValueForUpdate($result = array(), $field = '', $newValue = '') {
		$preparedValueList = array();
		$fieldValue = json_decode($result[$field], true);
		if(is_array($fieldValue) && !self::isAssoc($fieldValue)) {
			$preparedValueList = $fieldValue;
		} else if(is_array($fieldValue) && self::isAssoc($fieldValue)) {
			$preparedValueList = array($fieldValue);
		} else if(trim($result[$field]) != ""){
			if(is_array($fieldValue)) {
				$preparedValueList = array($fieldValue);
			} else {
				$preparedValueList = array($result[$field]);
			}
		}

		$preparedValueList[] = self::isJson($newValue) ? json_decode($newValue, true) : $newValue;

		if(is_array($preparedValueList) && count($preparedValueList) > 1) {
			return json_encode($preparedValueList);
		} else if(is_array($preparedValueList) && count($preparedValueList) == 1) {
			if(is_array($preparedValueList[0])) {
				return json_encode($preparedValueList[0]);
			} else {
				return $preparedValueList[0];
			}
		}

		return $newValue;
	}

	public static function updatePreparedData($id, $data) {
		if(!empty($data) && !empty($id)) {
			$binds = array();
			
			$ids = $id;
			if(!is_array($id)) {
				$ids = array($id);
			}

			foreach ($ids as $idk => $id_item) {
				$exiting_result = sqlQuery("SELECT * FROM `vh_action_reminder_log` WHERE id = ?  ", array($id_item));
		
				if(empty($exiting_result)) {
					$exiting_result = array();
				}
				$setColsList = array();

				foreach ($data as $data_field => $item) {
					$setColsList[] = $data_field." = ?";
					
					if($data_field == "status" || $data_field == "request_body" || $data_field == "request_responce") {
						$binds[] =  self::prepareFieldValueForUpdate($exiting_result, $data_field, $item);
					} else {
						$binds[] = $item;
					}
				}

				$setStr = implode(", ", $setColsList);
				$binds[] = $id_item;

				sqlStatementNoLog("UPDATE `vh_action_reminder_log` SET ".$setStr." WHERE id = ? ", $binds);
			}
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

	/*
	public static function setTimeZone() {
		$glres = sqlQuery(
        "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name = 'gbl_time_zone'" .
	        "ORDER BY gl_name, gl_index"
	    );

	    if (!empty($glres['gl_value'])) {
            date_default_timezone_set($glres['gl_value']);
        }
	}*/

	public static function sendEmail($data) {
		$status = true;

		if(!empty($data)) {
			try {

				$email_direct = '';
				$env_mode = Reminder::getEnvMode();
				
				// if($email_direct === false) {
				// 	return false;
				// }

				$message_list = new \wmt\Options('Reminder_Email_Messages');

				$subject = '';
				$template = isset( $data['template_id']) ?  $data['template_id'] : '';

				if(!empty($message_list) && isset($message_list->list[$template])) {
					$subject = $message_list->list[$template]['notes'];
				}

				$fromValue = isset($data['email_from']) ? $data['email_from'] : $GLOBALS['patient_reminder_sender_email'];
				$temail_direct = isset($data['to_send']) ? $data['to_send'] : '';
				$email_direct = array();
				if(!empty($temail_direct)) {
					$temail_direct = explode(",",$temail_direct);
					foreach ($temail_direct as $edi => $edItem) {
						$edItem = trim($edItem);
						if(filter_var($edItem, FILTER_VALIDATE_EMAIL)) {
							if(!in_array($edItem, $email_direct)) {
								$email_direct[] = $edItem;
							}
						}
					}
				}

				if(empty($email_direct)) {
					throw new \Exception("Empty email list.");
				}

				if(isset($data['config_item']) && isset($data['config_item']['test_mode']) && $data['config_item']['test_mode'] == 1) {
					$data['message'] = "Message To: ". implode(',', $email_direct) ."<br/>" . $data['message'];
					$email_direct = Reminder::getTestModeValue("email");
				}

				$patientValue = isset($data['email_patient']) ? $data['email_patient'] : ' ';

				$eItem = array(
				'pid' => $data['pid'],
				'data' => array(
					'from' => $fromValue,
					'email' => $email_direct,
					'template' => $data['template_id'],
					'subject' => $subject,
					'patient' => $patientValue,
					'html' => $data['message'],
					'text' => $data['message'],
					'request_data' => array(),
					'files' => array(),
				));

				$eData = EmailMessage::TransmitEmail(
					array($eItem['data']), 
					array('pid' => $eItem['pid'], 'logMsg' => true)
				);

				if(is_array($eData) && count($eData) >= 1) {
					$responce = $eData[0];
					if(isset($responce) && isset($responce['errors']) && !empty($responce['errors'])) {
						throw new \Exception(implode(",",$responce['errors']));
					}
				} else {
					throw new \Exception("Something went wrong.");
				}

				// $email_data = array(
				// 	'patient' => $patientValue,
				// 	'from' => $fromValue,
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
				// self::setTimeZone();

			} catch (Exception $e) {
				$status = $e->getMessage();
			}

			// if(isset($data['pid']) && !empty($data['pid'])) {
			// 	$isActive = EmailMessage::isActive($status);

			// 	if($isActive === false) {
			// 		foreach ($email_direct as $eik => $emailI) {
			// 			$email_data['email'] = $emailI;
			// 			$email_data['pid'] = $data['pid'];
			// 			$email_data['request'] = array(
			// 				'message' => $email_data['message_content'],
			// 				'pid' => $data['pid'],
			// 				'email_id' => $emailI, 
			// 				'subject' => $email_data['subject'],
			// 				'baseDocList' => array()
			// 			);

			// 			$msgLogId = EmailMessage::logEmailData($status, $email_data);
			// 		}
					
			// 	}
			// }
		}

		return $status;
	}

	/*Get Patient Data*/
	public static function getPatientData($pid, $given = "*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS"){
	    $sql = "select $given from patient_data where pid=? order by date DESC limit 0,1";
	    return sqlQuery($sql, array($pid));
	}
}