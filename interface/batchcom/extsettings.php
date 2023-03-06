<?php
/**
 * REminder Tool for selecting/communicating with subsets of patients
 */

//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
require_once("../globals.php");
require_once("$srcdir/registry.inc");
require_once("$srcdir/wmt-v3/wmt.globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\OemrAd\Reminder;

// Option lists
$notification_tpl_list = new wmt\Options('Notification_Template');

// If we are saving, then save.
if (isset($_POST['formaction']) && $_REQUEST['formaction'] == 'save') {
	$opt = $_POST['opt'];

	$isDeleted = Reminder::deleteActionEventConfiguration();

	if($isDeleted) {
		foreach ($opt as $key => $optItem) {
			$savedata = array();
			$savedata['id'] = isset($optItem['id']) ? $optItem['id'] : "";
			$savedata['seq'] = $key;
			$savedata['trigger_type'] = isset($optItem['trigger_type']) ? $optItem['trigger_type'] : "";
			$savedata['action_type'] = isset($optItem['action_type']) ? $optItem['action_type'] : "";
			$savedata['configuration_id'] = isset($optItem['configuration_id']) ? $optItem['configuration_id'] : "";
			$savedata['active'] = isset($optItem['active']) ? $optItem['active'] : "0";

			$isConfigurationExist = Reminder::isActionEventConfigurationExist($savedata['id']);

			if($isConfigurationExist === false) {
				//Insert Data
				Reminder::saveActionEventConfiguration($savedata);
			}
		}
	}
} else if(isset($_REQUEST['formaction']) && isset($_REQUEST['id']) && $_REQUEST['formaction'] == 'inactive') {
	$actionId = $_REQUEST['id'];
	Reminder::updateActionEventConfiguration($actionId, array("active" => "1"));
} else if(isset($_REQUEST['formaction']) && isset($_REQUEST['id']) && $_REQUEST['formaction'] == 'active') {
	$actionId = $_REQUEST['id'];
	Reminder::updateActionEventConfiguration($actionId, array("active" => "0"));
} else if(isset($_REQUEST['formaction']) && isset($_REQUEST['id']) && $_REQUEST['formaction'] == 'delete') {
	$actionId = $_REQUEST['id'];
	Reminder::deleteActionEventConfiguration($actionId);
}

//Fetch configuration
$configurationList = Reminder::getActionEventConfiguration();

function writeOptionLine(
	$id = '',
	$trigger_type = '',
	$action_type = '',
	$configuration_id = '',
	$active = ''
) {

	global $opt_line_no, $notification_tpl_list;
	++$opt_line_no;

	$trigger_type_List = array(
		'' => 'Select',
		'time' => 'Time',
		'event' => 'Trigger'
	);

	$action_type_List = array(
		'' => 'Select',
		'messaging' => 'Messaging',
		'internal_messaging' => 'Internal Messaging',
		'api' => 'API',
		'idempiere_webservice' => 'Idempiere Webservice',
		'hubspot_sync' => 'Hubspot Sync',
		'action_reminder' => 'Action/Reminder'
	);

	?>
	<tr>
		<td width="280">
			<input type="text" class="form-control" name="opt[<?php echo $opt_line_no; ?>][id]" value="<?php echo htmlspecialchars($id, ENT_QUOTES); ?>" class="optin" />
		</td>
		<td>
			<select id="<?php echo "trigger_type_".$opt_line_no; ?>" data-id="<?php echo $opt_line_no; ?>" name="opt[<?php echo $opt_line_no; ?>][trigger_type]" class="optin trigger_type form-control" style="width: 120px;">
				<?php
				foreach ($trigger_type_List as $key => $desc) {
		            ?>
		            <option value="<?php echo $key ?>" <?php echo ($key == $trigger_type) ? "selected" : ""; ?> ><?php echo htmlspecialchars($desc) ?></option>
		            <?php
		        }
				?>
			</select>
		</td>
		<td>
			<select id="<?php echo "action_type_".$opt_line_no; ?>" name="opt[<?php echo $opt_line_no; ?>][action_type]" class="optin form-control" style="width: 220px;">
				<?php
				foreach ($action_type_List as $key => $desc) {
		            ?>
		            <option value="<?php echo $key ?>" <?php echo ($key == $action_type) ? "selected" : ""; ?> ><?php echo htmlspecialchars($desc) ?></option>
		            <?php
		        }
				?>
			</select>
		</td>
		<td style="width: 80px!important;">
			<button type="button" class="btn btn-primary" onClick="time_configuration_list('<?php echo htmlspecialchars($configuration_id, ENT_QUOTES); ?>','<?php echo "configuration_id_".$opt_line_no; ?>', '<?php echo $opt_line_no; ?>', '<?php echo $id; ?>')">Edit</button>
			<!-- <input type="hidden" id="<?php //echo "configuration_id_".$opt_line_no; ?>" name="opt[<?php //echo $opt_line_no; ?>][configuration_id]" value="<?php //echo htmlspecialchars($configuration_id, ENT_QUOTES); ?>" class="optin" style="width: 100%;" onClick="time_configuration_list('<?php //echo htmlspecialchars($configuration_id, ENT_QUOTES); ?>','<?php //echo "configuration_id_".$opt_line_no; ?>', '<?php //echo $opt_line_no; ?>')" readonly /> -->
			<textarea class="optin" id="<?php echo "configuration_id_".$opt_line_no; ?>" name="opt[<?php echo $opt_line_no; ?>][configuration_id]" style="display: none;" ><?php echo htmlspecialchars($configuration_id, ENT_QUOTES); ?></textarea>
			<input type="hidden" name="opt[<?php echo $opt_line_no; ?>][active]" value="<?php echo $active; ?>" />
		</td>
		<td width="400" valign="middle">
			<?php if(isset($id) && !empty($id)) { ?>
			<?php 
				if($active == "0") {
					$inactiveUrl = $GLOBALS['webroot']."/interface/batchcom/extsettings.php?formaction=inactive&id=".$id;
					$btnTitle = "Inactive";
				} else if($active == "1") {
					$inactiveUrl = $GLOBALS['webroot']."/interface/batchcom/extsettings.php?formaction=active&id=".$id;
					$btnTitle = "Active";
				} 
			?>
			<button type="button" class="btn btn-primary" onClick="onAction('<?php echo $inactiveUrl; ?>')"><?php echo $btnTitle; ?></button>

			<?php $deleteUrl = $GLOBALS['webroot']."/interface/batchcom/extsettings.php?formaction=delete&id=".$id; ?>
			<button type="button" class="btn btn-primary" onClick="onAction('<?php echo $deleteUrl; ?>')">Delete</button>
			
			<button type="button" class="btn btn-primary" onClick="prepareConfigPopup('<?php echo htmlspecialchars($id, ENT_QUOTES); ?>')">Prepare/Action</button>

			<?php } ?>
			<?php if($active == "0") { ?>
				<span class="statuslabel">Active</span>
			<?php } else if($active == "1") { ?>
				<span class="statuslabel">Inactive</span>
			<?php } ?>
		</td>
	</tr>
	<?php
}

?>
<html>
<head>
<title><?php echo xlt('Action Events'); ?></title>
<?php Header::setupHeader(['datetime-picker']); ?>

<style type="text/css">
	.statuslabel {
		font-size: 12px;
		margin-left: 10px;
		padding-top: 8px;
	    height: 30px;
	    display: inline-block;
	    vertical-align: middle;
	}
</style>

</head>
<body class="body_top">
<header class="row">
    <h1 class="col-md-12 text-left">
        <?php echo xlt('Action Events')?>
    </h1>
</header>
<div>
	<form method='post' name='theform' id='theform' action='extsettings.php'>
		<table class="table" style="margin-top:15px; width: 100%!important; max-width: 1200px;">
			<thead class="table-light">
    			<tr>
    				<th><?php xl('Event Name', 'e'); ?></th>
    				<th><?php xl('Trigger Type', 'e'); ?></th>
    				<th><?php xl('Action Type', 'e'); ?></th>
    				<th style="width: 80px!important;"><?php xl('Configuration', 'e'); ?></th>
    				<th></th>
    			</tr>
    		</thead>
    		<tbody>
    			<?php
    			$opt_line_no = 0;

    			foreach ($configurationList as $key => $item) {
    				$itemid = isset($item['id']) ? $item['id'] : "";
					$itemtrigger_type = isset($item['trigger_type']) ? $item['trigger_type'] : "";
					$itemaction_type = isset($item['action_type']) ? $item['action_type'] : "";
					$itemconfiguration_id = isset($item['configuration_id']) ? $item['configuration_id'] : "";
					$itemactive = isset($item['active']) ? $item['active'] : "";

					writeOptionLine($itemid, $itemtrigger_type, $itemaction_type, $itemconfiguration_id, $itemactive);
    			}

    			for ($i = 0; $i < 3; ++$i) {
	                writeOptionLine('', '', '', '', '');
	            }
    			?>
    		</tbody>
    	</table>
    	<input type="hidden" name="formaction" value="save">
		<p>
		    <button type="submit" name="form_save" id="form_save" class="btn btn-primary btn-save">Save</button>
		</p>
    </form>
</div>

<script type="text/javascript">	
	var ele_id = '';

	// This invokes the select-time-configuration popup.
	function time_configuration_list(cValue = '', id = '', inx = 0, event_id = '') {
		var config_val = $('#configuration_id_'+inx).val();
		var timeData = $('#'+id).text();
		var triggerType = $('#trigger_type_'+inx).val();
		var actionType = $('#action_type_'+inx).val();
		ele_id = id;

		if(triggerType == "time") {
			timeConfiguration(config_val, triggerType, actionType, event_id);
		} else if(triggerType == "event") {
			eventConfiguration(config_val, triggerType, actionType, event_id);
		}
	}

	function timeConfiguration(cValue = '', triggerType, actionType, event_id) {
		var url = encodeURI('<?php echo $GLOBALS['webroot']."/interface/batchcom/php/time_configuration_list.php?pid=". $pid ."&trigger_type="; ?>'+triggerType+'&action_type='+actionType+'&selectedItem='+cValue+'&eId='+event_id);
	  	let title = '<?php echo xlt('Time Configuration'); ?>';
	  	dlgopen(url, 'timeConfigurationSelection', 1200, 400, '', title);
	}

	function eventConfiguration(cValue = '', triggerType, actionType, event_id) {
		var url = encodeURI('<?php echo $GLOBALS['webroot']."/interface/batchcom/php/time_configuration_list.php?pid=". $pid ."&trigger_type="; ?>'+triggerType+'&action_type='+actionType+'&selectedItem='+cValue+'&eId='+event_id);
	  	let title = '<?php echo xlt('Trigger Configuration'); ?>';
	  	dlgopen(url, 'eventConfigurationSelection', 1200, 400, '', title);
	}

	function prepareConfigPopup(event_id = '') {
		if(event_id != '') {
			var url = encodeURI('<?php echo $GLOBALS['webroot']."/interface/batchcom/php/action_event_log.php?pid=". $pid ; ?>&event_id='+event_id);
		  	let title = '<?php echo xlt('Prepare/Action'); ?>';
		  	dlgopen(url, 'prepareConfigPopup', 900, 400, '', title);
	  	}
	}

	// This is for callback by the config id popup.
	function setConfigId(id) {
		$('#'+ele_id).val(JSON.stringify(id));
		ele_id = "";
	}

	function onAction(url) {
		window.location=url;
	}

	$(document).ready(function(){
		$('.trigger_type').change(function(){
			var ordId = $(this).data('id');
			var eleid = "configuration_id_"+ordId;
			$('#'+eleid).val('');
		});
	});
</script>

</body>
</html>