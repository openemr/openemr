<?php

require_once("../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);


if($pid) {
	$data = EmailMessage::getCaseList($pid);
	$dataHTML = json_encode($data);

	$jsonData = array();
	$childDAta = array();

	//print_r($data);
	?>
	<style type="text/css">
		table#document_results thead th{
			/*border-top: 1px solid black;*/
		}
		table#document_results thead th, table#document_results thead td {
			border-bottom: 0px solid black;
		}
		table#document_results thead th, table#document_results thead td,
		table#document_results tr th, table#document_results tr td {
			padding: 0px!important;
		}

		table#document_results tr td {
			border-top: 1px solid black;
			vertical-align: text-top;
		}
		.sectionTitle {
			padding: 0px 10px;
			margin-top: 20px;
			margin-bottom: 10px;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button {
			padding: 5px 10px!important;
		    font-size: 12px!important;
		    line-height: 1.5!important;
		    border-radius: 3px!important;
		    box-shadow: none!important;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button.current{
			background: #2672ec!important;
			color: #FFF!important;
		}
		#document_results_wrapper {
			margin-top: 20px;
		}
		#document_results_filter {
			margin-right: 10px;
			/*margin-bottom: 10px;*/
		}
		#document_results_filter input{
			padding: 5px 12px;
		    font-size: 14px;
		    line-height: 1.42857143;
		    color: #555;
		    background-color: #fff;
		    background-image: none;
		    border: 1px solid #ccc;
		    border-radius: 4px;
		}
		td.details-control,
    	td.details-control1 {
            background: url('<?php echo $GLOBALS['images_static_relative']."/details_open.png"; ?>') no-repeat center center;
            background-size: 18px 18px;
            cursor: pointer;
        }
        tr.details td.details-control,
        tr.details td.details-control1 {
            background: url('<?php echo $GLOBALS['images_static_relative']."/details_close.png"; ?>') no-repeat center center;
            background-size: 18px 18px;
        }

        #document_results {
        	width: 100%;
        }

        #document_results .childRowContainer table {
        	width: 100%;
        	margin-bottom: 10px;
        	margin-top: 8px;
        }

        .childRowContainer {
        	margin-left: 20px;
        }

        table#document_results .childRowContainer table tr > td {
        	padding: 0px!important;
        }

        .childRowContainer table tr > td,
        .childRowContainer table tr {
        	border:0px!important;

        }

        .checkboxContainer {
        	padding-left: 10px;
        }
        .parentCheck {
        	margin-left: 10px!important;
        }

        .checkboxes {
        	margin-left: 15px!important;
        }

        .hrRow{
        	background-color: #FFFBEB;
        }

        .tbrow > td {
        	background-color: #E0E0E0;
        }
	</style>
	<div class="includeDemoContainer">
		<label>Include demographic details only</label>
		<input type="checkbox" name="encounterins_include_demo" class="include_demo" id="encounterins_include_demo" />
	</div>
	<table id="document_results">
		<thead>
			<tr class="hrRow">
				<th><input type="checkbox" id="checkall" class="checkboxes allCheck" value="<?php echo $insItem['id'] ?>" /></th>
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
			<?php
			foreach ($data as $parentKey => $item) {
				$jsonData[$item['id']]  = array(
					'id' => $item['id'],
					'case_dt' => $item['case_dt'],
					'case_description' => $item['case_description'],
					'pid' => $item['pid']
				);
				?>
				<tr class="tbrow" data-index-value="<?php echo $parentKey; ?>">
					<td class="checkboxContainer" data-id="<?php echo $item['id']; ?>">
						<input type="checkbox" class="checkboxes parentCheck" value="<?php echo $item['id'] ?>">
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
					<td colspan="8">
					<div class="childRowContainer">
					<table>
						<tr>
							<th> </th>
							<th>Company </th>
							<th>Policy  </th>
							<th>Group   </th>
							<th>Effective </th>
						</tr>
					<?php
						foreach ($item['ins_data'] as $childkey => $insItem) {
						$childDAta[$item['id']]['cnt'.($childkey+1)][$insItem['id']] = array(
							'id' => $insItem['id'],
							'name' => $insItem['name'],
							'policy_number' => $insItem['policy_number'],
							'group_number' => $insItem['group_number'],
							'date' => $insItem['date'],
							'pid' => $insItem['pid']
						)
					?>
						<tr>
							<td><input type="checkbox" data-cnt="<?php echo 'cnt'.($childkey+1); ?>" class="checkboxes childCheck" data-parent="<?php echo $item['id']; ?>"  value="<?php echo $insItem['id'] ?>" />	</td>
							<td><?php echo $insItem['name']; ?></td>
							<td><?php echo $insItem['policy_number']; ?></td>
							<td><?php echo $insItem['group_number']; ?></td>
							<td><?php echo $insItem['date']; ?></td>		
						</tr>
					<?php
					}
					?>
					</table>
					</div>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<script type="text/javascript">
		$dataValue = <?php echo json_encode($jsonData); ?>;
		$childDAta = <?php echo json_encode($childDAta); ?>;

		$(document).ready(function(){
			$('#encounterins_include_demo').prop('checked', window['checkEncounterInsDemo']);
		});

		jQuery(document).ready(function(){
			$.each(selectedEncounterIns, function(i, n){
				if(n['id'] != undefined) {
					$('.parentCheck[value='+n['id']+']').prop('checked', true);

					for($i=1; $i<=3;$i++) {
						if(n['cnt'+$i] != undefined && n['cnt'+$i]['id'] != undefined) {
							$('.childCheck[data-parent='+n['id']+'][value='+n['cnt'+$i]['id']+']').prop('checked', true);
						}
					}
				}
			});
		});

		jQuery('#document_results').on('click', '.allCheck', function () {
			if($(this).prop("checked") == true){
				$('.parentCheck').prop('checked', true);
				$('.childCheck').prop('checked', true);
			} else if($(this).prop("checked") == false){
				$('.parentCheck').prop('checked', false);
				$('.childCheck').prop('checked', false);
            }
		});

		jQuery('#document_results').on('click', '.childCheck', function () {
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

		jQuery('#document_results').on('click', '.parentCheck', function () {
			$val = $(this).val();
			$childEle = $('.childCheck[data-parent='+$val+']');

			if($(this).prop("checked") == true){
				$childEle.prop('checked', true);
			} else if($(this).prop("checked") == false){
                $childEle.prop('checked', false);
            }
		});

		jQuery('.btn-demosinsurancessaveBtn').on('click', function(e){
			var rows_selected = $('.parentCheck:checkbox:checked');
			var tempSelected = {};
			jQuery.each(rows_selected, function(index, rowId){
				$checkVal = $(rowId).val();
				tempSelected[$checkVal] = $dataValue[$checkVal];
				
			});

			var child_rows_selected = $('.childCheck:checkbox:checked');
			jQuery.each(child_rows_selected, function(index, rowId){
				$child_checkVal = $(rowId).val();
				$child_cnt = $(rowId).data('cnt');
				$child_parent = $(rowId).data('parent');
				
				tempSelected[$child_parent][$child_cnt] = {};
				tempSelected[$child_parent][$child_cnt] = $childDAta[$child_parent][$child_cnt][$child_checkVal];
				
			});
			
			selectedEncounterIns = tempSelected;
			checkEncounterInsDemo = $("#encounterins_include_demo").prop("checked");
		});

		function format(index) {
			var html = '';
			if(typeof $dataValue[index] !== 'undefined') {
				$rowdata = $dataValue[index];
				html = $rowdata['case_dt'];
			}
	        return html;
	    }
	</script>
	<?php
}