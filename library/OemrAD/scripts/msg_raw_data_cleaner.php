<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

require_once(dirname( __FILE__, 2 ) . "/interface/globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Attachment;

global $log_file_data, $encpc_count;
$log_file_data = dirname( __FILE__, 2 ) . '/log/msg_raw_script_log.log';

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}

function isHTML($string){
	return $string != strip_tags($string) ? true:false;
}

function isJson($string) {
   json_decode($string);
   return json_last_error() === JSON_ERROR_NONE;
}

function wh_log($log_msg) {
	global $log_file_data;
    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
}


$fh = fopen($log_file_data, 'w' );
fclose($fh);

$encpc_count = 0;
$msg_update_count = 0;
$attachment_update_count = 0;
$item_count = 1;
$oldfield_update_count = 0;
$in_correct_json_count = 0;

$perPage = 5000;
$rcount = sqlQuery("SELECT count(id) as total from message_log ml where ml.raw_data != '' order by id desc");
$totalPages = ceil($rcount['total'] / $perPage);

for ($pi = 1; $pi <= $totalPages; $pi++) {
	$startAt = $perPage * ($pi - 1);
	$msgresult = sqlStatement("SELECT ml.id, ml.raw_data from message_log ml where ml.raw_data != '' order by id desc LIMIT $startAt, $perPage", array());

	while ($msgrow = sqlFetchArray($msgresult)) {
		$isMsgUpdated = false;
		$isInCorrectJsonUpdated = false;
		$isAttachmentUpdated = false;
		$isOldFieldRemoved = false;

		$msg_id = $msgrow['id'];

		// echo $item_count . "\n";
		// $item_count++;
		// continue;

		if($msgrow['raw_data'] !== "" && (!isJson($msgrow['raw_data']) || $msgrow['raw_data'] == "0")) {
			$msg_raw_data = '';
			$isInCorrectJsonUpdated = true;
			$in_correct_json_count++;
		}

		if($isInCorrectJsonUpdated === false) {
			$msg_raw_data = json_decode($msgrow['raw_data'], true);
			$msg_template = isset($msg_raw_data['message']) ? $msg_raw_data['message'] : '';
			$msg_doclist = isset($msg_raw_data['baseDocList']) ? $msg_raw_data['baseDocList'] : array();

			$unusedfieldupdate = false;
			$fieldList = array('files_length', 'notes', 'documentFiles', 'encounters', 'encounters1', 'encounterIns', 'docsList', 'prevData');
			$msg_raw_data1 = $msg_raw_data;

			foreach ($msg_raw_data1 as $mrfield => $mrfieldValue) {
				if(in_array($mrfield, $fieldList)) {
					unset($msg_raw_data[$mrfield]);
					$unusedfieldupdate = true;
				}
			}

			if($unusedfieldupdate === true) {
				$oldfield_update_count++;
				$isOldFieldRemoved = true;
			}


			if(!empty($msg_template) && isHTML($msg_template)) {
				$msg_raw_data['message'] = '';
				$msg_update_count++;
				$isMsgUpdated = true;
			}

			if(!empty($msg_doclist)) {
				$msg_doclist = json_decode($msg_doclist, true);
				// Reparepare data for old message attachment data.
		        $preparedData = @Attachment::prepareOldMessageAttachmentData($msg_doclist);
				//$preparedData = Attachment::prepareMessageAttachment($msg_doclist);

				foreach ($preparedData as $pdk => $pdI) {
					if(empty($pdI)) {
						unset($preparedData[$pdk]);
					}
				}

				if(!empty($preparedData)) {
					$msg_raw_data['attachments'] = $preparedData;
					unset($msg_raw_data['baseDocList']);
					$attachment_update_count++;
					$isAttachmentUpdated = true;
				}
			}
		}

		if($isAttachmentUpdated === true || $isMsgUpdated === true || $isOldFieldRemoved === true || $isInCorrectJsonUpdated === true) {

			if(!empty($msg_id)) {
	            //sqlStatementNoLog("UPDATE `message_log` SET `raw_data` = ? WHERE id = ?", array(is_array($msg_raw_data) ? json_encode($msg_raw_data) : '', $msg_id));
	        }

			ob_start();
			echo "\n\n**************** ".$item_count." ****************\n";
			echo "MsgID = " . $msgrow['id'] . "\n";

			if($isAttachmentUpdated === true) echo "isAttachmentUpdated = " . $isAttachmentUpdated . "\n";
			if($isMsgUpdated === true) echo "isMsgUpdated = " . $isMsgUpdated . "\n";
			if($isOldFieldRemoved === true) echo "isOldFieldRemoved = " . $isOldFieldRemoved . "\n";
			if($isInCorrectJsonUpdated === true) echo "isInCorrectJsonUpdated = " . $isInCorrectJsonUpdated . "\n";


			print_r(json_encode($msg_raw_data));

			$item_count++;

			$sc_output = ob_get_clean();

			echo $sc_output;
			//write log into msg file
			wh_log($sc_output);
		}
	}

	unset($msgresult);
}

ob_start();
echo "\n\n";
echo "encpc_count = " . $encpc_count . "\n";
echo "msg_update_count = " . $msg_update_count . "\n";
echo "attachment_update_count = " . $attachment_update_count . "\n";
echo "oldfield_update_count = " . $oldfield_update_count . "\n";
echo "in_correct_json_count = " . $in_correct_json_count . "\n";
$sc_output2 = ob_get_clean();

echo $sc_output2;
//write log into msg file
wh_log($sc_output2);