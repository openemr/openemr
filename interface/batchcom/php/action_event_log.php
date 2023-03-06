<?php

use OpenEMR\Core\Header;

require_once("../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Reminder;

$event_id = isset($_REQUEST['event_id']) ? $_REQUEST['event_id'] : "";
$config_id = isset($_REQUEST['config_id']) ? $_REQUEST['config_id'] : "";
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";

if(isset($type) && !empty($type)) {
	if($type === "log") {
		
		$logData = Reminder::getLogFile();
		echo $logData;

	} else if($type === "prepare_data") {
		
		$cmdOutput = Reminder::prepareShellCmd($event_id, $config_id);
		echo $cmdOutput;

	} else if($type === "send_data") {

		$cmdOutput = Reminder::prepareSendShellCmd($event_id, $config_id);
		echo $cmdOutput;

	}
	exit();
}

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Prepare/Action'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme', 'datetime-picker']); ?>
	<script type="text/javascript">
		var refreshtime = 3000;

		function encodeData(data) {
		    return Object.keys(data).map(function(key) {
		        return [key, data[key]].map(encodeURIComponent).join("=");
		    }).join("&");
		}

		async function handlePrepareData(event_id = '', config_id = '') {
			var ajaxParam = {};

			if(event_id != '') {
				ajaxParam['event_id'] = event_id;
			}

			if(config_id != '') {
				ajaxParam['config_id'] = config_id;
			}

			//ajaxParam['type'] = 'prepare_data';

			var btnEle = $('#prepareDataBtn');
			btnEle.html('Prepare Data...');
			btnEle.prop('disabled', true);

			if(event_id != '') {
				var paramQuery = encodeData(ajaxParam);
				var ajaxUrl = "<?php echo $GLOBALS['webroot'].'/library/OemrAD/crons/reminder/cron_prepare_notification.php'; ?>?" + paramQuery;

				const result = await $.ajax({
			        url: ajaxUrl,
			        type: 'GET'
			    });
			}

			btnEle.html('Prepare Data');
			btnEle.prop('disabled', false);
		}

		async function handleSendData(event_id = '', config_id = '') {
			var ajaxParam = {};

			if(event_id != '') {
				ajaxParam['event_id'] = event_id;
			}

			if(config_id != '') {
				ajaxParam['config_id'] = config_id;
			}

			//ajaxParam['type'] = 'send_data';

			var btnEle = $('#sendDataBtn');
			btnEle.html('Send Data...');
			btnEle.prop('disabled', true);

			if(event_id != '') {
				var paramQuery = encodeData(ajaxParam);
				var ajaxUrl = "<?php echo $GLOBALS['webroot'].'/library/OemrAD/crons/reminder/cron_notification.php'; ?>?" + paramQuery;

				const result = await $.ajax({
			        url: ajaxUrl,
			        type: 'GET'
			    });
			}

			btnEle.html('Send Data');
			btnEle.prop('disabled', false);
		}

		$(document).ready(function(){
			async function ajaxRefresh(type = '') {
				const result = await $.ajax({
			        url: "<?php echo $GLOBALS['webroot'].'/interface/batchcom/php/action_event_log.php'; ?>",
			        type: 'POST',
			        data: { 'type' : type }
			    });

				if(result != "") {
					var logContainer = $('#logContainer');
					if(logContainer) {
						logContainer.html(result);
						logContainer.scrollTop(logContainer[0].scrollHeight);
					}

					return true;
				}

    			return false;
			}

			//refreshTimer = setInterval(ajaxRefresh, 100);
			async function tc() {
				var responce = await ajaxRefresh('log');

				if(responce !== false) {
					setTimeout(tc,refreshtime);
				}
			}

			//Init Call
			tc();
		});
	</script>
	<style type="text/css">
		#logContainer {
			white-space: pre-wrap;
   			height: calc(100% - 60px);
    		overflow: auto;

    		background-color: #000;
    		color: #fff;
    		font-size: 13px;
		}
		#prepareDataBtn:disabled, 
		#sendDataBtn:disabled {
			opacity: 0.5;
		}
		#sendDataBtn {
			margin-left: 10px;
		}
		.btnContainer {
			float: right;
    		padding: 15px 10px;
		}
	</style>
</head>
<body>
	<div class="mainContainer">
		<div id="logContainer">
		</div>
		<div class="btnContainer">
			<button type="button" id="prepareDataBtn" class="" onClick="handlePrepareData('<?php echo $event_id; ?>', '<?php echo $config_id; ?>')">Prepare Data</button>
			<button type="button" id="sendDataBtn" class="" onClick="handleSendData('<?php echo $event_id; ?>', '<?php echo $config_id; ?>')">Send Data</button>
		</div>
	</div>
</body>
</html>
