<?php

include_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/api.inc");
require_once("{$GLOBALS['srcdir']}/acl.inc");
require_once("{$GLOBALS['srcdir']}/calendar.inc");
require_once("{$GLOBALS['srcdir']}/pnotes.inc");
require_once("{$GLOBALS['srcdir']}/forms.inc");
require_once("{$GLOBALS['srcdir']}/translation.inc.php");
require_once("{$GLOBALS['srcdir']}/formatting.inc.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtpatient.class.php");
include_once($GLOBALS['srcdir']."/wmt-v2/rto.inc");
include_once($GLOBALS['srcdir']."/wmt-v2/rto.class.php");
include_once($GLOBALS['srcdir'] . "/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);

function getAllRTON($thisPid)
{
  $sql = "SELECT * FROM form_rto WHERE pid=? ".
			"ORDER BY date DESC";
	$all=array();
  $res = sqlStatement($sql, array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++) { 
		$links = LoadLinkedTriggers($row{'id'}, $thisPid);
		if($links) {
			$settings = explode('|', $links);
			foreach($settings as $test) {
				$tmp = explode('^',$test);
				$key = $tmp[0];
				$val = $tmp[1];
				$row[$key] = $val;
			}
		}
		$all[] = $row;
	}
  return $all;
}

if($pid) {
	
	$rto_data = getAllRTON($pid);

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

        table#document_results tr td.checkboxContainer,
        table#document_results tr th.checkboxContainer {
        	padding-left: 5px !important;
        	padding-right: 10px !important;
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
	<table id="document_results">
		<thead>
			<tr class="hrRow">
				<th class="checkboxContainer"><input type="checkbox" id="checkall" class="checkboxes allCheck" value="1" /></th>
				<th>Order Id</th>
				<th>Order Type</th>
				<th>Ordered By</th>
				<th>Status</th>
				<th>Assigned To</th>
				<th>Date Created</th>
			</tr>
			<?php
				foreach ($rto_data as $key => $rto) {
					$itemTitle = 'Order: '.ListLook($rto['rto_action'],'RTO_Action');

					if(!empty($rto['rto_status'])) {
						$itemTitle .= ' - '.ListLook($rto['rto_status'],'RTO_Status');
					}

					?>
					<tr>
						<td class="checkboxContainer" data-id="<?php echo $rto['id']; ?>">
							<input type="checkbox" class="checkboxes itemCheck" data-title="<?php echo $itemTitle; ?>" id="<?php echo 'order_'.$rto['id']; ?>" value="<?php echo $rto['id'] ?>">
						</td>
						<td><?php echo $rto['id']; ?></td>
						<td><?php echo ListLook($rto['rto_action'],'RTO_Action'); ?></td>
						<td><?php echo UserNameFromName($rto['rto_ordered_by']); ?></td>
						<td><?php echo ListLook($rto['rto_status'],'RTO_Status'); ?></td>
						<td><?php echo !empty($rto['rto_resp_user']) ? UserNameFromName($rto['rto_resp_user']) : ''; ?></td>
						<td><?php echo $rto['date']; ?></td>
					</tr>
					<?php
				}
			?>
		</thead>
		<tbody>
			
		</tbody>
	</table>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			$.each(selectedOrder, function(i, n){
				if(n['id'] != undefined) {
					$('.itemCheck[value='+n['id']+']').prop('checked', true);
				}
			});

			jQuery('#document_results').on('click', '.allCheck', function () {
				if($(this).prop("checked") == true){
					$('.itemCheck').prop('checked', true);
				} else if($(this).prop("checked") == false){
					$('.itemCheck').prop('checked', false);
	            }
			});

			jQuery('.btn-ordersaveBtn').on('click', function(e){
				var rows_selected = $('.itemCheck:checkbox:checked');
				var selectedItem = {};

				jQuery.each(rows_selected, function(index, rowId){
					var chekVal = $(rowId).val();
					var datatitle = $(rowId).data('title');
					var eleId = $(rowId).attr('id');

					selectedItem[eleId] = {'id' : chekVal , 'title' : datatitle};
				});

				selectedOrder = selectedItem;
			});
		});
	</script>
	<?php
}