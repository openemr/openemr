<?php
/**
 * REminder Tool for selecting/communicating with subsets of patients
 */

require_once("../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\Reminder;
use OpenEMR\OemrAd\IdempiereWebservice;

$pid = strip_tags($_REQUEST['pid']);
$config_id = $_REQUEST['config_id'];
$trigger_type = strip_tags($_REQUEST['trigger_type']);
$action_type = strip_tags($_REQUEST['action_type']);
$action_mode = isset($_REQUEST['action_mode']) ? $_REQUEST['action_mode'] : "";
$selectedItem = strip_tags($_REQUEST['selectedItem']);
$selectedItem = (isJson($selectedItem) === true) ? json_decode($selectedItem, true) : array($selectedItem);
$showAll = isset($_REQUEST['result']) ? $_REQUEST['result'] : "";
$eId = isset($_REQUEST['eId']) ? $_REQUEST['eId'] : "";

// Delete Action
if (isset($action_mode) && !empty($config_id)  && $action_mode == 'delete') {
	Reminder::deleteNotificationConfiguration($config_id);
}

//Fetch configuration
$configurationList = Reminder::getNotificationConfiguration('', $trigger_type, $action_type);
$totalCheckConfig = totalCheckConfiguration($configurationList);

$communication_List = array(
	'email' => 'Email',
	'sms' => 'SMS',
	'fax' => 'Fax',
	'postalmethod' => 'Postal Method',
	'internalmessage' => 'Internal Message'
);

$trigger_List = array(
	'time' => 'Time',
	'event' => 'Trigger'
);

function isJson($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

function generateQtrStr($paramStr = array()) {
	$current_qtrStr = $_SERVER['QUERY_STRING'];
	parse_str($current_qtrStr, $tmp_qtrStr_array);

	$qtrStr_array = array();
	$param_list = array('pid', 'trigger_type', 'selectedItem', 'action_type', 'eId');

	foreach ($tmp_qtrStr_array as $param => $value) {
		if(in_array($param, $param_list)) {
			$qtrStr_array[$param] = $value;
		}
	}

	if(is_array($paramStr)) {
		foreach ($paramStr as $param => $val) {
			$qtrStr_array[$param] = $val;
		}
	}
	return http_build_query($qtrStr_array);
}

function generateFullUrl($url, $paramStr) {
	return $url ."?". generateQtrStr($paramStr);
}

function getDeleteButton($data) {
	if(!empty($data['id'])) {
		$isAlreadyInUse = Reminder::isAlreadyInUse($data['id']);
		if($isAlreadyInUse === false) {
			$deleteUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/time_configuration_list.php", array('action_mode' => 'delete', 'config_id' => $data['id']));
			?>
			<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="onDelete(event, '<?php echo $deleteUrl; ?>')"><span>Delete</span></a>
			<?php
		}
	}
}

function totalCheckConfiguration($configs) {
	global $selectedItem;

	$total = 0;
	foreach ($configs as $key => $item) {
		$isChecked = (in_array($item['id'], $selectedItem) == true) ? "checked" : "";
		if($isChecked == "checked") {
			$total++;
		}
	}
	return $total;
}

function writeConfigurationLine($data) {
	global $communication_List, $trigger_List, $trigger_type, $selectedItem, $showAll, $totalCheckConfig, $action_type, $eId;

	if(!empty($data)) {
		foreach ($data as $key => $item) {
			$timeData = isset($item['time_trigger_data']) ? json_decode($item['time_trigger_data'], true) : array();
			$timeTitle = isset($timeData['title']) ? $timeData['title'] : "";
			$templateTitle = Reminder::getTemplateByID($item['communication_type'], $item['notification_template']);
			$selectedClass = (in_array($item['id'], $selectedItem) == true) ? 'selectedRow' : '';
			$isChecked = (in_array($item['id'], $selectedItem) == true) ? "checked" : "";

			$syncModeList = array(
				'0' => 'Out',
				'1' => 'In'
			);
			
			if($totalCheckConfig != 0 && $showAll != "all" && $isChecked != "checked") {
				continue;
			}

		if($action_type == "action_reminder") {
			$title_text = IdempiereWebservice::getApiConfigTitle($item['api_config']);
		?>
			<tr class="selectRow <?php echo $selectedClass; ?>" data-id="<?php echo $item['id'] ?>">
				<td><input type="checkbox" class="selectedCheck" name="selectedItem[]" value="<?php echo $item['id'] ?>" <?php echo $isChecked; ?> /></td>
				<td><?php echo $item['id'] ?></td>
				<td><?php echo $title_text; ?></td>
				<td><?php echo $syncModeList[$item['sync_mode']]; ?></td>
				<td><?php echo isset($trigger_List[$item['trigger_type']]) ? $trigger_List[$item['trigger_type']] : ""; ?></td>
				
				<?php if($trigger_type == "time") { ?>
				<td width="150"><?php echo $timeTitle ?></td>
				<?php } ?>

				<td><?php echo $item['time_delay'] ?></td>
				
				<td width="150" class="actionContainer">
					<?php
						$editUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/save_configuration.php", array('action_mode' => 'update', 'config_id' => $item["id"]));
					?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="onEdit(event, '<?php echo $editUrl; ?>')"><span>Edit</span></a>
					<?php getDeleteButton($item); ?>

					<?php if(!empty($eId) && !empty($item['id'])) { ?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="prepareConfigPopup('<?php echo $eId ?>', '<?php echo $item['id'] ?>')"><span>Prepare/Action</span></a>
					<?php } ?>
				</td>
			</tr>
		<?php
		} else if($action_type == "hubspot_sync") {
			$title_text = IdempiereWebservice::getApiConfigTitle($item['api_config']);
		?>
			<tr class="selectRow <?php echo $selectedClass; ?>" data-id="<?php echo $item['id'] ?>">
				<td><input type="checkbox" class="selectedCheck" name="selectedItem[]" value="<?php echo $item['id'] ?>" <?php echo $isChecked; ?> /></td>
				<td><?php echo $item['id'] ?></td>
				<td><?php echo $title_text; ?></td>
				<td><?php echo $syncModeList[$item['sync_mode']]; ?></td>
				<td><?php echo isset($trigger_List[$item['trigger_type']]) ? $trigger_List[$item['trigger_type']] : ""; ?></td>
				
				<?php if($trigger_type == "time") { ?>
				<td width="150"><?php echo $timeTitle ?></td>
				<?php } ?>

				<td><?php echo $item['time_delay'] ?></td>
				
				<td width="150" class="actionContainer">
					<?php
						$editUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/add_configuration.php", array('action_mode' => 'update', 'config_id' => $item["id"]));
					?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="onEdit(event, '<?php echo $editUrl; ?>')"><span>Edit</span></a>
					<?php getDeleteButton($item); ?>

					<?php if(!empty($eId) && !empty($item['id'])) { ?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="prepareConfigPopup('<?php echo $eId ?>', '<?php echo $item['id'] ?>')"><span>Prepare/Action</span></a>
					<?php } ?>
				</td>
			</tr>
		<?php
		} else if($action_type == "idempiere_webservice") {
				$title_text = IdempiereWebservice::getApiConfigTitle($item['api_config']);
		?>
			<tr class="selectRow <?php echo $selectedClass; ?>" data-id="<?php echo $item['id'] ?>">
				<td><input type="checkbox" class="selectedCheck" name="selectedItem[]" value="<?php echo $item['id'] ?>" <?php echo $isChecked; ?> /></td>
				<td><?php echo $item['id'] ?></td>
				<td><?php echo $item['seq'] ? $item['seq'] : ""; ?></td>
				<td><?php echo $title_text; ?></td>
				<td><?php echo isset($trigger_List[$item['trigger_type']]) ? $trigger_List[$item['trigger_type']] : ""; ?></td>
				
				<?php if($trigger_type == "time") { ?>
				<td width="150"><?php echo $timeTitle ?></td>
				<?php } ?>
				
				<?php if($trigger_type == "event") { ?>
				<td><?php echo $item['event_trigger'] ?></td>
				<?php } ?>

				<td><?php echo $item['time_delay'] ?></td>
				
				<td width="150" class="actionContainer">
					<?php
						$editUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/add_configuration.php", array('action_mode' => 'update', 'config_id' => $item["id"]));
					?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="onEdit(event, '<?php echo $editUrl; ?>')"><span>Edit</span></a>
					<?php getDeleteButton($item); ?>

					<?php if(!empty($eId) && !empty($item['id'])) { ?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="prepareConfigPopup('<?php echo $eId ?>', '<?php echo $item['id'] ?>')"><span>Prepare/Action</span></a>
					<?php } ?>
				</td>
			</tr>
		<?php } else { ?>
			<tr class="selectRow <?php echo $selectedClass; ?>" data-id="<?php echo $item['id'] ?>">
				<td><input type="checkbox" class="selectedCheck" name="selectedItem[]" value="<?php echo $item['id'] ?>" <?php echo $isChecked; ?> /></td>
				<td><?php echo $item['id'] ?></td>
				<td><?php echo isset($communication_List[$item['communication_type']]) ? $communication_List[$item['communication_type']] : ""; ?></td>
				<td width="200"><div><?php echo $templateTitle ?></div></td>
				
				<?php if($trigger_type == "time") { ?>
				<td width="300"><div><?php echo $item['data_set'] ?></div></td>
				<?php } ?>

				<td><?php echo isset($trigger_List[$item['trigger_type']]) ? $trigger_List[$item['trigger_type']] : ""; ?></td>
				
				<?php if($trigger_type == "time") { ?>
				<td width="150"><?php echo $timeTitle ?></td>
				<?php } ?>
				
				<?php if($trigger_type == "event") { ?>
				<td><?php echo $item['event_trigger'] ?></td>
				<?php } ?>

				<td><?php echo $item['time_delay'] ?></td>
				
				<td width="150" class="actionContainer">
					<?php
						$editUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/add_configuration.php", array('action_mode' => 'update', 'config_id' => $item["id"]));
					?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="onEdit(event, '<?php echo $editUrl; ?>')"><span>Edit</span></a>
					<?php getDeleteButton($item); ?>

					<?php if(!empty($eId) && !empty($item['id'])) { ?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="prepareConfigPopup('<?php echo $eId ?>', '<?php echo $item['id'] ?>')"><span>Prepare/Action</span></a>
					<?php } ?>
				</td>
			</tr>
		<?php
			}
		}
	} else {
		?>
		<tr>
			<td colspan="8">
				<div class="noRecordsContainer">
					<span>No Records Founds</span>
				</div>
			</td>
		</tr>
		<?php
	}
}

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Select Configuration'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme', 'datetime-picker']); ?>

    <script language="JavaScript">

	 function setConfigId(id) {
		if (opener.closed || ! opener.setConfigId)
		alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
		else
		opener.setConfigId(id);
		window.close();
		return false;
	 }

    </script>

    <style type="text/css">
    	.headtr {
    		white-space: pre;
    	}
    	.actionBtn {
    		font-size: 12px;
    	}
    	.selectRow {
    		cursor: pointer;
    	}
    	.noRecordsContainer {
    		text-align: center;
    		padding-top: 80px;
    		padding-bottom: 80px;
    		max-width: 100%!important;
    	}
    	.table {
    		font-size: 14px;
    	}
    	.table td > div {
	        max-width: 250px;
	        overflow: hidden;
	        text-overflow: ellipsis;
	        white-space: nowrap;
    	}
    	.containerList {
    		margin-left: 15px;
    		margin-right: 15px;
    	}
    	.actionContainer {
    		width: 1px;
    		white-space: nowrap;
    	}
    	.containerList {
    		min-height: 350px;
    	}
    	.selectedRow td {
    		/*background-color: #bbb;*/
    	}
    	.css_button {
    		margin-bottom: 5px;
    	}
    </style>
</head>
<body>
	<div class="containerList">
	<table class="table table-striped table-condensed" style="margin-top:15px;">
		<thead>
			<tr>
				<th></th>
				<?php if($action_type == "action_reminder") { ?>
					<th class="headtr"><b><?php xl('Event Name', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Api Config', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Sync Mode', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Trigger Type ', 'e'); ?></b></th>
					<?php if($trigger_type == "time") { ?>
					<th class="headtr"><b><?php xl('Time Trigger', 'e'); ?></b></th>
					<?php } ?>
					<th class="headtr"><b><?php xl('Time Delay', 'e'); ?></b></th>
					<th class="headtr"></th>
				<?php } else if($action_type == "hubspot_sync") { ?>
					<th class="headtr"><b><?php xl('Event Name', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Api Config', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Sync Mode', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Trigger Type ', 'e'); ?></b></th>
					<?php if($trigger_type == "time") { ?>
					<th class="headtr"><b><?php xl('Time Trigger', 'e'); ?></b></th>
					<?php } ?>
					<th class="headtr"><b><?php xl('Time Delay', 'e'); ?></b></th>
					<th class="headtr"></th>
				<?php } else if($action_type == "idempiere_webservice") { ?>
					<th class="headtr"><b><?php xl('Event Name', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Seq No', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Api Config', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Trigger Type ', 'e'); ?></b></th>
					<?php if($trigger_type == "time") { ?>
					<th class="headtr"><b><?php xl('Time Trigger', 'e'); ?></b></th>
					<?php } ?>
					<?php if($trigger_type == "event") { ?>
					<th class="headtr"><b><?php xl('Trigger', 'e'); ?></b></th>
					<?php } ?>
					<th class="headtr"><b><?php xl('Time Delay', 'e'); ?></b></th>
					<th class="headtr"></th>
				<?php } else { ?>
					<th class="headtr"><b><?php xl('Event Name', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Type', 'e'); ?></b></th>
					<th class="headtr"><b><?php xl('Template', 'e'); ?></b></th>
					
					<?php if($trigger_type == "time") { ?>
					<th class="headtr"><b><?php xl('Data Set', 'e'); ?></b></th>
					<?php } ?>

					<th class="headtr"><b><?php xl('Trigger Type ', 'e'); ?></b></th>
					<?php if($trigger_type == "time") { ?>
					<th class="headtr"><b><?php xl('Time Trigger', 'e'); ?></b></th>
					<?php } ?>
					<?php if($trigger_type == "event") { ?>
					<th class="headtr"><b><?php xl('Trigger', 'e'); ?></b></th>
					<?php } ?>
					<th class="headtr"><b><?php xl('Time Delay', 'e'); ?></b></th>
					<th class="headtr"></th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php writeConfigurationLine($configurationList); ?>
		</tbody>
	</table>
	</div>
	<div style="float: left; padding-left: 12px;">
		<?php
			if($action_type == "action_reminder") { 
				$addantoherUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/save_configuration.php", array('action_mode' => 'add'));
			} else {
				$addantoherUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/add_configuration.php", array('action_mode' => 'add'));
			}
			
			$showAllUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/time_configuration_list.php", array('result' => 'all'));
		?>
		<a href="javascript:;" class="css_button submitBtn btn btn-primary" onclick=""><span>Submit</span></a>
		<a href="javascript:;" class="css_button btn btn-primary" onclick="window.location='<?php echo $addantoherUrl; ?>';"><span>Add Another</span></a>
		<a href="javascript:;" class="css_button btn btn-primary" onclick="window.location='<?php echo $showAllUrl; ?>';"><span>Show All</span></a>
	</div>

	<script type="text/javascript">
		var selectedItem = [];

		$(document).ready(function(){
			$('.submitBtn').click(function(){
				$('input.selectedCheck:checkbox:checked').each(function () {
    				var sThisVal = $(this).val();
    				selectedItem.push(sThisVal);
				});
				
				var config_id = $(this).data('id');
				setConfigId(selectedItem);
			});

			$('.selectRow').click(function(){
				var config_id = $(this).data('id');
				//setConfigId(config_id);
			});
		});

		<?php if($trigger_type == "event") { ?>
		/*$(document).ready(function(){
		    $('input:checkbox').click(function() {
		        $('input:checkbox').not(this).prop('checked', false);
		    });
		});*/
		<?php } ?>

		function onDelete(event, url) {
			event.stopPropagation();
			window.location=url;
		}

		function onEdit(event, url) {
			event.stopPropagation();
			window.location=url;
		}

		function generateQueryParams(newUrl = '', paramList = '') {
			var url = window.location;
			const params = new URLSearchParams(url.search);

			if(paramList) {
				const params1 = new URLSearchParams(paramList);
				for(const entry of params1.entries()) {
  					params.set(entry[0], entry[1]);
				}
			}

			return newUrl + '?' + params.toString();
		}

		function prepareConfigPopup(event_id = '', config_id = '') {
			if(event_id != '' && config_id != '') {
				var url = encodeURI('<?php echo $GLOBALS['webroot']."/interface/batchcom/php/action_event_log.php?pid=". $pid ; ?>&event_id='+event_id+'&config_id='+config_id);
			  	let title = '<?php echo xlt('Prepare/Action'); ?>';
			  	dlgopen(url, 'prepareConfigPopup1', 900, 400, '', title);
		  	}
		}
	</script>
</body>
</html>