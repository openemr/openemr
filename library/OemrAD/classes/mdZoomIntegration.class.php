<?php

namespace OpenEMR\OemrAd;

@include_once(__DIR__ . "/../interface/globals.php");
@include_once(__DIR__ . "/mdReminder.class.php");

use OpenEMR\OemrAd\Reminder;

class ZoomIntegration {

	const ACCEPTED_CODES = '200, 201, 202';
	

	public static function getConfigVars() {
		$returnList = new \stdClass();
		$returnList->user_id = isset($GLOBALS['zoom_user_id']) ? $GLOBALS['zoom_user_id'] : "";
		$returnList->access_token = isset($GLOBALS['zoom_access_token']) ? $GLOBALS['zoom_access_token'] : "";
		$returnList->api_key = isset($GLOBALS['zoom_api_key']) ? $GLOBALS['zoom_api_key'] : "";
		$returnList->api_secret = isset($GLOBALS['zoom_api_secret']) ? $GLOBALS['zoom_api_secret'] : "";
		$returnList->event_id = isset($GLOBALS['zoom_notify_event_id']) ? $GLOBALS['zoom_notify_event_id'] : "";
		$returnList->config_id= isset($GLOBALS['zoom_notify_config_id']) ? $GLOBALS['zoom_notify_config_id'] : "";

		return $returnList;
	}

	/*Handle to create or update meeting*/
	public static function handleZoomApptEvent($appt_id, $cat_id = '', $appt_data = '', $force_update = false, $isNeedToCreate = false ) {
		$configList = self::getConfigVars();

		if(isset($configList->event_id) && !empty($configList->event_id)) {
			$appointmentData = self::getAppoinmentData($appt_id);
			$apptsCategorys = array_map('trim', explode(",", $GLOBALS['zoom_appt_category']));
			$apptsFacilitys = array_map('trim', explode(",", $GLOBALS['zoom_appt_facility']));

			$performStatus = false;

			if(!empty($appointmentData) && empty($cat_id)) {
				$cat_id = $appointmentData['pc_catid'];
			}

			if (!empty($cat_id) && in_array($cat_id, array('2', '3', '4', '8', '11'))) {
				return false;
			}

			if(!empty($appointmentData) && empty($appt_data)) {
				$appt_data = array(
					'pc_title' => $appointmentData['pc_title'],
	                'event_datetime' => $appointmentData['pc_eventDate']." ".$appointmentData['pc_startTime'],
	                'pc_duration' => $appointmentData['pc_duration'],
	                'pc_hometext' => $appointmentData['pc_hometext']
				);
			}

			if(!isset($appt_data['pc_aid'])) {
				$appt_pc_aid = '';
				if(!empty($appointmentData) && isset($appointmentData['pc_aid'])) {
					$appt_pc_aid = $appointmentData['pc_aid'];
				}
				$appt_data['pc_aid'] = $appt_pc_aid;
			}

			if (!empty($appointmentData['pc_facility']) && in_array($appointmentData['pc_facility'], $apptsFacilitys)) {
				$performStatus = true;
			}

			if (!empty($cat_id) && in_array($cat_id, $apptsCategorys)) {
				$performStatus = true;
			}

			if ($performStatus === true) {
				//Appoinment Data
				if(!empty($appointmentData) && !empty($appointmentData['username'])) {
					//Get Zoom Meeting Details
					$zoomMeetingData =  self::getZoomMeetingDetails($appt_id);

					$apptStartTime = $appointmentData['pc_eventDate'] ." ".$appointmentData['pc_startTime'];
					$apptDuration = isset($appointmentData['pc_duration']) && !empty($appointmentData['pc_duration']) ? ($appointmentData['pc_duration']/60) : 0;
					$apptEndTime = date('Y-m-d H:i:s', strtotime($apptStartTime . ' + '. $apptDuration .' minute'));

					$provider_email = self::generateZoomMeetingHost($appt_id, $zoomMeetingData, $appt_data, $appointmentData['username'], $apptStartTime, $apptEndTime, $appt_data['pc_aid']);

					//Check Appt provider meeting exists
					// $pResultSet = self::getProviderMeeting($apptStartTime, $appt_data['pc_aid']);
					// if(isset($pResultSet) && is_array($pResultSet) && count($pResultSet) > 0) {
					// 	if(!empty($zoomMeetingData) && ($zoomMeetingData['host_email'] != $provider_email || (strtotime($appt_data['event_datetime']) != strtotime($zoomMeetingData['start_time']) ||  $pc_duration1 != $zoom_duration1 ))) {
					// 		//self::handleZoomApptDeleteEvent($appt_id);
					// 	}
					// 	return false;
					// }

					if($isNeedToCreate === true) {
						$isNeedToCreateMeetingStatus = self::isNeedToRecreateMeeting($appt_data, $zoomMeetingData, $provider_email);

						if($isNeedToCreateMeetingStatus === false) {
							return true;
						}
					}

					self::handleZoomUser($provider_email, array(
						'email' => $provider_email,
						'first_name' => isset($appointmentData['fname']) ? $appointmentData['fname'] : '',
						'last_name' => isset($appointmentData['lname']) ? $appointmentData['lname'] : ''
					));

					//$zoomMeetingData =  self::getZoomMeetingDetails($appt_id);
					if(!empty($zoomMeetingData) && $zoomMeetingData['host_email'] != $provider_email) {
						self::handleZoomApptDeleteEvent($appt_id);
					}

					$appt_data['user_id'] = $provider_email;
					$createResData = self::createZoomMeetingForAppt($appt_id, $appt_data, $force_update);
				}
			}
		}
	}

	public static function recreateZoomMeeting($appt_id, $cat_id = '', $force_update = false, $isNeedToCreate = false) {
		$configList = self::getConfigVars();

		if(isset($configList->event_id) && !empty($configList->event_id)) {
			$appointmentData = self::getAppoinmentData($appt_id);
			$apptsCategorys = array_map('trim', explode(",", $GLOBALS['zoom_appt_category']));
			$apptsFacilitys = array_map('trim', explode(",", $GLOBALS['zoom_appt_facility']));

			$performStatus = false;

			if(!empty($appointmentData)) {
				$appt_data = $appointmentData;
				$appt_data['event_datetime'] = $appointmentData['pc_eventDate'] ." ".$appointmentData['pc_startTime'];
				$appt_data['pc_duration'] = $appointmentData['pc_duration'];

				$cat_id = isset($appointmentData['pc_catid']) ? $appointmentData['pc_catid'] : "";
				$pc_aid = isset($appointmentData['pc_aid']) ? $appointmentData['pc_aid'] : "";

				if (!empty($appointmentData['pc_facility']) && in_array($appointmentData['pc_facility'], $apptsFacilitys)) {
					$performStatus = true;
				}

				if (!empty($cat_id) && in_array($cat_id, $apptsCategorys)) {
					$performStatus = true;
				}


				if ($performStatus === true) {
					//Appoinment Data
					if(!empty($appointmentData) && !empty($appointmentData['username'])) {
						//Get Zoom Meeting Details
						$zoomMeetingData =  self::getZoomMeetingDetails($appt_id);

						$apptStartTime = $appointmentData['pc_eventDate'] ." ".$appointmentData['pc_startTime'];
						$apptDuration = isset($appointmentData['pc_duration']) && !empty($appointmentData['pc_duration']) ? ($appointmentData['pc_duration']/60) : 0;
						$apptEndTime = date('Y-m-d H:i:s', strtotime($apptStartTime . ' + '. $apptDuration .' minute'));

						$provider_email = self::generateZoomMeetingHost($appt_id, $zoomMeetingData, array('pc_aid' => $pc_aid), $appointmentData['username'], $apptStartTime, $apptEndTime);

						self::handleZoomUser($provider_email, array(
							'email' => $provider_email,
							'first_name' => isset($appointmentData['fname']) ? $appointmentData['fname'] : '',
							'last_name' => isset($appointmentData['lname']) ? $appointmentData['lname'] : ''
						));

						if(!empty($zoomMeetingData)) {
							self::handleZoomApptDeleteEvent($appt_id);
						}

						
						$appt_data['user_id'] = $provider_email;
						$createResData = self::createZoomMeetingForAppt($appt_id, $appt_data, $force_update);


						if(isset($createResData['status']) === true) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	public static function getProviderMeeting($appt_time, $appt_end_time, $provider_id, $appt_id) {
		$resultItems = array();
		if(!empty($appt_time) && !empty($provider_id)) {
			$bindArray = array($appt_time, $appt_end_time, $appt_time, $appt_end_time, $provider_id);
			$whereStr = "";

			if(!empty($appt_id)) {
				$whereStr = " AND ope.pc_eid != ? ";
				$bindArray[] = $appt_id;
			}

			$sqlQ = "SELECT str_to_date(cast(concat(ope.pc_eventDate,' ',ope.pc_startTime) as datetime),'%Y-%m-%d %h:%i:%s') as start_time ,date_Add(str_to_date(cast(concat(ope.pc_eventDate,' ',ope.pc_startTime) as datetime),'%Y-%m-%d %h:%i:%s'),INTERVAL ope.pc_duration SECOND) end_time,ope.pc_duration, zae.host_email, ope.* FROM openemr_postcalendar_events ope, zoom_appointment_events zae WHERE ope.pc_eid = zae.pc_eid and ((str_to_date(cast(concat(ope.pc_eventDate,' ',ope.pc_startTime) as datetime),'%Y-%m-%d %h:%i:%s') >= ? and str_to_date(cast(concat(ope.pc_eventDate,' ',ope.pc_startTime) as datetime),'%Y-%m-%d %h:%i:%s') < ?) or (date_Add(str_to_date(cast(concat(ope.pc_eventDate,' ',ope.pc_startTime) as datetime),'%Y-%m-%d %h:%i:%s'),INTERVAL ope.pc_duration SECOND) > ? and date_Add(str_to_date(cast(concat(ope.pc_eventDate,' ',ope.pc_startTime) as datetime),'%Y-%m-%d %h:%i:%s'),INTERVAL ope.pc_duration SECOND) <= ?)) AND ope.pc_aid = ? ".$whereStr." and ope.pc_catid not in ('2', '3', '4', '8', '11') order by ope.pc_eid DESC;";

			$result = sqlStatementNoLog($sqlQ, $bindArray);
			while ($result_data = sqlFetchArray($result)) {
				$resultItems[] = $result_data;
			}
		}

		return $resultItems;
	}

	/*Check and create zoom user.*/
	public static function handleZoomUser($user_id, $data = array()) {
		$zoomUserData = self::getZoomUser($user_id);
		if(isset($zoomUserData) && isset($zoomUserData['code']) && $zoomUserData['code'] == "1001") {
			$createZoomUserData = self::createZoomUser($data);
		}
	}

	/*Get Zoom User Details*/
	public static function getZoomUser($user_id) {
		$user_api_url = "https://api.zoom.us/v2/users/".$user_id;
		$serviceRes = self::curl(null, $user_api_url);
		return $serviceRes;
	}

	public static function isNeedToRecreateMeeting($appt_data = array(), $zoom_data = array(), $provider_email = '') {
		$res_status = false;

		if(!empty($appt_data) && !empty($zoom_data) && is_array($appt_data) && is_array($zoom_data)) {
			$zoom_responce = isset($zoom_data['responce_data']) && $zoom_data['responce_data'] != "" ? json_decode($zoom_data['responce_data'], true) : array();
			$pc_title = isset($appt_data['pc_title']) ? $appt_data['pc_title'] : "";
			$zoom_title = isset($zoom_data['topic']) ? $zoom_data['topic'] : "";

			$pc_duration = isset($appt_data['pc_duration']) && !empty($appt_data['pc_duration']) ? ($appt_data['pc_duration']/60) : 0;
			$zoom_duration = isset($zoom_data['duration']) ? $zoom_data['duration'] : 0;

			$pc_hometext = isset($appt_data['pc_hometext']) ? $appt_data['pc_hometext'] : "";
			$zoom_hometext = isset($zoom_responce['agenda']) ? $zoom_responce['agenda'] : "";

			$event_start_date_unix = strtotime($appt_data['event_datetime']);
			$current_time_unix = strtotime('now');
			$start_time_unix = strtotime($zoom_data['start_time']);

			$host_email = isset($zoom_data['host_email']) ? $zoom_data['host_email'] : "";
			
			if($current_time_unix < $event_start_date_unix) {
				if($event_start_date_unix != $start_time_unix) {
					$res_status = true;
				}
			}

			if($pc_title != $zoom_title) {
				$res_status = true;
			}

			if($pc_duration != $zoom_duration) {
				$res_status = true;
			}

			if($pc_hometext != $zoom_hometext) {
				$res_status = true;
			}

			if($host_email != $provider_email) {
				$res_status = true;
			}
			
		} else {
			$res_status = true;
		}

		return $res_status;
	}

	/*Create Zoom User*/
	public static function createZoomUser($data) {
		$user_api_url = "https://api.zoom.us/v2/users";

		$body_params = array(
			'action' => 'custCreate',
			'user_info' => array(
				'email' => isset($data['email']) ? $data['email'] : '',
				'type' => '1',
				'first_name' => isset($data['first_name']) ? $data['first_name'] : '',
				'last_name' => isset($data['last_name']) ? $data['last_name'] : ''
			)
		);

		$serviceRes = self::curl($body_params, $user_api_url, 'POST');
		return $serviceRes;
	}

	/*Create Zoom Telehealth meeting*/
	public function createZoomTeleHealthForAppt($appt_id, $appt_data, $force_update = false) {
		$configList = self::getConfigVars();
		$telehealthUrl = 'https://applications.zoom.us/telehealth';
		$apptData = self::getAppoinmentData($appt_id);
		$zoomMeetingData =  self::getZoomTelehealthMeetingDetails($appt_id);

		if(!empty($apptData)) {
			$provider_data_params = array(
				'usertype' => '1',
				'sessionid' => $apptData['pc_eid'],
				'userid' => $apptData['pc_aid'],
				'firstname' => $apptData['fname'],
				'lastname' => $apptData['lname']
			);

			$patient_data_params = array(
				'usertype' => '2',
				'sessionid' => $apptData['pc_eid'],
				'userid' => $apptData['pc_pid'],
				'firstname' => $apptData['p_fname'],
				'lastname' => $apptData['p_lname']
			);

			$providerDataQtr = http_build_query($provider_data_params);
			$patientDataQtr = http_build_query($patient_data_params);

			$providerCiperText = self::handleDataEncryption($providerDataQtr);
			$providerUncodedCiperText = urlencode($providerCiperText);

			$patientCiperText = self::handleDataEncryption($patientDataQtr);
			$patientUncodedCiperText = urlencode($patientCiperText);

			$providerUrlData = array(
				'org_id' => $configList->api_key,
				'data' => $providerUncodedCiperText
			);

			$patientUrlData = array(
				'org_id' => $configList->api_key,
				'data' => $patientUncodedCiperText
			);

			$providerUrlDataQtr = http_build_query($providerUrlData);
			$patientUrlDataQtr = http_build_query($patientUrlData);

			$providerUrl = $telehealthUrl.'?'.$providerUrlDataQtr;
			$patientUrl = $telehealthUrl.'?'.$patientUrlDataQtr;

			if(empty($zoomMeetingData)) {
				$meeting_details = array(
					'pc_eid' => $appt_id,
					'session_id' => isset($apptData['pc_eid']) ? $apptData['pc_eid'] : "",
					'topic' => isset($appt_data['pc_title']) ? $appt_data['pc_title'] : "",
					'start_time' => gmdate("Y-m-d\TH:i:s\Z", strtotime($appt_data['event_datetime'])),
					'duration' => (isset($appt_data['pc_duration']) && !empty($appt_data['pc_duration'])) ? intval($appt_data['pc_duration'] / 60) : '',
					'timezone' => self::getTimeZone(),
					'provider_join_url' => $providerUrl,
					'patient_join_url' => $patientUrl
				);

				if(isset($meeting_details) && !empty($meeting_details)) {
					self::saveZoomTeleHealthMeetingDetails($meeting_details);
				}
			} else if(isset($zoomMeetingData['session_id']) && $force_update === true) {
				$meeting_details = array(
					'pc_eid' => $appt_id,
					'session_id' => isset($apptData['pc_eid']) ? $apptData['pc_eid'] : "",
					'topic' => isset($appt_data['pc_title']) ? $appt_data['pc_title'] : "",
					'start_time' => gmdate("Y-m-d\TH:i:s\Z", strtotime($appt_data['event_datetime'])),
					'duration' => (isset($appt_data['pc_duration']) && !empty($appt_data['pc_duration'])) ? intval($appt_data['pc_duration'] / 60) : '',
					'timezone' => self::getTimeZone(),
					'provider_join_url' => $providerUrl,
					'patient_join_url' => $patientUrl
				);

				if(isset($meeting_details) && !empty($meeting_details)) {
					self::updateZoomTelehealthMeetingDetails($meeting_details);
				}
			}
		}
	}

	public static function handleDataEncryption($data) {
		$configList = self::getConfigVars();
		$plaintext = $data;
		$cipher = "aes-128-gcm";
		$key = $configList->api_key;

		if(in_array($cipher, openssl_get_cipher_methods())){
			$ivlen = openssl_cipher_iv_length($cipher);
    		$iv = openssl_random_pseudo_bytes($ivlen);
    		$ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv, $tag);

    		return $ciphertext;
		}

		return false;
	}

	/*Handle to send zoom details*/
	public static function handleSendZoomDetails($appt_id, $extraData = '') {
		$configList = self::getConfigVars();

		if(!isset($configList->config_id) || empty($configList->config_id)) {
			return array('status' => false, 'total_sent_item' => 0, 'total_failed_item' => 0, 'message' => 'Please configure config id to send notification.');
		}
		
		if(!isset($configList->event_id) || empty($configList->event_id)) {
			return array('status' => false, 'total_sent_item' => 0, 'total_failed_item' => 0, 'message' => 'Please configure event id to send notification.');
		}

		$appointmentData = self::getAppoinmentData($appt_id);
		$event_ids = array_map('trim', explode(",", $configList->event_id));
		
		$configs = Reminder::getActionConfiguration($event_ids);
		$config_ids = array_map('trim', explode(",", $configList->config_id));
		$notifIdList = array();
		$notifItemList = array();

		$totalItem = 0;
		$totalsentItem = 0;
		$totalFailedItem = 0;
		$sendStatus = true;

		if(isset($configs)) {
		 	foreach ($configs as $key => $action_config) {
		 		foreach ($action_config['config_data'] as $key => $config_data) {
		 			$config = array_merge($action_config, $action_config['config_data'][$key]);
					$event_id = $config['event_id'];
					$config_id = $config['id'];

					if(isset($extraData['selectedType']) && in_array($config['communication_type'], $extraData['selectedType'])) {
						if(in_array($config['id'], $config_ids)) {
			 				if(isset($config['configuration_id']) && !empty($config['configuration_id']) && $config['active'] == "0" && !empty($config['id'])) {

			 					$notif_id = "";
			 					$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
								$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";

								$eventResultData = self::getUnSentEventTriggerData($event_id, $config_id, $appt_id, false);

								if(empty($eventResultData)) {
				 					if($config['as_trigger_type'] == "time") {
				 						$prepareDataForCreate = array(
											'event_id' => $config['event_id'],
											'config_id' => $config['id'],
											'event_type' => 1,
											'tablename' => 'openemr_postcalendar_events',
											'uniqueid' => $appt_id,
											'pid' => $appointmentData['pc_pid'],
											'uid' => $uid,
											'user_type' => $user_type,
											'sent' => '-1',
											'trigger_time' => date('Y-m-d H:i:s')
										);

										$notif_id = self::createEventNotifyData($prepareDataForCreate);
				 					} else if($config['as_trigger_type'] == "event") {
				 						$prepareDataForCreate = array(
											'event_id' => $config['event_id'],
											'config_id' => $config['id'],
											'event_type' => 2,
											'tablename' => 'openemr_postcalendar_events',
											'uniqueid' => $appt_id,
											'pid' => $appointmentData['pc_pid'],
											'uid' => $uid,
											'user_type' => $user_type,
											'sent' => '-1',
											'trigger_time' => date('Y-m-d H:i:s')
										);

										//$notif_id = self::createEventNotifyData($prepareDataForCreate);
				 					}
			 					} else if(count($eventResultData)== 1 && isset($eventResultData[0]['id'])){
			 						$notif_id = $eventResultData[0]['id'];
			 					}

			 					if(isset($notif_id) && !empty($notif_id)) {
			 						if(!isset($notifItemList[$config_id])) {
			 							$notifItemList[$config_id] = array(
			 								'config' => $config,
			 								'items' => array()
			 							);
			 						}

			 						//Set notif_id
			 						$notifItemList[$config_id]['items'][] = $notif_id;
			 						$notifIdList[] = $notif_id;

			 						$rsData = self::getNotifyDataById(array($notif_id), 1);
			 						if(!empty($rsData)) {
			 							foreach ($rsData as $rsK => $rsItem) {
			 								$preparedCount = Reminder::prepareDataForUpdate(array($rsItem), $config);
				 							if($preparedCount > 0) {
					 							if($config['as_trigger_type'] == "time") {
					 								Reminder::updatePreparedData($rsItem['id'],array('event_type' => 1));
					 							}
				 							}
			 							}
			 						}
			 					}
			 				}
			 			}
			 		}
		 		}
		 	}

		 	if(!empty($notifIdList)) {
				//Send Zoom notification
				$dataItems = self::getNotifyDataById($notifIdList, 2);
				$statusMsg = array();

				foreach ($dataItems as $key => $item) {
					if(isset($extraData['selectedType']) && in_array($item['msg_type'], $extraData['selectedType'])) {
						if(in_array($item['config_id'], $config_ids)) {

							$trigger_time = $item['trigger_time'];

							//Unix time
							$current_unix_time = strtotime('now');
							$trigger_unix_time = strtotime($item['trigger_time']);

							if(!empty($item['time_delay']) && $item['time_delay'] != 0) {
								sleep($item['time_delay']);
							}

							if($item['msg_type'] == "email") {
								$itemStatus = Reminder::sendEmail($item);
								if($itemStatus !== true) $statusMsg[] = 'EMAIL - ' . ($itemStatus === false ? 'Something went wrong' : $itemStatus);
							} else if($item['msg_type'] == "sms") {
								$itemStatus = Reminder::sendSMS($item);
								if($itemStatus !== true) $statusMsg[] = 'SMS - ' . ($itemStatus === false ? 'Something went wrong' : $itemStatus);
							} else if($item['msg_type'] == "fax") {
								$itemStatus = Reminder::sendFAX($item);
								if($itemStatus !== true) $statusMsg[] = 'FAX - ' . ($itemStatus === false ? 'Something went wrong' : $itemStatus);
							} else if($item['msg_type'] == "postalmethod") {
								$itemStatus = Reminder::sendPostalLetter($item);
								if($itemStatus !== true) $statusMsg[] = 'POSTAL LETTER - ' . ($itemStatus === false ? 'Something went wrong' : $itemStatus);
							} else if($item['msg_type'] == "internalmessage") {
								$itemStatus = Reminder::sendInternalMessage($item);
								if($itemStatus !== true) $statusMsg[] = 'INTERNAL MSG - ' . ($itemStatus === false ? 'Something went wrong' : $itemStatus);
							}

							if(isset($itemStatus) && $itemStatus === true) {
								$totalsentItem++;
							} else {
								$totalFailedItem++;
								$sendStatus = false;
							}

						}
					}
				}
		 	}
		}

		return array('status' => $sendStatus, 'total_sent_item' => $totalsentItem, 'total_failed_item' => $totalFailedItem, 'status_msg' => $statusMsg);
	}

	/*Handle to send zoom details*/
	public function handleSendZoomDetails1($appt_id, $extraData = '') {
		$configList = self::getConfigVars();

		if(!isset($configList->config_id) || empty($configList->config_id)) {
			return array('status' => false, 'total_sent_item' => 0, 'total_failed_item' => 0, 'message' => 'Please configure config id to send notification.');
		}
		
		if(!isset($configList->event_id) || empty($configList->event_id)) {
			return array('status' => false, 'total_sent_item' => 0, 'total_failed_item' => 0, 'message' => 'Please configure event id to send notification.');
		}

		$appointmentData = self::getAppoinmentData($appt_id);
		$event_ids = array_map('trim', explode(",", $configList->event_id));
		
		$configs = Reminder::getActionConfiguration($event_ids);
		$config_ids = array_map('trim', explode(",", $configList->config_id));

		if(isset($configs)) {
		 	foreach ($configs as $key => $action_config) {
		 	foreach ($action_config['config_data'] as $key => $config_data) {
				$config = array_merge($action_config, $action_config['config_data'][$key]);
				$event_id = $config['event_id'];
				$config_id = $config['id'];

				if(isset($extraData['selectedType']) && in_array($config['communication_type'], $extraData['selectedType'])) {
				if(in_array($config['id'], $config_ids)) {
		 		if(isset($config['configuration_id']) && !empty($config['configuration_id']) && $config['active'] == "0" && !empty($config['id'])) {
		 		 		
		 				if($config['as_trigger_type'] != "event") {
			 		 		$eventResultData = self::getEventTriggerData($event_id, $config_id, $appt_id, false);

			 		 		if(empty($eventResultData)) {
			 		 			$uid = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "";
								$user_type = isset($uid) && !empty($uid) ? "User" : "Cron";
			 		 			$prepareDataForCreate = array(
									'event_id' => $config['event_id'],
									'config_id' => $config['id'],
									'tablename' => 'openemr_postcalendar_events',
									'uniqueid' => $appt_id,
									'pid' => $appointmentData['pc_pid'],
									'uid' => $uid,
									'user_type' => $user_type,
									'trigger_time' => date('Y-m-d H:i:s')
								);

								self::createNotifyData($prepareDataForCreate);
			 		 		}
		 		 		}

		 		 		$resultData = self::getEventTriggerData($event_id, $config_id, $appt_id);

		 		 		if(!empty($resultData)) {
							Reminder::prepareDataForUpdate($resultData, $config);
						}
		 		}
		 		}
		 		}
		 	}
		 	}
		}

		$totalItem = 0;
		$totalsentItem = 0;
		$totalFailedItem = 0;
		$sendStatus = true;

		//Send Zoom notification
		$dataItems = self::getDataForSend($event_ids, array($appt_id));

		foreach ($dataItems as $key => $item) {
			if(isset($extraData['selectedType']) && in_array($item['msg_type'], $extraData['selectedType'])) {
			if(in_array($item['config_id'], $config_ids)) {

				$trigger_time = $item['trigger_time'];

				//Unix time
				$current_unix_time = strtotime('now');
				$trigger_unix_time = strtotime($item['trigger_time']);

				if(!empty($item['time_delay']) && $item['time_delay'] != 0) {
					sleep($item['time_delay']);
				}

				if($item['msg_type'] == "email") {
					$itemStatus = Reminder::sendEmail($item);
				} else if($item['msg_type'] == "sms") {
					$itemStatus = Reminder::sendSMS($item);
				} else if($item['msg_type'] == "fax") {
					$itemStatus = Reminder::sendFAX($item);
				} else if($item['msg_type'] == "postalmethod") {
					$itemStatus = Reminder::sendPostalLetter($item);
				} else if($item['msg_type'] == "internalmessage") {
					$itemStatus = Reminder::sendInternalMessage($item);
				}

				if(isset($itemStatus) && $itemStatus === true) {
					$totalsentItem++;
				} else {
					$totalFailedItem++;
					$sendStatus = false;
				}

			}
			}
		}

		return array('status' => $sendStatus, 'total_sent_item' => $totalsentItem, 'total_failed_item' => $totalFailedItem);
	}

	/*Create Notify Records*/
	public static function createNotifyData($data) {
		
		//Extract value to variable
		extract($data);

		//Write new record
		$sql = "INSERT INTO `notif_log` ( ";
		$sql .= "event_id, config_id, tablename, uniqueid, pid, uid, user_type, trigger_time, created_time ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$event_id,
			$config_id,
			$tablename,
			$uniqueid,
			$pid,
			$uid,
			$user_type,
			$trigger_time,
			date('Y-m-d H:i:s')
		));

		return true;
	}

	/*Create Event Notify Records*/
	public static function createEventNotifyData($data) {
		
		//Extract value to variable
		extract($data);

		//Write new record
		$sql = "INSERT INTO `notif_log` ( ";
		$sql .= "event_id, config_id, event_type, tablename, uniqueid, pid, uid, user_type, sent, trigger_time, created_time ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		return sqlInsert($sql, array(
			$event_id,
			$config_id,
			$event_type,
			$tablename,
			$uniqueid,
			$pid,
			$uid,
			$user_type,
			$sent,
			$trigger_time,
			date('Y-m-d H:i:s')
		));
	}

	/*Get Data for send*/
	public static function getDataForSend($event_id = '', $appt_ids = array(), $limit = 100, $offset = 0) {
		$sql = "SELECT * FROM `notif_log` WHERE `tablename` = 'openemr_postcalendar_events' AND ";

		if(!empty($event_id) && !is_array($event_id)) {
			$sql .= " `event_id` = '".$event_id."' AND ";
		}

		if(!empty($event_id) && is_array($event_id)) {
			$ids = "'".implode("','", $event_id)."'";
			$sql .= " `event_id` IN (".$ids.") AND ";
		}

		if(!empty($appt_ids)) {
			$sql .= " `uniqueid` IN (".implode(",", $appt_ids).") ";
		}

		$sql .= " ORDER BY trigger_time LIMIT ".$offset.", ".$limit."";

		$resultItems = array();
		$result = sqlStatementNoLog($sql);
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	/*Handle prepare data*/
	public static function getEventTriggerData($event_id = '', $config_id = '', $appt_id = '', $template = true) {
		$sql = "SELECT * FROM `notif_log` WHERE event_id = ? AND config_id = ? AND tablename = ? AND uniqueid = ? ";

		if($template === true) {
			$sql .= "AND (template_id IS NULL OR template_id = '') ";
		}

		$resultItems = array();
		$result = sqlStatementNoLog($sql, array($event_id, $config_id, 'openemr_postcalendar_events', $appt_id));
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	/*Handle prepare data*/
	public static function getUnSentEventTriggerData($event_id = '', $config_id = '', $appt_id = '', $template = true) {
		$sql = "SELECT * FROM `notif_log` WHERE event_id = ? AND config_id = ? AND tablename = ? AND uniqueid = ? AND sent IN ('0', '-1') ";

		if($template === true) {
			$sql .= "AND (template_id IS NULL OR template_id = '') ";
		}

		$resultItems = array();
		$result = sqlStatementNoLog($sql, array($event_id, $config_id, 'openemr_postcalendar_events', $appt_id));
		while ($result_data = sqlFetchArray($result)) {
			$resultItems[] = $result_data;
		}

		return $resultItems;
	}

	/*Handle prepare data*/
	public static function getNotifyDataById($id = array(), $template = 1) {
		$resultItems = array();

		if(!empty($id)) {
			$sql = "SELECT * FROM `notif_log` WHERE id IN ('".implode("','",$id)."') ";

			if($template === 1) {
				$sql .= "AND (template_id IS NULL OR template_id = '') ";
			} else if($template === 2) {
				$sql .= "AND (template_id IS NOT NULL OR template_id != '') ";
			}

			$result = sqlStatementNoLog($sql);

			while ($row = sqlFetchArray($result)) {
				$resultItems[] = $row;
			}
		}

		return $resultItems;
	}

	/*Handle to delete tele health meeting*/
	public function handleZoomTelehealthApptDeleteEvent($appt_id) {
		if(isset($appt_id) && !empty($appt_id)) {
			$zoomMeetingData =  self::getZoomTelehealthMeetingDetails($appt_id);

			sqlStatement("DELETE FROM `zoom_telehealth_appointments` WHERE pc_eid = ? AND session_id = ?", array($appt_id, $zoomMeetingData['session_id']));
		}
	}

	/*Handle to create or update meeting*/
	public static function handleZoomApptDeleteEvent($appt_id) {
		if(isset($appt_id) && !empty($appt_id)) {
			$zoomMeetingData =  self::getZoomMeetingDetails($appt_id);

			if(!empty($zoomMeetingData) && isset($zoomMeetingData['m_id'])) {
				$deleteServiceRes = self::deleteZoomMeetingForAppt($zoomMeetingData['m_id']);

				if($deleteServiceRes !== false && !isset($deleteServiceRes['message'])) {
					sqlStatement("DELETE FROM `zoom_appointment_events` WHERE pc_eid = ? AND m_id = ?", array($appt_id, $zoomMeetingData['m_id']));
				}
			}
		}
	}

	/*Create Zoom meeting*/
	public static function createZoomMeetingForAppt($appt_id, $appt_data, $force_update = false) {
		$meeting_api = 'https://api.zoom.us/v2/users/'.$appt_data['user_id'].'/meetings';
		$zoomMeetingData =  self::getZoomMeetingDetails($appt_id);
		$messages = array();

		if(empty($zoomMeetingData)) {
			$body_params = array(
				'topic' => isset($appt_data['pc_title']) ? $appt_data['pc_title'] : "",
				'type' => '2',
				'start_time' => gmdate("Y-m-d\TH:i:s\Z", strtotime($appt_data['event_datetime'])),
				'duration' => (isset($appt_data['pc_duration']) && !empty($appt_data['pc_duration'])) ? intval($appt_data['pc_duration'] / 60) : '',
				'timezone' => self::getTimeZone(),
				'agenda' => isset($appt_data['pc_hometext']) ? $appt_data['pc_hometext'] : "",
			);

			$serviceRes = self::curl($body_params, $meeting_api);

			if(isset($serviceRes) && isset($serviceRes['message'])) {
				$messages[] = $serviceRes['message'];
				self::writeLogMsg($serviceRes['message']);
			}

			if(isset($serviceRes) && isset($serviceRes['id']) && !empty($serviceRes['id']) && isset($serviceRes['join_url']) && !empty($serviceRes['join_url'])) {
				$meeting_details = array(
					'pc_eid' => $appt_id,
					'pc_aid' => isset($appt_data['pc_aid']) ? $appt_data['pc_aid'] : "",
					'm_id' => isset($serviceRes['id']) ? $serviceRes['id'] : "",
					'host_email' => isset($serviceRes['host_email']) ? $serviceRes['host_email'] : "",
					'topic' => isset($serviceRes['topic']) ? $serviceRes['topic'] : "",
					'start_time' => isset($serviceRes['start_time']) ? date("Y-m-d H:i:s", strtotime($serviceRes['start_time'])) : "",
					'duration' => isset($serviceRes['duration']) ? $serviceRes['duration'] : "",
					'timezone' => isset($serviceRes['timezone']) ? $serviceRes['timezone'] : "",
					'start_url' => isset($serviceRes['start_url']) ? $serviceRes['start_url'] : "",
					'join_url' => isset($serviceRes['join_url']) ? $serviceRes['join_url'] : "",
					'password' => isset($serviceRes['password']) ? $serviceRes['password'] : "",
					'responce_data' => isset($serviceRes) ? json_encode($serviceRes) : ""
				);

				if(isset($meeting_details) && !empty($meeting_details)) {
					self::saveZoomMeetingDetails($meeting_details);
				}
			}
		} else if(isset($zoomMeetingData['m_id']) && $force_update === true) {
			$updateResData = self::updateZoomMeetingForAppt($zoomMeetingData['m_id'], $appt_id, $appt_data);

			if(isset($updateResData) && $updateResData['status'] === false) {
				$messages = array_merge($messages, $updateResData['messages']);
			}
		}

		return array(
			'status' => empty($messages) ? true : false,
			'messages' => $messages
		);
	}

	/*Update Zoom meeting update*/
	public static function updateZoomMeetingForAppt($meeting_id, $appt_id, $appt_data) {
		$meeting_api = 'https://api.zoom.us/v2/meetings/'.$meeting_id;
		$body_params = array(
			'topic' => isset($appt_data['pc_title']) ? $appt_data['pc_title'] : "",
			'type' => '2',
			'start_time' => gmdate("Y-m-d\TH:i:s\Z", strtotime($appt_data['event_datetime'])),
			'duration' => (isset($appt_data['pc_duration']) && !empty($appt_data['pc_duration'])) ? intval($appt_data['pc_duration'] / 60) : '',
			'timezone' => self::getTimeZone(),
			'agenda' => isset($appt_data['pc_hometext']) ? $appt_data['pc_hometext'] : "",
		);
		$messages = array();

		$updateServiceRes = self::curl($body_params, $meeting_api, 'PATCH');

		if(isset($updateServiceRes) && isset($updateServiceRes['message'])) {
			$messages[] = $updateServiceRes['message'];
			self::writeLogMsg($updateServiceRes['message']);
		}

		if($updateServiceRes !== false && !isset($updateServiceRes['message'])) {
			$serviceRes = self::getZoomMeetingForAppt($meeting_id);

			//Error
			if(isset($serviceRes) && isset($serviceRes['message'])) {
				$messages[] = $serviceRes['message'];
				self::writeLogMsg($serviceRes['message']);
			}

			if(isset($serviceRes) && isset($serviceRes['id']) && !empty($serviceRes['id']) && isset($serviceRes['join_url']) && !empty($serviceRes['join_url'])) {
				$meeting_details = array(
					'pc_eid' => $appt_id,
					'pc_aid' => isset($appt_data['pc_aid']) ? $appt_data['pc_aid'] : "",
					'm_id' => isset($serviceRes['id']) ? $serviceRes['id'] : "",
					'host_email' => isset($serviceRes['host_email']) ? $serviceRes['host_email'] : "",
					'topic' => isset($serviceRes['topic']) ? $serviceRes['topic'] : "",
					'start_time' => isset($serviceRes['start_time']) ? date("Y-m-d H:i:s", strtotime($serviceRes['start_time'])) : "",
					'duration' => isset($serviceRes['duration']) ? $serviceRes['duration'] : "",
					'timezone' => isset($serviceRes['timezone']) ? $serviceRes['timezone'] : "",
					'start_url' => isset($serviceRes['start_url']) ? $serviceRes['start_url'] : "",
					'join_url' => isset($serviceRes['join_url']) ? $serviceRes['join_url'] : "",
					'password' => isset($serviceRes['password']) ? $serviceRes['password'] : "",
					'responce_data' => isset($serviceRes) ? json_encode($serviceRes) : ""
				);

				if(isset($meeting_details) && !empty($meeting_details)) {
					self::updateZoomMeetingDetails($meeting_details);
				}
			}
		}

		return array(
			'status' => empty($messages) ? true : false,
			'messages' => $messages
		);
	}

	/*Delete Zoom meeting*/
	public static function deleteZoomMeetingForAppt($meeting_id) {
		$meeting_api = 'https://api.zoom.us/v2/meetings/'.$meeting_id;
		$serviceRes = self::curl(null, $meeting_api, 'DELETE');

		if(isset($serviceRes) && isset($serviceRes['message'])) {
			$messages[] = $serviceRes['message'];
			self::writeLogMsg($serviceRes['message']);
		}

		return $serviceRes;
	}

	/*Get Zoom meeting Details*/
	public static function getZoomMeetingForAppt($meeting_id) {
		$meeting_api = 'https://api.zoom.us/v2/meetings/'.$meeting_id;
		$serviceRes = self::curl(null, $meeting_api);

		if(isset($serviceRes) && isset($serviceRes['message'])) {
			$messages[] = $serviceRes['message'];
			self::writeLogMsg($serviceRes['message']);
		}

		return $serviceRes;
	}

	/* Handle CURL response from servers. */
	public static function handle($result, $httpCode) {
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

	/*Handle cURL call*/
	public static function curl($data = null, $api_url, $method = null) {
		$configList = self::getConfigVars();

		// Force data object to array
		$data = $data ? (array) $data : $data;
		
		// Define header values
		$headers = [
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Bearer '.$configList->access_token
		];
		
		// Set up client connection
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		if($method == "PATCH") {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
		} else if($method == "DELETE") {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		
		// Specify the raw post data
		if ($data && $data != null) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}
		
		// Send data
		$result = curl_exec($ch);
		$errCode = curl_errno($ch);
		$errText = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Handle result
		return self::handle($result, $httpCode);
	}

	/*Save Zoom Meeting Details*/
	public static function saveZoomMeetingDetails($data) {
		extract($data);

		//Write new record
		$sql = "INSERT INTO `zoom_appointment_events` ( ";
		$sql .= "pc_eid, pc_aid, m_id, host_email, topic, start_time, duration, timezone, start_url, join_url, password, responce_data ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$pc_eid,
			$pc_aid,
			$m_id,
			$host_email,
			$topic,
			$start_time,
			$duration,
			$timezone,
			$start_url,
			$join_url,
			$password,
			$responce_data
		));

		return true;
	}

	/*Save Zoom TeleHealth Meeting Details*/
	public static function saveZoomTeleHealthMeetingDetails($data) {
		extract($data);

		//Write new record
		$sql = "INSERT INTO `zoom_telehealth_appointments` ( ";
		$sql .= "pc_eid, session_id, topic, start_time, duration, timezone, provider_join_url, patient_join_url ) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ";
			
		sqlInsert($sql, array(
			$pc_eid,
			$session_id,
			$topic,
			$start_time,
			$duration,
			$timezone,
			$provider_join_url,
			$patient_join_url
		));

		return true;
	}

	/*Update Zoom Telehealth Meeting Details*/
	public static function updateZoomTelehealthMeetingDetails($data) {
		extract($data);

		//Write new record
		sqlStatementNoLog("UPDATE `zoom_telehealth_appointments` SET topic=?, start_time=?, duration=?, timezone=?, provider_join_url=?, patient_join_url=?  WHERE pc_eid = ? AND session_id = ?", array(
			$topic,
			$start_time,
			$duration,
			$timezone,
			$provider_join_url,
			$patient_join_url,
			$pc_eid,
			$session_id
		));

		return true;
	}

	/*Update Zoom Meeting Details*/
	public static function updateZoomMeetingDetails($data) {
		extract($data);

		//Update new record	
		sqlStatementNoLog("UPDATE `zoom_appointment_events` SET pc_aid=?, host_email=?, topic=?, start_time=?, duration=?, timezone=?, start_url=?, join_url=?, password=?, responce_data=?  WHERE pc_eid = ? AND m_id = ?", array(
			$pc_aid,
			$host_email,
			$topic,
			$start_time,
			$duration,
			$timezone,
			$start_url,
			$join_url,
			$password,
			$responce_data,
			$pc_eid,
			$m_id
		));

		return true;
	}

	public function getCategoryDetails($pc_catid) {
		$cat_record = sqlQueryNoLog("SELECT `pc_catname` FROM `openemr_postcalendar_categories` WHERE `pc_catid` = ?", array($pc_catid));
		return $cat_record;
	}

	/*Get zoom meeting details by id appointment by id*/
	public static function getZoomMeetingDetails($appt_id) {
		$sql = "SELECT * FROM `zoom_appointment_events` WHERE pc_eid = ?";
		$result = sqlQuery($sql, array($appt_id));
		return $result;
	}

	/*Get zoom telehealth meeting details by id appointment by id*/
	public static function getZoomTelehealthMeetingDetails($appt_id) {
		$sql = "SELECT * FROM `zoom_telehealth_appointments` WHERE pc_eid = ?";
		$result = sqlQuery($sql, array($appt_id));
		return $result;
	}

	/*Get Timezone*/
	public static function getTimeZone() {
		$glres = sqlQuery(
        "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name = 'gbl_time_zone'" .
	        "ORDER BY gl_name, gl_index"
	    );

	    if (!empty($glres['gl_value'])) {
            return $glres['gl_value'];
        }

        return "";
	}

	/*Get Appoinment*/
	public static function getAppoinmentData($appt_id) {
		$row = sqlQuery("SELECT e.*, u.username, u.fname, u.mname, u.lname, u.email_direct, pd.fname as p_fname, pd.mname as p_mname, pd.lname as p_lname, pd.email_direct as p_email_direct, za.`m_id` as `zm_id`, za.`join_url` as `zm_join_url`, za.`password` as `zm_password` " .
          "FROM openemr_postcalendar_events AS e " .
          "LEFT JOIN `zoom_appointment_events` as za ON za.`pc_eid` = e.`pc_eid` " .
          "LEFT OUTER JOIN users AS u ON u.id = e.pc_aid " .
          "LEFT OUTER JOIN patient_data AS pd ON pd.pid = e.pc_pid " .
          "WHERE e.pc_eid = ?", array($appt_id));
		return $row;
	}

	/*Write log line into file*/
	public static function writeLogMsg($msg) {
		$strMsg = date("Y-m-d H:i:s")." - ".$msg;
		/* Write cron log inside log file*/
		self::wh_log($strMsg);
	}

	/*Help to write log*/
	public static function wh_log($log_msg){
	    $log_filename = "../log";
	    if (!file_exists($log_filename)) 
	    {
	        // create directory/folder uploads.
	        mkdir($log_filename, 0777, true);
	    }
	    $log_file_data = $log_filename.'/zoomintegation_log_' . date('d-M-Y') . '.log';
	    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
	    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
	}

	/*Check Meeting already exist for same provider on same time*/
	public static function generateZoomMeetingHost($appt_id, $zm_data, $appt_data, $username, $start_time) {
		$email_domain = 'procaremedcenter.com';
		$provider_email = strtolower($username) . '@' . $email_domain;

		$use_existing = false;
		$zaHostEmailList = array();

		$pResultSet = self::getProviderMeeting($start_time, $end_time, $appt_data['pc_aid'], $appt_id);

		if(isset($pResultSet) && is_array($pResultSet) && count($pResultSet) > 0) {
			foreach ($pResultSet as $pRk => $za_result_data) {
				if($za_result_data['pc_eid'] == $appt_id) {
					$use_existing = true;
				}

				$zaHostEmailList[] = isset($za_result_data['host_email']) ? $za_result_data['host_email'] : "";
			}
		}
				
		// $za_sql = "SELECT * FROM `zoom_appointment_events` WHERE pc_aid = '".$appt_data['pc_aid']."' AND '".$start_time."' between start_time and DATE_ADD(start_time,interval duration minute)";
		
		// $za_result = sqlStatementNoLog($za_sql);
		// while ($za_result_data = sqlFetchArray($za_result)) {
		// 	if($za_result_data['pc_eid'] == $appt_id) {
		// 		$use_existing = true;
		// 	}
		// 	$zaHostEmailList[] = isset($za_result_data['host_email']) ? $za_result_data['host_email'] : "";
		// }

		if($zm_data['pc_aid'] != $appt_data['pc_aid']) {
			$use_existing = false;
		}

		if($use_existing === true) {
			$provider_email = $zm_data['host_email'];
		} else {
			if(!empty($zaHostEmailList)) {
				$provider_email = self::generateRandomEmailHost($username, $zaHostEmailList);
			}
		}

		return $provider_email;
	}

	/*Get Existing Email List*/
	public function getHostEmailList($appt_id, $pc_aid, $start_time) {
		$zaHostEmailList = array();
				
		$za_sql = "SELECT * FROM `zoom_appointment_events` WHERE pc_aid = '".$pc_aid."' AND '".$start_time."' between start_time and DATE_ADD(start_time,interval duration minute)";
		
		$za_result = sqlStatementNoLog($za_sql);
		while ($za_result_data = sqlFetchArray($za_result)) {
			$zaHostEmailList[] = isset($za_result_data['host_email']) ? $za_result_data['host_email'] : "";
		}

		return $zaHostEmailList;
	}

	/*Generate Random Email List*/
	public static function generateRandomEmailHost($username, $email_list) {
		$email_domain = 'procaremedcenter.com';
		$provider_email = strtolower($username) . '@' . $email_domain;

		if(!empty($email_list)) {
			$maxCounter = 0;
			$isEmailExits = false;

			while ($isEmailExits === false) {
				if($maxCounter === 0) {
					$tmp_provider_email	= strtolower($username) . '@' . $email_domain;
				} else if($maxCounter > 0){
					$tmp_provider_email	= strtolower($username) .'_zoom_' .$maxCounter. '@' . $email_domain;
				}

				if(isset($tmp_provider_email) && !in_array($tmp_provider_email, $email_list)) {
					$isEmailExits = true;
					$provider_email = $tmp_provider_email;
					break;
				}
				$maxCounter++;
			}
		}

		return $provider_email;
	}
}