<?php

include_once("../../../globals.php");
include_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\Caselib;
use OpenEMR\OemrAd\Smslib;
use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\FaxMessage;
use OpenEMR\OemrAd\PostalLetter;

// Option lists
$postal_message_list = new wmt\Options('Postal_Letters');

$selected_rp_list = isset($_REQUEST['selected_rp']) ? json_decode($_REQUEST['selected_rp'], true) : array();
$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : "";

$pat_data = wmt\Patient::getPidPatient($pid);
$pat_name = $pat_data->format_name;

$finalList = array();
$up_count = 0;
foreach ($selected_rp_list as $rpl => $rp_item) {
	if($rp_item['communication_mode'] == "email") {
		if(!empty($rp_item['template']) && $rp_item['template'] != 'free_text' && !empty($rp_item['email'])) {
			$finalList[] = $rp_item;
			$up_count++;
		}
	} else if($rp_item['communication_mode'] == "sms") {
		/*if(!empty($rp_item['template']) && $rp_item['template'] != 'free_text' && !empty($rp_item['mobile'])) {
			$finalList[] = $rp_item;
		}*/
	} else if($rp_item['communication_mode'] == "fax") {
		if(!empty($rp_item['template']) && $rp_item['template'] != 'free_text' && !empty($rp_item['fax'])) {
			$finalList[] = $rp_item;
			$up_count++;
		}
	} else if($rp_item['communication_mode'] == "postal_letter") {
		if(!empty($rp_item['template']) && $rp_item['template'] != 'free_text' && !empty($rp_item['address'])) {
			$finalList[] = $rp_item;
			$up_count++;
		}
	}
}

function getMessageListObj($type) {
	$message_list = new wmt\Options('CareTeam_Communication_Templates');

	// if($type == "email") {
	// 	$message_list = new wmt\Options('Care_Team_Providers_Email_Messages');
	// } else if($type == "sms") {
	// 	$message_list = new wmt\Options('Care_Team_Providers_SMS_Messages');
	// } else if($type == "fax") {
	// 	$message_list = new wmt\Options('Care_Team_Providers_Fax_Messages');
	// }
	
	return $message_list;
}

function getVals($value) {
	return isset($value) ? $value : "";
}

$c_up_count = 0;
$total_sent = 0;
$total_failed = 0;

$eItems = array();
$fItems = array();

foreach ($finalList as $rk => $rpItem) {
	$message_list = getMessageListObj($rpItem['communication_mode']);
	//$subject = $message_list->getItem($rpItem['template']);

	$subject = '';
	if(isset($message_list->list[$rpItem['template']])) {
		$subject = $message_list->list[$rpItem['template']]['notes'];
	}

	$rp_data = Caselib::getRpData(array($rpItem['id']));
	$rp_data = !empty($rp_data) ? $rp_data[0] : array();

	$rp_name = $rp_data['fname'] . " " . $rp_data['lname'];

	if(!empty($rpItem['template']) && $rpItem['template'] != 'free_text') {
		// Get message template
		$template = wmt\Template::Lookup($rpItem['template'], getVals($pat_data->language));

		//Fetch merge data
		$data = new wmt\Grab(getVals($pat_data->language));
		$data->loadData(getVals($pat_data->pid), $_SESSION['authId']);
		
		if(!empty($subject)) {
			$subject = $template->MergeText($data->getData(), $subject);
		}
		
		// Perform data merge
		$template->Merge($data->getData());
		$content = $template->html_merged;
		
		// Deal with imbedded links
		$content = str_replace('http:', 'https:', $content);
		$content = str_replace('target="_blank"', '', $content);
		$content = str_replace("target='_blank'", '', $content);
	}

	if($rpItem['communication_mode'] == "email") {
		if(!empty($rpItem['template']) && $rpItem['template'] != 'free_text') {
			$eItems[] = array(
				'pid' => $pat_data->pid,
				'data' => array(
					'email' => $rpItem['email'],
					'template' => $rpItem['template'],
					'subject' => $subject,
					'patient' => $rp_name,
					'html' => $content,
					'text' => $template->text_merged,
					'request_data' => $_REQUEST,
					'files' => $_FILES,
				));
		}
	} else if($rpItem['communication_mode'] == "sms") {
		/*if(!empty($rpItem['template']) && $rpItem['template'] != 'free_text') {
			$sms_data = array(
				'to_phone' => $rpItem['mobile'],
				'template' => $rpItem['template'],
				'html' => $content,
				'text' => $template->text_merged
			);

			$sms_status = smsSend($sms_data, true);

			if(isset($sms_status['status']) && empty($sms_status['status'])) {
				$total_sent++;
			} else {
				$toal_failed++;
			}
		}*/
	} else if($rpItem['communication_mode'] == "fax") {
		if(!empty($rpItem['template']) && $rpItem['template'] != 'free_text') {
			$fItems[] = array(
				'pid' => $pat_data->pid,
				'data' => array(
					'template' => $rpItem['template'],
					'fax_number' => $rpItem['fax'],
					'receiver_name' => $rp_name,
					'html' => $content,
					'text' => $template->text_merged,
					'fax_from_type' => 'custom',
					'request_data' => $_REQUEST,
					'files' => $_FILES,
				));
		}
	} else if($rpItem['communication_mode'] == "postal_letter") {
		if(!empty($rpItem['template']) && $rpItem['template'] != 'free_text') {
			$pItems[] = array(
				'pid' => $pat_data->pid,
				'data' => array(
					'template' => $rpItem['template'],
					'html' => $content,
					'text' => $template->text_merged,
					'address' => $rpItem['address'],
					'address_json' => $rpItem['address_json'],
					'reply_address' => $GLOBALS['POSTAL_LETTER_REPlY_ADDRESS'],
					'reply_address_json' => $GLOBALS['POSTAL_LETTER_REPlY_ADDRESS_JSON'],
					'receiver_name' => $rp_name,
					'address_from_type' => 'custom',
					'base_address' => $rpItem['address'],
					'request_data' => $_REQUEST,
					'files' => $_FILES,
				));
		}
	}
}

// Send email
foreach ($eItems as $eik => $eItem) {
	$eData = EmailMessage::TransmitEmail(
				array($eItem['data']), 
				array('pid' => $eItem['pid'], 'request_data' => $_REQUEST, 'files' => $_FILES, 'logMsg' => true)
			);

	if(isset($eData)) {
		foreach ($eData as $edk => $edItem) {
			if($edItem['status'] === true) $total_sent++;
			if($edItem['status'] !== true) $total_failed++;
		}
	}
}

// Send fax
foreach ($fItems as $fik => $fItem) {
	$fData = FaxMessage::TransmitFax(
				array($fItem['data']), 
				array('pid' => $fItem['pid'], 'request_data' => $_REQUEST, 'files' => $_FILES, 'logMsg' => true)
			);

	if(isset($fData)) {
		foreach ($fData as $fdk => $fdItem) {
			if($fdItem['status'] === true) $total_sent++;
			if($fdItem['status'] !== true) $total_failed++;
		}
	}
}

// Send Postal Letter
foreach ($pItems as $pik => $pItem) {
	$pData = PostalLetter::TransmitPostalLetter(
				array($pItem['data']), 
				array('pid' => $pItem['pid'], 'request_data' => $_REQUEST, 'files' => $_FILES, 'logMsg' => true)
			);

	if(isset($pData)) {
		foreach ($pData as $pdk => $pdItem) {
			if($pdItem['status'] === true) $total_sent++;
			if($pdItem['status'] !== true) $total_failed++;
		}
	}
}

echo json_encode(array(
	'sent' => $total_sent,
	'failed' => $total_failed
));