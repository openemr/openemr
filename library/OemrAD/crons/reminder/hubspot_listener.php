<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
ini_set('display_errors',1);

$ignoreAuth = true; // signon not required!!
$_GET['site'] = 'default';

require_once("../../interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Reminder;
use OpenEMR\OemrAd\HubspotSync;

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}

//Config Data
$jsonConfigData = json_decode('{"config":{"contact.creation":{"event_id":"hubsport_sync","config_id":"hubspot_in_create_update","mode":"INSERT_UPDATE"},"contact.propertyChange":{"event_id":"hubsport_sync","config_id":"hubspot_in_create_update","mode":"INSERT_UPDATE"},"contact.deletion":{"event_id":"hubsport_sync","config_id":"hubspot_in_delete","mode":"DELETE"}}}', true);

if(!isset($GLOBALS['hubspot_listener_sync_config']) || empty($GLOBALS['hubspot_listener_sync_config'])) {
	exit();
}

$jsonConfigData1 = json_decode($GLOBALS['hubspot_listener_sync_config'], true);

$filterData = isset($jsonConfigData1['filterData']) ? $jsonConfigData1['filterData'] : array();
$token = isset($jsonConfigData1['token']) ? $jsonConfigData1['token'] : '';
$jsonConfig = isset($jsonConfigData['config']) ? $jsonConfigData['config'] : array();

$jsonData = json_decode(file_get_contents('php://input'), true);
$syncAll = isset($_REQUEST['syncAll']) ? $_REQUEST['syncAll'] : "0";

if(isCommandLineInterface() === true) {
	if (!empty($argv[1])) {
		$syncAll = $argv[1];
	}
}

//logDataIntoFile(json_encode($jsonData));

function logDataIntoFile($log = "") {
	$log = $log . "\n\n";
	file_put_contents('../../log/hubspot_log_'.date("j.n.Y").'.log', $log, FILE_APPEND);
}

function createlogData($data = array()) {
	extract($data);

	if(!empty($data)) {
		$binds = array();

		foreach ($data as $di => $dItem) {
			$binds[] = $dItem !== '' ? $dItem : '';
		}

		$nId = sqlInsert("INSERT INTO vh_hubspot_sync_log ( event_id, config_id, event_type, mode, tablename, uniqueid, user_type, sent, trigger_time, time_delay, request_responce) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )", $binds);
	}

	return $nId;
}

function getLogCount($event_id = '', $config_id = '', $uniqueid = '') {
	if(!empty($event_id) && !empty($config_id) && !empty($uniqueid)) {
		$count_result = sqlQuery("SELECT count(*) as total from vh_hubspot_sync_log vhsl where vhsl.event_id = ? and vhsl.config_id  = ? and vhsl.uniqueid = ? and sent = 0", array($event_id, $config_id, $uniqueid));

		return isset($count_result['total']) ? intval($count_result['total']) : 0;
	}

	return 0;
}

function handleCreateUpdateDelete($data = array(), $objectId = '', $optType = '', $changeSource = false) {
	global $jsonConfig, $filterData, $token, $jsonData;
	$eventId = isset($jsonConfig[$optType]['event_id']) ? $jsonConfig[$optType]['event_id'] : "";
	$configId = isset($jsonConfig[$optType]['config_id']) ? $jsonConfig[$optType]['config_id'] : "";
	$mode = isset($jsonConfig[$optType]['mode']) ? $jsonConfig[$optType]['mode'] : "";

	if($changeSource === "API") {
		return false;
	}

	if(!isset($jsonConfig[$optType])) {
		return false;
	}

	if(empty($data) && !empty($objectId)) {
		$contactData = HubspotSync::getContactData($objectId, array(
			'bearer_token' => $token,
			'properties' => 'properties=pro_care_contact_types'
		));

		if(!empty($contactData) && isset($contactData['id'])) {
			$data = $contactData;
		}
	}

	if(!empty($data)) {
		$objectId = isset($data['id']) ? $data['id'] : "";
		$properties = isset($data['properties']) ? $data['properties'] : "";
	}

	if(!empty($objectId)) {
		$fField = isset($filterData['field']) ? $filterData['field'] : '';
		$fValue = isset($filterData['value']) ? $filterData['value'] : '';

		$fStatus = true;
		
		if(!empty($fField)) {
			if($optType === "contact.propertyChange" || $optType === "contact.creation") {
				$proCareVal = isset($properties[$fField]) ? explode(";", $properties[$fField]) : array();
				if(!in_array($fValue, $proCareVal)) {
					$fStatus = false;
				}
			}
		}

		//Check is mapping exits.
		if($optType === "contact.propertyChange") {
			$mData = HubspotSync::getMappingData(array('hubspot_id' => $objectId));
			if(!empty($mData) && $fStatus === false) {
				
				//Handle Delete
				handleCreateUpdateDelete(array(), $objectId, 'contact.deletion');
				return false;
			}
		}
		
		if($fStatus === true && !empty($eventId) && !empty($configId) && !empty($mode)) {
			$rCount = getLogCount($eventId, $configId, $objectId);
			if($rCount === 0) {
				$hpData = array(
					'event_id' => $jsonConfig[$optType]['event_id'],
					'config_id' => $jsonConfig[$optType]['config_id'],
					'event_type' => '2',
					'mode' => $jsonConfig[$optType]['mode'],
					'tablename' => 'users',
					'uniqueid' => $objectId,
					'user_type' => 'Cron',
					'sent' => 0,
					'trigger_time' => date('Y-m-d H:i:s', strtotime("+2 minutes")),
					'time_delay' => 0,
					'request_responce' => json_encode($jsonData)
				);

				if(!empty($hpData)) {
					createlogData($hpData);
					return true;
				}
			}
		}
	}

	return false;
}

function syncAllData($pData = array()) {
	global $token;
	$tCount = 0;

	$propStr = "properties=pro_care_contact_types&limit=100";
	if(!empty($pData) && isset($pData['next']) && isset($pData['next']['after'])) {
		$propStr .= "&after=" . $pData['next']['after'];
	} else if(!empty($pData)) {
		return false;
	}

	$allContactData = HubspotSync::getAllContactData(array(
		'bearer_token' => $token,
		'properties' => $propStr
	));

	if(!empty($allContactData)) {
		if(isset($allContactData['results'])) {
			$res = isset($allContactData['results']) ? $allContactData['results'] : array();
			if(!empty($res)) {
				foreach ($res as $rk => $rItem) {
					$resStatus = handleCreateUpdateDelete($rItem, '', 'contact.creation');
					if($resStatus === true) {
						$tCount++;
					}
				}
			}
		}

		if(isset($allContactData['paging']) && !empty($allContactData['paging'])) {
			$syncCount = syncAllData($allContactData['paging']);
			if(!empty($syncCount)) {
				$tCount = $tCount + $syncCount;
			}
		}
	}

	return $tCount;
}

if($syncAll === "1") {
	
	$syncCount = syncAllData();
	echo "Total Items: " . $syncCount;

} else if($syncAll === "0") {
	if(!empty($jsonData)) {
		foreach ($jsonData as $ji => $jItem) {
			$optType = isset($jItem['subscriptionType']) ? $jItem['subscriptionType'] : "";
			$objectId = isset($jItem['objectId']) ? $jItem['objectId'] : "";
			$changeSource = isset($jItem['changeSource']) ? $jItem['changeSource'] : "";
			$hpData = array();

			if(!empty($objectId)) {
				if(isset($jsonConfig[$optType])) {
					handleCreateUpdateDelete(array(), $objectId, $optType, $changeSource);
				}
			}
		}
	}
} else if($syncAll === "2") {
	if(!empty($_REQUEST)) {
		logDataIntoFile(json_encode($_REQUEST));
	}
	logDataIntoFile(json_encode($jsonData));
}
