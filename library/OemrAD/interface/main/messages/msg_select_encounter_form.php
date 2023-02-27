<?php

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/api.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/forms.inc');
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\Utility;
use OpenEMR\OemrAd\Attachment;


$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : "";

$encfData = Attachment::getEncounterFormDataForSelection(array('pid' => $pid));
$prepared_data = isset($encfData['items']) ? $encfData['items'] : array();
$jsonData = addslashes(json_encode($encfData['json_items']));

?>
<html>
<head>
	<meta charset="utf-8">
	<title>Encounters & Forms</title>

	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']);  ?>

	<link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>

	<style type="text/css">
		.encounter_data input[type=checkbox] {
			margin-right: 8px;
		}
		.encounter_data .encounter_forms {
    		padding-left: 20px;
		}
		.encounter_data .line-items {
			margin-bottom: 5px;
		}
	</style>
</head>
<body>
<div>
	<div class="table-responsive table-container datatable-container c-table-bordered o-overlay">
		<table id="encounter_form_results" class="table table-sm">
			<thead class="thead-dark">
				<tr>
					<th>Items</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($prepared_data as $pkey => $pItems) {
					?>
					<tr>
						<td>
							<?php
								//foreach ($prepared_data as $pkey => $pItems) {
									echo "<div class='encounter_data'>";
							        echo "<div class='line-items'><input type=checkbox ".
							        		" data-title='" . $pItems['raw_text'] . "'".
							        		" data-encounter='" . $pItems['encounter'] . "'".
							        		" data-id='encf_" . $pItems['id'] . "'".
							                " name='encf_" . $pItems['id'] . "'".
                                            " id='encf_" . $pItems['id'] . "'".
							                " value='" . $pItems{"id"} . "'" .
							                " class='encounter'".
							                " >";
							        echo trim($pItems['raw_text']) . "</div>";
							        echo "<div class='encounter_forms'>";
										foreach ($pItems['childs'] as $ckey => $cItems) {
											foreach ($cItems as $c1key => $c1Item) {
												echo "<div class='line-items'><input type='checkbox' ".
        										" data-title='" . xl_form_title($c1Item{"form_name"}) . "'".
        										" data-encounter='" . $c1Item['encounter'] . "'".
        										" data-id='encf_" . $c1Item['id'] . "'".
                                                " name='encf_" . $c1Item['id'] . "'".
                                                " id='encf_" . $c1Item['id'] . "'".
                                                " value='" . $c1Item['id'] . "'" .
                                                " class='encounter_form' ".
                                                ">" . xl_form_title($c1Item{"form_name"}) . "</div>";
											}
										}
							        echo "</div>"; // end DIV encounter_forms
             						echo "</div>";  //end DIV encounter_data
								//}
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
    </div>
</div>
<script type="text/javascript">
	var prefix = 'encf_';
	var table = null;
	var selectedEncounterForms = window.items ? prepareIntialData(window.items) : {};

	var jsonData = JSON.parse('<?php echo $jsonData; ?>');

	//Prepare intial data
	function prepareIntialData(items) {
		let pItems = {};
		items.forEach(function (item, index) {
			//pItems[prefix + item.formid] = item;
			pItems[item.id] = item;
		});

		return pItems;
	}

	function getSelectedEncounterFormList() {
		return Object.values(selectedEncounterForms);
	}

	function getCountOfChild() {
		let parentId = {};
		jQuery.each(selectedEncounterForms, function(i, obj) {
			if(obj['parentId'] != undefined) {
				if(parentId[obj['parentId']] == undefined) {
					parentId[obj['parentId']] = 0;
				}

				parentId[obj['parentId']] = parentId[obj['parentId']] + 1;
			}
		});

		return parentId;
	}

	$(document).ready(function() {
		$( ".encounter_data input[type=checkbox]" ).each(function( index ) {
			if(selectedEncounterForms.hasOwnProperty($(this).attr('id'))) {
				$(this).prop('checked', true);
			} 
		});

		$(document).on("click", ".encounter_form", function(e) {
			var isChecked = $(this).prop("checked");
			let parentId = $(this).data('id');
			let childParentId = jsonData[parentId]['parentId'] != undefined ? jsonData[parentId]['parentId'] : '';

			if(isChecked == true) {
				selectedEncounterForms[parentId] = jsonData[parentId];
			} else {
				delete selectedEncounterForms[parentId];
			}

			if(childParentId != '') {
				let pCountData = getCountOfChild();
				if(pCountData[childParentId] != undefined && pCountData[childParentId] > 0) {
					$('input[data-id="'+childParentId+'"]').prop('checked', true);
					selectedEncounterForms[childParentId] = jsonData[childParentId];
				} else {
					let isChecked = $('input[data-id="'+childParentId+'"]').prop('checked');
					if(isChecked === true) {
						$('input[data-id="'+childParentId+'"]').prop('checked', false);
						delete selectedEncounterForms[childParentId]
					}
				}
			}
		});

		$(document).on("click", ".encounter", function(e) {
			var isChecked = $(this).prop("checked");
			var childContainer = $(this).parent().parent().find('.encounter_forms input[type=checkbox]');
			let parentId = $(this).data('id');

			if(isChecked == true) {
				selectedEncounterForms[parentId] = jsonData[parentId];
			} else {
				delete selectedEncounterForms[parentId];
			}

			$(childContainer).each(function( index ) {
				$(this).prop('checked', isChecked);
				let childId = $(this).data('id');

				if(isChecked === true) {
					selectedEncounterForms[childId] = jsonData[childId];
				} else {
					delete selectedEncounterForms[childId];
				}
			});
		});

		table = jQuery('#encounter_form_results').DataTable({
			'initComplete': function(settings){
				//Handle Footer
				handleDataTable(this);
		    },
			'order': [[0, 'asc']],
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