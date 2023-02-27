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

	$documentData = Attachment::getDocumentDataForSelection(array("pid" => $pid));
	$documentList = $documentData['items'];
	$documentListHTML = addslashes(json_encode($documentData['json_items']));

	/*$pid = explode(";", $pid);
	$documentList = MessagesLib::getDocumentList($pid);

	foreach ($documentList as $id => $docItem) {
		$patient_id = $docItem['foreign_id'];
		//$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
		//$patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));
	    //$patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);
	    //$documentList[$id]['patient_name'] = $patientName;
	    //$documentList[$id]['patient_DOB'] = $patientDOB;
	    //$documentList[$id]['pubpid'] = $patientData['pubpid'];
	    $documentList[$id]['pid'] = $patient_id;
	}

	$documentListHTML = addslashes(json_encode($documentList));*/

	?>
<html>
<head>
	<meta charset="utf-8">
	<title>Select Document</title>
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
					<th width="100"><?php echo xl('Date') ?></th>
					<th width="120"><?php echo xl('Issue') ?></th>
					<th><?php echo xl('Name') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($documentList as $key => $item) {
					?>
					<tr>
						<td class="checkboxContainer" data-id="<?php echo $item['id']; ?>"><?php echo 'doc_' . $item['id']; ?></td>
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
	<script type="text/javascript">
		var table = null;

		var jsonData = JSON.parse('<?php echo $documentListHTML; ?>');
		//var selectedDocuments = opener.selectedDocuments ? opener.selectedDocuments : {};
		var selectedDocuments = window.items ? prepareIntialData(window.items) : {};

		document.addEventListener("close-dialog", function(e) {
		  	dlgclose();
		});

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
			//"pid": item['pid'] ? item['pid'] : "",
			// return {
			// 	"doc_id": item['id'] ? item['id'] : "",
			// 	"text_title": item['baseName'] ? item['baseName'] : "",
			// }
			// console.log(item);
			return item;
		}

		function getSelectedDocumentList() {
			var rows_selected = table.column(0).checkboxes.selected();
			var tempSelected = {};

			jQuery.each(rows_selected, function(index, rowId){
	         	tempSelected[rowId] = prepareJsonObject(jsonData[rowId]);
		    });

			return Object.values(tempSelected);
		}

		$(document).ready(function() {
			table = jQuery('#document_results').DataTable({
				'initComplete': function(settings){
					//Handle Footer
					handleDataTable(this);

					var api = this.api();
					api.columns.adjust();

					api.cells(api.rows(function(idx, data, node){
						return (selectedDocuments[data[0]]) ? true : false;
					}).indexes(), 0).checkboxes.select();
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
				'pageLength': 10,
				'bLengthChange': true,
				'ordering': false,
				'autoWidth':true,
				'scrollY': '100vh',
        		'scrollCollapse': true,
        		'responsive': {
				    details: false
				}
			});
		});

		/*jQuery('.btn-documentsaveBtn').on('click', function(e){
			var rows_selected = table.column(0).checkboxes.selected();
			var tempSelected = {};
			jQuery.each(rows_selected, function(index, rowId){
	         	tempSelected['doc_'+rowId] = jsonData[rowId];
		    });
		    selectedDocuments = tempSelected;
		});*/
	</script>
</body>
</html>
	<?php

}
?>