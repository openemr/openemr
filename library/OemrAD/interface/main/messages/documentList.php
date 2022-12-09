<?php

require_once("../../globals.php");
require_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);


if($pid) {
	/*Fetch Document List*/
	$documentList = EmailMessage::getDocumentList($pid);
	$documentListHTML = json_encode($documentList);

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
			padding: 4px!important;
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
	</style>
	<table id="document_results">
		<thead>
			<tr>
				<th></th>
				<th>Date</th>
				<th>Issue</th>
				<th>Name</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($documentList as $key => $item) {
				?>
				<tr>
					<td class="checkboxContainer" data-id="<?php echo $item['id']; ?>"><?php echo $item['id']; ?></td>
					<td><?php echo $item['docdate'] ?></td>
					<td><?php echo $item['issue'] ?></td>
					<td><?php echo xl('Document') . ": " . $item['baseName'] ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<script type="text/javascript">
		var jsonFilesList = JSON.parse('<?php echo $documentListHTML; ?>');
		var table = jQuery('#document_results').DataTable({
			'initComplete': function(settings){
		         var api = this.api();

		         api.cells(
		            api.rows(function(idx, data, node){
		               return (selectedDocuments[data[0]]) ? true : false;
		            }).indexes(),
		            0
		         ).checkboxes.select();
		     },
			'columnDefs': [
			 {
			    'targets': 0,
			    'checkboxes': {
			       'selectRow': true
			    }
			 }
			],
				'select': {
				'style': 'multi'
			},
			'order': [[1, 'asc']],
			'pageLength': 6,
			'bLengthChange': false,
			'ordering': false,
		});

		jQuery('.btn-saveBtn').on('click', function(e){
			var rows_selected = table.column(0).checkboxes.selected();
			var tempSelected = {};
			jQuery.each(rows_selected, function(index, rowId){
	         	tempSelected[rowId] = jsonFilesList[rowId];
		    });
		    selectedDocuments = tempSelected;
		});
	</script>
	<?php

}
?>