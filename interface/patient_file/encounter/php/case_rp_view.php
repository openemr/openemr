<?php

require_once("../../../globals.php");
require_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');
include_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\Caselib;
use OpenEMR\OemrAd\Attachment;
use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\PostalLetter;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : '';

$encData = Attachment::getFormEncountersById($pid, $encounter);
$encList = generateEncounterList($pid, $encData);
$encListJson = json_encode(Attachment::prepareMessageAttachment(array("selectedEncounterList" => $encList)));

$cs_rp_list = Caselib::getCsRpExists($encounter, $pid);
$rp_data = Caselib::getRpData($cs_rp_list);

// Option lists
$default_message = 'free_text';

function generateEncounterList($pid, $encData = array()) {
	$tmpEncData = array();
	$parentId = '';

	foreach ($encData as $encK => $item) {
		$id = $item['formdir'].'_'.$item['form_id'];
		$title = $item{"reason"}." (" . date("Y-m-d", strtotime($item{"date"})) .") ". $item['drname'];

		if($item["form_name"] == "New Patient Encounter") {
			$parentId = $id;
			$tmpEncData[$id] = array(
				"title" => $title,
				"value" => $item['form_id'],
				"pid" => $pid
			);
		}
	}

	foreach ($encData as $encK => $item) {
		$id = $item['formdir'].'_'.$item['form_id'];
		$title = $item["form_name"];

		if($item["form_name"] !== "New Patient Encounter" && !empty($parentId)) {
			$tmpEncData[$id] = array(
				"title" => $title,
				"value" => $item['form_id'],
				"pid" => $pid,
				"parentId" => $parentId
			);
		}
	}

	return $tmpEncData;
}

function getMessageListObj($type) {
	return new wmt\Options('CareTeam_Communication_Templates');
}

function isValidData($item) {
	$responceList = [];

	if($item['ct_communication'] == 'email') {
		if(empty($item['email1'])) {
			$responceList[] = 'Empty email.';
		}
	} else if($item['ct_communication'] == 'sms') {
		/*if(empty($item['phonecell'])) {
			$responceList[] = 'Empty mobile number.';
		}*/
	} else if($item['ct_communication'] == 'fax') {
		if(empty($item['fax'])) {
			$responceList[] = 'Empty fax number.';
		}
	}

	return $responceList;
}

function getInfoIcon($data) {
	$title_msg = implode("\n", $data);
	return "<i class=\"fa fa-info-circle\" aria-hidden=\"true\" title='".$title_msg."'></i>";
}

$ct_communication_type = array(
    'email' => 'Email',
    'sms' => 'SMS',
    'fax' => 'Fax',
    'postal_letter' => 'Postal Letter'
);

$validDataList = array();

?>
<html>
<head>
	<title><?php echo htmlspecialchars( xl('Care Team Providers'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad']); ?>
	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/interface/main/attachment/js/attachment.js"></script>
	<link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>


	<style type="text/css">
		.page-container {
			display: grid;
		    grid-template-rows: 1fr auto;
		    height: 100%;
		    grid-row-gap: 15px;
		}

		.inner-page-container {
			height: 100%;
		    position: relative;
		    overflow: auto;
		    display: grid;
		    max-height: 100%;
		    grid-template-rows: auto 1fr;
		}

		.table-container {
			height: 100%;
			min-height: 220px;
		}

		.infoContainer {
			text-align: center;
		}
	</style>
	<script type="text/javascript">
		var attachClassObject = null;
		$(document).ready(function(){
			attachClassObject = $('#itemsContainer').attachment({
				empty_title: "No items"
			});
		});
	</script>
</head>
<body>
	<div id="send_spinner_container" class="hideLoader">
		<div class="loaderContainer backWhite">
			<div class="spinner-border"></div>
		</div>
	</div>
	<div class="page-container">
		<div class="px-1 inner-page-container">
			<div class="table-responsive table-container datatable-container c-table-bordered footer-p o-overlay c-container">
				<table id="table_results" class="table table-sm valign-middle" class="border-0 display">
					<thead class="thead-dark">
						<tr class="hrRow">
							<th width="15"></th>
							<th width="20"></th>
							<th width="180">Name</th>
							<th width="180">Care team Communication</th>
							<th width="180">Template</th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach ($rp_data as $rpk => $rp_item) {
								$rp_name = $rp_item['fname'] . " " . $rp_item['lname'];
								$rp_email = !empty($rp_item['email']) ? $rp_item['email'] : "";
								$rp_phonecell =  !empty($rp_item['phonecell']) ? $rp_item['phonecell'] : "";
								$rp_fax =  !empty($rp_item['fax']) ? $rp_item['fax'] : "";

								$ct_type_val = isset($ct_communication_type[$rp_item['ct_communication']]) ? $ct_communication_type[$rp_item['ct_communication']] : 'None';

								$rp_item1 = $rp_item;
								$rp_item1['email1'] = $rp_email;
								$validData = isValidData($rp_item1);

								$message_list = null;
								if($ct_type_val != 'None' && $ct_type_val != 'SMS') {
									$message_list = getMessageListObj($rp_item['ct_communication']);
								}
								
								$rp_fullAddress =  PostalLetter::generatePostalAddress(array(
								    'street' => $rp_item['street'],
								    'street1' => $rp_item['streetb'],
								    'city' => $rp_item['city'],
								    'state' => $rp_item['state'],
								    'postal_code' => $rp_item['zip'],
								    'country' => "",
								), "\n");

								if(isset($rp_fullAddress) && $rp_fullAddress['status'] !== true && $rp_item['ct_communication'] == "postal_letter") {
									$validData = explode("\n", $rp_fullAddress['errors']);
								}

								?>
								<tr>
									<td class="infoContainer">
										<?php
											if(!empty($validData)) {
												$validDataList[] = $rp_item['id'];
												echo getInfoIcon($validData);
											}
										?>
									</td>
									<td class="checkboxContainer" data-id="<?php echo $rp_item['id']; ?>"><?php echo $rp_item['id']; ?></td>
									<td><?php echo $rp_name; ?></td>
									<td><?php echo $ct_type_val ?></td>
									<td>
										<input type="hidden" id="communication_<?php echo $rp_item['id']; ?>" value="<?php echo $rp_item['ct_communication']; ?>">
										<input type="hidden" id="email_<?php echo $rp_item['id']; ?>" value="<?php echo $rp_email; ?>">
										<input type="hidden" id="mobile_<?php echo $rp_item['id']; ?>" value="<?php echo $rp_phonecell; ?>">
										<input type="hidden" id="fax_<?php echo $rp_item['id']; ?>" value="<?php echo $rp_fax; ?>">
										<textarea style="display: none" id="address_status_<?php echo $rp_item['id']; ?>"><?php echo $rp_fullAddress['status']; ?></textarea>
										<textarea style="display: none" id="address_<?php echo $rp_item['id']; ?>"><?php echo $rp_fullAddress['address']; ?></textarea>
										<textarea style="display: none" id="address_json_<?php echo $rp_item['id']; ?>"><?php echo json_encode($rp_fullAddress['address_json']); ?></textarea>
										<?php if(!empty($message_list)) {?>
										<select id="template_<?php echo $rp_item['id']; ?>" class='template_field form-control'>
											<?php $message_list->showOptions($default_message) ?>
										</select>
										<?php } else { ?>
											<?php echo "<div class='nonetitle'>None</div>"  ?>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
					</tbody>
				</table>
			</div>
			
			<div>
				<div id="itemsContainer" class="file-items-container mt-3 mb-3" role="alert"></div>
				<div class="btn-group" role="group">
					<button type="button" class="btn btn-primary" id="select_document" onClick="attachClassObject.handleDocument('<?php echo $pid; ?>')">Select Documents</button>
				</div>
			</div>
		</div>
		<div class="modal-footer" id="oefooter">
			<button type="button" class="btn btn-primary btn-SaveBtn" onclick="sendModal()">Send</button>
			<button type="button" class="btn btn-secondary btn-default btn-CloseBtn" onclick="closeModal()">Cancel</button>
		</div>
	</div>
	<?php
		$validDataListJson = json_encode($validDataList);
	?>
	<script type="text/javascript">
		var table = null;
		var validDataList = JSON.parse('<?php echo $validDataListJson; ?>');
		var encListJson = <?php echo $encListJson; ?>;

		//Set Encounter ListselectedEncounterList
		//attachClassObject.selectedEncounterList = encListJson;

		$(document).ready(function(){
			attachClassObject.setItemsList(encListJson, true);

			$('.btn-rpSelectBtn').click(function(){
				//sendModal();
			});
		
			table = jQuery('#table_results').DataTable({
				'initComplete': function(settings){
					 
					 //Handle Footer
					 handleDataTable(this);

			         var api = this.api();

			         api.cells(
			            api.rows(function(idx, data, node){
			               return true;
			            }).indexes(),
			            1
			         ).checkboxes.select();

			         api.cells(
			            api.rows(function(idx, data, node) {
			               return validDataList.includes(data[1]);
			            }).indexes(),
			            1
			         ).checkboxes.disable();
			     },
				'columnDefs': [
				 {
				    'targets': 1,
				    'checkboxes': {
				       'selectRow': true
				    }
				 }
				],
					'select': {
					'style': 'multi'
				},
				'searching': true,
				'order': [[2, 'asc']],
				'pageLength': 5,
				'bLengthChange': true,
				'ordering': false,
				'paging':   true,
				'scrollY': '100vh',
        		'scrollCollapse': true,
        		'responsive': {
				    details: false
				},
				'lengthMenu': [
		            [5, 10, 25, 50],
		            [ '5', '10', '25', '50' ]
		        ]
			});
		});

		function closeModal() {
			window.close();
			return false;
		}

		async function sendModal() {
			var rows_selected = table.column(1).checkboxes.selected();

		    if(rows_selected.length <= 0) {
		    	alert("Please select reference provider.");
		    	return false;
		    }

			var tempSelected = {};
			jQuery.each(rows_selected, function(index, rowId){
				var tmplate_val = $('#template_'+rowId).val();
				var communication_val = $('#communication_'+rowId).val();
				var email_val = $('#email_'+rowId).val();
				var mobile_val = $('#mobile_'+rowId).val();
				var fax_val = $('#fax_'+rowId).val();
				var address_val = $('#address_'+rowId).val();
				var address_json_val = $('#address_json_'+rowId).val();
				
	         	tempSelected["rp_"+rowId] = { 
	         		id : rowId, 
	         		template : tmplate_val, 
	         		communication_mode : communication_val,
	         		email : email_val,
	         		mobile : mobile_val,
	         		fax : fax_val,
	         		address : address_val,
	         		address_json : address_json_val
	         	};
		    });

		    await ajaxTransmitWithFile(tempSelected);
		}

		async function ajaxTransmitWithFile(selectedList) {
		    // show spinner
			$('#send_spinner_container').show();

			var status = '';
			var formData = new FormData(); // Currently empty

			formData.append('selected_rp', JSON.stringify(selectedList));
			formData.append('pid', '<?php echo $pid; ?>');

			attachClassObject.appendDataToForm(formData);

			await $.ajax ({
				type: "POST",
				url: "<?php echo $GLOBALS['webroot']."/interface/patient_file/encounter/ajax/send_rp_reminder.php"; ?>",
				processData: false,
            	contentType: false,
				data: formData,
				success: function(resultStr) {
					$('#send_spinner_container').hide();
					var result = JSON.parse(resultStr);

					if(result) {
						alert("Total sent: "+result.sent+"\n"+"Total failed: "+result.failed);
					}

					// Close window and refresh
	 				//opener.doRefresh('<?php //echo $form_action ?>');
					dlgclose(); 				
				},
				error: function() {
					//$('#send_spinner_container').hide();
					alert('Send Failed...');
				},
				async:   true
			});

		}
	</script>
</body>
</html>