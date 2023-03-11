<?php

use OpenEMR\Core\Header;

require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/calendar.inc");
require_once("{$GLOBALS['srcdir']}/pnotes.inc");
require_once("{$GLOBALS['srcdir']}/forms.inc");
require_once("{$GLOBALS['srcdir']}/translation.inc.php");
require_once("{$GLOBALS['srcdir']}/formatting.inc.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/rto.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtpatient.class.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/rto.class.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmt.msg.inc");

use OpenEMR\Common\Acl\AclMain;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$rto_id = isset($_REQUEST['rto_id']) ? $_REQUEST['rto_id'] : '';

function fetchRtoLogs($rto_id, $pid = '') {
	$typeList = array(
		'INTERNAL_NOTE' => 'Internal Note',
		'EMAIL' => 'Email',
		'FAX' => 'Fax'
	); 

	if(!empty($rto_id)) {
		$result = sqlStatement("SELECT rl.*, u.username as user_name, u.fname as u_fname, u.mname as u_mname, u.lname as u_lname, pd.fname AS patient_data_fname, pd.lname AS patient_data_lname FROM rto_action_logs rl LEFT JOIN users As u ON u.id = rl.created_by LEFT JOIN patient_data as pd ON rl.pid = pd.pid WHERE rto_id = ? ", array($rto_id));

		$data = array();
		while ($row = sqlFetchArray($result)) {

			$patientName = htmlspecialchars($row['patient_data_fname'] . ' ' . $row['patient_data_lname']);

			$name = $row['u_lname'];
            if ($row['u_fname']) {
                $name .= ", " . $row['u_fname'];
            }

            $sentToTitle = '';

            if($row['type'] == "INTERNAL_NOTE") {
            	$sentToTitle = $patientName;
            } else {
            	$sentToTitle = $row['sent_to'];
            }

            $row['user_fullname'] = $name;
            $row['patient_name'] = $patientName;
            $row['sent_to_title'] = $sentToTitle;
            $row['type_title'] = isset($typeList[$row['type']]) ? $typeList[$row['type']] : "";

			$data[] = $row;
		}
		return $data;
	}

	return false;
}

function getMessageData($msgId) {
	$result = sqlQuery("SELECT * FROM `message_log` WHERE id = ? LIMIT 1", array($msgId));
	return $result;
}

function getInternalMsgData($noteId) {
	$result = sqlQuery("SELECT * FROM `pnotes` WHERE id = ? LIMIT 1", array($noteId));
	return $result;
}

$logsData = fetchRtoLogs($rto_id, $pid);
$alertLogsData = fetchAlertLogs($rto_id);

$fieldList = array(
	'rto_action'  => 'Order',
	'rto_status'  => 'Status',
	'rto_ordered_by' => 'Ordered By',
	'rto_resp_user' => 'Assigned To',
	'rto_notes' => 'Notes'
);

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Logs'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
		<?php Header::setupHeader(['opener', 'dialog', 'jquery']); ?>
	</script>
	<style type="text/css">
		.alert_log_table {
			width: 100%;
		}
		.alert_log_table_container table tr td,
		.alert_log_table_container table tr th {
			padding: 5px;
		}

		.alert_log_table_container table tr:nth-child(even) {
			background: #EEEEEE !important;
		}

		.content {
			word-break: break-word;
		}

		.alert_log_table_container{
			padding: 10px;
		}

		.alert_log_table2 {
			margin-top: 20px;
		}
	</style>
</head>
<body>
<div class="alert_log_table_container">
	<table class="alert_log_table text">
		<tr class="showborder_head">
			<th width="25" align="center">Sr.</th>
			<th>Type</th>
			<th>Sent To</th>
			<th>Created By</th>
			<th>Create Date & Time</th>
			<th>Content / Link</th>
		</tr>

		<?php if(!empty($logsData )) {
		$cnt = 1;
		foreach ($logsData as $lk => $logItem) {
			if(!empty($logItem['foreign_id'])) {
				if($logItem['type'] == "INTERNAL_NOTE") {
					$noteData = getInternalMsgData($logItem['foreign_id']);
					$linkTitle = '';
					$linkStr = '';

					if(!empty($noteData)) {
						if(!empty($logItem['operation'])) {
							$linkTitle .= $logItem['operation'] . " - ";
						}

						if(!empty($noteData['user'])) {
							$assigned = isset($noteData['assigned_to']) ? $noteData['assigned_to'] : "";
							$linkTitle .= " (". $noteData['user'] . " to ".$assigned.")";
						}

						if(!empty($noteData['message_status'])) {
							$linkTitle .= " Status: ".$noteData['message_status'];
						}

						$onclickFun = "selectInternalMsg('".$logItem['foreign_id']."')";
						$linkStr = '<a href="javascript:void(0);" onclick="'.$onclickFun.'">'.$linkTitle.'</a>';
					}
				} else if($logItem['type'] == "EMAIL" || $logItem['type'] == "FAX") {
					$msgData = getMessageData($logItem['foreign_id']);
					$rawData = json_decode($msgData['raw_data'], true);

					$linkTitle = '';
					$linkStr = '';

					if(!empty($msgData)) {
						if(!empty($logItem['operation'])) {
							$linkTitle .= $logItem['operation'] . " - ";
						}

						if($logItem['type'] == "EMAIL") {
							if(!empty($msgData['event'])) {
								$linkTitle .= $msgData['event'];
							}

							if(!empty($msgData['msg_to'])) {
								$linkTitle .= " (".$msgData['msg_to'].") ";
							}
						} else if($logItem['type'] == "FAX") {
							if(!empty($rawData['rec_name'])) {
								$linkTitle .= $rawData['rec_name'];
							}

							if(!empty($msgData['msg_to'])) {
								$linkTitle .= " (".$msgData['msg_to'].") ";
							}
						}

						$onclickFun = "selectMsg('".$logItem['type']."','".$logItem['foreign_id']."', '".$logItem['pid']."')";
						$linkStr = '<a href="javascript:void(0);" onclick="'.$onclickFun.'">'.$linkTitle.'</a>';
					}
				}
			}

			?>
			<tr>
				<td><?php echo $cnt; ?></td>
				<td><?php echo $logItem['type_title'] ?></td>
				<td><?php echo $logItem['sent_to_title'] ?></td>
				<td><?php echo $logItem['user_fullname'] ?></td>
				<td><?php echo $logItem['created_date'] ?></td>
				<td class="content"><?php echo $linkStr; ?></td>
			</tr>
			<?php
			$cnt++;
		}
		} else {
			?>
			<tr>
				<td colspan="6" align="center">No records</td>
			</tr>
			<?php
		}
		?>
	</table>

	<table class="alert_log_table text alert_log_table2">
		<tr class="showborder_head">
			<th width="25" align="center">Sr.</th>
			<th>Field</th>
			<th>New Value</th>
			<th>Old Value</th>
			<th>Username</th>
			<th>DateTime</th>
		</tr>

		<?php
			if(!empty($alertLogsData)) {
				$acnt = 1;
				foreach ($alertLogsData as $alk => $alogItem) {
					$fieldVal = isset($fieldList[$alogItem['field_id']]) ? $fieldList[$alogItem['field_id']] : $alogItem['field_id'];

					$nvalue = $alogItem['new_value'];
					$ovalue = $alogItem['old_value'];

					if($alogItem['field_id'] == "rto_ordered_by") {
						$nvalue = UserNameFromName($alogItem['new_value']);
						$ovalue = UserNameFromName($alogItem['old_value']);
					} else if($alogItem['field_id'] == "rto_status") {
						$nvalue = ListLook($alogItem['new_value'], 'RTO_Status');
						$ovalue = ListLook($alogItem['old_value'], 'RTO_Status');
					} else if($alogItem['field_id'] == "rto_action") {
						$nvalue = ListLook($alogItem['new_value'], 'RTO_Action');
						$ovalue = ListLook($alogItem['old_value'], 'RTO_Action');
					} else if($alogItem['field_id'] == "rto_resp_user") {
						$nvalue = MsgUserGroupDisplay($alogItem['new_value']);
						$ovalue = MsgUserGroupDisplay($alogItem['old_value']);
					}

					?>
					<tr>
						<td><?php echo $acnt; ?></td>
						<td><?php echo $fieldVal ?></td>
						<td><?php echo $nvalue ?></td>
						<td><?php echo $ovalue ?></td>
						<td><?php echo $alogItem['user_name'] ?></td>
						<td><?php echo $alogItem['date'] ?></td>
					</tr>
					<?php
					$acnt++;
				}
			} else {
				?>
				<tr>
					<td colspan="6" align="center">No records</td>
				</tr>
				<?php
			}
		?>
	</table>
</div>
<script type="text/javascript">
		function selectInternalMsg(noteId) {
			return selInternalMsg(noteId);
		}

		function selectMsg(type, id, pid) {
			return selMsg(type, id, pid);
		}

		function selInternalMsg(noteId) {
			if (opener.closed || ! opener.setInternalMsg)
			alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
			else
			opener.setInternalMsg(noteId);
			window.close();
			return false;
		}

		function selMsg(type, id, pid) {
			if (opener.closed || ! opener.setMsg)
			alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
			else
			opener.setMsg(type, id, pid);
			window.close();
			return false;
		}
	</script>
</body>
</html>