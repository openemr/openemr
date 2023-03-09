<?php

// Sanitize escapes
$sanitize_all_escapes = true;

// Stop fake global registration
$fake_register_globals = false;

require_once("../../globals.php");
require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
if(!isset($GLOBALS['wmt::use_email_direct'])) $GLOBALS['wmt::use_email_direct'] = '';

//Included EXT_Message File
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\Attachment;
use OpenEMR\OemrAd\FaxMessage;


// Determine processing mode
$form_id = trim(strip_tags($_REQUEST['id']));
$form_pid = trim(strip_tags($_REQUEST['pid']));
$form_mode = trim(strip_tags($_REQUEST['mode']));
$form_message = trim(strip_tags($_REQUEST['message']));
$form_content = $_REQUEST['content'];
$form_fax_number = trim(strip_tags($_REQUEST['fax_number']));
$form_rec_name = trim(strip_tags($_REQUEST['rec_name']));
$form_submod = trim(strip_tags($_REQUEST['submod']));
$form_prevData = $_REQUEST['prevData'];
$clean_fax_number = preg_replace("/[^0-9]/", "", $form_fax_number);

$form_action = trim(strip_tags($_REQUEST['action']));
$form_msgId = trim(strip_tags($_REQUEST['msgId']));

$requestStr = '';
if(!empty($form_action) && !empty($form_msgId)) {
	$requestStr = '&'.http_build_query(array('action' => $form_action, 'msgId' => $form_msgId));
}

// Option lists
$message_list = new wmt\Options('Fax_Messages');

// Message content
$data = '';
$content = '';
$subject = '';

// Validate Patient
if (isset($form_pid) && !empty($form_pid)) $pid = $form_pid;
if (empty($pid)) $pid = $_SESSION['pid'];
	
// Retrieve patient
$pat_data = wmt\Patient::getPidPatient($pid);
$pat_name = $pat_data->format_name;

// Retrieve user
$user_data = sqlQueryNoLog("SELECT CONCAT(LEFT(`fname`,1), '. ',`lname`) AS 'name' FROM `users` WHERE `id` = ?", array($_SESSION['authUserID']));
$user_name = (empty($user_data['name']))? 'PORTAL SUPPORT' : $user_data['name'];

$readonly = 0;
if($form_id) {
	$readonly = 1;
}

//Defualt Data
$default_message = 'free_text';

if($form_action == "resend" && !empty($form_msgId)) {
	$messageData = FaxMessage::getFax('', $form_msgId);

	$variableList = array(
		'default_pid' => 'pid',
		'default_message' => 'message',
		'rec_name' => 'rec_name',
		'default_fax_from' => 'fax_from',
		'default_address_book' => 'address_book',
		'default_insurance_companies' => 'insurance_companies',
		'default_baseDocList' => 'baseDocList',
		'default_attachments' => 'attachments'
	);
	if(!empty($messageData[0]['raw_data'])) {
		$previousData = json_decode($messageData[0]['raw_data'], true);
	}
	extract(EmailMessage::extractVariable($previousData, $variableList));

	if(isset($default_baseDocList)) {
		$default_baseDocList = json_decode($default_baseDocList, true);
	}

	if(count($messageData) > 0) {
		$msgContent = EmailMessage::getMsgContent($messageData[0]['message']);
		$fax_content = $msgContent['content'];
		$content = $msgContent['content_html'];

		if($default_pid == $pid) {
			if($messageData[0]['direction'] == "out") {
				$faxno = isset($messageData[0]['msg_to']) ? $messageData[0]['msg_to'] : "";
			} else if($messageData[0]['direction'] == "in") {
				$faxno = isset($messageData[0]['msg_from']) ? $messageData[0]['msg_from'] : "";	
			}
		} else {
			unset($rec_name);
			unset($default_fax_from);
			unset($default_address_book);
			unset($default_insurance_companies);
		}
	}

} else if(isset($form_id)) {
	$docsData = EmailMessage::getMsgDocs($form_id);
	$docsList = EmailMessage::generateDocList($docsData);
}

// Ajax check for new messages 
if ($form_mode == 'retrieve') {

	$content_html = "";
	
	if($form_message != 'free_text') {
	// Retrieve content
	try {
		// Get message template
		$template = wmt\Template::Lookup($form_message, $pat_data->language);
		
		// Fetch merge data
		$data = new wmt\Grab($pat_data->language);
		$data->loadData($pat_data->pid, $_SESSION['authId']);
		
		// Perform data merge
		$template->Merge($data->getData());
		$content = $template->text_merged;
		$content_html = $template->html_merged;
		
		// Deal with imbedded links
		$content = str_replace('<br>', "\n", $content);
		$content = str_replace('http:', 'https:', $content);
		$content = str_replace('target="_blank"', '', $content);
		$content = str_replace("target='_blank'", '', $content);
		
	} catch (Exception $e) {
		$content = $e->getMessage();
	}
	}
	
	// Return new messages
	echo json_encode(array('content'=>$content, 'content_html'=>$content_html));
	
	// Done with ajax
	exit();
}
	
// Ajax transmit new message
if ($form_mode == 'transmit') {

	$responData = array(
		'status' => true,
		'data' => ''
	);

	$fItem = array(
		'pid' => $pat_data->pid,
		'data' => array(
			'template' => $form_message,
			'fax_number' => $clean_fax_number,
			'receiver_name' => $form_rec_name,
			'html' => $form_content,
			'text' => $form_content,
			'fax_from_type' => $_REQUEST['fax_from'],
			'request_data' => $_REQUEST,
			'files' => $_FILES,
		));

	if($form_submod == "check") {
		try {

			$fData = FaxMessage::TransmitFax(
				array($fItem['data']), 
				array('pid' => $fItem['pid'], 'request_data' => $_REQUEST, 'files' => $_FILES, 'logMsg' => true, 'calculate_cost' => true)
			);

			if(is_array($fData) && count($fData) == 1) {
				$responData = array(
					'status' => true,
					'data' => $fData[0]
				);
			} else {
				throw new \Exception("Something went wrong.");
			}

		} catch (Exception $e) {
			$status = $e->getMessage();
			$responData = array(
				'status' => false,
				'error' => $status
			);
		
		}

		// Return new messages
		echo json_encode($responData);
		exit();
	}

	if($form_submod == "confirm") {
		try {
			$fData = FaxMessage::TransmitFax(
				array($fItem['data']), 
				array('pid' => $fItem['pid'], 'request_data' => $_REQUEST, 'files' => $_FILES, 'logMsg' => true)
			);

			$tMessage = array();
			if(isset($fData)) {
				foreach ($fData as $fdk => $fdItem) {
					if($fdItem['status'] === true) {
						if(isset($fdItem['data'])) {
							foreach ($fdItem['data'] as $fddk => $fdDataItem) {
								$msgLogId = $fdDataItem['msgid'];
								$toFaxItem = $fdDataItem['to'];

								$orderList = (isset($_REQUEST['orders']) && !empty($_REQUEST['orders'])) ? json_decode($_REQUEST['orders'], true) : array();

								if(!empty($orderList)) {
									MessagesLib::addMessageOrderLog($pid, 'FAX', $orderList, $msgLogId, $toFaxItem);
								}
							}
						}
					}

					if(!empty($fdItem['errors'])) {
						$tMessage = array_merge($tMessage,$fdItem['errors']);
					}
				}
			}

			$responData = array(
				'status' => true,
				'message' => implode("\n", $tMessage)
			);

		} catch (Exception $e) {
			$status = $e->getMessage();
			$responData = array(
				'status' => false,
				'error' => $status
			);
		}

		// Return new messages
		echo json_encode($responData);
		exit();
	}
}

if($form_id) {
	$msg = sqlQuery('SELECT * FROM `message_log` WHERE `id` = ?', $form_id);
  	//$content = $msg{'message'};
  	$content = EmailMessage::getMessageContent($msg{'message'}, 2);
  	$subject = $msg{'event'};
  	$faxno = $msg{'msg_to'};

  	$variableList = array(
		'default_pid' => 'pid',
		'default_message' => 'message',
		'rec_name' => 'rec_name',
		'default_fax_from' => 'fax_from',
		'default_address_book' => 'address_book',
		'default_insurance_companies' => 'insurance_companies',
		'default_baseDocList' => 'baseDocList',
		'default_attachments' => 'attachments'
	);
	if(!empty($msg{'raw_data'})) {
		$previousData = json_decode($msg{'raw_data'}, true);
	}
	extract(EmailMessage::extractVariable($previousData, $variableList));
}
?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>	<html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>	<html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />

	<title>Fax Message</title>
	<meta name="description" content="Fax Message" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<script type="text/javascript">
		var basePath = '<?php echo $rootdir; ?>';
	</script>
  	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme', 'oemr_ad']); ?>

  	<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/tinymce/tinymce.min.js"></script>
  	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/interface/main/attachment/js/attachment.js"></script>

	<link rel="shortcut icon" href="images/favicon.ico" />

	<style>
		html, body {
			margin: 0;
			height: 100vh;
			min-height: 100vh;
		}
		body {
			/*margin: 0 10px;*/
			display: flex;
			flex-direction: column;
			align-items: center;
			overflow-y: hidden;
		}
		header {
			order: 1;
		    flex-shrink: 0;
		    flex-basis: 40px;
		    width: 100%;
		    min-width: 600px;
		    margin-bottom: 10px;
		}
		main {
			order: 2;
			flex-grow: 1;
		    width: 100%;
		    min-width: 600px;
		    border: 1px solid #ccc;
		    margin-left: 0px;
		}
		footer {
			order: 3;
			flex-shrink: 0;
			flex-basis: 50px;
		    width: 100%;
		    min-width: 600px;
		    margin-top: 10px;
		    margin-bottom: 10px;
		    border: 1px solid #ccc;
		    white-space: nowrap;
		}
		.attachmentContainer {
			order: 3;
			flex-grow: 1;
		    margin-left: 0px;
		    width: 100%;
		}
		#content {
			line-height: 24px;
			white-space: pre-wrap;
			width: 94%;
			height: 86%;
			margin: 10px;
			padding: 10px;
			border: none;
			border-radius: 5px;
			resize: vertical;
			background-color: transparent;
			vertical-align: middle;
		}
		#send_spinner_container {
			position: fixed;
			width: 100%;
			height: 100%;
			background-color: rgba(255,255,255,0.5);
			display: none;
			top: 0;
			z-index: 100;
		}
		.readonlyInput {
			background-color: #fff!important;
		}
	</style>

	<style type="text/css">
		body {
			overflow-y: auto!important;
		}
		.btn-file {
		    position: relative;
		    overflow: hidden;
		}
	    .btn-file input[type=file] {
	       	background: #1050b6;
		    color: #ffffff !important;
		    display: block;
		    float: left;
		    font-weight: 400;
	        position: absolute;
	        top: 0;
	        right: 0;
	        min-width: 100%;
	        min-height: 100%;
	        text-align: right;
	        filter: alpha(opacity=0);
	        opacity: 0;
	        outline: none;
	        cursor: inherit;
	        border-radius: 0px!important; 
	    }
	    .uploadBtnContainer {
	    	width: 95px;
			height: 30px;
	    }
	    .files input[type="button"], input[type="submit"], .files button {
	    	float: none!important;
	    }
	    .files .btnContainer {
	    	display: inline-block;
	    	vertical-align: top;
	    }
	    .files .fileList {
	    	padding-left: 20px;
			margin-top: 15px;
			margin-bottom: 20px;
			float: left;
	    }
	    textarea.form-control {
	    	height: 100px!important;
	    }
	    main {
	    	flex-grow: unset!important;
	    }
	    #send_fax {
	    	float: right;
	    }
	    .containerFax {
	    	order: 3;
	    	width: 100%;
	    	margin-top: 15px;
	    }
	    .containerFile {
	    	order: 3;
	    	width: 100%;
	    	margin-top: 15px;
	    }
	    .fileList li {
	    	border-bottom: 1px solid;
	    }
	    .childContainer li:last-child {
	    	border-bottom: 0px solid;
	    }
	    .counterListContainer {
	    	padding: 10px;
	    	margin-bottom: 10px;
	    }
	    .encounter_data input[type=checkbox] {
	    	margin-right: 8px;
	    }
	    .encounter_data .encounter_forms {
	    	padding-left: 20px;
	    }
	    .hideContainer {
	    	display: none;
	    }
	    .readonlyInput {
	    	background-color: #fff!important;
	    }
	</style>

	<script type="text/javascript">
		var attachClassObject = null;
		$(document).ready(function(){
			attachClassObject = $('#itemsContainer').attachment({
				empty_title: "No items"
			});

			<?php if($readonly !== 1) { ?>
			<?php if(!empty($default_baseDocList)) { ?>
				attachClassObject.setItemsList(<?php echo json_encode(Attachment::prepareMessageAttachment($default_baseDocList)); ?>, false);
			<?php } else if(!empty($default_attachments)) { ?>
				attachClassObject.setItemsList(<?php echo json_encode(Attachment::prepareMessageAttachment($default_attachments)); ?>, false);
			<?php } ?>
			<?php } ?>
			
		});
	</script>
</head>
<body class="mx-2">
	<header>
		<table>
			<tr>
				<td style='text-align:right;min-width:60px'>
					<b><?php echo xlt('Message'); ?>:&nbsp;</b>
				</td>
				<td>
				<?php if($readonly === 1) { ?>
					<select id="message" name="message" class='form-control form-select' disabled>
						<?php $message_list->showOptions($default_message) ?>
					</select>
					<!-- <input type='text' class='form-control' value='<?php //echo $subject; ?>' disabled /> -->
				<?php } else { ?>
					<select id="message" name="message" class='form-control form-select'>
						<?php $message_list->showOptions($default_message) ?>
					</select>
				<?php } ?>
				</td>
			</tr>
			<?php if($form_action == "resend") { ?>
			<tr>
				<td style='text-align:right'>
					<b><?php echo xlt('Patient'); ?>:&nbsp;</b>
				</td>
				<td>
					<?php if($form_action == "resend") { ?>
						<input type='text' class='form-control readonlyInput' value='<?php echo isset($pat_data->format_name) ? $pat_data->format_name : ''; ?>' onClick="selectPatientButton()" readonly />
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			<?php if($readonly === 0) {  ?>
				<tr>
					<td style='text-align:right'>
						<b><?php echo xlt('Select recipient'); ?>:&nbsp;</b>
					</td>
					<td>
						<select id="fax_from" name="fax_from" class="form-control form-select">
							<option value="">Please Select</option>
							<option value="address_book">Address Book</option>
							<option value="insurance_companies">Insurance Companies</option>
							<option value="patient">Patient</option>
							<option value="custom">Custom</option>
						</select>
					</td>
				</tr>
				<tr id="address_book_container" class="hideContainer searchContainer">
					<td style='text-align:right'>
						<b><?php echo xlt('Address Book'); ?>:&nbsp;</b>
					</td>
					<td>
						<input type='text' id="address_book" name="address_book" onClick='sel_addressbook_fax()' class='form-control readonlyInput' value='' readonly/>
					</td>
				</tr>
				<tr id="insurance_companies_container" class="hideContainer searchContainer">
					<td style='text-align:right'>
						<b><?php echo xlt('Insurance Companies'); ?>:&nbsp;</b>
					</td>
					<td>
						<input type='text' id="insurance_companies" name="insurance_companies" onClick="sel_insurancecompanies_fax()" class='form-control readonlyInput' value='' readonly/>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td style='text-align:right'>
					<b><?php echo xlt('Fax No'); ?>:&nbsp;</b>
				</td>
				<td>
					<input type='text' id="fax_number" name="fax_number" class='form-control' value='<?php echo isset($faxno) ? $faxno : '' ?>' disabled />
				</td>
			</tr>
			<tr>
				<td style='text-align:right'>
					<b><?php echo xlt('Receivers Name'); ?>:&nbsp;</b>
				</td>
				<td>
					<input type='text' id="rec_name" name="rec_name" class='form-control' value='<?php echo isset($rec_name) ? $rec_name : '' ?>' disabled />
				</td>
			</tr>
			<tr>
				<td style='text-align:right'>
					<b><?php echo xlt('From'); ?>:&nbsp;</b>
				</td>
				<td>
					<input type='text' class='form-control' value='<?php echo $user_name ?>' disabled />
				</td>
			</tr>			
		</table>
	</header>

	<main>
		<textarea id="content" name="content" class='form-control' ><?php echo $content ?></textarea>
	</main>

	<?php if($readonly === 1) { ?>
	<?php echo EmailMessage::htmlDocFileList($docsList, $docsData, ($msg ? $msg : array())); ?>
	<?php } else if($form_action == "resend") { ?>
	<div class="attachmentContainer">
		<div id="itemsContainer" class="file-items-container mt-3 mb-3" role="alert"></div>
	</div>
	<?php } else { ?>
	<div class="attachmentContainer">
		<div id="itemsContainer" class="file-items-container mt-3 mb-3" role="alert"></div>
		<div class="btn-group" role="group">
			<span class="btn btn-primary btn-flie-b btn-file">
			    Upload File  <input type="file" name="files1" multiple onChange="attachClassObject.fileUploader(event)" />
			</span>
			<button type="button" class="btn btn-primary" id="select_document" onClick="attachClassObject.handleDocument('<?php echo $pid; ?>')"><?php echo xlt('Select Documents'); ?></button>
			<button type="button" class="btn btn-primary" id="select_encounters" onClick="attachClassObject.handleEncounterForm('<?php echo $pid; ?>')"><?php echo xlt('Select Encounters & Forms'); ?></button>
			<button type="button" class="btn btn-primary" id="select_encounters_1" onClick="attachClassObject.handleDemosIns('<?php echo $pid; ?>')"><?php echo xlt('Demos and Ins'); ?></button>
			<button type="button" class="btn btn-primary" id="select_order" onClick="attachClassObject.handleOrder('<?php echo $pid; ?>')"><?php echo xlt('Order'); ?></button>
		</div>
	</div>

	<?php } ?>

	<div id="send_spinner_container">
		<div id="send_spinner" class="notification" style="position:absolute;color:white;font-weight:bold;padding:20px;border-radius:10px;background-color:red;left:45%;top:40%;z-index:850;">
			Processing...
		</div>
	</div>
					
	<footer style="border:none">
		<form id="resend_form">
			<input type="hidden" id="re_msgId" name="msgId" value="<?php echo $form_msgId ?>" />
			<input type="hidden" id="re_pid" name="pid" value="<?php echo $form_pid ?>" />
			<input type="hidden" id="re_form_action" name="action" value="<?php echo $form_action ?>" />
		</form>
		<form>
			<input type="hidden" id="mode" name="mode" value="" />
			<input type="hidden" id="id" name="id" value="<?php echo $form_id ?>" />
			<input type="hidden" id="pid" name="pid" value="<?php echo $form_pid ?>" />
			
			<div style="float:right;width:20%;text-align:right;">
			<?php if($form_action == 'resend') { ?>
				<input id="send_fax" class="btn btn-primary" type="button" onclick="ajaxTransmitWithFile('fax')" style="margin:10px 0 5px;padding:5px 12px;" value="<?php echo xlt('RESEND FAX'); ?>">
			<?php } else if(!$form_id) { ?>
				<input id="send_fax" class="btn btn-primary" type="button" onclick="ajaxTransmitWithFile('fax')" style="margin:10px 0 5px;padding:5px 12px;" value="<?php echo xlt('SEND FAX'); ?>">
			<?php } ?>
				<!-- br>
				<input id="store_note" type="button" onclick="ajaxTransmit('NOTE')" style="margin:5px 0 10px;padding:5px 12px;" value="PRIVATE NOTE" -->
			</div>
		</form>
	</footer>

	<script type="text/javascript">
		$('#fax_from').change(function(){
			var selectedFaxTypeVal = $(this).children("option:selected").val();
			$('.searchContainer').hide();
			$('#'+selectedFaxTypeVal+'_container').css('display', 'table-row');;
			
			var fax_val = "";
			var rec_val = "";
			var isDisabled = true;
			if(selectedFaxTypeVal == "patient") {
				var fax_val = '<?php echo $pat_data->fax_number; ?>'; 
				var rec_val = decodeHTMLEntities('<?php echo htmlspecialchars($pat_data->format_name, ENT_QUOTES); ?>'); 
			} else if(selectedFaxTypeVal == "custom") {
				isDisabled = false;
			}
			$('#'+selectedFaxTypeVal).val("");
			$('#fax_number').val(fax_val).attr("disabled", isDisabled);
			$('#rec_name').val(rec_val).attr("disabled", isDisabled);
		});

		//Help to decode HTML entities 
		function decodeHTMLEntities (str) {
			var element = document.createElement('div');
		    if(str && typeof str === 'string') {
		      // strip script/html tags
		      str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
		      str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
		      element.innerHTML = str;
		      str = element.textContent;
		      element.textContent = '';
		    }

		    return str;
		}

		// This is for callback by the find-addressbook popup.
		function setAddressBook(id, name, fax) {
			$('#address_book').val(name);
			$('#rec_name').val(name);
			$('#fax_number').val(fax);
		}

		// This invokes the find-addressbook popup.
		function sel_addressbook_fax() {
			var url = '<?php echo $GLOBALS['webroot']."/interface/main/attachment/find_addressbook_popup.php?pid=". $pid; ?>&pagetype=fax';
		  	let title = '<?php echo xlt('Address Book Search'); ?>';
		  	dlgopen(url, 'findAddressbook', 1100, 500, '', title);
		}

		// This is for callback by the find-insurance_companies popup.
		function setInsurancecompanies(id, name, fax) {
			$('#insurance_companies').val(name);
			$('#rec_name').val(name);
			$('#fax_number').val(fax);
		}

		// This invokes the find-insurance_companies popup.
		function sel_insurancecompanies_fax() {
			var url = '<?php echo $GLOBALS['webroot']."/interface/main/attachment/find_insurancecompanies_popup.php?pid=". $pid; ?>&pagetype=fax';
		  	let title = '<?php echo xlt('Insurance Companies Search'); ?>';
		  	dlgopen(url, 'findInsurancecompanies', 1100, 500, '', title);
		}
	</script>
	<script type="text/javascript">
		var tinymceReadOnly = <?php echo $readonly; ?>;
    	var tinyMCE = tinymce.init({
			entity_encoding : "raw",
			selector: "#content",
			setup: function (editor) {
		        editor.on('change', function () {
		            editor.save();
		        });
		    },
		    readonly : tinymceReadOnly,
			theme : "modern",
			mode : "exact",
			br_in_pre : false,
			force_br_newlines : true,
			force_p_newlines : false,
			forced_root_block : false,
			relative_urls : false,
			document_base_url : "<?php echo $GLOBALS['web_root'] ?>/",
			plugins  : "visualblocks visualchars image link media template code codesample table hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern",
			toolbar1 : "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
			toolbar2 : "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
			toolbar3 : "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | visualchars visualblocks nonbreaking template pagebreak restoredraft | code",
//			toolbar1 : "formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat",
			toolbar_items_size : "small",
			templates : [
				{ title: 'PDF Document', description: 'Default layout for PDF documents', url: 'pdf_template.html' }
			],
			menubar : false
		});

		// background update process
		function ajaxTransmitWithFile(type) {
			top.restoreSession();
			
			var _msg = '';
			if($('#content').val() == '') {
				if(_msg) _msg = '' + _msg + "\n";
				_msg += 'You must include text in the message.';
			}
			if($('#fax_number').val() == '') {
				if(_msg) _msg = '' + _msg + "\n";
				_msg += 'You must enter fax number.';
			}
			if($('#rec_name').val() == '') {
				if(_msg) _msg = '' + _msg + "\n";
				_msg += 'You must enter receivers name.';
			}
			if(_msg) {
				alert(_msg);
				return false;
			}
			

			// show spinner
			$('#send_spinner_container').show();

			// organize the data
			var status = '';
			var formData = new FormData(); // Currently empty
			formData.append('mode', 'transmit');
			formData.append('message', $('#message').val());
			formData.append('pid', $('#pid').val());
			formData.append('fax_number', $('#fax_number').val());
			formData.append('rec_name', $('#rec_name').val());
			
			formData.append('content', tinymce.get('content').getContent());

			//Extra param
			formData.append('fax_from', $('#fax_from').val());
			formData.append('address_book', $('#address_book').val());
			formData.append('insurance_companies', $('#insurance_companies').val());

			attachClassObject.appendDataToForm(formData);

   			// run request
 			$.ajax ({
				type: "POST",
				url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/fax_message.php?submod=check'.$requestStr; ?>",
				processData: false,
            	contentType: false,
				data: formData,
				success: function(resultStr) {
					//console.log(resultStr);
					var result = JSON.parse(resultStr);

		 			if (result.status == true) {

		 				if(result.data.cost_data != undefined && result.data.cost_data.alert) {
		 					alert(result.data.cost_data.alert);
		 					ajaxTransmitConfirm(formData, result.data);
		 				}

		 				if(result.data.cost_data != undefined && result.data.cost_data.confirm) {
		 					var confirmResult = confirm(result.data.cost_data.confirm);

		 					if(confirmResult == true) {
		 						ajaxTransmitConfirm(formData, result.data);
		 					} else {
		 						$('#send_spinner_container').hide();
		 					}
		 				}

		 				if(result.data.cost_data != undefined && result.data.cost_data.noalert) {
		 					ajaxTransmitConfirm(formData, result.data);
		 				}

		 				if(result.data.cost_data != undefined && result.data.cost_data.error) {
		 					$('#send_spinner_container').hide();

							// Display error condition
				 	 		alert(result.data.cost_data.error);
		 				}

		 			} else if (result.status == false) {
		 				$('#send_spinner_container').hide();

						// Display error condition
			 	 		alert(result.error);
		 			} 				
				},
				error: function() {
					$('#send_spinner_container').hide();
					alert('Send Failed...')
				}, 	 					

				async:   true
			});
		}

		// background update process confirm
		function ajaxTransmitConfirm(formData, prevData) {
			
			//Added prevData
			formData.append("prevData", JSON.stringify(prevData));

			// run request
 			$.ajax ({
				type: "POST",
				url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/fax_message.php?submod=confirm'.$requestStr; ?>",
				processData: false,
            	contentType: false,
				data: formData,
				success: function(resultStr) {
					//console.log(resultStr);
					var result = JSON.parse(resultStr);

					$('#send_spinner_container').hide();

		 			if (result.status == true) {
						if(result.message) {
							alert(result.message);
						}

		 				// Close window and refresh
		 				opener.doRefresh();
						dlgclose();

		 			} else if (result.status == false) {
						// Display error condition
			 	 		alert(result.error);
		 			} 				
				},
				error: function() {
					$('#send_spinner_container').hide();
					alert('Send Failed...')
				}, 	 					

				async:   true
			});
		}

		// background refresh process
		function ajaxRetrieveWithHTML() {
			top.restoreSession();

			if($('#message').val() == '') return true;

			// organize the data
			var data = [];
			data.push({name: "mode", value: "retrieve"});
			data.push({name: "message", value: $('#message').val()});			

 			$.ajax ({
				type: "POST",
				url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/fax_message.php'; ?>",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
		 			if (result.content == 'error') {
	 	 				alert('Retrieve Failed...');
	 				} else {
 	 					$('#content').val(result.content_html);
 	 					tinymce.get('content').setContent(result.content_html);

						$('#content').animate({
							scrollTop: 0
						});
	 				}
				},
				error: function() {
					alert('Retrieve Failed...');
				}, 	 					

				async:   true
			});
		}
    </script>

	<script>
		var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
		var refreshTimer = null;

		<?php include_once($GLOBALS['srcdir']."/restoreSession.php"); ?> 
	
		<?php include_once($GLOBALS['srcdir']."/wmt-v2/ajax/init_ajax.inc.js"); ?> 
		function backToMessages() {
			window.location.sasign('../../patient_file/summary/messages_full.php?mode=fax_update'); 
		}

		// setup jquery exit check
		$(document).ready(function(){
			// scroll to bottom
			$('#content').animate({
				scrollTop: 0
			});

			<?php if($readonly !== 1) { ?>
				//Init load
				ajaxRetrieveWithHTML();
			<?php } ?>

			// message selection
			$('#message').change(ajaxRetrieveWithHTML);

		});

		// setup jquery exit check
		$(document).ready(function(){
			<?php if(isset($default_fax_from)) { ?>
				$('#fax_from').val('<?php echo $default_fax_from; ?>').trigger('change');
			<?php } ?>

			<?php if(isset($default_address_book)) { ?>
				$('#address_book').val('<?php echo $default_address_book; ?>');
			<?php } ?>

			<?php if(isset($default_insurance_companies)) { ?>
				$('#insurance_companies').val('<?php echo $default_insurance_companies; ?>');
			<?php } ?>

			<?php if(isset($rec_name)) { ?>
				$('#rec_name').val("<?php echo $rec_name; ?>");
			<?php } ?>

			<?php if(isset($faxno)) {
			 ?>
				$('#fax_number').val('<?php echo $faxno; ?>');
			<?php } ?>
		});

		<?php if($form_action == "resend") { ?>
			$(document).ready(function(){
				$('.fileList .removeUploadFile').remove();
				$('.fileList .removeDocumentFile').remove();
				$('.fileList .removeEncountersFile').remove();
				$('.fileList .removeEncountersInsFile').remove();
			});
		<?php } ?>
		
	</script>
</body>

</html>
