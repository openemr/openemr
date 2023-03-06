<?php

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\OemrAd\MessagesLib;

$msgId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";

if(!empty($msgId)) {
	$messageData = MessagesLib::getMessageByIds(array($msgId));
	$message = (is_array($messageData) && count($messageData) > 0)  ? $messageData[0]['message'] : "";

	$formatedMessage = MessagesLib::formateMessageContent($message);	

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
