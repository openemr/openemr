<?php

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtpatient.class.php");
include_once($GLOBALS['srcdir']."/wmt-v2/rto.inc");
include_once($GLOBALS['srcdir']."/wmt-v2/rto.class.php");

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);

if($pid) {
	?>
<html>
<head>
	<meta charset="utf-8">
	<title>Select Orders</title>

	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']);  ?>

	<link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>
</head>
<body>
	<div class="table-responsive table-container datatable-container c-table-bordered o-overlay">
		<table id="document_results" class="table table-sm">
			<thead class="thead-dark">
				<tr class="hrRow">
					<th></th>
					<th><?php echo xl('Order Id'); ?></th>
					<th><?php echo xl('Order Type'); ?></th>
					<th><?php echo xl('Ordered By'); ?></th>
					<th><?php echo xl('Status'); ?></th>
					<th><?php echo xl('Assigned To'); ?></th>
					<th><?php echo xl('Date Created'); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	<script type="text/javascript">
		var prefix = 'order_';
		var table = null;
		//var selectedOrders = opener.selectedOrders ? opener.selectedOrders : {};
		var selectedOrders = window.items ? prepareIntialData(window.items) : {};

		//Prepare intial data
		function prepareIntialData(items) {
			let pItems = {};
			items.forEach(function (item, index) {
				pItems[item.id] = item;
			});

			console.log(pItems);

			return pItems;
		}

		//Prepare json
		function prepareJsonObject(item) {
			// return {
			// 	"order_id": item['id'] ? item['id'] : "",
			// 	"pid": item['pid'] ? item['pid'] : "",
			// 	"text_title": item['title'] ? item['title'] : ""
			// }
			return item ? item : {};
		}

		function getSelectedOrderList() {
			return Object.values(selectedOrders);
		}

		document.addEventListener("close-dialog", function(e) {
		  	dlgclose();
		});

		$(document).ready(function(){
		    table = $('#document_results').DataTable({
		    	'initComplete': function(settings){
					//Handle Footer
					handleDataTable(this);

					let api = this.api();
					api.cells(api.rows(function(idx, data, node){
						let row_data = data['row_data'] ? data['row_data'] : {};
						return (selectedOrders[row_data['id']]) ? true : false;
					}).indexes(), 0).checkboxes.select();
			    },
				'processing': true,
				'serverSide': true,
				'serverMethod': 'post',
				'ajax': {
				  'url':'<?php echo $GLOBALS['webroot']."/interface/main/attachment/ajax/msg_select_order.php?pid=".$_REQUEST['pid']."&type=ajax"; ?>'
		      	},
		      	"drawCallback": function(settings) {
				    let api = this.api();
					api.cells(api.rows(function(idx, data, node) {
						let row_data = data['row_data'] ? data['row_data'] : {};
						return (selectedOrders[row_data['id']]) ? true : false;
					}).indexes(), 0).checkboxes.select();
				},
				'columns': [
					 { data: 'row_select' },
					 { data: 'id' },
					 { data: 'rto_action' },
					 { data: 'rto_ordered_by' },
					 { data: 'rto_status' },
					 { data: 'rto_resp_user' },
					 { data: 'date' }
				],
				'columnDefs': [ 
					{
						'targets': [0], /* column index */
						'orderable': false, /* true or false */
					},
					{
							'targets': 0,
				            'checkboxes': {
				               'selectRow': true,
				               'selectCallback': function(nodes, selected){
				               		$.each(nodes, function(nodeIndex, node) {
			               				let rowData = table.row($(node).closest('tr')).data();
			               				let row_data = rowData['row_data'];
			               				let msg_id = row_data['id'] ? row_data['id'] : '';
					               		
					               		if(selected === true) {
					               			selectedOrders[msg_id] = prepareJsonObject(row_data);
					               		} else if(selected === false) {
					               			delete selectedOrders[msg_id];
					               		}
				               		});
				               		     
				               }
				            }
					}
				],
				'pageLength': 10,
				'bLengthChange': true,
				'searching': true,
				'order': [[ 6, "desc" ]],
				'scrollY': '100vh',
        		'scrollCollapse': true,
        		'responsive': {
				    details: false
				}
		    });
		});
	</script>
</body>
</html>
	<?php
}