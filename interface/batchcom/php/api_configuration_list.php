<?php

require_once("../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\Reminder;
use OpenEMR\OemrAd\IdempiereWebservice;

$pid = strip_tags($_REQUEST['pid']);
$config_id = $_REQUEST['config_id'];
$api_type = strip_tags($_REQUEST['api_type']);
$action_mode = isset($_REQUEST['action_mode']) ? $_REQUEST['action_mode'] : "";
$selectedItem = strip_tags($_REQUEST['selectedItem']);
$selectedItem = (isJson($selectedItem) === true) ? json_decode($selectedItem, true) : array($selectedItem);
$showAll = isset($_REQUEST['result']) ? $_REQUEST['result'] : "";

// Delete Action
if (isset($action_mode) && !empty($config_id)  && $action_mode == 'delete') {
	IdempiereWebservice::deleteApiEventConfiguration($config_id);
}

//Fetch configuration
$configurationList = IdempiereWebservice::getApiEventConfiguration('', $api_type);
$totalCheckConfig = totalCheckConfiguration($configurationList);

$api_configuration_type_List = IdempiereWebservice::getApiConfigurationTypeList();

function isJson($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

function generateQtrStr($paramStr = array()) {
	$current_qtrStr = $_SERVER['QUERY_STRING'];
	parse_str($current_qtrStr, $tmp_qtrStr_array);

	$qtrStr_array = array();
	$param_list = array('pid', 'selectedItem', 'api_type');

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

function getConfigurationDerails($item) {
	$api_configuration_type = $item['api_configuration_type'] ? $item['api_configuration_type'] : "";

	if($api_configuration_type == "idempiere_webservice") {
		?>
		<table class="detailsTable">
			<tr>
				<td width="100"><b><?php xl('Service Url:', 'e'); ?></b></td>
				<td colspan="3"><?php echo $item['service_url'] ? $item['service_url'] : "" ?></td>
			</tr>
			<tr>
				<td width="100"><b><?php xl('User:', 'e'); ?></b></td>
				<td width="150"><?php echo $item['user'] ? $item['user'] : "" ?></td>
				<td width="100"><b><?php xl('Password:', 'e'); ?></b></td>
				<td width="150"><?php echo $item['password'] ? $item['password'] : "" ?></td>
			</tr>
			<tr>
				<td width="100"><b><?php xl('Role:', 'e'); ?></b></td>
				<td width="150"><?php echo $item['role'] ? $item['role'] : "" ?></td>
				<td width="100"><b><?php xl('Organization:', 'e'); ?></b></td>
				<td width="150"><?php echo $item['organization'] ? $item['organization'] : "" ?></td>
			</tr>
			<tr>
				<td width="100"><b><?php xl('Client:', 'e'); ?></b></td>
				<td width="150"><?php echo $item['client'] ? $item['client'] : "" ?></td>
				<td width="100"><b><?php xl('Warehouse:', 'e'); ?></b></td>
				<td width="150"><?php echo $item['warehouse'] ? $item['warehouse'] : "" ?></td>
			</tr>	
		</table>
		<?php
	} else if($api_configuration_type == "hubspot_sync") {
		?>
		<table class="detailsTable">
			<tr>
				<td width="50"><b><?php xl('Token:', 'e'); ?></b></td>
				<td colspan="3"><?php echo $item['token'] ? $item['token'] : "" ?></td>
			</tr>
		</table>
		<?php
	}
}

function getDeleteButton($data) {
	if(!empty($data['id'])) {
		$isAlreadyInUse = Reminder::isAlreadyInUse($data['id']);
		if($isAlreadyInUse === false) {
			$deleteUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/api_configuration_list.php", array('action_mode' => 'delete', 'config_id' => $data['id']));
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
	global $selectedItem, $showAll, $totalCheckConfig, $api_configuration_type_List;

	if(!empty($data)) {
		foreach ($data as $key => $item) {
			$selectedClass = (in_array($item['id'], $selectedItem) == true) ? 'selectedRow' : '';
			$isChecked = (in_array($item['id'], $selectedItem) == true) ? "checked" : "";
			$api_configuration_type = ($item['api_configuration_type'] && $api_configuration_type_List[$item['api_configuration_type']]) ? $api_configuration_type_List[$item['api_configuration_type']] : "";
			$title_text = IdempiereWebservice::getApiConfigTitle('', $item);

			if($totalCheckConfig != 0 && $showAll != "all" && $isChecked != "checked") {
				continue;
			}
		?>
			<tr class="selectRow <?php echo $selectedClass; ?>" data-id="<?php echo $item['id'] ?>">
				<td width="50"><input type="checkbox" class="selectedCheck" name="selectedItem[]" value="<?php echo $item['id'] ?>" data-title="<?php echo $title_text; ?>" <?php echo $isChecked; ?> /></td>
				<td width="250"><?php echo $api_configuration_type; ?></td>
				<td>
					<?php getConfigurationDerails($item); ?>
				</td>
				<td width="150" class="actionContainer">
					<?php
						$editUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/add_api_configuration.php", array('action_mode' => 'update', 'config_id' => $item["id"]));
					?>
					<a href="javascript:;" class="css_button actionBtn btn btn-primary" onclick="onEdit(event, '<?php echo $editUrl; ?>')"><span>Edit</span></a>
					<?php getDeleteButton($item); ?>
				</td>
			</tr>
		<?php
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

	 function setApiConfigId(id) {
		if (opener.closed || ! opener.setApiConfigId)
		alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
		else
		opener.setApiConfigId(id);
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
    	.detailsTable {
    		font-size: 13px;
    	}
    	.detailsTable tr > td {
    		word-break: break-all;
    	}
    </style>
</head>
<body>
	<div class="containerList">
	<table class="table table-striped table-condensed" style="margin-top:15px;">
		<thead>
			<tr>
				<th></th>
				<th class="headtr"><b><?php xl('Api Configuration Type', 'e'); ?></b></th>
				<th class="headtr"></th>
				<th class="headtr"></th>
			</tr>
		</thead>
		<tbody>
			<?php writeConfigurationLine($configurationList); ?>
		</tbody>
	</table>
	</div>
	<div style="float: left; padding-left: 12px;">
		<?php
			$addantoherUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/add_api_configuration.php", array('action_mode' => 'add'));
			$showAllUrl = generateFullUrl($GLOBALS['webroot']."/interface/batchcom/php/api_configuration_list.php", array('result' => 'all'));
		?>
		<a href="javascript:;" class="css_button submitBtn btn btn-primary" onclick=""><span>Submit</span></a>
		<a href="javascript:;" class="css_button btn btn-primary" onclick="window.location='<?php echo $addantoherUrl; ?>';"><span>Add Another</span></a>
		<a href="javascript:;" class="css_button btn btn-primary" onclick="window.location='<?php echo $showAllUrl; ?>';"><span>Show All</span></a>
	</div>

	<script type="text/javascript">
		var selectedItem = [];

		$(document).ready(function(){
			$('input.selectedCheck').click(function(){
				$('input.selectedCheck').not(this).prop('checked', false); 
			});

			$('.submitBtn').click(function(){
				$('input.selectedCheck:checkbox:checked').each(function () {
    				var sThisVal = $(this).val();
    				var sThisTitleVal = $(this).data('title');
    				selectedItem.push({
    					value : sThisVal,
    					title : sThisTitleVal
    				});
				});
				
				var config_id = $(this).data('id');
				setApiConfigId(selectedItem);
			});

			$('.selectRow').click(function(){
				var config_id = $(this).data('id');
				//setApiConfigId(config_id);
			});
		});

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
	</script>
</body>
</html>