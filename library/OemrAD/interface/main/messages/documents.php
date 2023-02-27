<?php

include_once("../../globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\EmailMessage;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);
$list = strip_tags($_REQUEST['list']);
$mid = strip_tags($_REQUEST['mid']);
$type = strip_tags($_REQUEST['type']);


if(isset($type) && $type == "ajax") {

	## Read value
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	//$searchValue = mysqli_real_escape_string($con,$_POST['search']['value']); // Search value

	## Total number of records without filtering
	$records = MessagesLib::getDocumentListByQuery($pid, 'count(*) as allcount');
	$totalRecords = !empty($records) ? $records[0]['allcount'] : 0;

	## Fetch records
	$recordsList = MessagesLib::getDocumentListByQuery($pid, 'd.id, d.type, d.size, d.url, d.docdate, d.list_id, d.encounter_id, c.name', $columnName, $columnSortOrder, $row, $rowperpage);

	$data = array();
	foreach ($recordsList as $i => $ritem) {
		$ritem['docname'] = '';
		$data[] = $ritem;
	}

	## Response
	$response = array(
	  "draw" => intval($draw),
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecords,
	  "aaData" => $data
	);

	echo json_encode($response);
	exit();
}

/*Fetch Document List*/
$documentList = EmailMessage::getDocumentList($pid);
$documentListHTML = json_encode($documentList);

?>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
	<?php Header::setupHeader(['common','esign','dygraphs', 'opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'datatables', 'datatables-colreorder', 'datatables-bs']);  ?>

    <link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
    <script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/tinymce/tinymce.min.js"></script>

	<style type="text/css">
		.childContainer li:last-child {
	    	border-bottom: 0px solid;
	    }
	    .counterListContainer {
	    	padding: 10px;
	    	margin-bottom: 10px;
	    }
	    .modal-container {
	    	height: 100%;
		    display: grid;
		    grid-template-rows: 1fr auto;
	    }
	    .modal-body {
	    	overflow: auto;
	    }
	    
	    /*Documents*/
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
</head>
<body>
	<div class="modal-container">
		<div class="modal-body counterListContainer">
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
		</div>
		<div class="modal-footer">
			<button class="btn btn-selectBtn btn-sm" data-dismiss="modal">Select</button>
			<button class="btn btn-default btn-Close btn-sm" data-dismiss="modal">Close</button>
		</div>
	</div>
	<script type="text/javascript">
		$mid = '<?php echo $mid; ?>';
		var jsonFilesList = JSON.parse('<?php echo $documentListHTML; ?>');

		jQuery(document).ready(function($){
			//Close modal
			$('.btn-Close').on('click', function(){
				window.close();
				return false;
			});

			$('.btn-selectBtn').on("click", function (e) {
				var rows_selected = table.column(0).checkboxes.selected();
				var tempSelected = {};
				jQuery.each(rows_selected, function(index, rowId){
		         	tempSelected[rowId] = jsonFilesList[rowId];
			    });
			    opener[$mid]['selectedDocuments'] = tempSelected;
				afterSelect(tempSelected);
			});

			var table = jQuery('#document_results').DataTable({
				'initComplete': function(settings){
			         var api = this.api();

			         api.cells(
			            api.rows(function(idx, data, node){
			               return (opener[$mid]['selectedDocuments'][data[0]]) ? true : false;
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
		});

		function afterSelect(list) {
			if (opener.closed || ! opener[$mid].handleDocumentsCallBack)
			alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
			else
			opener[$mid].handleDocumentsCallBack(list);
			window.close();
			return false;
		}
	</script>
</body>
</html>
