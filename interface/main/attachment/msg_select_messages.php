<?php

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once($GLOBALS['fileroot'].'/modules/ext_message/message/EmailMessage.php');

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
if(!isset($_REQUEST['assigned_to'])) $_REQUEST['assigned_to'] = '';

$pid = strip_tags($_REQUEST['pid']);
$assigned_to = strip_tags($_REQUEST['assigned_to']);

if($pid) {
	$pid = explode(";", $pid);

	?>
<html>
<head>
	<meta charset="utf-8">
	<title>Select Messages</title>

	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']);  ?>

	<link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>
</head>
<body>
	<div class="table-responsive table-container datatable-container c-table-bordered o-overlay">
		<table id="document_results" class="table table-sm">
			<thead class="thead-dark">
				<tr>
					<th></th>
					<th><?php echo xl('From'); ?></th>
					<th><?php echo xl('To'); ?></th>
					<th><?php echo xl('Patient'); ?></th>
					<th><?php echo xl('Type'); ?></th>
					<th><?php echo xl('Date'); ?></th>
					<th><?php echo xl('Status'); ?></th>
				</tr>
			</thead>
		</table>
	</div>
	<script type="text/javascript">
		var prefix = 'msg_';
		var table = null;
		//var selectedMessages = opener.selectedMessages ? opener.selectedMessages : {};
		var selectedMessages = window.items ? prepareIntialData(window.items) : {};

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
			// return {
			// 	"message_id": item['id'] ? item['id'] : "",
			// 	"pid": item['pid'] ? item['pid'] : "",
			// 	"text_title": item['link_title'] ? item['link_title'] : ""
			// }
			return item;
		}

		function getSelectedMessageList() {
			console.log(selectedMessages);
			return Object.values(selectedMessages);
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
						return (selectedMessages[row_data['id']]) ? true : false;
					}).indexes(), 0).checkboxes.select();
			    },
				'processing': true,
				'serverSide': true,
				'serverMethod': 'post',
				'ajax': {
				  'url':'<?php echo $GLOBALS['webroot']."/interface/main/attachment/ajax/msg_select_messages.php?pid=".$_REQUEST['pid']."&assigned_to=".$_REQUEST['assigned_to']."&type=ajax"; ?>'
		      	},
		      	"drawCallback": function(settings) {
					let api = this.api();
					api.cells(api.rows(function(idx, data, node){
						let row_data = data['row_data'] ? data['row_data'] : {};
						return (selectedMessages[row_data['id']]) ? true : false;
					}).indexes(), 0).checkboxes.select();
				},
				'columns': [
					 { data: 'row_select' },
					 { data: 'user_fullname' },
					 { data: 'msg_to' },
					 { data: 'patient_fullname' },
					 { data: 'title' },
					 { data: 'date' },
					 { data: 'message_status' }
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
				               			selectedMessages[msg_id] = prepareJsonObject(row_data);
				               		} else if(selected === false) {
				               			delete selectedMessages[msg_id];
				               		}
			               		});
			               		     
			               }
			            }
					}
				],
				'pageLength': 10,
				'bLengthChange': true,
				'searching': false,
				'order': [[ 5, "desc" ]],
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
?>