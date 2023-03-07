<?php

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Attachment;

$dateFormat = DateFormatRead("jquery-datetimepicker");
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : "";

$encounterData = Attachment::getEncounterDataForSelection(array("pid" => $pid));
$enounterList = $encounterData['items'];
$encounterListHTML = addslashes(json_encode($encounterData['json_items']));

?>
<html>
<head>
	<meta charset="utf-8">
	<title>Select Encounter</title>

	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']);  ?>

	<link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>

	<style type="text/css">
		.encounterContainer {
			padding-top: 0px;
			font-size: 16px;
		}

		.encounterContainer ul li {
			line-height: 25px;
		}
	</style>
</head>
<body>
<div>
	<div class="table-responsive table-container datatable-container c-table-bordered o-overlay">
		<table id="encounter_results" class="table table-sm">
			<thead class="thead-dark">
				<tr>
					<th></th>
					<th width="90"><?php echo xl('Date') ?></th>
					<th><?php echo xl('Encounter') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($enounterList as $i => $item) {
					?>
					<tr>
						<td class="checkboxContainer" data-id="<?php echo 'enc_' . $item['id']; ?>"><?php echo 'enc_' . $item['id']; ?></td>
						<td><?php echo $item['enc_date']; ?></td>
						<td><?php echo $item['encounter_title']; ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
    </div>
</div>
<script type="text/javascript">
	var prefix = 'encounter_';
	var table = null;
	var jsonData = JSON.parse('<?php echo $encounterListHTML; ?>');
	//var selectedEncounterList = opener.selectedEncounterList ? opener.selectedEncounterList : {};
	var selectedEncounterList = window.items ? prepareIntialData(window.items) : {};

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
		// return {
		// 	"encounter_id": item['encounter'] ? item['encounter'] : "",
		// 	"pid": item['pid'] ? item['pid'] : "",
		// 	"text_title": item['enc_date'] + " "+item['encounter_title']
		// }
		return item;
	}

	function getSelectedEncounterList() {
		var rows_selected = table.column(0).checkboxes.selected();
		var tempSelected = {};

		jQuery.each(rows_selected, function(index, rowId) {
         	tempSelected[rowId] = prepareJsonObject(jsonData[rowId]);
	    });

		return Object.values(tempSelected);
	}

	$(document).ready(function() {
		table = jQuery('#encounter_results').DataTable({
			'initComplete': function(settings){
				//Handle Footer
				handleDataTable(this);

				var api = this.api();
				api.columns.adjust();

				api.cells(api.rows(function(idx, data, node){
					return (selectedEncounterList[data[0]]) ? true : false;
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
</script>
</body>
</html>