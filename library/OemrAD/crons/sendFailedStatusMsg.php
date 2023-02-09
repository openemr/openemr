<?php
$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME']='localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SESSION['site'] = 'default';
$backpic = "";
$ignoreAuth=1;

@require_once(dirname( __FILE__, 2 ) . "/interface/globals.php");
@require_once("$srcdir/OemrAD/oemrad.globals.php");
@include_once($GLOBALS['srcdir']."/pnotes.inc");

use OpenEMR\OemrAd\FaxMessage;
use OpenEMR\OemrAd\PostalLetter;
use OpenEMR\OemrAd\MessagesLib;

function isCommandLineInterface(){
    return (php_sapi_name() === 'cli');
}
?>
<?php if(isCommandLineInterface() === false) { ?>
<html>
<head>
	<title>Cronjob - Send Status</title>
</head>
<body>
<?php } ?>
<?php

$messageType = "Message Board";

$resultData = sqlStatement("SELECT ml.*, us.username AS user_username, fm.fax_id, fm.status_code AS fax_status_code, fm.description AS fax_description, pl.letter_id, pl.status_code AS pl_status_code, pl.description AS pl_description, pn.id AS pn_id FROM `message_log` AS ml LEFT JOIN `fax_messages` AS fm ON fm.message_id = ml.id  AND ml.type = 'FAX' LEFT JOIN `postal_letters` AS pl ON pl.message_id = ml.id AND ml.type = 'P_LETTER' LEFT JOIN `gprelations` AS gp ON gp.type1 = '104' AND gp.type2 = '6' AND gp.id1 = ml.id LEFT JOIN `pnotes` AS pn ON pn.title = '".$messageType."' AND pn.id = gp.id2 LEFT JOIN `users` us ON ml.`userid` = us.`id` WHERE ml.type IN ('FAX', 'P_LETTER') AND (pn.id = '' OR pn.id IS NULL) AND ((ml.type = 'FAX' AND fm.status_code NOT IN ('success')) OR (ml.type = 'P_LETTER' AND pl.status_code NOT IN ('-1000', '-1002', '1', '2', '3'))) AND ml.date > DATE_SUB(NOW(), INTERVAL 24 HOUR) ", array());

$countItems = 0;
$countSentItems = 0;

while ($resultRow = sqlFetchArray($resultData)) {
	if(!empty($resultRow)) {
		$status_msg = "";
		if($resultRow['type'] == "FAX") {
			$status_msg = isset($resultRow['fax_description']) ? $resultRow['fax_description'] : "";
		} else if($resultRow['type'] == "P_LETTER") {
			$status_msg = isset($resultRow['pl_description']) ? $resultRow['pl_description'] : "";
		}

		$pid = isset($resultRow['pid']) ? $resultRow['pid'] : "";
		$note = "Note: Above message was ( ".$status_msg." )";
		$title = $messageType;
		$assigned_to = isset($resultRow['user_username']) ? $resultRow['user_username'] : "";
		$msg_id = isset($resultRow['id']) ? $resultRow['id'] : "";

		$noteId = @addPnote($pid, $note, '1', '1', $title, $assigned_to, '', "New", $assigned_to);
		if(!empty($noteId)) {
			MessagesLib::saveMsgGprelation('104', $msg_id, '6', $noteId);
			$countSentItems++;
		}
	}
	$countItems++;
}

echo "Total Items: ".$countItems." Total Sent Messages: ".$countSentItems;

?>

<?php if(isCommandLineInterface() === false) { ?>
</body>
</html>
<?php
}