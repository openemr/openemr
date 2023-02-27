<?php

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Attachment;
use OpenEMR\OemrAd\EmailMessage;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);

if($pid) {
	$demoInsData = Attachment::getDemosInsDataForSelection(array("pid" => $pid));
	$demoInsList = $demoInsData['items'];
	$demoInsJSON = addslashes(json_encode($demoInsData['json_items']));
?>
<html>
<head>
	<meta charset="utf-8">
	<title>Select Demos & Insurances</title>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']);  ?>
	<link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>

	<style type="text/css">
		.inner-child-table tr th,
		.inner-child-table tr td {
			padding: 5px 10px !important;
		}
		.checkboxContainer {
			text-align: center !important;
		}
	</style>
</head>
<body>
	<div class="includeDemoContainer">
		<label>Include demographic details only
		<input type="checkbox" name="demoins_inc_demographic" class="include_demo" id="demoins_inc_demographic" /></label>
	</div>
	<div class="table-responsive table-container datatable-container c-table-bordered o-overlay">
		<table id="demoins_results" class="table table-sm">
			<thead class="thead-dark">
				<tr>
					<th width="20"><input type="checkbox" id="checkall" class="checkboxes allCheck" value="all" /></th>
					<th>Case Number</th>
					<th>Case Date</th>
					<th>Case Description</th>
					<th>Empl</th>
					<th>Auto</th>
					<th>Cash</th>
					<th># Encs</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($demoInsList as $itemKey => $item) { ?>
					<tr class="tbrow table-secondary" data-index-value="<?php echo $itemKey; ?>">
						<td class="checkboxContainer" data-id="<?php echo "di_" . $item['id']; ?>">
							<input type="checkbox" class="checkboxes parentCheck" value="<?php echo "di_" . $item['id'] ?>">
						</td>
						<td><?php echo $item['id'] ?></td>
						<td><?php echo $item['case_dt'] ?></td>
						<td><?php echo $item['case_description'] ?></td>
						<td><?php echo ($item['employment_related'] ? 'Yes' : 'No'); ?></td>
						<td><?php echo ($item['auto_accident'] ? 'Yes' : 'No'); ?></td>
						<td><?php echo ($item['cash'] ? 'Yes' : 'No'); ?></td>
						<td><?php echo $item['enc_count']; ?></td>
					</tr>
					<tr class="childRow">
						<td></td>
						<td colspan="7">
							<div class="childRowContainer">
								<table class="table table-borderless table-sm inner-child-table valign-middle">
								<thead>
									<tr>
										<th width="20"></th>
										<th>Company</th>
										<th>Policy</th>
										<th>Group</th>
										<th>Effective</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($item['ins_data'] as $childkey => $insItem) { ?>
									<tr>
										<td class="checkboxContainer"><input type="checkbox" data-cnt="<?php echo 'cnt'.($childkey+1); ?>" class="checkboxes childCheck" data-parent="<?php echo "di_" . $item['id']; ?>" data-id="<?php echo "di_" . $item['id'] . "_" . $insItem['id'] . "_" . ($childkey+1) ?>" value="<?php echo "di_" . $item['id'] . "_" . $insItem['id'] . "_" . ($childkey+1) ?>" /></td>
										<td><?php echo $insItem['name']; ?></td>
										<td><?php echo $insItem['policy_number']; ?></td>
										<td><?php echo $insItem['group_number']; ?></td>
										<td><?php echo $insItem['date']; ?></td>		
									</tr>
								<?php } ?>
								</tbody>
								</table>
							</div>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
    </div>
	<script type="text/javascript">
		var table = null;
		var jsonData = JSON.parse('<?php echo $demoInsJSON; ?>');
		var selectedDemoIns = window.items ? prepareIntialData(window.items) : {};
		var demoins_inc_demographic = window.demoins_inc_demographic ? window.demoins_inc_demographic : false;

		//Prepare intial data
		function prepareIntialData(items) {
			let pItems = {};
			items.forEach(function (item, index) {
				pItems[item.id] = item;
			});

			return pItems;
		}

		//Prepare json
		function prepareJsonObject(item) {
			return item;
		}

		function getNeedToIncludeDemographic() {
			return $("#demoins_inc_demographic").prop("checked");
		}

		function getSelectedDemoInsList() {
			var rows_selected = $('.parentCheck:checkbox:checked');
			var tempSelected = {};
			jQuery.each(rows_selected, function(index, rowId){
				let checkVal = $(rowId).val();
				tempSelected[checkVal] = jsonData[checkVal];
				tempSelected[checkVal]['childs'] = {};
			});

			var child_rows_selected = $('.childCheck:checkbox:checked');
			jQuery.each(child_rows_selected, function(index, rowId){
				let child_checkVal = $(rowId).val();
				let child_parent = $(rowId).data('parent');
				let child_cnt = $(rowId).data('cnt');

				if(!tempSelected[child_parent]) {
					tempSelected[child_parent] = jsonData[checkVal];
					tempSelected[child_parent]['childs'] = {};
				}

				if(tempSelected[child_parent] && tempSelected[child_parent]['childs']) {
					tempSelected[child_parent]['childs'][child_cnt] = jsonData[child_checkVal];
				}
			});

			return Object.values(tempSelected);
		}

		$(document).ready(function() {
			if(demoins_inc_demographic) {
				$('#demoins_inc_demographic').prop('checked', demoins_inc_demographic);
			}

			$.each(selectedDemoIns, function(i, n){
				if(n['id'] != undefined) {
					$('.parentCheck[value='+n['id']+']').prop('checked', true);

					let iChilds = n['childs'];
					Object.keys(iChilds).forEach(function(key) {
                       let cItemData = iChilds[key];
                       $('.childCheck[value='+cItemData['id']+']').prop('checked', true);
                    });
				}
			});

			jQuery('#demoins_results').on('click', '.allCheck', function () {
				if($(this).prop("checked") == true){
					$('.parentCheck').prop('checked', true);
					$('.childCheck').prop('checked', true);
				} else if($(this).prop("checked") == false){
					$('.parentCheck').prop('checked', false);
					$('.childCheck').prop('checked', false);
	            }
			});

			jQuery('#demoins_results').on('click', '.childCheck', function () {
				$val = $(this).val();
				$child_parent = $(this).data('parent');
				if($(this).prop("checked") == true){
					$('.parentCheck[value='+$child_parent+']').prop('checked', true);
				} else if($(this).prop("checked") == false){
					$elelength = $('.childCheck[data-parent='+$child_parent+']:checkbox:checked').length;
					if($elelength == 0) {
						$('.parentCheck[value='+$child_parent+']').prop('checked', false);
					}
	            }
			});

			jQuery('#demoins_results').on('click', '.parentCheck', function () {
				$val = $(this).val();
				$childEle = $('.childCheck[data-parent='+$val+']');

				if($(this).prop("checked") == true){
					$childEle.prop('checked', true);
				} else if($(this).prop("checked") == false){
	                $childEle.prop('checked', false);
	            }
			});
		});
		
	</script>
</body>
</html>
<?php } ?>