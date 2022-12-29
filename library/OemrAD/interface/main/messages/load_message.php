<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'] . "/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\Core\Header;

$msgId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$typeId = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";

sleep(3);

?>
<html>
<head>
	<meta charset="utf-8">
	<title>Message</title>
	<?php Header::setupHeader(['common']); ?>

</head>
<body>
	<div class="text">
		<?php if(isset($typeId) && $typeId == "notes") {
			if(!empty($msgId)) {
				$note_data = sqlQuery("SELECT p.* FROM pnotes AS p where p.id = ?" .$searchQuery, array($msgId));

				$body = $note_data['body'];
				if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
					$body = nl2br(oeFormatPatientNote($body));
				} else {
					$body = htmlspecialchars(oeFormatSDFT(strtotime($note_data['date'])) . date(' H:i', strtotime($note_data['date'])), ENT_NOQUOTES) . ' (' . htmlspecialchars($note_data['user'], ENT_NOQUOTES) . ') ' .'<br>'. nl2br(oeFormatPatientNote($body));
				}
				$body = preg_replace('/(\sto\s)-patient-(\))/', '${1}' . $patientname . '${2}', $body);
				
				echo $body;
			}
		} else { 
			if(!empty($msgId)) {
				$messageData = EmailMessage::getMessageByIds(array($msgId));
				$message = (is_array($messageData) && count($messageData) > 0)  ? $messageData[0]['message'] : "";
				$formatedMessage = MessagesLib::formateMessageContent($message);	

				ob_start();

				?>
				<style type="text/css">
					/*.plainText {
						white-space: pre;
			    		font-family: lato, Helvetica, sans-serif;
			    		font-size: 12px;
					}
					body{
						font-family: lato, Helvetica, sans-serif;
			    		font-size: 12px;
					}*/
				</style>
				<?php

				echo $formatedMessage;
				$html = ob_get_clean();

				echo $html;
			}
		} ?>
	</div>
</body>
</html>
<?php
