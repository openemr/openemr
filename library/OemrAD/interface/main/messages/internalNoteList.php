<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'] . "/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;


if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);


if($pid) {
	
	/*Fetch Document List*/
	$noteList = EmailMessage::getInternalNoteList($pid);
	$noteListHTML = json_encode($noteList);

	?>
	<style type="text/css">
		table#notes_results thead th, table#notes_results thead td {
			border-bottom: 0px solid black;
		}
		table#notes_results thead th, table#notes_results thead td,
		table#notes_results tr th, table#notes_results tr td {
			padding: 4px!important;
		}

		table#notes_results tr td {
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
		#notes_results_wrapper {
			margin-top: 20px;
		}
		#notes_results_wrapper {
			margin-right: 10px;
			/*margin-bottom: 10px;*/
		}
		#notes_results_wrapper input{
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
	<table id="notes_results">
		<thead>
			<tr>
				<th></th>
				<th><?php echo xlt('Assigned'); ?></th>
				<th><?php echo xlt('Type'); ?></th>
				<th><?php echo xlt('Content'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($noteList as $key => $item) {
				?>
				<tr>
					<td class="checkboxContainer" data-id="<?php echo $item['id']; ?>"><?php echo $item['id']; ?></td>
					<td><?php echo $item['name'] ?></td>
					<td><?php echo $item['title'] ?></td>
					<td><?php echo $item['raw_body'] ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<script type="text/javascript">
		var jsonNoteList = <?php echo $noteListHTML; ?>;
		var table = jQuery('#notes_results').DataTable({
			'initComplete': function(settings){
		         var api = this.api();

		         api.cells(
		            api.rows(function(idx, data, node){
		               return (selectedNotes[data[0]]) ? true : false;
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
			'pageLength': 5,
			'bLengthChange': false,
			'ordering': false,
		});

		jQuery('.btn-notesaveBtn').on('click', function(e){
			var rows_selected = table.column(0).checkboxes.selected();
			var tempSelected = {};
			jQuery.each(rows_selected, function(index, rowId){
	         	tempSelected[rowId] = jsonNoteList[rowId];
		    });
		    selectedNotes = tempSelected;
		});
	</script>
	<?php
}