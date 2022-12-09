<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'] . "/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;

$msgId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";

if(!empty($msgId)) {
	$messageData = EmailMessage::getMessageByIds(array($msgId));
	$message = (is_array($messageData) && count($messageData) > 0)  ? $messageData[0]['message'] : "";
	// $raw_data = (is_array($messageData) && count($messageData) > 0 && !empty($messageData[0]['raw_data']))  ? json_decode($messageData[0]['raw_data'], true) : array();

	// if(!empty($raw_data) && $messageData[0]['direction'] == "in") {
	// 	if(isset($raw_data['mail']) && isset($raw_data['mail']['content']) && is_array($raw_data['mail']['content'])) {
	// 		foreach ($raw_data['mail']['content'] as $mi => $contentItem) {
	// 			if(isset($contentItem['mime']) && $contentItem['mime'] == "TEXT/HTML") {
	// 				$message = $contentItem['data'];
	// 			}
	// 		}
	// 	}
	// }

	$formatedMessage = EmailMessage::formateMessageContent($message);	

	ob_start();
	
	?>
	<style type="text/css">
		.plainText {
			white-space: pre;
    		font-family: lato, Helvetica, sans-serif;
    		font-size: 12px;
		}
		body{
			font-family: lato, Helvetica, sans-serif;
    		font-size: 12px;
		}
	</style>
	<?php
	echo $formatedMessage;
	
	$html = ob_get_clean();

	echo $html;
}
