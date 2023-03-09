<?php

namespace OpenEMR\OemrAd;

class Demographicslib {
	
	function __construct(){
	}

	public static function fetchAlertLogs($pid, $limit = '') {
		$sql = "SELECT fl.*, u.username as user_name  FROM form_value_logs As fl LEFT JOIN users As u ON u.id = fl.username WHERE fl.field_id = ? AND fl.form_name = ? AND fl.pid = ? ORDER BY date DESC ";

		if(!empty($limit)) {
			$sql .= ' LIMIT '.$limit;
		}

		$lres=sqlStatement($sql, array("alert_info", "DEM", $pid));
  		$result = array();

  		while ($lrow = sqlFetchArray($lres)) {
  			$result[] = $lrow;
  		}
  		return $result;
	}

	function dem_layout_tabs($group_name_esc, $group_fields = array()) {
		global $pid;
		$group_field_id = isset($group_fields['field_id']) ? $group_fields['field_id'] : "";

		if($group_field_id !== "alert_info") {
			return false;
		}

		$tab_id = str_replace(' ', '_', $group_name_esc);
		if($tab_id == "Misc" && isset($pid) && !empty($pid)) {
			$logsData = self::fetchAlertLogs($pid, 5);
		?>
			<script type="text/javascript">
				async function open_notes_log(pid) {
				    var url = top.webroot_url + '/interface/patient_file/summary/php/dem_view_logs.php'+'?pid='+pid;
				    let title = 'Logs';
				    let dialogObj = await dlgopen(url, 'dem-alert-log', 'modal-mlg', '', '', title, {
				        allowDrag: false,
				        allowResize: false,
				        sizeHeight: 'full'
				    });

				    dialogLoader(dialogObj.modalwin);
				}

				$(function() {
				    var alertEles = document.querySelectorAll("#form_alert_info");
				    alertEles.forEach(function (alertElement, index) {
				        var alert_val = alertElement.value;
				        let alertInfoEle = document.querySelector('#form_current_alert_info');

				        if(alertInfoEle != undefined) {
				            alertInfoEle.value = alert_val;
				        }
				    });
				});
			</script>
			
			<div class="alert_log_table_container">
				<input type="hidden" name="form_current_alert_info" id="form_current_alert_info"/>
				<?php if(!empty($logsData)) { ?>
				<table class="alert_log_table text table dataTable smallsize" style="display:none;">
					<thead class="thead-dark">
						<tr class="showborder_head">
							<th>Sr.</th>
							<th>New Value</th>
							<th>Old Value</th>
							<th>Username</th>
							<th>DateTime</th>
						</tr>
					</thead>
					<tbody>
					<?php
						$ci = 1;
						foreach ($logsData as $key => $item) {
							?>
							<tr>
								<td><?php echo $ci; ?></td>
								<td><?php echo $item['new_value']; ?></td>
								<td><?php echo $item['old_value']; ?></td>
								<td><?php echo $item['user_name']; ?></td>
								<td><?php echo date('d-m-Y h:i:s',strtotime($item['date'])); ?></td>
							</tr>
							<?php
							$ci++;
						}
					?>
					</tbody>
				</table>
				<a href="javascript:void(0)" onClick="open_notes_log('<?php echo $pid ?>')">View logs</a>
				<?php } ?>
			</div>
		<?php
		}
	}

	function dem_after_save($mode = '') {
		global $pid;
		
		if(isset($_POST['mode']) && $_POST['mode'] == "save" && !empty($pid)) {
			$form_alert_info = isset($_POST['form_alert_info']) ? trim($_POST['form_alert_info']) : "";
			$form_current_alert_info = isset($_POST['form_current_alert_info']) ? trim($_POST['form_current_alert_info']) : "";
			//$db_id = isset($_POST['db_id']) ? $_POST['db_id'] : "";
			$resultData = self::fetchAlertLogs($pid);

			/*if(count($resultData) == 0 && empty($form_current_alert_info)) {
				return true;
			}*/
 
			if($form_alert_info !== $form_current_alert_info) {
				$sql = "INSERT INTO `form_value_logs` ( field_id, form_name, new_value, old_value, pid, username ) VALUES (?, ?, ?, ?, ?, ?) ";
				sqlInsert($sql, array(
					"alert_info",
					"DEM",
					$form_alert_info,
					$form_current_alert_info,
					$pid,
					$_SESSION['authUserID']
				));
			}
		}

		if(!empty($mode) && $mode == 'new') {
			$form_alert_info = isset($_POST['form_alert_info']) ? trim($_POST['form_alert_info']) : "";
			$form_current_alert_info = "";

			if(!empty($form_alert_info)) {
				$sql = "INSERT INTO `form_value_logs` ( field_id, form_name, new_value, old_value, pid, username ) VALUES (?, ?, ?, ?, ?, ?) ";
				sqlInsert($sql, array(
					"alert_info",
					"DEM",
					$form_alert_info,
					$form_current_alert_info,
					$pid,
					$_SESSION['authUserID']
				));
			}
		}
	}
}