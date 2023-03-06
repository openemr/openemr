<?php

namespace OpenEMR\OemrAd;

@include_once(__DIR__ . "/../interface/globals.php");
@include_once(__DIR__ . "/mdReminder.class.php");

use OpenEMR\OemrAd\Reminder;

class HubspotSync {

	const ACCEPTED_CODES = '200, 201, 202';
	static $fieldMapping = array(
		'fname' => 'firstname',
		'mname' => 'middlename',
		'lname' => 'lastname',
		'organization' => 'company',
		'specialty' => 'jobtitle',
		'email' => 'email',
		'fax' => 'fax',
		'phonecell' => 'mobilephone',
		'street' => 'address',
		'city' => 'city',
		'state' => 'state',
		'zip' => 'zip',
	);

	/*Constructor*/
	public function __construct() {
	}

	public function prepareNotificationData($prepareFor = 'both', $eventid_param = '', $configid_param = '') {
		$configs = self::getActionConfigurationByParam(array(
			'id' => $eventid_param,
			'config_id' => $configid_param,
			'action_type' => 'hubspot_sync',
		));
		$totalPreparedItem = 0;
		$preparedItemStatus = array();

		if(isset($configs)) {
			foreach ($configs as $key => $action_config) {
				foreach ($action_config['config_data'] as $key => $config_data) {
					$config = array_merge($action_config, $action_config['config_data'][$key]);
					$event_id = $config['event_id'];

					if(isset($config['configuration_id']) && !empty($config['configuration_id']) && $config['active'] == "0" && !empty($config['id'])) {

						if($config['action_type'] == "hubspot_sync") {
							if($config['as_trigger_type'] == "time" && ($prepareFor == 'both' || $prepareFor == 'time')) {
								
								if(isset($config['data_set']) && !empty($config['data_set'])) {
									$dataSetQtr = trim($config['data_set']);

									$resultItems = array();
									$result = sqlStatementNoLog($dataSetQtr);
									if($result) {
										while ($result_data = sqlFetchArray($result)) {
											$resultItems[] = $result_data;

											//$pc_id = isset($result_data['pid']) ? $result_data['pid'] : "";
											$tablename = isset($result_data['tablename']) ? $result_data['tablename'] : "";
											$uniqueid = isset($result_data['uniqueid']) ? $result_data['uniqueid'] : "";
											$qtr_trigger_time = isset($result_data['trigger_time']) ? $result_data['trigger_time'] : "";
											$mode_val = isset($result_data['mode']) ? $result_data['mode'] : "";

											if(!empty($tablename) && !empty($uniqueid)) {
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

													if(!empty($triggerTime)) {
														$preparedData = array(
															'event_id' => $event_id,
															'config_id' => $config['id'],
															'event_type' => '1',
															'mode' => $mode_val,
															'tablename' => $tablename,
															'uniqueid' => $uniqueid,
															'uid' => $uid,
															'user_type' => $user_type,
															'sent' => '0',
															'sent_time' => NULL,
															'trigger_time' => $triggerTime,
															'time_delay' => $config['time_delay']
														);

														self::savePreparedData($preparedData);
														$totalPreparedItem++;

														$preparedItemStatus = Reminder::prepareItemStatus($config, $preparedItemStatus);
													}
												}
											}
										}
									}
								}
								
							} else if($config['as_trigger_type'] == "event" && ($prepareFor == 'both' || $prepareFor == 'event')) {
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
				}
			}
		}

		return array('total_prepared_item' => $totalPreparedItem, 'prepared_item_status' => $preparedItemStatus);
	}

	public static function hubspotSyncByEvent($type = 0, $eventid_param = '', $configid_param = '') {
		$itemIdList = array();
		$event_type = (isset($type) && $type !== 0) ? $type : false;
		
		$iIds = Reminder::getSendItemIdByEvent(array(
			'tablename' => 'vh_hubspot_sync_log',
			'event_type' => $event_type,
			'event_id' => $eventid_param,
			'config_id' => $configid_param
		));

		if(!empty($iIds)) {
			return self::hubspotSync($type, $iIds);
		}

		return array('total_items' => 0, 'total_sent_item' => 0);
	}

	public function hubspotSync($type = 0, $itemIds = array()) {
		$totalItem = 0;
		$totalsentItem = 0;
		$event_type = (isset($type) && $type !== 0) ? $type : false;

		//Run Pre processing query
		$dataItems = self::preProcessingQuery($itemIds, $event_type);
		//$preparedDataForSync = array();

		foreach ($dataItems as $key => $item) {
			if(!empty($item['trigger_time'])) {
				$p_data_item = array();

				$trigger_time = $item['trigger_time'];

				//Unix time
				$current_unix_time = strtotime('now');
				$trigger_unix_time = strtotime($item['trigger_time']);

				if($item['sent'] == 0 && $trigger_unix_time <= $current_unix_time) {

					$euniqueId = isset($item['uniqueid']) ? $item['uniqueid'] : '';

					try {
						//If Config Empty
						if(!isset($item['config_item']) || empty($item['config_item'])) {
							throw new \Exception('Config Not Found.');
						}

						//If Config not active
						if(!isset($item['config_item']['active']) || $item['config_item']['active'] === 1 ) {
							throw new \Exception('Config Not active.');
						}

						//If Api Config Empty
						if(!isset($item['config_item']['api_config']) || empty($item['config_item']['api_config'])) {
							throw new \Exception('Api config empty.');
						}

						//If Api Config Not Found
						if(!isset($item['config_item']['api_config_id']) || empty($item['config_item']['api_config_id'])) {
							throw new \Exception('Api config not found.');
						}

						$config = isset($item['config_item']) ? $item['config_item'] : array();
						$token = isset($config['token']) ? $config['token'] : '';
						$reqTemplate = isset($config['request_template']) ? base64_decode($config['request_template']) : "";

						if(isset($config['sync_mode']) && $config['sync_mode'] === '0') {
							//Fetch Data For Sync	
							$syncData = self::fetchDataForSync($item);

							if(is_array($syncData) && count($syncData) > 1) {
								throw new \Exception('More then one data set found.');
							}

							$syncData = (is_array($syncData) && count($syncData) === 1) ? $syncData[0] : $syncData;

							//Get mapping data
							$mData = self::getMappingData(array(
								'address_id' => $euniqueId
							));

							$item['mabook_id'] = isset($mData['address_id']) ? $mData['address_id'] : '';
							$item['mhubspot_id'] = isset($mData['hubspot_id']) ? $mData['hubspot_id'] : '';

							$contactData = array();
							if(!empty($item['mhubspot_id'])) {
								//Get contact data
								$contactData = self::getContactData($item['mhubspot_id'], array(
									'hapiKey' => $token,
									'properties' => 'properties=pro_care_contact_types'
								));
							}

							$reqtData = self::generateBodyParam($reqTemplate);
							$oemrFilterData = isset($reqtData['oemr_filter']) ? $reqtData['oemr_filter'] : array();
							
							$fData = $hubspotFilterData;

							//Filter openemr data
							if(!empty($syncData)) {
								$oemrFilterData = isset($reqtData['oemr_filter']) ? $reqtData['oemr_filter'] : array();
								$oFilterStatus = self::filterStatus($syncData, $oemrFilterData);

								if($oFilterStatus === false) {
									$syncData = array();
								}
							}

							//Filter Hubspot Data
							if(!empty($contactData)) {
								$hubspotFilterData = isset($reqtData['hubspot_filter']) ? $reqtData['hubspot_filter'] : array();
								$cData = isset($contactData['properties']) ? $contactData['properties'] : array();
								$hFilterStatus = self::filterStatus($cData, $hubspotFilterData);

								if($hFilterStatus === false) {
									$contactData = array();
								}
							}

							$p_data_item = array(
								'event' => $item,
								'config' => $config,
								'contact_data' => !empty($contactData) ? $contactData : array(),
								'map_data' => !empty($mData) ? $mData : array(),
								'data_item' => !empty($syncData) ? $syncData : array()
							);
							//$preparedDataForSync[] = $p_data_item;
						} else if(isset($config['sync_mode']) && $config['sync_mode'] === '1') {
							//Get contact data
							$contactData = self::getContactData($euniqueId, array(
								'hapiKey' => $token,
								'properties' => 'properties=pro_care_contact_types'
							));

							$companyData = self::getCompanyData($euniqueId, array(
								'hapiKey' => $token
							));

							//Get mapping data
							$mData = self::getMappingData(array(
								'hubspot_id' => $euniqueId
							));

							$item['mabook_id'] = isset($mData['address_id']) ? $mData['address_id'] : '';
							$item['mhubspot_id'] = isset($mData['hubspot_id']) ? $mData['hubspot_id'] : '';

							//Fetch Data For Sync	
							$syncData = self::fetchDataForSync($item);
							
							if(is_array($syncData) && count($syncData) > 1) {
								throw new \Exception('More then one data set found.');
							}

							$syncData = (is_array($syncData) && count($syncData) === 1) ? $syncData[0] : $syncData;

							$reqtData = self::generateBodyParam($reqTemplate);
							$oemrFilterData = isset($reqtData['oemr_filter']) ? $reqtData['oemr_filter'] : array();
							
							$fData = $hubspotFilterData;

							//Filter openemr data
							if(!empty($syncData)) {
								$oemrFilterData = isset($reqtData['oemr_filter']) ? $reqtData['oemr_filter'] : array();
								$oFilterStatus = self::filterStatus($syncData, $oemrFilterData);

								if($oFilterStatus === false) {
									$syncData = array();
								}
							}

							//Filter Hubspot Data
							if(!empty($contactData)) {
								$hubspotFilterData = isset($reqtData['hubspot_filter']) ? $reqtData['hubspot_filter'] : array();
								$cData = isset($contactData['properties']) ? $contactData['properties'] : array();
								$hFilterStatus = self::filterStatus($cData, $hubspotFilterData);

								if($hFilterStatus === false) {
									$contactData = array();
								}
							}

							$p_data_item = array(
								'event' => $item,
								'config' => $config,
								'contact_data' => !empty($contactData) ? $contactData : array(),
								'company_data' => !empty($companyData) ? $companyData : array(),
								'map_data' => !empty($mData) ? $mData : array(),
								'data_item' => !empty($syncData) ? $syncData : array()
							);
							//$preparedDataForSync[] = $p_data_item;
						}

					} catch (\Exception $e) {
						$p_data_item = array(
							'dStatus' => false,
							'dError' => $e->getMessage(),
							'event' => $item,
							'config' => isset($item['config_item']) ? $item['config_item'] : array(),
							'data_item' => array()
						);
						//$preparedDataForSync[] = $p_data_item;
					}

					if(!empty($p_data_item)) {
						$configItem = isset($p_data_item['config']) ? $p_data_item['config'] : array();
						$event_id = (isset($p_data_item['event']) && isset($p_data_item['event']['id'])) ? $p_data_item['event']['id'] : '';

						if(!empty($configItem['time_delay']) && $configItem['time_delay'] != 0) {
							sleep($configItem['time_delay']);
						}

						$updateData = array(
							'sent' => 2,
							'sent_time' => date('Y-m-d H:i:s'),
							'status' => "Something went wrong",
							'request_body' => ''
						);

						if(isset($p_data_item['dStatus']) && $p_data_item['dStatus'] === false) {
							$updateData['status'] = $p_data_item['dError'];
						} else {
							if(isset($configItem['sync_mode']) && $configItem['sync_mode'] === '0') {
								//Handle Out Sync.
								$rsData = self::handleHubspotOutSync($p_data_item);
							} else if(isset($configItem['sync_mode']) && $configItem['sync_mode'] === '1') {
								//Handle In Sync.
								$rsData = self::handleHubspotInSync($p_data_item);
							}

							//Assign Data
							if(isset($rsData) && $rsData !== false) {
								$updateData = $rsData;
							}
						}

						if(!empty($event_id) && !empty($updateData)) {
							self::updatePreparedData(
								$event_id,
								$updateData
							);

							if(isset($updateData['sent']) && $updateData['sent'] == 1) {
								$totalsentItem++;
							}
						}

						$totalItem++;
					}
				}
			}
		}

		return array('total_items' => $totalItem, 'total_sent_item' => $totalsentItem);
	}

	public static function fetchDataForSync($item = array()) {
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
			  '@id' => isset($item['uniqueid']) ? $item['uniqueid'] : '',
			  '@nt_id' => isset($item['id']) ? $item['id'] : '',
			  '@mabook_id' => isset($item['mabook_id']) && !empty($item['mabook_id']) ? $item['mabook_id'] : 'null',
			  '@mhubspot_id' => isset($item['mhubspot_id']) && !empty($item['mhubspot_id']) ? $item['mhubspot_id'] : 'null'
			);

			$rpvars = array_merge($rpvars, $preparedIdArray);

			if(isset($dataQuery) && $dataQuery != "") {
				$fetchDataSql = strtr($dataQuery, $rpvars);
			} else {
				$fetchDataSql = "SELECT * FROM `".$tablename."` as tb WHERE id = '".$uniqueid."'";
			}

			$resultItems = array();
			$result = sqlStatement($fetchDataSql);

			
			while ($result_data = sqlFetchArray($result)) {
				$resultItems[] = $result_data;
			}

			return $resultItems;
		}

		return false;
	}

	public static function filterStatus($cData = array(), $fData = array()) {
		$fStatus = true;

		if(!empty($cData)) {
			foreach ($fData as $fk => $fItem) {
				$cValue = isset($cData[$fk]) ? $cData[$fk] : '';
				$fValue = isset($fItem['value']) ? $fItem['value'] : '';

				if(isset($fItem['seperator'])) {
					$cValue = explode($fItem['seperator'], $cValue);
				}

				if(is_array($cValue)) {
					if(!in_array($fValue, $cValue)) {
						$fStatus = false;
					}
				} else if($cValue != $fValue) {
					$fStatus = false;
				}
			}
		}

		return $fStatus;
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
			$sql .= "WHERE " . implode(" AND ", $sqlWhere) . " ";
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

	public static function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	public static function isNotifyDataExists($event_id, $tablename, $uniqueid, $send_status, $config_id) {
		$row = sqlQuery("SELECT * FROM `vh_hubspot_sync_log` WHERE event_id = ? AND config_id = ? AND tablename = ? AND uniqueid = ? AND sent = ? ", array($event_id, $config_id, $tablename, $uniqueid, $send_status));
		
		if(isset($row) && !empty($row)) {
			return $row;
		}

		return false;
	}

	public static function savePreparedData($data) {
		//Extract value to variable
		extract($data);

		//Write new record
		$sql = "INSERT INTO `vh_hubspot_sync_log` ( ";
		$sql .= "event_id, config_id, event_type, mode, tablename, uniqueid, uid, user_type, sent, sent_time, trigger_time, time_delay, created_time ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$event_id,
			$config_id,
			$event_type,
			$mode,
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

	public static function getEventTriggerData($event_id = '', $config_id = '') {
		$sql = "SELECT * FROM `vh_hubspot_sync_log` WHERE event_id = ? AND config_id = ? AND (event_type IS NULL OR event_type = '' OR event_type = '0') ";

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

				$preparedData = array(
					'event_type' => '2',
					'uid' => $uid,
					'user_type' => $user_type,
					'time_delay' => $config['time_delay'],
					'sent_time' => NULL
				);

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
				sqlStatementNoLog("UPDATE `vh_hubspot_sync_log` SET ".$setStr." WHERE id = ?", $binds);
			}
		}
	}

	public static function preProcessingQuery($ids = array(), $event_type = false) {
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
		$sql = "SELECT * FROM `vh_hubspot_sync_log` WHERE ";

		if(!empty($ids)) {
			$sql .= " `id` IN (".implode(",", $ids).") AND ";
		}

		if($event_type !== false) {
			$sql .= " event_type = '".$event_type."' AND ";
		}

		$sql .= " sent = 0 ORDER BY created_time LIMIT ".$offset.", ".$limit."";

		$resultItems = array();
		$result = sqlStatementNoLog($sql);
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	public static function getConfiguration($id = '', $type = '', $actionType = '', $eventId = '') {
		$result_list = array();
		$binds = array();

		$sql = "SELECT nc.*, ";
		$sql .= "aec.`api_configuration_type` AS api_configuration_type, ";
		$sql .= "aiwc.api_config_id, aiwc.`user` AS user, aiwc.`password` AS password, aiwc.`role` AS role, aiwc.`organization` AS organization, aiwc.`client` AS client, aiwc.`service_url` AS service_url, aiwc.`warehouse` AS warehouse, aiwc.`token` as token ";
		$sql .= "FROM `notification_configurations` nc ";
		$sql .= "LEFT JOIN `vh_api_event_configurations` aec ON aec.`id` = nc.`api_config` ";
		$sql .= "LEFT JOIN `vh_api_idempiere_webservice_configurations` as aiwc ON aiwc.api_config_id = aec.id AND aec.api_configuration_type = 'hubspot_sync' ";

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

		$aConfigs = array();
		if(!empty($eventId)) {
			$tConfigs = self::getActionConfigurationByParam(array(
				'id' => $eventId,
				'config_id' => $id,
				'action_type' => 'hubspot_sync',
			));

			if(isset($tConfigs)) {
				foreach ($tConfigs as $tek => $teItem) {
					if(isset($teItem['config_data'])) {
						foreach ($teItem['config_data'] as $tck => $tcItem) {
							if(isset($tcItem['id']) && isset($teItem['active']) && $teItem['active'] == 0) {
								$aConfigs[] = $tcItem['id'];
							}
						}
					}
				}
			}
		}

		$result = sqlStatementNoLog($sql, $binds);

		while ($result_data = sqlFetchArray($result)) {
			$cId = isset($result_data['id']) ? $result_data['id'] : '';
			
			$result_data['active'] = 0;

			if(!empty($eventId)) {
				$result_data['active'] = in_array($cId, $aConfigs) ? 0 : 1;
			}

			$result_list[] = $result_data;
		}
		return $result_list;
	}

	//Handle Our Sync
	public static function handleHubspotOutSync($item = array()) {
		$rData = array(
			'sent' => 2,
			'sent_time' => date('Y-m-d H:i:s'),
			'status' => "Something went wrong (handleHubspotOutSync)",
			'request_body' => '',
			'request_responce' => ''
		);

		$config = isset($item['config']) ? $item['config'] : array();
		$contact_data = isset($item['contact_data']) ? $item['contact_data'] : array();
		$map_data = isset($item['map_data']) ? $item['map_data'] : array();
		$data_item = isset($item['data_item']) ? $item['data_item'] : array();

		$modeVal = isset($item['event']) && isset($item['event']['mode']) ? $item['event']['mode'] : "";
		$token = isset($config['token']) ? $config['token'] : '';
		$bodyRequestTemplate = isset($config['request_template']) ? base64_decode($config['request_template']) : "";
		
		//Address Id/Hubspot Id
		$abookId = isset($data_item['abook_id']) ? $data_item['abook_id'] : '';
		$mabookId = isset($map_data['address_id']) ? $map_data['address_id'] : '';
		$mhubspotId = isset($map_data['hubspot_id']) ? $map_data['hubspot_id'] : '';

		$bOpt = array('hapiKey' => $token);

		//Prepare Request
		$bParam = self::prepareRequest(
			$bodyRequestTemplate,
			array(
				'config' => $config,
				'data_item' => $data_item
			)
		);

		try {
			if($modeVal === "INSERT_UPDATE") {
				if(empty($abookId)) {
					throw new \Exception('AddressId is empty - (INSERT_UPDATE)');
				}

				if(!empty($abookId) && empty($map_data) && empty($contact_data)) {
					//Handle Create Operation
					$cData = self::handleOutCreateData($abookId, $bParam, $bOpt);
				} else if(!empty($abookId) && !empty($map_data) && !empty($contact_data)) {
					//Handle Update Operation
					$cData = self::handleOutUpdateData($mabookId, $mhubspotId, $bParam, $bOpt);
				} else {
					throw new \Exception('Unable to perform operation - (INSERT_UPDATE)');
				}

			} else if($modeVal === "INSERT") {
				if(empty($abookId)) {
					throw new \Exception('AddressId is empty - (INSERT)');
				}

				if(!empty($map_data)) {
					throw new \Exception('Mapping values exists - (INSERT)');
				}

				if(!empty($contact_data)) {
					throw new \Exception('Contact data is not empty - (INSERT)');
				}

				//Handle Create Operation
				$cData = self::handleOutCreateData($abookId, $bParam, $bOpt);
			} else if($modeVal === "UPDATE") {
				if(empty($abookId)) {
					throw new \Exception('AddressId is empty - (UPDATE)');
				}

				if(empty($map_data)) {
					throw new \Exception('Mapping value not exists - (UPDATE)');
				}

				if(empty($contact_data)) {
					throw new \Exception('Contact data is empty - (UPDATE)');
				}

				//Handle Update Operation
				$cData = self::handleOutUpdateData($mabookId, $mhubspotId, $bParam, $bOpt);
			} else if($modeVal === "DELETE") {
				if(!empty($abookId)) {
					throw new \Exception('AddressId is not empty - (DELETE)');
				}

				if(empty($map_data)) {
					throw new \Exception('Mapping value not exists - (DELETE)');
				}

				if(empty($contact_data)) {
					throw new \Exception('Contact data is empty - (DELETE)');
				}

				//Handle Delete Operation
				$cData = self::handleOutDeleteData($mabookId, $mhubspotId, $bParam, $bOpt);
			} else {
				throw new \Exception('Undefined mode val');
			}

			//Assign Responce
			if(isset($cData) && !empty($cData)) {
				$rData = $cData;
			}

		} catch (\Exception $e) {
			$rData['sent'] = 2;
			$rData['status'] = $e->getMessage() . '(handleHubspotOutSync)';
		}

		return $rData;
	}

	public static function handleHubspotInSync($item = array()) {
		$rData = array(
			'sent' => 2,
			'sent_time' => date('Y-m-d H:i:s'),
			'status' => "Something went wrong (handleHubspotInSync)",
			'request_body' => ''
		);

		$event_data = isset($item['event']) ? $item['event'] : array();
		$config = isset($item['config']) ? $item['config'] : array();
		$contact_data = isset($item['contact_data']) ? $item['contact_data'] : array();
		$company_data = isset($item['company_data']) ? $item['company_data'] : array();
		$map_data = isset($item['map_data']) ? $item['map_data'] : array();
		$data_item = isset($item['data_item']) ? $item['data_item'] : array();

		$modeVal = isset($item['event']) && isset($item['event']['mode']) ? $item['event']['mode'] : "";
		$token = isset($config['token']) ? $config['token'] : '';
		$bodyRequestTemplate = isset($config['request_template']) ? base64_decode($config['request_template']) : "";
		
		//Address Id/Hubspot Id
		$mabookId = isset($map_data['address_id']) ? $map_data['address_id'] : '';
		$mhubspotId = isset($map_data['hubspot_id']) ? $map_data['hubspot_id'] : '';

		$bOpt = array('hapiKey' => $token);
		$cprops = array(
			'name'
		);

		try {
			$contactId = !empty($contact_data) && isset($contact_data['id']) ? $contact_data['id'] : "";
			$properties = !empty($contact_data) && isset($contact_data['properties']) ? $contact_data['properties'] : array();

			$cproperties = !empty($company_data) && isset($company_data['properties']) ? $company_data['properties'] : array();

			if(!empty($cprops)) {
				foreach ($cprops as $ck => $cVal) {
					$properties['c_'.$cVal] = isset($cproperties[$cVal]) ? $cproperties[$cVal] : '';
				}
			}

			//Prepare Request
			$bParam = self::prepareRequest(
				$bodyRequestTemplate,
				array(
					'config' => $config,
					'data_item' => $properties
				)
			);

			if($modeVal === "INSERT_UPDATE") {
				if(empty($contactId)) {
					throw new \Exception('Contact data is empty - (INSERT_UPDATE)');
				}

				if(!empty($contactId) && empty($map_data) && empty($data_item)) {
					//Handle Create Operation
					$cData = self::handleInCreateData($contactId, $bParam, $bOpt);
				} else if(!empty($contactId) && !empty($map_data) && !empty($data_item)) {
					//Handle Update Operation
					$cData = self::handleInUpdateData($mabookId, $mhubspotId, $bParam, $bOpt);
				} else {
					throw new \Exception('Unable to perform operation - (INSERT_UPDATE)');
				}

			} else if($modeVal === "INSERT") {
				if(empty($contactId)) {
					throw new \Exception('Contact data is empty - (CREATE)');
				}

				if(!empty($map_data)) {
					throw new \Exception('Mapping value exists - (CREATE)');
				}

				if(!empty($data_item)) {
					throw new \Exception('Dataset value exists - (CREATE)');
				}

				//Handle Create Operation
				$cData = self::handleInCreateData($contactId, $bParam, $bOpt);
			} else if($modeVal === "UPDATE") {
				if(empty($contactId)) {
					throw new \Exception('Contact data is empty - (UPDATE)');
				}

				if(empty($map_data)) {
					throw new \Exception('Mapping value not exists - (UPDATE)');
				}

				if(empty($data_item)) {
					throw new \Exception('Dataset not found - (UPDATE)');
				}

				//Handle Update Operation
				$cData = self::handleInUpdateData($mabookId, $mhubspotId, $bParam, $bOpt);
			} else if($modeVal === "DELETE") {
				if(!empty($contactId)) {
					throw new \Exception('Contact data is exists in hubspot - (DELETE)');
				}

				if(empty($map_data)) {
					throw new \Exception('Mapping value not exists - (DELETE)');
				}

				if(empty($data_item)) {
					throw new \Exception('Dataset not found - (DELETE)');
				}

				//Handle Delete Operation
				$cData = self::handleInDeleteData($mabookId, $mhubspotId, $bParam, $bOpt);
			} else {
			 	throw new \Exception('Undefined mode value');
			}

			//Assign Responce
			if(isset($cData) && !empty($cData)) {
				$rData = $cData;
			}

		} catch (\Exception $e) {
			$rData['sent'] = 2;
			$rData['status'] = $e->getMessage() . '(handleHubspotInSync)';
		}

		return $rData;
	}

	public static function handleInCreateData($hId = '', $param = array(), $opt = array()) {
		$rData = array(
			'sent' => 2,
			'sent_time' => date('Y-m-d H:i:s'),
			'status' => "Something went wrong (handleInCreateData)",
			'request_body' => ''
		);

		try {
			if(!empty($hId)) {
				$newAbookId = self::createAddressBook($param);

				if(!empty($newAbookId)) {
					self::logMappingData(array(
						"address_id" => $newAbookId,
						"hubspot_id" => $hId
					));

					$rData = array(
						'sent' => 1,
						'sent_time' => date('Y-m-d H:i:s'),
						'status' => "Success (handleInCreateData)",
						'request_body' => isset($param) && !empty($param) ? json_encode($param) : ""
					);
				} else {
					throw new \Exception('Unable to create contact.');
				}
			}
		} catch (\Exception $e) {
			$rData['sent'] = 2;
			$rData['status'] = $e->getMessage() . '(handleInCreateData)';
		}

		return $rData;
	}

	public static function handleInUpdateData($aId = '', $hId = '', $param = array(), $opt = array()) {
		$rData = array(
			'sent' => 2,
			'sent_time' => date('Y-m-d H:i:s'),
			'status' => "Something went wrong (handleInUpdateData)",
			'request_body' => ''
		);

		try {
			if(!empty($aId)) {
				$newAbookId = self::updateAddressBook($aId, $param);

				if(!empty($newAbookId)) {
					// self::logMappingData(array(
					// 	"address_id" => $aId,
					// 	"hubspot_id" => $hId
					// ));

					$rData = array(
						'sent' => 1,
						'sent_time' => date('Y-m-d H:i:s'),
						'status' => "Success (handleInUpdateData)",
						'request_body' => isset($param) && !empty($param) ? json_encode($param) : ""
					);
				} else {
					throw new \Exception('Unable to update contact.');
				}
			}
		} catch (\Exception $e) {
			$rData['sent'] = 2;
			$rData['status'] = $e->getMessage() . '(handleInUpdateData)';
		}

		return $rData;
	}

	public static function handleInDeleteData($aId = '', $hId = '', $param = array(), $opt = array()) {
		$rData = array(
			'sent' => 2,
			'sent_time' => date('Y-m-d H:i:s'),
			'status' => "Something went wrong (handleInDeleteData)",
			'request_body' => ''
		);

		try {
			if(!empty($aId)) {
				$dRs = self::deleteAddressBook($aId);

				if($dRs) {
					self::deleteLogMappingData($aId, $hId);

					$rData = array(
						'sent' => 1,
						'sent_time' => date('Y-m-d H:i:s'),
						'status' => "Success (handleInDeleteData)",
						'request_body' => isset($param) && !empty($param) ? json_encode($param) : ""
					);
				} else {
					throw new \Exception('Unable to delete contact.');
				}
			}
		} catch (\Exception $e) {
			$rData['sent'] = 2;
			$rData['status'] = $e->getMessage() . '(handleInDeleteData)';
		}

		return $rData;
	}

	public static function handleOutCreateData($aId = '', $param = array(), $opt = array()) {
		$rData = array(
			'sent' => 2,
			'sent_time' => date('Y-m-d H:i:s'),
			'status' => "Something went wrong (handleOutCreateData)",
			'request_body' => '',
			'request_responce' => ''
		);

		try {
			if(!empty($aId)) {
				$contactResponce = self::createContactData(json_encode($param), $opt);

				if(isset($contactResponce)) {
					if(isset($contactResponce['id']) && !empty($contactResponce['id'])) {
						//Log data into mapping table.
						self::logMappingData(array(
							'address_id' => $aId,
							'hubspot_id' => $contactResponce['id']
						));
					}

					$rData = array(
						'sent' => (isset($contactResponce['status']) && $contactResponce['status'] === "error") ? 2 : 1,
						'sent_time' => date('Y-m-d H:i:s'),
						'status' => isset($contactResponce['message']) ? $contactResponce['message'] : "Success (handleOutCreateData)",
						'request_body' => isset($param) && !empty($param) ? json_encode($param) : "",
						'request_responce' => isset($contactResponce) && !empty($contactResponce) ? json_encode($contactResponce) : ""
					);
				}
			}
		} catch (\Exception $e) {
			$rData['sent'] = 2;
			$rData['status'] = $e->getMessage()  . '(handleOutCreateData)';
		}

		return $rData;
	}

	public static function handleOutUpdateData($aId = '', $hId = '', $param = array(), $opt = array()) {
		$rData = array(
			'sent' => 2,
			'sent_time' => date('Y-m-d H:i:s'),
			'status' => "Something went wrong (handleOutUpdateData)",
			'request_body' => '',
			'request_responce' => ''
		);

		try {
			if(!empty($aId) && !empty($hId)) {
				$contactResponce = self::updateContactData($hId, json_encode($param), $opt);
				
				if(isset($contactResponce)) {
					// if(isset($contactResponce['id']) && !empty($contactResponce['id'])) {
					// 	//Log data into mapping table.
					// 	self::logMappingData(array(
					// 		'address_id' => $aId,
					// 		'hubspot_id' => $contactResponce['id']
					// 	));
					// }

					$rData = array(
						'sent' => (isset($contactResponce['status']) && $contactResponce['status'] === "error") ? 2 : 1,
						'sent_time' => date('Y-m-d H:i:s'),
						'status' => isset($contactResponce['message']) ? $contactResponce['message'] : "Success (handleOutUpdateData)",
						'request_body' => isset($param) && !empty($param) ? json_encode($param) : "",
						'request_responce' => isset($contactResponce) && !empty($contactResponce) ? json_encode($contactResponce) : ""
					);
				}
			}
		} catch (\Exception $e) {
			$rData['sent'] = 2;
			$rData['status'] = $e->getMessage() . '(handleOutUpdateData)';
		}


		return $rData;
	}

	public static function handleOutDeleteData($aId = '', $hId = '', $param = array(), $opt = array()) {
		$rData = array(
			'sent' => 2,
			'sent_time' => date('Y-m-d H:i:s'),
			'status' => "Something went wrong (handleOutDeleteData)",
			'request_body' => '',
			'request_responce' => ''
		);

		try {
			if(!empty($aId) && !empty($hId)) {
				$contactResponce = self::deleteContactData($hId, $opt);

				if(!isset($contactResponce['status'])) {
					self::deleteLogMappingData($aId, $hId);
				}
				
				$rData = array(
					'sent' => (isset($contactResponce['status']) && $contactResponce['status'] === "error") ? 2 : 1,
					'sent_time' => date('Y-m-d H:i:s'),
					'status' => isset($contactResponce['message']) ? $contactResponce['message'] : "Success (handleOutDeleteData)",
					'request_body' => isset($param) && !empty($param) ? json_encode($param) : "",
					'request_responce' => isset($contactResponce) && !empty($contactResponce) ? json_encode($contactResponce) : ""
				);
			}
		} catch (\Exception $e) {
			$rData['sent'] = 2;
			$rData['status'] = $e->getMessage() . '(handleOutDeleteData)';
		}

		return $rData;
	}

	public function prepareOutData($data = array()) {
		$pData = array();

		if(!empty(self::fieldMapping)) {
			foreach (self::fieldMapping as $fmKey => $fmItem) {
				$pData[$fmItem] = isset($data[$fmKey]) ? $data[$fmKey] : "";
			}
		}

		return $pData;
	}

	public static function getContactData($objectId, $options = array()) {
		if(isset($objectId) && !empty($objectId)) {
			$hapiKey = isset($options['hapiKey']) ? $options['hapiKey'] : '';
			$extraProp = isset($options['properties']) ? '&' . $options['properties'] : '';
			$contactResponce = self::callRequest(
				'', 
				array(
					"url" => "https://api.hubapi.com/crm/v3/objects/contacts/$objectId?properties=email&properties=firstname&properties=lastname&properties=company&properties=jobtitle&properties=fax&properties=phone&properties=mobilephone&properties=address&properties=city&properties=state&properties=zip&properties=country" . $extraProp . "&hapikey=$hapiKey",
					"method" => "GET",
				)
			);

			return $contactResponce;
		}

		return false;
	}

	public static function getCompanyData($objectId, $options = array()) {
		if(isset($objectId) && !empty($objectId)) {
			$hapiKey = isset($options['hapiKey']) ? $options['hapiKey'] : '';
			$extraProp = isset($options['properties']) ? '&' . $options['properties'] : '';
			$contactAssociationsRes = self::callRequest(
				'', 
				array(
					"url" => "https://api.hubapi.com/crm/v3/objects/contacts/$objectId/associations/company?limit=500" . $extraProp . "&hapikey=$hapiKey",
					"method" => "GET",
				)
			);

			if(!empty($contactAssociationsRes) && isset($contactAssociationsRes['results'])) {
				if(count($contactAssociationsRes['results']) > 0) {
					$assocItem = isset($contactAssociationsRes['results'][0]) ? $contactAssociationsRes['results'][0] : array();
 
					if(isset($assocItem['id'])) {
						$companyRes = self::callRequest(
							'', 
							array(
								"url" => "https://api.hubapi.com/crm/v3/objects/companies/".$assocItem['id']."?archived=false" . $extraProp . "&hapikey=$hapiKey",
								"method" => "GET",
							)
						);

						if(isset($companyRes) && isset($companyRes['id'])) {
							return $companyRes;
						}
					}
				}
			}
		}

		return false;
	}

	public static function getAllContactData($options = array()) {
		$hapiKey = isset($options['hapiKey']) ? $options['hapiKey'] : '';
		$extraProp = isset($options['properties']) ? '&' . $options['properties'] : '';
		$contactResponce = self::callRequest(
			'', 
			array(
				"url" => "https://api.hubapi.com/crm/v3/objects/contacts?properties=email&properties=firstname&properties=lastname&properties=company&properties=jobtitle&properties=fax&properties=phone&properties=mobilephone&properties=address&properties=city&properties=state&properties=zip&properties=country" . $extraProp . "&hapikey=$hapiKey",
				"method" => "GET",
			)
		);

		return $contactResponce;
	}

	public static function createContactData($payload = array(), $options = array()) {
		if(isset($payload) && !empty($payload)) {
			$hapiKey = isset($options['hapiKey']) ? $options['hapiKey'] : '';
			$contactResponce = self::callRequest(
				$payload, 
				array(
					"url" => "https://api.hubapi.com/crm/v3/objects/contacts?hapikey=$hapiKey",
					"method" => "POST",
					"header" => ["Content-Type: application/json", "Accept: application/json"]
				)
			);

			return $contactResponce;
		}

		return false;
	}

	public static function updateContactData($objectId = '', $payload = array(), $options = array()) {
		if(isset($payload) && !empty($payload) && !empty($objectId)) {
			$hapiKey = isset($options['hapiKey']) ? $options['hapiKey'] : '';
			$contactResponce = self::callRequest(
				$payload, 
				array(
					"url" => "https://api.hubapi.com/crm/v3/objects/contacts/$objectId?hapikey=$hapiKey",
					"method" => "PATCH",
					"header" => ["Content-Type: application/json", "Accept: application/json"]
				)
			);

			return $contactResponce;
		}

		return false;
	}

	public static function deleteContactData($objectId = '', $options = array()) {
		if(!empty($objectId)) {
			$hapiKey = isset($options['hapiKey']) ? $options['hapiKey'] : '';
			$contactResponce = self::callRequest(
				'', 
				array(
					"url" => "https://api.hubapi.com/crm/v3/objects/contacts/$objectId?hapikey=$hapiKey",
					"method" => "DELETE"
				)
			);

			return $contactResponce;
		}

		return false;
	}

	public static function prepareRequest($req = '', $data = array(), $items = array()) {
		$variableList = self::getParamForRequest($data);
		if(!empty($items)) {
			$variableList['items'] = $items;
		}

		$bodyParam = self::generateBodyParam($req, $variableList);
		$bodyParam = isset($bodyParam['data_mapping']) ? $bodyParam['data_mapping'] : '';

		return $bodyParam;
	}

	public static function getParamForRequest($item) {
		$variableList = array();
		// $fieldList = array(
		// 	'config' => array(
		// 		'api_configuration_type',
		// 		'user',
		// 		'password',
		// 		'client',
		// 		'role',
		// 		'organization',
		// 		'warehouse'
		// 	)
		// );

		// foreach ($fieldList as $sk => $sItem) {
		// 	if(isset($item[$sk]) && !empty($item[$sk])) {
		// 		foreach ($sItem as $fk => $fItem) {
		// 			if(isset($item[$sk][$fItem])) {
		// 				$variableList[$fItem] = $item[$sk][$fItem];
		// 			}
		// 		}
		// 	}
		// }

		if(isset($item['data_item']) && is_array($item['data_item'])) {
			foreach ($item['data_item'] as $ik => $dItem) {
				$variableList[$ik] = $dItem;
			}
		}

		return $variableList;
	}

	public static function generateBodyParam($req, $variableList = array()) {
		extract($variableList);

		$bodyRequest = eval("return ".$req . ";");

		return $bodyRequest;
	}


	/*Log mapping data*/
	public static function getMappingData($filter = array()) {
		$binds = array();
		$whereStr = array();

		if(!empty($filter)) {
			foreach ($filter as $fk => $fv) {
				$whereStr[] = $fk." = ?";
				$binds[] = $fv;
			}
		}

		if(!empty($whereStr)) {
			$whereStr = "WHERE ".implode(" AND ", $whereStr);
		} else {
			$whereStr = "";
		}

		return sqlQuery("SELECT * FROM vh_hubspot_data_mapping $whereStr ", $binds);
	}

	public static function logMappingData($data = array()) {
		extract($data);

		$cnt = 0;
		if(!empty($address_id)) {
			$count_result = sqlQuery("SELECT count(*) as total FROM vh_hubspot_data_mapping WHERE address_id = ? ", array($address_id));

			if(isset($count_result['total'])) {
				$cnt = $count_result['total'];
			}
		}

		if(!empty($data)) {
			if($cnt > 0) {
				$logR = sqlStatementNoLog("UPDATE `vh_hubspot_data_mapping` SET hubspot_id = ? WHERE address_id = ? ", array($hubspot_id, $address_id));
			} else {

				$logR = sqlInsert("INSERT INTO `vh_hubspot_data_mapping` ( address_id, hubspot_id ) VALUES ( '$address_id',  '$hubspot_id') ");
			}
		}

		return $logR;
	}

	public function createlogMappingData($data = array()) {
		extract($data);

		if(!empty($data)) {
			$logId = sqlInsert("INSERT INTO vh_hubspot_data_mapping ( address_id, hubspot_id ) VALUES ( '$address_id',  '$hubspot_id') ");
		}

		return $logId;
	}

	public static function deleteLogMappingData($address_id, $hubspot_id) {
		if(!empty($address_id) && !empty($hubspot_id)) {
			return sqlStatement("DELETE FROM vh_hubspot_data_mapping WHERE address_id = ? AND hubspot_id = ? ", array($address_id, $hubspot_id));
		}

		return false;
	}

	public static function callRequest($body, $config = array()) {
		if(isset($config["method"])) {
			//if($config["method"] == "GET") {
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $config["url"]);
    			
    			if(isset($config["header"])) {
    				curl_setopt($ch, CURLOPT_HTTPHEADER, $config["header"]);
    				curl_setopt($ch, CURLOPT_HEADER, 0);
    			}

    			if($config["method"] == "GET") {
    				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
    			} else if($config["method"] == "POST") {
    				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
    			} else if($config["method"] == "PATCH") {
    				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PATCH' );
    			} else if($config["method"] == "DELETE") {
    				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'DELETE' );
    			}

    			//curl_setopt($ch, CURLOPT_VERBOSE, true);
				//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    			if(isset($body) && !empty($body)) {
    				curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
    			} 

    			// Send data
				$result = curl_exec($ch);
				$errCode = curl_errno($ch);
				$errText = curl_error($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

				// Handle result
				return self::handle($result, $httpCode);
			//}
		}

		return false;
	}

	/* Handle CURL response from servers. */
	protected static function handle($result, $httpCode) {
		// Check for non-OK statuses
		$codes = explode(",", static::ACCEPTED_CODES);
		if (!in_array($httpCode, $codes)) {
			if($httpCode == "400") {
				//$xml = simplexml_load_string($result);
				//$json = json_encode($xml);
				return json_decode($result,TRUE);
			} else {
				return json_decode($result, true);
			}
		} else {
			return json_decode($result, true);
		}
	}

	public static function createAddressBook($data = array()) {
		extract($data);

		$binds = array(
			self::getValObj('username', $data, ''),
	        self::getValObj('password', $data, ''),
	        self::getValObj('authorized', $data, 0),
	        self::getValObj('info', $data, ''),
	        self::getValObj('source', $data, NULL),
	        self::getValObj('title', $data, ''),
	        self::getValObj('fname', $data, ''),
	        self::getValObj('lname', $data, ''),
	        self::getValObj('mname', $data, ''),
	        self::getValObj('suffix', $data, ''),
	        self::getValObj('federaltaxid', $data, ''),
	        self::getValObj('federaldrugid', $data, ''),
	        self::getValObj('upin', $data, ''),
	        self::getValObj('facility', $data, ''),
	        self::getValObj('see_auth', $data, 0),
	        self::getValObj('active', $data, 1),
	        self::getValObj('npi', $data, ''),
	        self::getValObj('taxonomy', $data, ''),
	        self::getValObj('cpoe', $data, ''),
	        self::getValObj('specialty', $data, ''),
	        self::getValObj('organization', $data, ''),
	        self::getValObj('valedictory', $data, ''),
	        self::getValObj('assistant', $data, ''),
	        self::getValObj('billname', $data, ''),
	        self::getValObj('email', $data, ''),
	        self::getValObj('email_direct', $data, ''),
	        self::getValObj('url', $data, ''),
	        self::getValObj('street', $data, ''),
	        self::getValObj('streetb', $data, ''),
	        self::getValObj('city', $data, ''),
	        self::getValObj('state', $data, ''),
	        self::getValObj('zip', $data, ''),
	        self::getValObj('street2', $data, ''),
	        self::getValObj('streetb2', $data, ''),
	        self::getValObj('city2', $data, ''),
	        self::getValObj('state2', $data, ''),
	        self::getValObj('zip2', $data, ''),
	        self::getValObj('phone', $data, ''),
	        self::getValObj('phonew1', $data, ''),
	        self::getValObj('phonew2', $data, ''),
	        self::getValObj('phonecell', $data, ''),
	        self::getValObj('fax', $data, ''),
	        self::getValObj('notes', $data, ''),
	        self::getValObj('abook_type', $data, ''),
	        self::getValObj('ct_communication', $data, '')
		);

		$userid = sqlInsert("INSERT INTO users ( " .
        "username, password, authorized, info, source, " .
        "title, fname, lname, mname, suffix, " .
        "federaltaxid, federaldrugid, upin, facility, see_auth, active, npi, taxonomy, cpoe, " .
        "specialty, organization, valedictory, assistant, billname, email, email_direct, url, " .
        "street, streetb, city, state, zip, " .
        "street2, streetb2, city2, state2, zip2, " .
        "phone, phonew1, phonew2, phonecell, fax, notes, abook_type, ct_communication "            .
        ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )", $binds);

        return $userid;
	}

	public static function updateAddressBook($userid, $data = array()) {
		if ($userid) {

			$qtr = array();
			$binds = array();
			foreach ($data as $fk => $fItem) {
				$qtr[] = "$fk = ?";
				$binds[] = $fItem;
			}

			if(!empty($qtr)) {
				$qtr = implode(", ", $qtr);
			}

			if(!empty($qtr)) {
				$binds[] = $userid;
				return sqlStatement("UPDATE users SET $qtr WHERE id = ? ", $binds);
			}
	    }

	    return false;
	}

	public static function deleteAddressBook($userid) {
		return sqlStatement("DELETE FROM users WHERE id = ? AND username = ''", array($userid));
	}

	public static function getValObj($name, $data = array(), $default = ''){
	    return isset($data[$name]) ? $data[$name] : $default;
	}

	public static function handleInSyncPrepare($userId = '', $opt = "") {
		if($GLOBALS['abook_hubspot_sync'] != 1) {
			return;
		}

		if(!empty($opt) && !empty($userId)) {

			$uRow = sqlQuery("SELECT * FROM users WHERE id = ?", array($userId));
			$uabookType = isset($uRow['abook_type']) ? $uRow['abook_type'] : '';

			if($opt === "INSERT") {
				if($uabookType === 'Attorney') {
					$hpData = array(
						'event_id' => 'hubsport_sync',
						'config_id' => 'hubspot_out_create_update',
						'event_type' => '2',
						'mode' => 'INSERT_UPDATE',
						'tablename' => 'users',
						'uniqueid' => $userId,
						'user_type' => 'Cron',
						'sent' => 0,
						'trigger_time' => date('Y-m-d H:i:s', strtotime("+1 minutes")),
						'time_delay' => 0
					);

					self::createlogData($hpData);
				}
			} else if($opt === "UPDATE") {
				if($uabookType === 'Attorney') {
					$hpData = array(
						'event_id' => 'hubsport_sync',
						'config_id' => 'hubspot_out_create_update',
						'event_type' => '2',
						'mode' => 'INSERT_UPDATE',
						'tablename' => 'users',
						'uniqueid' => $userId,
						'user_type' => 'Cron',
						'sent' => 0,
						'trigger_time' => date('Y-m-d H:i:s', strtotime("+1 minutes")),
						'time_delay' => 0
					);

					self::createlogData($hpData);
				} else {
					$uRow1 = sqlQuery("SELECT 1 from vh_hubspot_data_mapping vhdm where vhdm.address_id = ?", array($userId));
					if(!empty($uRow1)) {
						$hpData = array(
							'event_id' => 'hubsport_sync',
							'config_id' => 'hubspot_out_delete',
							'event_type' => '2',
							'mode' => 'DELETE',
							'tablename' => 'users',
							'uniqueid' => $userId,
							'user_type' => 'Cron',
							'sent' => 0,
							'trigger_time' => date('Y-m-d H:i:s', strtotime("+1 minutes")),
							'time_delay' => 0
						);

						self::createlogData($hpData);
					}
				}
			} else if($opt === "DELETE") {
				if($uabookType === 'Attorney') {
					$hpData = array(
						'event_id' => 'hubsport_sync',
						'config_id' => 'hubspot_out_delete',
						'event_type' => '2',
						'mode' => 'DELETE',
						'tablename' => 'users',
						'uniqueid' => $userId,
						'user_type' => 'Cron',
						'sent' => 0,
						'trigger_time' => date('Y-m-d H:i:s', strtotime("+1 minutes")),
						'time_delay' => 0
					);

					self::createlogData($hpData);
				}
			}
		}
	}

	public static function createlogData($data = array()) {
		extract($data);

		if(!empty($data)) {
			$binds = array();

			foreach ($data as $di => $dItem) {
				$binds[] = $dItem !== '' ? $dItem : '';
			}

			$nId = sqlInsert("INSERT INTO vh_hubspot_sync_log ( event_id, config_id, event_type, mode, tablename, uniqueid, user_type, sent, trigger_time, time_delay) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $binds);
		}

		return $nId;
	}

	/*Get State mapping data*/
	public static function getStateMappingData($filter = array()) {
		$binds = array();
		$whereStr = array();

		if(!empty($filter)) {
			foreach ($filter as $fk => $fv) {
				$whereStr[] = $fk." = ?";
				$binds[] = $fv;
			}
		}

		if(!empty($whereStr)) {
			$whereStr = "WHERE ".implode(" AND ", $whereStr);
		} else {
			$whereStr = "";
		}

		return sqlQuery("SELECT * FROM vh_hubspot_state_mapping $whereStr ", $binds);
	}

	public static function formatePhoneNumber($value = '', $isNeededToAdd = false) {
		if(!empty($value)) {
			if($isNeededToAdd === false) {
				$value = preg_replace("/^\+?1/", '',$value);
			} else if($isNeededToAdd === true) {
				if(substr($value, 0, 1 ) !== "+") {
					if(substr($value, 0, 1 ) === "1") {
						$value = '+'.$value;
					} else {
						$value = '+1'.$value;
					}
				}
			}
		} 

		return $value;
	}

	public static function getStateVal($value = '', $mapType = '') {
		if(!empty($value)) {
			$tValue = '';
			$filterVals = array();

			if($mapType == 'OEMR') {
				$filterVals = array("state_value" => $value);
			} else if($mapType == 'HUBSPOT') {
				$filterVals = array("hubspot_state_value" => $value);
			}

			if(!empty($filterVals)) {
				$mState = self::getStateMappingData($filterVals);

				if(!empty($mState)) {
					if($mapType == 'OEMR' && isset($mState['hubspot_state_value'])) {
						$tValue = $mState['hubspot_state_value'];
					} else if($mapType == 'HUBSPOT' && isset($mState['state_value'])) {
						$tValue = $mState['state_value'];
					}
				}
			}

			$value = $tValue;
		}

		return $value;
	}
}