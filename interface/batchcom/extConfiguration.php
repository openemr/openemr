<?php
/**
 * REminder Tool for selecting/communicating with subsets of patients
 */

//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
require_once("../globals.php");
require_once("$srcdir/registry.inc");
require_once("batchcom.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\OemrAd\Reminder;

// Option lists
$notification_tpl_list = new wmt\Options('Notification_Template');

// Option lists
function getEmailTpl() {
	ob_start();
	$tpl_list = new wmt\Options('Reminder_Email_Messages');
	$tpl_list->showOptions('free_text');
	$msgtpl = ob_get_clean();
	$htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
	return $htmlStr;
}

function getSmsTpl() {
	ob_start();
	$tpl_list = new wmt\Options('Reminder_SMS_Messages');
	$tpl_list->showOptions('free_text');
	$msgtpl = ob_get_clean();
	$htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
	return $htmlStr;
}

function getFaxTpl() {
	ob_start();
	$tpl_list = new wmt\Options('Reminder_Fax_Messages');
	$tpl_list->showOptions('free_text');
	$msgtpl = ob_get_clean();
	$htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
	return $htmlStr;
}

function getPostalTpl() {
	ob_start();
	$tpl_list = new wmt\Options('Reminder_Postal_Letters');
	$tpl_list->showOptions('free_text');
	$msgtpl = ob_get_clean();
	$htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
	return $htmlStr;
}

function getInternalMsgTpl() {
	ob_start();
	$tpl_list = new wmt\Options('Reminder_Internal_Messages');
	$tpl_list->showOptions('free_text');
	$msgtpl = ob_get_clean();
	$htmlStr = preg_replace( "/\r|\n/", "", $msgtpl );
	return $htmlStr;
}

function getDefaultTpl() {
	$htmlStr = "<option value=''>Select Template</option>";
	return $htmlStr;
}

// If we are saving, then save.
if (isset($_POST['formaction']) && $_POST['formaction'] == 'save') {
	$opt = $_POST['opt'];

	$isDeleted = Reminder::deleteNotificationConfiguration();

	if($isDeleted) {
		foreach ($opt as $key => $optItem) {
			$savedata = array();
			$savedata['id'] = isset($optItem['id']) ? $optItem['id'] : "";
			$savedata['seq'] = $key;
			$savedata['communication_type'] = isset($optItem['communication_type']) ? $optItem['communication_type'] : "";
			$savedata['notification_template'] = isset($optItem['notification_template']) ? $optItem['notification_template'] : "";
			$savedata['data_set'] = isset($optItem['data_set']) ? $optItem['data_set'] : "";
			$savedata['trigger_type'] = isset($optItem['trigger_type']) ? $optItem['trigger_type'] : "";
			$savedata['time_trigger_data'] = isset($optItem['time_trigger_data']) ? $optItem['time_trigger_data'] : "";
			$savedata['event_trigger'] = isset($optItem['event_trigger']) ? $optItem['event_trigger'] : "";

			$isConfigurationExist = Reminder::isConfigurationExist($savedata['id']);

			if($isConfigurationExist === false) {
				//Insert Data
				Reminder::saveNotificationConfiguration($savedata);
			}
		}
	}
}

//Fetch configuration
$configurationList = Reminder::getNotificationConfiguration();

function writeOptionLine(
	$id = '',
	$communication_type = '',
	$notification_template = '',
	$data_set = '',
	$trigger_type = '',
	$time_trigger = '',
	$event_trigger = ''
) {
	global $opt_line_no, $notification_tpl_list;
	++$opt_line_no;

	$timeData = isset($time_trigger) ? json_decode($time_trigger, true) : array();
	$timeTitle = isset($timeData['title']) ? $timeData['title'] : "";

	$communication_type_List = array(
		'' => 'Select',
		'email' => 'Email',
		'sms' => 'SMS',
		'fax' => 'Fax',
		'postalmethod' => 'Postal Method',
		'internalmessage' => 'Internal Message'
	);

	$notification_type_List = array(
		'' => 'Select',
		'notification' => 'Notification',
		'reminder' => 'Reminder'
	);

	$addditional_info_List = array(
		'' => 'Select',
		'yes' => 'Yes',
		'no' => 'No'
	);

	$trigger_type_List = array(
		'' => 'Select',
		'time' => 'Time',
		'event' => 'Event'
	);

	?>
	<tr>
		<td width="150">
			<input type="text" name="opt[<?php echo $opt_line_no; ?>][id]" value="<?php echo htmlspecialchars($id, ENT_QUOTES); ?>" class="optin" style="width: 100%;" />
		</td>
		<td width="100">
			<select id="communication_type_<?php echo $opt_line_no; ?>" name="opt[<?php echo $opt_line_no; ?>][communication_type]" class="optin communication_type" data-index="<?php echo $opt_line_no; ?>" style="min-width: 100%; height: 30px; max-width: 120px; margin: 3px;">
				<?php
				foreach ($communication_type_List as $key => $desc) {
		            ?>
		            <option value="<?php echo $key ?>" <?php echo ($key == $communication_type) ? "selected" : ""; ?> ><?php echo htmlspecialchars($desc) ?></option>
		            <?php
		        }
				?>
			</select>
		</td>
		<td width="80">
			<select name="opt[<?php echo $opt_line_no; ?>][notification_template]" id="notification_template_<?php echo $opt_line_no; ?>" class="optin notification_template" style="min-width: 100%; max-width: 150px; height: 30px; margin: 3px;">
				<?php echo getDefaultTpl() ?>
			</select>
			<script type="text/javascript">
				$(document).ready(function(){
					setOptionVal('<?php echo $communication_type; ?>', '<?php echo $opt_line_no; ?>', '<?php echo $notification_template; ?>');
				});
			</script>
		</td>
		<td width="250">
			<textarea name="opt[<?php echo $opt_line_no; ?>][data_set]" style="min-width: 100%; height: 30px; min-height: 30px; margin: 3px; resize: vertical;"><?php echo $data_set; ?></textarea>
		</td>
		<td>
			<select name="opt[<?php echo $opt_line_no; ?>][trigger_type]" class="optin" style=" max-width: 100px; width: 100%; height: 30px; margin: 3px;">
				<?php
				foreach ($trigger_type_List as $key => $desc) {
		            ?>
		            <option value="<?php echo $key ?>" <?php echo ($key == $trigger_type) ? "selected" : ""; ?> ><?php echo htmlspecialchars($desc) ?></option>
		            <?php
		        }
				?>
			</select>
		</td>
		<td width="30">
			<input type="text" id="<?php echo "time_trigger_".$opt_line_no; ?>" name="opt[<?php echo $opt_line_no; ?>][time_trigger]" value="<?php echo htmlspecialchars($timeTitle, ENT_QUOTES); ?>" class="optin time_trigger" style="width: 100%;" onClick="select_time('<?php echo "time_trigger_".$opt_line_no; ?>')" readonly />
			<textarea id="<?php echo "time_trigger_".$opt_line_no."_raw"; ?>" name="opt[<?php echo $opt_line_no; ?>][time_trigger_data]" style="display: none;"><?php echo $time_trigger; ?></textarea>
		</td>
		<td width="30">
			<input type="text" name="opt[<?php echo $opt_line_no; ?>][event_trigger]" value="<?php echo htmlspecialchars($event_trigger, ENT_QUOTES); ?>" class="optin" style="width: 100%;" />
		</td>
	</tr>
	<?php
}

?>
<html>
<head>
<title><?php echo xlt('Action Events'); ?></title>
<?php Header::setupHeader(['datetime-picker']); ?>
</head>
<body class="body_top container">
<header class="row">
	<nav>
	    <ul class="nav nav-tabs nav-justified">
	        <li role="presentation" title="<?php echo xla('Action Events'); ?>">
	            <a href="<?php echo $GLOBALS['rootdir']; ?>/batchcom/extsettings.php">
	                <?php echo xlt('Action Events'); ?>
	            </a>
	        </li>
	        <li role="presentation" title="<?php echo xla('Configurations'); ?>">
	            <a href="<?php echo $GLOBALS['rootdir']; ?>/batchcom/extConfiguration.php">
	                <?php echo xlt('Configurations'); ?>
	            </a>
	        </li>
	    </ul>
	</nav>
    <h1 class="col-md-12 text-left">
        <?php echo xlt('Action Events')?>
    </h1>
</header>
<main>
	<form method='post' name='theform' id='theform' action='extsettings.php'>
		<table class="table table-striped table-condensed" style="margin-top:15px;">
			<thead>
    			<tr>
    				<th><b><?php xl('ID', 'e'); ?></b></th>
    				<th><b><?php xl('Type', 'e'); ?></b></th>
    				<th><b><?php xl('Template', 'e'); ?></b></th>
    				<th><b><?php xl('Data Set', 'e'); ?></b></th>
    				<th><b><?php xl('Trigger Type ', 'e'); ?></b></th>
    				<th><b><?php xl('Time Trigger', 'e'); ?></b></th>
    				<th><b><?php xl('Event Trigger', 'e'); ?></b></th>
    			</tr>
    		</thead>
    		<tbody>
    			<?php
    			$opt_line_no = 0;

    			foreach ($configurationList as $key => $item) {
    				$itemid = isset($item['id']) ? $item['id'] : "";
					$itemcommunication_type = isset($item['communication_type']) ? $item['communication_type'] : "";
					$itemnotification_template = isset($item['notification_template']) ? $item['notification_template'] : "";
					$itemdata_set = isset($item['data_set']) ? $item['data_set'] : "";
					$itemtrigger_type = isset($item['trigger_type']) ? $item['trigger_type'] : "";
					$itemtime_trigger = isset($item['time_trigger_data']) ? $item['time_trigger_data'] : "";
					$itemevent_trigger = isset($item['event_trigger']) ? $item['event_trigger'] : "";

					writeOptionLine($itemid, $itemcommunication_type, $itemnotification_template, $itemdata_set, $itemtrigger_type, $itemtime_trigger, $itemevent_trigger);
    			}

    			for ($i = 0; $i < 3; ++$i) {
	                writeOptionLine('', '', '', '', '', '', '');
	            }
    			?>
    		</tbody>
		</table>
		<input type="hidden" name="formaction" value="save">
		<p>
		    <button type="submit" name="form_save" id="form_save" class="btn btn-default btn-save">Save</button>
		</p>
	</form>
</main>

<script type="text/javascript">	
	// This invokes the select-time popup.
	function select_time(id = '') {
		var timeData = $('#'+id+"_raw").text();

		var url = '<?php echo $GLOBALS['webroot']."/interface/batchcom/php/select_time.php?pid=". $pid; ?>&id='+id+'&data_set='+encodeURI(timeData);
	  	let title = '<?php echo xlt('Time'); ?>';
	  	dlgopen(url, 'timeSelection', 900, 400, '', title);
	}

	// This is for callback by the time popup.
	function setTime(id, data) {
		$('#'+id).val(data['title']);
		$('#'+id+'_raw').text(JSON.stringify(data));
	}

	$(document).ready(function(){
		$('.communication_type').change(function(){
			var selectedVal = $(this).val();
			var eleIndex = $(this).data('index');
			var id = 'notification_template_'+eleIndex;
			setOptionVal(selectedVal, eleIndex);
		});
	});

	//Set Option
	function setOptions(val, id) {
		if(val == "email") {
			$('#'+id).html("<?php echo getEmailTpl(); ?>");
		} else if(val == "sms") {
			$('#'+id).html("<?php echo getSmsTpl(); ?>");
		} else if(val == "fax") {
			$('#'+id).html("<?php echo getFaxTpl(); ?>");
		} else if(val == "postalmethod") {
			$('#'+id).html("<?php echo getPostalTpl(); ?>");
		} else if(val == "internalmessage") {
			$('#'+id).html("<?php echo getInternalMsgTpl(); ?>");
		} else {
			$('#'+id).html("<?php echo getDefaultTpl(); ?>");
		}
	}

	//Init
	function setOptionVal(val, index, selectVal = '') {
		var id = 'notification_template_'+index;
		setOptions(val, id);
		if(selectVal != "") {
			$('#'+id).val(selectVal);
		}
	}
</script>

</body>
</html>