<?php

namespace OpenEMR\OemrAd;

require_once("../interface/globals.php");
require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
require_once('./mdTwilioUtilitylib.class.php');

class TwilioUtilitylib {
	/**
	 * Constructor for the 'SMS' class
	 */
	public function __construct() {
	}

	public static function appendExtraMessagePart(&$content, $otherData =  array()) {
		$toNumber = isset($otherData['rawToNumber']) ? $otherData['rawToNumber'] : "";
		$smsText = $GLOBALS['EXTRA_SMS_TEXT'];
		$dayInterval = $GLOBALS['EXTRA_SMS_TEXT'];

		if(!empty($toNumber) && !empty($smsText) && !empty($dayInterval)) {
			$sql = "SELECT ml.*, DATE(ml.`msg_time`) as msg_date, IF(DATE_ADD(DATE(ml.`msg_time`), INTERVAL 30 DAY) <= CURDATE(), 'YES', 'NO') as is_need_to_append from message_log as ml where type = 'SMS' and direction = 'out' and msg_to = ? and message LIKE ? ORDER BY `msg_time` DESC LIMIT 1";
			$row = sqlQuery($sql, array($toNumber, '%'.$smsText.'%'));

			if(empty($row) || $row['is_need_to_append'] == "YES") {
				$appendText = "<br><br>".$smsText."<br>";
				$appendText = str_replace('<br>', "\n", $appendText);
			}

			$content = $content . $appendText;

			return $content;
		}
		return $content;
	}

	/*Make appointment confirm */
	public static function confirmApp($data = array()) {
		if(!empty($data)) {
			$pid = isset($data['pid']) ? $data['pid'] : "";
			$fromNumber = isset($data['fromNumber']) ? $data['fromNumber'] : "";
			$reg_fromNumber = isset($data['reg_fromNumber']) ? $data['reg_fromNumber'] : "";
			$msg_date = isset($data['msg_date']) ? $data['msg_date'] : "";
			$msgText = isset($data['text']) ? trim($data['text']) : "";
			$newMsgId = isset($data['msg_id']) ? trim($data['msg_id']) : "";
			$configIds = isset($GLOBALS['APPT_CONFIRM_CONFIG_ID']) && !empty(trim($GLOBALS['APPT_CONFIRM_CONFIG_ID'])) ? explode(",", $GLOBALS['APPT_CONFIRM_CONFIG_ID']) : array();

			$isConfirmMsg = self::isConfirmMsg($msgText);

			if($isConfirmMsg === true && !empty($configIds)) {
				if(!empty($pid) && !empty($fromNumber) && !empty($reg_fromNumber) && !empty($msg_date)) {
					$configIdsStr = "'".implode("','",$configIds)."'";

					$sql = "SELECT nl.*, ml.id as msgId FROM notif_log nl JOIN message_log ml on ml.id = nl.msg_id JOIN openemr_postcalendar_events ope on ope.pc_eid = nl.uniqueid where nl.config_id IN(".$configIdsStr.") and nl.tablename = 'openemr_postcalendar_events' and nl.msg_id is not null and ope.pc_apptstatus = '-' and ml.direction = 'out' and nl.sent = 1 and ml.pid = ? and DATE_ADD(ml.`date`, INTERVAL 1 DAY) >= ? and ml.`date` <= ? and replace(replace(replace(replace(replace(replace(ml.msg_to,' ',''),'(','') ,')',''),'-',''),'/',''),'+','') regexp ?";

					$resultItems = array();
					$result = sqlStatementNoLog($sql, array($pid, $msg_date, $msg_date, $reg_fromNumber));
					while ($row = sqlFetchArray($result)) {
						$resultItems[] = $row;
					}
					
					if(!empty($resultItems) && count($resultItems) === 1) {
						$app_id = isset($resultItems[0]['uniqueid']) ? $resultItems[0]['uniqueid'] : "";
						$notif_log_id = isset($resultItems[0]['id']) ? $resultItems[0]['id'] : "";
						
						if(!empty($app_id)) {
							sqlStatementNoLog("UPDATE openemr_postcalendar_events set pc_apptstatus = '+' WHERE pc_eid = ? LIMIT 1 ", array($app_id));
							self::saveApptConfirmationLog(array(
								'notif_log_id' => $notif_log_id,
								'appt_id' => $app_id,
								'msg_id' => $newMsgId,
								'pid' => $pid,
								'status' => 1
							));
							self::updateMsgData($newMsgId, array('activity' => 0));
						}
					} else {
						if(!empty($resultItems) && count($resultItems) > 1) {
							foreach ($resultItems as $rk => $rItem) {
								$app_id = isset($rItem['uniqueid']) ? $rItem['uniqueid'] : "";
								$notif_log_id = isset($rItem['id']) ? $rItem['id'] : "";

								self::saveApptConfirmationLog(array(
									'notif_log_id' => $notif_log_id,
									'appt_id' => $app_id,
									'msg_id' => $newMsgId,
									'pid' => $pid,
									'status' => 0
								));

								//self::updateMsgData($newMsgId, array('activity' => 0));
							}
						}
					}
				}
			}
		}
	}

	public static function isConfirmMsg($msg = '') {
		$resStatus = false;
		$msgText = trim($msg);
		$msgWordList = explode(" ", strtolower($msgText));

		$keyworldList = array("confirm", "confirmed");

		if(!empty($msgText)) {
			if(count($msgWordList) === 1) {
				foreach ($keyworldList as $klm => $kValue) {
					if(in_array($kValue, $msgWordList)) {
						$resStatus = true;
					}
				}
			}
		}

		return $resStatus;
	}

	public static function saveApptConfirmationLog($data = array()) {
		if(!empty($data)) {

			extract($data);

			$sql = "INSERT INTO `vh_appt_confirmation_log` ( notif_log_id, appt_id, msg_id, pid, status ) VALUES (?, ?, ?, ?, ?) ";
			return sqlInsert($sql, array(
				$notif_log_id,
				$appt_id,
				$msg_id,
				$pid,
				$status
			));
		}

		return false;
	}

	public static function updateMsgData($id, $data = array()) {
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
				sqlStatementNoLog("UPDATE `message_log` SET ".$setStr." WHERE id = ? LIMIT 1", $binds);
			}
		}
	}
}