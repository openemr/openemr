<?php
/** *****************************************************************************************
 *	email_message.php
 *
 *	Copyright (c)2019 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage messages
 *  @version 1.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

// Sanitize escapes
$sanitize_all_escapes = true;

// Stop fake global registration
$fake_register_globals = false;

require_once("../../globals.php");
require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
if(!isset($GLOBALS['wmt::use_email_direct'])) $GLOBALS['wmt::use_email_direct'] = '';

use OpenEMR\Core\Header;

require_once($GLOBALS['fileroot']. '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
require_once($GLOBALS['fileroot']. '/vendor/phpmailer/phpmailer/src/SMTP.php');
require_once($GLOBALS['fileroot']. '/vendor/phpmailer/phpmailer/src/Exception.php');

//Included EXT_Message File
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\Attachment;

// Determine processing mode
$form_id = trim(strip_tags($_REQUEST['id']));
$form_pid = trim(strip_tags($_REQUEST['pid']));
$form_mode = trim(strip_tags($_REQUEST['mode']));
$form_message = trim(strip_tags($_REQUEST['message']));
$form_content = isset($_REQUEST['content']) ? $_REQUEST['content'] : "";
$form_subject = trim(strip_tags($_REQUEST['subject']));

$form_action = trim(strip_tags($_REQUEST['action']));
$form_msgId = trim(strip_tags($_REQUEST['msgId']));

$form_email_id = trim(strip_tags($_REQUEST['email_id']));
$form_custom_email_id = trim(strip_tags($_REQUEST['custom_email_id']));
$form_custom_email_check = trim(strip_tags($_REQUEST['custom_email_check']));

$form_custom_email_check = trim(strip_tags($_REQUEST['custom_email_check']));
$form_enable_btn = trim(strip_tags($_REQUEST['enable_btn']));

$requestStr = '';
if(!empty($form_action) && !empty($form_msgId)) {
	$requestStr = http_build_query(array('action' => $form_action, 'msgId' => $form_msgId));
}

// Option lists
$message_list = new wmt\Options('Email_Messages');

// Message content
$data = '';
$content = '';
$subject = '';

// Validate Patient
if (isset($form_pid) && !empty($form_pid)) $pid = $form_pid;
if (empty($pid)) $pid = $_SESSION['pid'];

//Set pid for replay
if($form_action == "reply") {
	$pid = $form_pid;
}

function getVals($value) {
	return isset($value) ? $value : "";
}

$email_list = array();
$pat_name = " ";
$email_direct = "";
$messaging_enabled = true;
$pat_data = new stdClass();

if(!empty($pid)) {

	// Retrieve patient
	$pat_data = wmt\Patient::getPidPatient($pid);
	$pat_name = $pat_data->format_name;

	$email_direct = $GLOBALS['wmt::use_email_direct'] ? $pat_data->email_direct : $pat_data->email;
		
	if(!empty($email_direct)) {
		$email_list[] = $email_direct;
	}

	if(!empty($pat_data->secondary_email)) {
		$email_list = array_merge($email_list, explode(",",$pat_data->secondary_email));
	}

	$messaging_enabled = ($pat_data->hipaa_allowemail != 'YES' || (empty($pat_data->email) && !$GLOBALS['wmt::use_email_direct']) || (empty($pat_data->email_direct) && $GLOBALS['wmt::use_email_direct'])) && empty($form_id) ? false : true;

	if($form_action == "reply") {
		$messaging_enabled = true;
	}
}


// Set TO/FROM
$to = $pat_name;
$from = isset($GLOBALS['EMAIL_SEND_FROM']) ? $GLOBALS['EMAIL_SEND_FROM'] : 'PATIENT SUPPORT';


// Retrieve user
$user_data = sqlQueryNoLog("SELECT CONCAT(LEFT(`fname`,1), '. ',`lname`) AS 'name' FROM `users` WHERE `id` = ?", array($_SESSION['authUserID']));
$user_name = (empty($user_data['name']))? 'PORTAL SUPPORT' : $user_data['name'];


$email_content = '';
$email_content_html = '';
$email_subject = '';

$readonly = 0;
if($form_id) {
	$readonly = 1;
}

//Defualt Data
$default_message = 'free_text';
$default_custom_email_check = "false";

//Get Full Message 
if($form_action == "reply" && !empty($form_msgId)) {
	//Get Message By MsgID
	$msgResponce = EmailMessage::getMsgLogHTML($form_msgId);
	if($msgResponce != false) {
		$email_content .= $msgResponce['content'];
		$email_content_html .= $msgResponce['content_html'];
	}

	$messageData = EmailMessage::getMessageById($form_msgId);

	$variableList = array(
		'default_subject' => 'subject',
		'default_custom_email_check' => 'custom_email_check'
	);
	if(!empty($messageData[0]['raw_data'])) {
		$previousData = json_decode($messageData[0]['raw_data'], true);
	}
	extract(EmailMessage::extractVariable($previousData, $variableList));
	
	$to_email = '';
	if(count($messageData) > 0) {
		if($messageData[0]['direction'] == "out") {
			$to_email = isset($messageData[0]['msg_to']) ? $messageData[0]['msg_to'] : "";
		} else if($messageData[0]['direction'] == "in") {
			$to_email = isset($messageData[0]['msg_from']) ? $messageData[0]['msg_from'] : "";	
		}
		$email_subject = $messageData[0]['event'];
	}
} else if($form_action == "resend" && !empty($form_msgId)) {
	$messageData = EmailMessage::getMessageById($form_msgId);

	$variableList = array(
		'default_pid' => 'pid',
		'default_message' => 'message',
		'default_email_id' => 'default_email_id',
		'default_subject' => 'subject',
		'default_baseDocList' => 'baseDocList',
		'default_attachments' => 'attachments',
		'default_custom_email_id' => 'custom_email_id',
		'default_custom_email_check' => 'custom_email_check'
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
		$email_content = $msgContent['content'];
		$email_content_html = $msgContent['content_html'];

		if($default_pid == $pid) {
			if($messageData[0]['direction'] == "out") {
				$to_email = isset($messageData[0]['msg_to']) ? $messageData[0]['msg_to'] : "";
			} else if($messageData[0]['direction'] == "in") {
				$to_email = isset($messageData[0]['msg_from']) ? $messageData[0]['msg_from'] : "";	
			}
		}
		$email_subject = $default_subject;
	}
} else if(isset($form_id)) {
	$docsData = MessagesLib::getMsgDocs($form_id);
	$docsList = EmailMessage::generateDocList($docsData);
}

// Ajax check for new messages 
if ($form_mode == 'retrieve') {
	
	if($form_message != 'free_text') {
	// Retrieve content
	try {

		// Get message template
		$template = wmt\Template::Lookup($form_message, getVals($pat_data->language));
		
		// Fetch merge data
		$data = new wmt\Grab(getVals($pat_data->language));
		$data->loadData(getVals($pat_data->pid), $_SESSION['authId']);
		
		// Perform data merge
		$template->Merge($data->getData());
		$content = $template->text_merged;
		$content_html = $template->html_merged;
		
		// Deal with imbedded links
		$content = str_replace('<br>', "\n", $content);
		$content = str_replace('http:', 'https:', $content);
		$content = str_replace('target="_blank"', '', $content);
		$content = str_replace("target='_blank'", '', $content);

		
		$contact .= $email_content;
		$content_html .= $email_content_html;
		//$subject = $message_list->getItem($form_message);

		$subject = '';
		if(isset($message_list->list[$form_message])) {
			$subject = $message_list->list[$form_message]['notes'];
			$subject = $template->MergeText($data->getData(), $subject);
		}

	} catch (Exception $e) {
		$content = $e->getMessage();
	}
	} else {
		$contact .= $email_content;
		$content_html .= $email_content_html;
		$subject = '';
	}
	
	// Return new messages
	echo json_encode(array('content'=>$content, 'content_html'=>$content_html, 'subject' => $subject));
	
	// Done with ajax
	exit();
}
	
// Ajax transmit new message
if ($form_mode == 'transmit') {

	$returnStatus = array();
	$cList = array("legal_team", "custom_email");
	$eItems = array();
	$errorData = array();

	try {

		//Assign Email id
		if($form_custom_email_check != "" && $form_custom_email_check !== false && in_array($form_custom_email_check, $cList)) {
			$form_email_id = array_map('trim', explode(",", $form_email_id));
		} else {
			$form_email_id = array($form_email_id);
		}

		$emailFromName = $GLOBALS['EMAIL_FROM_NAME'];

		if($form_message != 'free_text') {
			// Get message template
			$template = wmt\Template::Lookup($form_message, getVals($pat_data->language));
			
			// Fetch merge data
			$data = new wmt\Grab(getVals($pat_data->language));
			$data->loadData(getVals($pat_data->pid), $_SESSION['authId']);
			
			// Perform data merge
			$template->Merge($data->getData());
			$content = $template->html_merged;
			
			// Deal with imbedded links
			$content = str_replace('http:', 'https:', $content);
			$content = str_replace('target="_blank"', '', $content);
			$content = str_replace("target='_blank'", '', $content);

			$emailContentHTML = $content;
			$emailContentTEXT = $template->text_merged;
		} else {
			$emailContentHTML = $form_content;
			$emailContentTEXT = $form_content;
		}

		$emailContentHTML = $form_content;
		$emailContentTEXT = trim(strip_tags($emailContentHTML));

		$eItem = array(
			'pid' => $pid,
			'data' => array(
				'from' => $from,
				'email' => $form_email_id,
				'template' => $form_message,
				'subject' => $form_subject,
				'patient' => $to,
				'html' => $emailContentHTML,
				'text' => $emailContentTEXT,
				'request_data' => $_REQUEST,
				'files' => $_FILES,
			));
		
		$eData = EmailMessage::TransmitEmail(
				array($eItem['data']), 
				array('pid' => $eItem['pid'], 'request_data' => $_REQUEST, 'files' => $_FILES, 'logMsg' => true)
			);

		
		if(isset($eData)) {
			foreach ($eData as $edk => $edItem) {
				if($edItem['status'] === true) {

					if(isset($edItem['data'])) {
						foreach ($edItem['data'] as $eddk => $edDataItem) {
							$msgLogId = $edDataItem['msgid'];
							$toEmailItem = $edDataItem['to'];

							if($form_action == "reply" && !empty($form_msgId) && !empty($msgLogId)) {
								EmailMessage::updateStatusOfMsg($form_msgId, false);
							}

							$orderList = (isset($_REQUEST['orders']) && !empty($_REQUEST['orders'])) ? json_decode($_REQUEST['orders'], true) : array();

							if(!empty($orderList)) {
								MessagesLib::addMessageOrderLog($pid, 'EMAIL', $orderList, $msgLogId, $toEmailItem);
							}
						}
					}

					
				}

				if($edItem['status'] === false) {
					if(!empty($edItem['errors'])) {
						$returnStatus[] = implode(",",$edItem['to']) . ": ". implode(",",$edItem['errors']);
					}
				}
			}
		}

	} catch (Exception $e) {
		$status = $e->getMessage();
		$returnStatus[] = $status;
	}

	// Return new messages
	echo json_encode(array('status'=> implode("\n", $returnStatus)));
	exit();
}

$content = $email_content_html;
$content_html = $email_content_html;
$subject = $email_subject;

if($form_id) {
	$msg = sqlQuery('SELECT * FROM `message_log` WHERE `id` = ?', $form_id);
  	//$content = nl2br($msg{'message'});
  	$content = EmailMessage::getMessageContent($msg{'message'}, 2);
  	$subject = $msg{'event'};

  	$variableList = array(
		'default_pid' => 'pid',
		'default_message' => 'message',
		'default_email_id' => 'default_email_id',
		'default_subject' => 'subject',
		'default_baseDocList' => 'baseDocList',
		'default_attachments' => 'attachments',
		'default_custom_email_id' => 'custom_email_id',
		'default_custom_email_check' => 'custom_email_check'
	);

	if(!empty($msg{'raw_data'})) {
		$previousData = json_decode($msg{'raw_data'}, true);
	}

	extract(EmailMessage::extractVariable($previousData, $variableList));

	if($msg{'direction'} == "out") {
		$to_email = isset($msg{'msg_to'}) ? $msg{'msg_to'} : "";
	} else if($msg{'direction'} == "in") {
		$to_email = isset($msg{'msg_from'}) ? $msg{'msg_from'} : "";	
	}
}

?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>	<html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>	<html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />

	<title>Email Message</title>
	<meta name="author" content="Ron Criswell" />
	<meta name="description" content="Email Message" />
	<meta name="copyright" content="&copy;<?php echo date('Y') ?> Williams Medical Technologies, Inc.  All rights reserved." />

	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<script type="text/javascript">
		var basePath = '<?php echo $rootdir; ?>';
	</script>
  	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'jquery-ui-base', 'fontawesome', 'main-theme', 'oemr_ad']); ?>

  	<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/tinymce/tinymce.min.js"></script>
  	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/interface/main/attachment/js/attachment.js"></script>

	<link rel="shortcut icon" href="images/favicon.ico" />

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
	    .files .fileList {
	    	padding-left: 20px;
			margin-top: 15px;
			margin-bottom: 20px;
			float: left;
	    }
	    textarea.form-control {
	    	height: 100px!important;
	    }
	    #send_email {
	    	float: right;
	    }
	    .containerEmail {
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
	    .includeDemoContainer {
	    	display: grid;
			grid-template-columns: auto 1fr;
			padding: 10px;
	    }
	    .include_demo {
	    	margin-left: 10px !important;
	    }
	    .hide {
	    	display: none;
	    }
	</style>

	<style>
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
		.customContainer {
			padding-left: 10px;
		}
		.custom_email_container,
		.custom_email_container .select_abook {
			display: none;
		}
		.custom_email_container.cemail .select_abook {
			display: block !important;
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
	<div style="width: 100%; max-width: 650px;">
		<div class="form-row">
		    <div class="form-group col-md-8">
		      	<label><?php echo xlt('Message'); ?></label>
		      	<?php if($readonly === 1) { ?>
				<select id="message" name="message" class='form-control' disabled>
					<?php $message_list->showOptions($default_message) ?>
				</select>
				<?php } else { ?>
				<select id="message" name="message" class='form-control'>
					<?php $message_list->showOptions($default_message) ?>
				</select>
				<?php } ?>
		    </div>
		    <div class="form-group col-md-4">
		      	<label><?php echo xlt('Patient'); ?></label>
		      	<?php if($form_action == "resend") { ?>
					<input type='text' class='form-control disabled' value='<?php echo getVals($pat_data->format_name) ?>' onClick="selectPatientButton()" readonly />
				<?php } else { ?>
					<input type='text' class='form-control' value='<?php echo getVals($pat_data->format_name) ?>' disabled />
				<?php } ?>
		    </div>
		</div>

		<div class="form-row">
		    <div class="form-group col-md-6">
		    	<label><?php echo xlt('To Email'); ?></label>
	    		<?php if($form_action == "reply" || $readonly === 1 ) { ?>
					<input type='text' name="email_id" id="email_id" class='form-control' value='<?php echo $to_email ?>' disabled />
				<?php } else { ?>
				<select name="email_id" id="email_id" class='form-control select_email_list'>
					<?php if(empty($email_list)) { ?>
						<option value=""></option>
					<?php 
					} else {
						foreach ($email_list as $value) {
						 	?>
						 	<option value="<?php echo $value; ?>"><?php echo $value; ?></option>
						 	<?php
						} 
					 } ?>
					 	<option value="custom_email">Custom Email</option>

					 	<?php if(!empty($form_pid)) { ?>
					 		<option value="legal_team">Legal team</option>
					 	<?php } ?>
				</select>
				<?php } ?>
		    </div>

		    <div class="form-group col-md-6">
		    	<label><?php echo xlt('From'); ?></label>
		    	<input type='text' class='form-control' value='<?php echo $user_name ?>' disabled />
		    </div>

		</div>

		<div class="custom_email_container">
			<div class="form-row">
				<div class="form-group col-md-6">
					<input type='text' name="custom_email_id" id="custom_email_id" class="form-control" value='<?php echo (isset($default_custom_email_id) && $default_custom_email_id != 'undefined') ? $default_custom_email_id : ''; ?>' placeholder="<?php echo xlt('Enter custom email'); ?>" />
			    </div>
			    <div class="form-group col-md-6">
			    	<a class='medium_modal select_abook btn btn-primary' style="max-width: 220px;" href='<?php echo $GLOBALS['webroot']. '/interface/forms/cases/php/find_user_popup.php?allow_multi_select=true'; ?>'><span> <?php echo xlt('Select from address book'); ?></span></a>
				</div>
			</div>
		</div>

		<div class="form-group">
			<label><?php echo xlt('Subject'); ?></label>
			<?php if($readonly === 1) { ?>
				<input type='text' class='form-control' name='subject' id='subject' value='<?php echo $subject; ?>' disabled />
			<?php } else { ?>
				<input type='text' class='form-control' name='subject' id='subject' value='<?php echo $subject; ?>' />
			<?php } ?>
		</div>
	</div>

	<div>
		<div class="form-group">
			<textarea id="content" name="content" class='form-control' ><?php echo $content ?></textarea>
		</div>
	</div>
	
	<?php if($readonly === 1) { ?>
	<?php echo EmailMessage::htmlDocFileList($docsList, $docsData, ($msg ? $msg : array())); ?>
	<?php } else if($form_action == "resend") { ?>
	<?php //echo EmailMessage::getFileContainer($pid); ?>
	<div id="itemsContainer" class="file-items-container mt-3 mb-3" role="alert"></div>
	<?php } else { ?>

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
			<input type="hidden" id="form_action" name="form_action" value="<?php echo $form_action ?>" />
			<input type="hidden" id="messaging_enable_input" name="messaging_enable_input" value="<?php echo $messaging_enabled ?>" />

			<div id="actionBtnContainer1" class="<?php echo ($messaging_enabled === true || $default_custom_email_check !== "false") ? 'hide' : '' ?>" style="text-align:center;width:100%;padding:20px 0;">
				<div class="alert alert-danger" role="alert">
				  <?php echo xlt('This patient has not approved email contact or no valid email address is present, email messaging is disabled'); ?>!
				</div>
			</div>
			<div id="actionBtnContainer2" class="<?php echo ($messaging_enabled === false && $default_custom_email_check === "false") ? 'hide' : '' ?>" style="float:right;width:20%;text-align:right;">
			<?php if($form_enable_btn == "reply") { ?>
				<input id="reply_email" type="button" class="btn btn-primary" onclick="replyEmailButton('<?php echo $form_pid ?>', '<?php echo $form_id ?>')" style="margin:10px 0 5px;padding:5px 12px;float: right;" value="<?php echo xlt('Reply'); ?>">
			<?php } ?>
			<?php if($form_action == 'resend') { ?>
				<input id="send_email" type="button" class="btn btn-primary" onclick="ajaxTransmitWithFile('email')" style="margin:10px 0 5px;padding:5px 12px;" value="<?php echo xlt('RESEND EMAIL'); ?>">
			<?php } else if(!$form_id) { ?>
				<input id="send_email" type="button" class="btn btn-primary" onclick="ajaxTransmitWithFile('email')" style="margin:10px 0 5px;padding:5px 12px;" value="<?php echo xlt('SEND EMAIL'); ?>">
			<?php } ?>
				<!-- br>
				<input id="store_note" type="button" onclick="ajaxTransmit('NOTE')" style="margin:5px 0 10px;padding:5px 12px;" value="PRIVATE NOTE" -->
			</div>
		</form>
	</footer>

	<script type="text/javascript">


		/*Custom email check*/
		$("#email_id").change(function() {
			var isMessagingEnable = $('#messaging_enable_input').val();

			if($(this).val() == "legal_team") {
				getLegalTeamEmails($('#pid').val());
			}

			if($(this).val() == "custom_email" || $(this).val() == "legal_team") {
				$(".custom_email_container").attr('class', 'custom_email_container');
				if($(this).val() == "custom_email") {
					$(".custom_email_container").addClass('cemail');
				}
				$('.custom_email_container').show();

				if(isMessagingEnable == 0) {
					$('#actionBtnContainer1').addClass("hide")
					$('#actionBtnContainer2').removeClass("hide");
				}
			} else {
				$(".custom_email_container").attr('class', 'custom_email_container');
				$('.custom_email_container').hide();

				if(isMessagingEnable == 0) {
					$('#actionBtnContainer1').removeClass("hide")
					$('#actionBtnContainer2').addClass("hide");
				}
			}
		});

		function arrayUnique(array) {
		    var a = array.concat();
		    for(var i=0; i<a.length; ++i) {
		        for(var j=i+1; j<a.length; ++j) {
		            if(a[i] === a[j])
		                a.splice(j--, 1);
		        }
		    }

		    return a;
		}

		function setLegalTeamEmails(caseItem) {
			let emailsAddress = caseItem['notes'];
			if(emailsAddress != "") {
				let cEmailValue = $('#custom_email_id').val();
				let cEmailList = cEmailValue != "" ? cEmailValue.split(",").map(function(item) {return item.trim();}) : [];
				let em_list = emailsAddress.split(",").map(element => element.trim());
				//let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
				let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w+)+$/;
				let newEmList = [];

				em_list.forEach((eItem, ei) => {
					if(eItem != "" && eItem.match(mailformat)) {
						newEmList.push(eItem);
					}
				});

				//Merge two array
				cEmailList = arrayUnique(cEmailList.concat(newEmList));

				if(cEmailList.length > 0) {
					$('#custom_email_id').val(cEmailList.join(","));
				}
			} else {
				alert('Empty email address');
			}
		}

		function setMulticase(caseList = []) {
			if(Array.isArray(caseList) && caseList.length === 1) {
				let caseItem = caseList[0];
				setLegalTeamEmails(caseItem);
			}
		}

		async function getLegalTeamEmails(pid) {
			if(pid != "") {
				var bodyObj = { pid : pid };
				const result = await $.ajax({
					type: "GET",
					url: "<?php echo $GLOBALS['webroot'].'/interface/main/attachment/ajax/get_pitype_insurance.php'; ?>",
					datatype: "json",
					data: bodyObj
				});

				if(result != '') {
					var resultObj = JSON.parse(result);
					if(Array.isArray(resultObj) && resultObj.length > 0) {
						if(resultObj.length === 1) {
							setLegalTeamEmails(resultObj[0]);
						} else {
							dlgopen('', '', 550, 300, '', 'Select Legal Team', {
			            //onClosed: 'refreshme',
			            allowResize: false,
			            allowDrag: true,
			            dialogId: '',
			            type: 'iframe',
			            url: '<?php echo $GLOBALS['webroot'].'/interface/main/attachment/select_pitype_insurance.php?pid='; ?>'+pid
			        });
						}
					}
				}
			}
		}

		//Open popup for patient selection
		function selectPatientButton() {
			dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 800, 400);
		}

		// This is for callback by the find-patient popup.
		function setpatient(pid, lname, fname, dob) {
			var form_action = $('#re_form_action').val();
			if(form_action == "resend") {
				$('#re_pid').val(pid);
				$('#resend_form').submit();
			}
		}


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
			//if($('#message').val() == 'free_text') {
				var _msg = '';
				if($('#email_id').val() == '') _msg = 'Please select email address.';
				if($('#subject').val() == '') _msg = 'Please specify a subject.';
				if($('#content').val() == '') {
					if(_msg) _msg = '      ' + _msg + "\n";
					_msg += 'You must include text in the message.';
				}
				if(_msg) {
					alert(_msg);
					return false;
				}
			//}

			//Custom Email Id Check
			var emailId = $('#email_id').val();
			var isCustomEmailSelected = false;

			if(emailId == "custom_email" || emailId == "legal_team") {
				isCustomEmailSelected = emailId;
				emailId = $('#custom_email_id').val();
			}

			// show spinner
			$('#send_spinner_container').show();

			// organize the data
			var status = '';
			var formData = new FormData(); // Currently empty
			formData.append('mode', 'transmit');
			formData.append('message', $('#message').val());
			formData.append('pid', $('#pid').val());
			formData.append('email_id', emailId);
			formData.append('custom_email_id', $('#custom_email_id').val());
			formData.append('custom_email_check', isCustomEmailSelected);
			
			if($('#message').val() == 'free_text') {
				formData.append('subject', $('#subject').val());
				formData.append('content', tinymce.get('content').getContent());
			} else {
				formData.append('subject', $('#subject').val());
				formData.append('content', tinymce.get('content').getContent());
			}

			attachClassObject.appendDataToForm(formData);

   			// run request
 			$.ajax ({
				type: "POST",
				url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/email_message.php?'.$requestStr; ?>",
				processData: false,
            	contentType: false,
				data: formData,
				success: function(resultStr) {
					var result = JSON.parse(resultStr);

					$('#send_spinner_container').hide();

		 			if (result.status == '') {
		 				// Close window and refresh
		 				opener.doRefresh('<?php echo $form_action ?>');
						dlgclose();

		 			} else {
						// Display error condition
			 	 		alert(result.status);
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
			if($('#message').val() == 'free_text') {
				//$('#subject_tr').css("display", "table-row");
				//$('#content').attr("readonly", false);
				//return true;
			} else {
				//$('#subject_tr').css("display", "none");
				//$('#content').attr("readonly", true);
			}
			if($('#message').val() == '') return true;

			// organize the data
			var data = [];
			data.push({name: "mode", value: "retrieve"});
			data.push({name: "message", value: $('#message').val()});
			data.push({name: "pid", value: $('#pid').val()});			

 			$.ajax ({
				type: "POST",
				url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/email_message.php?'.$requestStr; ?>",
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

						$('#subject').val(result.subject);
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
			window.location.sasign('../../patient_file/summary/messages_full.php?mode=email_update'); 
		}
		
 		// background refresh process
		function ajaxRetrieve() {
			top.restoreSession();
			if($('#message').val() == 'free_text') {
				//$('#subject_tr').css("display", "table-row");
				//$('#content').attr("readonly", false);
				return true;
			} else {
				//$('#subject_tr').css("display", "none");
				//$('#content').attr("readonly", true);
			}
			if($('#message').val() == '') return true;

			// organize the data
			var data = [];
			data.push({name: "mode", value: "retrieve"});
			data.push({name: "message", value: $('#message').val()});			

 			$.ajax ({
				type: "POST",
				url: "email_message.php",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
		 			if (result.content == 'error') {
	 	 				alert('Retrieve Failed...');
	 				} else {
 	 					$('#content').val(result.content);
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

 		// background update process
		function ajaxTransmit(type) {
			top.restoreSession();
			if($('#message').val() == 'free_text') {
				var _msg = '';
				if($('#subject').val() == '') _msg = 'Please specify a subject.';
				if($('#content').val() == '') {
					if(_msg) _msg = '      ' + _msg + "\n";
					_msg += 'You must include text in the message.';
				}
				if(_msg) {
					alert(_msg);
					return false;
				}
			}

			// show spinner
			$('#send_spinner').show();
			
			// organize the data
			var data = [];
			data.push({name: "mode", value: 'transmit'});
			data.push({name: "message", value: $('#message').val()});
			data.push({name: "pid", value: $('#pid').val()});
			if($('#message').val() == 'free_text') {
				data.push({name: "subject", value: $('#subject').val()});			
				data.push({name: "content", value: $('#content').val()});			
			}

			// result
			var status = '';

			// run request
 			$.ajax ({
				type: "POST",
				url: "email_message.php",
				dataType: "json",
				data: $.param(data),
				success: function(result) {
					$('#send_spinner').hide();

		 			if (result.status == '') {
		 				// Close window and refresh
		 				opener.doRefresh();
						dlgclose();

		 			} else {
						// Display error condition
			 	 		alert(result.status);
		 			} 				
				},
				error: function() {
					$('#send_spinner').hide();
					alert('Send Failed...')
				}, 	 					

				async:   true
			});
			
		}

		// setup jquery exit check
		$(document).ready(function(){
			// scroll to bottom
			$('#content').animate({
				scrollTop: 0
			});

			<?php if($form_action != "reply" && $readonly !== 1) { ?>
				//Init Load
				ajaxRetrieveWithHTML();
			<?php } ?>

			// message selection
			$('#message').change(ajaxRetrieveWithHTML);

		});

		// setup jquery exit check
		$(document).ready(function(){
			<?php if($form_action != "reply" && $readonly !== 1 ) { ?>
			<?php if(isset($default_custom_email_check) && $default_custom_email_check !== "false") { ?>
				let dropOptionVal = '<?php echo ($default_custom_email_check === "true" || $default_custom_email_check == "custom_email") ? "custom_email" : $default_custom_email_check; ?>';

				$('#email_id').val(dropOptionVal);

				if(dropOptionVal == "custom_email") {
					$(".custom_email_container").addClass('cemail');
				}	

				$('.custom_email_container').show();
			<?php } else { ?>
			<?php if(isset($to_email)) { ?>
				$('#email_id').val('<?php echo $to_email; ?>').trigger('change');
			<?php } ?>
			<?php } ?>
			<?php } ?>
		});

		function replyEmailButton(pid, messageId) {
			$('#set_id').val(messageId);
		    let title = '<?php echo xla('Reply A Email'); ?><div style="font-size:14px"><b>Disclaimer:</b> Maximum allowed size for attachment is <?php echo EmailMessage::getMaxSize(); ?> mb.</div>';
			    var url = '<?php echo $GLOBALS['rootdir']; ?>/main/messages/email_message.php?pid='+pid+'&msgId='+messageId+"&action=reply";
			
		    dlgopen(url, 'emailPop', 'modal-lg', 'modal-lg', '', title);
		}

		async function doRefresh(paging) {
			dlgclose();
		}

		<?php if($form_action == "resend") { ?>
			$(document).ready(function(){
				$('.fileList .removeUploadFile').remove();
				$('.fileList .removeDocumentFile').remove();
				$('.fileList .removeEncountersFile').remove();
				$('.fileList .removeEncountersInsFile').remove();
			});

		<?php } ?>

		//Set Custom Email From Addressbook
		function setMultiuser(userList = []) {
			let eList = [];
			let validEmailStatus = true;
			//let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
			let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w+)+$/;
			
			if(Array.isArray(userList)) {
				userList.forEach(function(uItem, uIndex) {
					if(uItem['email'] != "" && uItem['email'] != null && uItem['email'].match(mailformat)) {
						eList.push(uItem['email']);
					} else {
						validEmailStatus = false;
					}
				});
			}

			if(validEmailStatus === false) {
				alert("Some selected users doesn't have valid email.");
			}

			if(eList.length > 0) {
				let cEmailValue = $('#custom_email_id').val();
				let cEmailList = cEmailValue != "" ? cEmailValue.split(",").map(function(item) {return item.trim();}) : [];

				//Merge two array
				cEmailList = arrayUnique(cEmailList.concat(eList));

				if(cEmailList.length > 0) {
					$('#custom_email_id').val(cEmailList.join(","));
				}
			}
		}

		$(document).on('click', '.medium_modal', function(e) {
	        e.preventDefault();
	        e.stopPropagation();
	        dlgopen('', '', 700, 400, '', '', {
	            buttons: [
	                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
	            ],
	            //onClosed: 'refreshme',
	            allowResize: false,
	            allowDrag: true,
	            dialogId: '',
	            type: 'iframe',
	            url: $(this).attr('href')
	        });
	    });

	</script>
</body>

</html>
