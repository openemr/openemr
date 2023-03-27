<?php
/** **************************************************************************
 *	messages_full.php
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

require_once ("../../globals.php");
require_once ($GLOBALS['srcdir'] . "/wmt-v3/wmt.globals.php");

// Include required globals
require_once ($GLOBALS['srcdir'] . '/patient.inc');
require_once ($GLOBALS['srcdir'] . '/options.inc.php');
require_once ($GLOBALS['srcdir'] . '/classes/Document.class.php');
require_once ($GLOBALS['srcdir'] . '/gprelations.inc.php');
require_once ($GLOBALS['srcdir'] . '/formatting.inc.php');
require_once ($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\FaxMessage;
use OpenEMR\OemrAd\PostalLetter;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\OemrAd\Utility;



// New pid set?
if ($_GET['set_pid']) {
	require_once ($GLOBALS['srcdir'] . '/pid.inc');
	setpid($_GET['set_pid']);
}

/*Get Incoming*/
EmailMessage::getIncomingEmail($pid);


// Validate we have a pid
if (!isset($pid) || empty($pid)) {
	throw new Exception("FATAL ERROR: no patient identifier present!!");
}

// Check authorization
if (! AclMain::aclCheckCore('patients', 'notes', '', array ('write', 'addonly'))) {
	die (htmlspecialchars(xl('You are not authorized to access this information'), ENT_NOQUOTES));
}

$internalNoteColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px"
		)
	),
	array(
		"name" => "assigned",
		"title" => xlt('Assigned'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "200px",
            "orderable" => false,
		)
	),
	array(
		"name" => "type",
		"title" => xlt('Type'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	),
	array(
		"name" => "action",
		"title" => xlt('Action'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "220px",
            "orderable" => false,
		)
	)
);


$emailColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "select",
		"title" => xlt('Select'),
		"data" => array(
            "width" => "35px",
            "orderable" => false,
            "needToRender" => false
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px"
		)
	),
	array(
		"name" => "assignment",
		"title" => xlt('Assignment'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "150px",
            "orderable" => false,
		)
	),
	array(
		"name" => "direction",
		"title" => xlt('Direction'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "100px",
            "orderable" => false,
		)
	),
	array(
		"name" => "author",
		"title" => xlt('Author'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "200px",
            "orderable" => false,
		)
	),
	array(
		"name" => "to_from",
		"title" => xlt('To/From'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	),
	array(
		"name" => "status",
		"title" => xlt('Status'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "visible" => false
		)
	),
	array(
		"name" => "action",
		"title" => xlt('Action'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "220px",
            "orderable" => false,
		)
	)
);

$smsColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "select",
		"title" => xlt('Select'),
		"data" => array(
            "width" => "35px",
            "orderable" => false,
            "needToRender" => false
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px"
		)
	),
	array(
		"name" => "assignment",
		"title" => xlt('Assignment'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "150px",
            "orderable" => false,
		)
	),
	array(
		"name" => "author",
		"title" => xlt('Author'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "200px",
            "orderable" => false,
		)
	),
	array(
		"name" => "to_from",
		"title" => xlt('To/From'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	),
	array(
		"name" => "status",
		"title" => xlt('Status'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "visible" => false
		)
	),
	array(
		"name" => "action",
		"title" => xlt('Action'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "220px",
            "orderable" => false,
		)
	)
);

$faxColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "select",
		"title" => xlt('Select'),
		"data" => array(
            "width" => "35px",
            "orderable" => false,
            "needToRender" => false
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px"
		)
	),
	array(
		"name" => "assignment",
		"title" => xlt('Assignment'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "150px",
            "orderable" => false,
		)
	),
	array(
		"name" => "author",
		"title" => xlt('Author'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "200px",
            "orderable" => false,
		)
	),
	array(
		"name" => "to_from",
		"title" => xlt('To/From'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	),
	array(
		"name" => "status",
		"title" => xlt('Status'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "visible" => false
		)
	),
	array(
		"name" => "action",
		"title" => xlt('Action'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "220px",
            "orderable" => false,
		)
	)
);

$postalLetterColumnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
			"className" => 'dt-control text',
			"width" => "30px",
			"orderable" => false,
		)
	),
	array(
		"name" => "select",
		"title" => xlt('Select'),
		"data" => array(
            "width" => "35px",
            "orderable" => false,
            "needToRender" => false
		)
	),
	array(
		"name" => "active",
		"title" => xlt('Active'),
		"data" => array(
            "width" => "35px",
            "needToRender" => false
		)
	),
	array(
		"name" => "date_time",
		"title" => xlt('Date/Time'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "140px"
		)
	),
	array(
		"name" => "assignment",
		"title" => xlt('Assignment'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "150px",
            "orderable" => false,
		)
	),
	array(
		"name" => "author",
		"title" => xlt('Author'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "200px",
            "orderable" => false,
		)
	),
	array(
		"name" => "to_from",
		"title" => xlt('To'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
		)
	),
	array(
		"name" => "status",
		"title" => xlt('Status'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "orderable" => false,
            "visible" => false
		)
	),
	array(
		"name" => "action",
		"title" => xlt('Action'),
		"data" => array(
            "defaultValue" => Utility::getHtmlString('<i class="defaultValueText">Empty</i>'),
            "width" => "220px",
            "orderable" => false,
		)
	)
);


// Control inputs
$mode = $_REQUEST['mode'];

$activity = $_REQUEST['activity'];
if (!isset($activity) || $activity == '') $activity = 'all';

$active_tab = $_REQUEST['active_tab'];
if (!isset($active_tab)) $active_tab = 'notes';

// Get the users list. The "Inactive" test is a kludge, we should create
// a separate column for this.
/*
$ures = sqlStatement("SELECT username, fname, lname FROM users " . "WHERE username != '' AND active = 1 AND " . "( info IS NULL OR info NOT LIKE '%Inactive%' ) " . "ORDER BY lname, fname");

$pres = getPatientData($pid, "lname, fname");
$patientname = $pres['lname'] . ", " . $pres['fname'];
*/

/*
// Retrieve all phone calls
$phone_list = array();
$sql = "SELECT ml.*, lo.`title` AS 'topic_name', ";
$sql .= "CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name' ";	
$sql .= "FROM `message_log` ml ";
$sql .= "LEFT JOIN `users` us ON ml.`userid` = us.`id` ";
$sql .= "LEFT JOIN `list_options` lo ON lo.`option_id` = ml.`event` AND lo.`list_id` LIKE 'Portal_Subject' ";
$sql .= "WHERE `pid` = ? ";
if ($activity != 'all') $sql .= "AND ml.`activity` = '$activity' ";
$sql .= "AND ml.`type` LIKE 'PHONE' ";

$sql .= generateOrderByQry($phone_orderby, $orderbyList);
$phone_pageDetails = pageDetails($sql, array($pid), $limit);
$sql .= pageOffSet($limit, $phone_pageno);

$phone_result = sqlStatementNoLog($sql, array($pid));
while ($phone_data = sqlFetchArray($phone_result)) {
	$phone_list[] = $phone_data;
}
*/

/*
// Retrieve all portal messages
$portal_list = array();
$sql = "SELECT ml.*, lo.`title` AS 'topic_name', ";
$sql .= "CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name' ";	
$sql .= "FROM `message_log` ml ";
$sql .= "LEFT JOIN `users` us ON ml.`userid` = us.`id` ";
$sql .= "LEFT JOIN `list_options` lo ON lo.`option_id` = ml.`event` AND lo.`list_id` LIKE 'Portal_Subject' ";
$sql .= "WHERE `pid` = ? ";
if ($activity != 'all') $sql .= "AND ml.`activity` = '$activity' ";
$sql .= "AND ml.`type` LIKE 'PORTAL' ";

$sql .= generateOrderByQry($portal_orderby, $orderbyList);
$portal_pageDetails = pageDetails($sql, array($pid), $limit);
$sql .= pageOffSet($limit, $portal_pageno);

$portal_result = sqlStatementNoLog($sql, array($pid));
while ($portal_data = sqlFetchArray($portal_result)) {
	$portal_list[] = $portal_data;
}

//Count Active/Inactive portal messages
$sqlCount = "SELECT ";
$sqlCount .= "SUM(CASE WHEN activity = 1 THEN 1 ELSE 0 END) AS active, ";
$sqlCount .= "SUM(CASE WHEN activity = 0 THEN 1 ELSE 0 END) AS inactive ";
$sqlCount .= "FROM `message_log` ml WHERE ml.`pid` = ? AND ml.`type` LIKE 'PORTAL' ";
$portalMsgCount = sqlQuery($sqlCount , array($pid));
*/

?>

<html>
<head>
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
  	<?php Header::setupHeader(['opener', 'common', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad' ]); ?>
	<script>
		<?php include_once($GLOBALS['srcdir']."/restoreSession.php"); ?>
	</script>

	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/js/messages.js"></script>

	<style type="text/css">
		table.table td.no-padding {
			padding: 0px !important;
		}

		.row-details-table.table tr:first-child td{
			border-top: 1px solid #fff!important;
		}

		.pagination {
			margin: 2px 0;
    		white-space: nowrap;
			justify-content: flex-end;
		}
	</style>
</head>
<body class="body_top">
	<div id="messages">
		<form method='post' name='msg_form' id="msg_form" action='messages_full.php'>
			<input type='hidden' name='set_note_type' id='set_note_type' value='' /> 
			<input type='hidden' name='set_uid' id='set_uid' value='' /> 
			<input type='hidden' name='set_group' id="set_group" value=""> 
			<input type='hidden' name='set_username' id="set_username" value=""> 

			<input type='hidden' name='set_id' id='set_id' value='' /> 
			<input type='hidden' name='set_mpid' id='set_mpid' value='' /> 
			<input type='hidden' name='mode' id="mode" value=""> 
			<input type='hidden' name='tmode' id="tmode" value=""> 
			<input type='hidden' name='active_tab' id="active_tab" value="<?php echo $active_tab ?>"> 
			<input type='hidden' name='activity' id="activity" value="<?php echo $activity; ?>">
			<input type='hidden' name='form_inactive' id="form_inactive" value="<?php echo htmlspecialchars( $form_inactive, ENT_QUOTES); ?>">
			<input type='hidden' name='noteid' id="noteid" value="<?php echo htmlspecialchars( $noteid, ENT_QUOTES); ?>"> 
			<input type='hidden' name='form_doc_only' id="form_doc_only" value="<?php echo htmlspecialchars( $form_doc_only, ENT_QUOTES); ?>">
			<input type='hidden' name='doc_category' id="doc_category" value=""> 
			<input type='hidden' name='doc_destination_name' id="doc_destination_name" value=""> 
			<input type='hidden' name='form_docdate' id="form_docdate" value=""> 

			<div id='namecontainer_messages' class='namecontainer_messages'>
				<span class="title"><?php echo xlt('Patient Messages') . $title_docname; ?></span>&nbsp;
				<?php echo htmlspecialchars( xl('for'), ENT_NOQUOTES);?>&nbsp;
				<span class="title"> 
					<a href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php" onclick="top.restoreSession()">
						<?php echo text(getPatientName($pid)); ?>
					</a>
				</span>
			</div>


			<div class="btn-group mt-2" role="group">
			  <button type="button" id="add_internal_button" class="btn btn-secondary notes_link" title='<?php echo xlt('Patient Note'); ?>'><?php echo xlt('Add Internal Note'); ?></button>
			  <button type="button" id="add_phone_call_button" class="btn btn-secondary" style="display:none;"><?php echo xlt('Document Phone Call'); ?></button>
			  <button type="button" id="add_email_button" class="btn btn-secondary notes_link" title='<?php echo xlt('Email Message'); ?>'><?php echo xlt('Send Email Message'); ?></button>
			  <button type="button" id="add_portal_button" class="btn btn-secondary notes_link" title='<?php echo xlt('Portal Message'); ?>' style="display:none;"><?php echo xlt('Send Portal Message'); ?></button>
			  <button type="button" id="add_sms_button" class="btn btn-secondary notes_link" title='<?php echo xlt('SMS Message'); ?>'><?php echo xlt('Open SMS Chat Form'); ?></button>
			  <button type="button" id="add_fax_button" class="btn btn-secondary notes_link" title='<?php echo xlt('Fax Message'); ?>'><?php echo xlt('Send Fax Message'); ?></button>
			  <button type="button" id="add_postal_letter_button" class="btn btn-secondary notes_link" title='<?php echo xlt('Postal Letter'); ?>'><?php echo xlt('Send Postal Letter'); ?></button>
			</div>
			
			<div style="margin-top:15px">
			    <?php if ($activity == "all") { ?>
			      <span class="text"><b><?php echo xlt('Show All'); ?></b></span>
			    <?php } else { ?>
			      <a href="javascript:;" class="text" onclick="doShow('all')"><span><?php echo xlt('Show All'); ?></span></a>
			    <?php } ?>
			    &nbsp;|&nbsp;
			    <?php if ($activity == '1') { ?>
			      <span class="text"><b><?php echo xlt('Show Active'); ?></b></span>
			    <?php } else { ?>
			      <a href="javascript:;" class="text" onclick="doShow('1')"><span><?php echo xlt('Show Active'); ?></span></a>
			    <?php } ?>
			    &nbsp;|&nbsp;
			    <?php if ($activity == '0') { ?>
			      <span class="text"><b><?php echo xlt('Show Inactive'); ?></b></span>
			    <?php } else { ?>
			      <a href="javascript:;" class="text" onclick="doShow('0')"><span><?php echo xlt('Show Inactive'); ?></span></a>
			    <?php } ?>
			</div>

			<?php
			// Get the billing note if there is one.
			$billing_note = "";
			$colorbeg = "";
			$colorend = "";
			$resnote = getPatientData($pid, "billing_note");
			if (! empty($resnote['billing_note'])) {
				$billing_note = $resnote['billing_note'];
				$colorbeg = "<span style='color:red'>";
				$colorend = "</span>";
			}
			
			// Display what the patient owes
			$balance = get_patient_balance($pid);
			if ($billing_note || $balance ) { ?>

			<div>
				<?php
					if ($balance != "0") {
						// $formatted = sprintf((xl('$').'%01.2f'), $balance);
						$formatted = oeFormatMoney($balance);
						echo "<span class='text'><b>" . $colorbeg . htmlspecialchars(xl('Balance Due'), ENT_NOQUOTES) . $colorend . ":</b>&nbsp;" . $colorbeg . htmlspecialchars($formatted, ENT_NOQUOTES) . $colorend . "</span>";
					}

					if ($billing_note) {
						echo "<span class='text'><b>" . $colorbeg . htmlspecialchars(xl('Billing Note'), ENT_NOQUOTES) . $colorend . ":</b>&nbsp;" . $colorbeg . htmlspecialchars($billing_note, ENT_NOQUOTES) . $colorend . "</span>";
					}
				?>
			</div>
			<br>

			<?php } ?>
	
			<ul class="tabNav">
				<li class="<?php if ($active_tab == 'notes') echo 'current' ?>"><a id="header_tab_notes" href="#"><?php echo htmlspecialchars(xl('Internal Notes'),ENT_NOQUOTES); ?></a></li>

				<?php if (isset($phone_list)) { ?>
				<li class="<?php if ($active_tab == 'phone') echo 'current' ?>" style="display:none;"><a id="header_tab_phone" href="#"><?php echo htmlspecialchars(xl('Phone Calls'),ENT_NOQUOTES); ?></a></li>
				<?php } ?>

				<li class="<?php if ($active_tab == 'email') echo 'current' ?>"><a id="header_tab_email" href="#"><?php echo htmlspecialchars(xl('Email Messages'),ENT_NOQUOTES); ?></a></li>
				
				<?php if (isset($portal_list)) { ?>
				<li class="<?php if ($active_tab == 'portal') echo 'current' ?>" style="display:none;"><a id="header_tab_portal" href="#"><?php echo htmlspecialchars(xl('Portal Messages'),ENT_NOQUOTES); ?></a></li>
				<?php } ?>

				<li class="<?php if ($active_tab == 'sms') echo 'current' ?>"><a id="header_tab_sms" href="#"><?php echo htmlspecialchars(xl('SMS Messages'),ENT_NOQUOTES); ?></a></li>
				<li class="<?php if ($active_tab == 'fax') echo 'current' ?>"><a id="header_tab_fax" href="#"><?php echo htmlspecialchars(xl('Fax Messages'),ENT_NOQUOTES); ?></a></li>
				<li class="<?php if ($active_tab == 'postal_letter') echo 'current' ?>"><a id="header_tab_postal_letter" href="#"><?php echo htmlspecialchars(xl('Postal Letters'),ENT_NOQUOTES); ?></a></li>
			</ul>
		
			<div class='tabContainer'>
	
				<!-- INTERNAL MESSAGES -->
				<div id='notes' class="tab <?php if ($active_tab == 'notes') echo 'current' ?>">
					<div class=pat_notes>
						<table class="text">
						<?php //if (count($notes_list) > 0) { ?>
							<tr>
								<td style="padding: 5px;">
									<div>
										<span><?php echo xl('Total Active Internal Notes'); ?>: <b><span id="notes_active_notes">0</span></b></span>
									</div>
									<div>
										<span><?php echo xl('Total Inactive Internal Notes'); ?>: <b><span id="notes_inactive_notes">0</span></b></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="padding: 5px;">
									<a href="javascript:;" class="change_activity" onclick="doUpdateAjax('notes')" >
										<span><?php echo htmlspecialchars( xl('Update Active'), ENT_NOQUOTES); ?></span>
									</a>
			 						&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doRefreshAjax()">
										<span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span>
									</a>
								</td>
							</tr>
						</table>

						<table id="internal_note_table" class="text table tableRowHighLight" style="width:100%">
							<thead>
								<tr>
									<?php
								      	foreach ($internalNoteColumnList as $clk => $cItem) {
								      		if($cItem["name"] == "dt_control") {
								      		?> <th><div class="dt-control text"></div></th> <?php
								      		} else {
								      		?> <th><?php echo $cItem["title"] ?></th> <?php
								      		}
								      	}
								     ?>
								</tr>
							</thead>
						</table>
					</div>
				</div>
						

				<!-- PHONE MESSAGES -->
				<?php if (isset($phone_list)) { ?>
				<div style="display:none;" id='phone' class="tab <?php if ($active_tab == 'phone') echo 'current' ?>">
					<div class="pat_notes">
						<table border='0' cellpadding="1" class="text">
						<?php if (count($phone_list) > 0) { ?>
							<tr>
								<td style="padding: 5px;">
									<a href="#" class="change_activity" onclick="doUpdate('phone')" >
										<span><?php echo htmlspecialchars( xl('Update Active'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="#" onclick="doRefresh()">
										<span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span>
									</a>
								</td>
							</tr>
						</table>
	
						<table class="text" style="width:100%">
							<tr class="showborder_head">
								<th style='text-align:center'><?php echo xlt('Action'); ?></th>
								<th style='text-align:center'>
									<a href="#" onclick="return doSort('phone','<?php //echo generateOrderBy('activity', $phone_orderby) ?>')" style="color:#000;padding:0px 3px;"><?php echo xlt('Active'); ?></a>
								</th>
								<th style='text-align:center'>
									<a href="#" onclick="return doSort('phone','<?php //echo generateOrderBy('msgtime', $phone_orderby) ?>')" style="color:#000"><?php echo xlt('Date/Time'); ?></a>
								</th>
								<th style='text-align:center'><?php echo xlt('Author'); ?></th>
								<th style='text-align:center'><?php echo xlt('Topic'); ?></th>
								<th><?php echo xlt('Content'); ?></th>
							</tr>
							<?php
							// display all of the notes for the day, as well as others that are active
							// from previous dates, up to a certain number, $N

							$phone_count = 0;
							$evenodd = '#fff';
							foreach ($phone_list AS $phone_data) {
								$phone_count ++;
								$row_phone_id = $phone_data['id'];
								
								$checked = ($phone_data['activity'] == '1') ? "checked" : "";
								$evenodd = ($evenodd == '#eee') ? '#fff' : '#eee';
								$hilite = $evenodd;

								// highlight the row if it's been selected for updating
								if ($_REQUEST['noteid'] == $row_phone_id) {
									$hilite = 'yellow';
								}
								
								$user_name = $phone_data['user_name'];
								if (empty($phone_data['userid']) && $phone_data['msg_from'] == 'PATIENT') $user_name = '[&nbsp;PATIENT&nbsp;]';
								if (empty($user_name) && $phone_data['msg_from'] == 'CLINIC') $user_name = '[&nbsp;SYSTEM&nbsp;]';
								
								$msg_date = $phone_data['msg_time'];
								$datetime = strtotime($phone_data['msg_time']);
								if ($datetime) $msg_date = date('Y-m-d h:ia', $datetime);
							?>
							<tr id="<?php echo $row_phone_id ?>" class="noterow <?php echo $hilite ?>" style="background-color:<?php echo $hilite?>" >
								<td style='text-align:center;white-space:nowrap' class="actionTD">
									<a href='../../main/messages/phone_call.php?mode=edit&id=<?php echo $row_phone_id ?>' class='css_button_small notes_link' 
										title='<?php echo htmlspecialchars(xl('Phone Calls'), ENT_QUOTES) ?>'>
											<span><?php echo htmlspecialchars(xl('Edit'), ENT_NOQUOTES) ?></span>
									</a>
								<?php 
									// if the user is an admin or if they are the author of the note, they can delete it
									if (($note_data['user'] == $_SESSION['authUser']) || (AclMain::aclCheckCore('admin', 'super', '', 'write'))) {
								?>
									<a href='#' class='css_button_small notes_link' onclick="doDelete('<?php echo $row_note_id ?>')" 
										title='<?php echo htmlspecialchars(xl('Delete this note'), ENT_QUOTES) ?>'>
											<span><?php echo htmlspecialchars(xl('Delete'), ENT_NOQUOTES) ?></span>
									</a>
								<?php } ?>
								</td>
								<td class='text bold' style='text-align:center'>
									<input type='hidden' name='phone_act[<?php echo $row_phone_id ?>]' value='1' />
									<input type='checkbox' name='phone_chk[<?php echo $row_phone_id ?>]' <?php echo $checked ?>/>
								</td>
								<td class='text'>
									<?php echo $msg_date ?>
								</td>
								<td class='text' style="white-space:nowrap">
									<?php echo $user_name ?>
								</td>
								<td class='text bold'>
									<?php echo (empty($phone_data['topic_name']))? 'Phone Call' : $phone_data['topic_name'] ?>
								</td>
								<td class='notecell' style='padding:6px;width:80%'>
									<?php echo MessagesLib::displayMessageContent($phone_data['message']) ?>
								</td>
							</tr>
							<?php 
								$phone_count ++;
							}
								
						} else { // no results
							$notice = "No ";
							if ($_REQUEST['activity'] == '1') $notice .= 'Active ';
							if ($_REQUEST['activity'] == '0') $notice .= 'Inactive ';
							$notice .= "Phone Calls On File";
							print "<tr><td class='text' style='text-align:center;font-size:1.3em;font-weight:bold;padding-top:30px'>" . htmlspecialchars(xl($notice), ENT_NOQUOTES) . "</td></tr>\n";
						}
						?>
						</table>
					</div>
	
					<table style="display: none;">
						<tr>
							<td><br>
							<?php
								if ($phone_offset > ($N - 1)) {
									echo "   <a class='link' href='#' onclick='doRefresh(\"prev\")'>[ " . htmlspecialchars(xl('Previous'), ENT_NOQUOTES) . " ]</a>\n";
								}
							?>
							</td>
							<td align='right'><br>
							<?php
								if ($phone_count == $N) {
									echo "   <a class='link' href='#' onclick='doRefresh(\"next\")'>[ " . htmlspecialchars(xl('Next'), ENT_NOQUOTES) . " ]</a>\n";
								}
							?>
							</td>
						</tr>
					</table>
					<?php //generatePagination('phone', $phone_pageDetails, $phone_pageno) ?>
				</div>
				<?php } ?>
		
				<!-- EMAIL MESSAGES -->
				<div id='email' class="tab <?php if ($active_tab == 'email') echo 'current' ?>">
					<div class="pat_notes">
						<table border='0' cellpadding="1" class="text">
						<?php //if (count($email_list) > 0) { ?>
							<tr>
								<td style="padding: 5px;">
									<div>
										<span><?php echo xl('Total Active Messages'); ?>: <b><span id="active_email">0</span></b></span>
									</div>
									<div>
										<span><?php echo xl('Total Inactive Messages'); ?>: <b><span id="inactive_email">0</span></b></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="padding: 5px;">
									<a href="javascript:;" class="change_activity" onclick="doUpdateAjax('email')" >
										<span><?php echo htmlspecialchars( xl('Update Active'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doRefreshAjax()">
										<span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doAdddocAjax('email')" >
										<span><?php echo htmlspecialchars( xl('Add to chart'), ENT_NOQUOTES); ?></span>
									</a>
								</td>
							</tr>
						</table>

						<table id="email_table" class="text table tableRowHighLight" style="width:100%">
							<thead>
								<tr>
									<?php
								      	foreach ($emailColumnList as $clk => $cItem) {
								      		if($cItem["name"] == "dt_control") {
								      		?> <th><div class="dt-control text"></div></th> <?php
								      		} else {
								      		?> <th><?php echo $cItem["title"] ?></th> <?php
								      		}
								      	}
								     ?>
								</tr>
							</thead>
						</table>
					</div>
					<textarea name="selected_email_list_data" style="display: none;"><?php echo isset($form_selected_email_list_data) ? json_encode($form_selected_email_list_data) : '[]'; ?></textarea>
				</div>
		
				<!-- PORTAL MESSAGES -->
				<?php if (isset($portal_list)) { ?>
				<div style="display:none;" id='portal' class="tab <?php if ($active_tab == 'portal') echo 'current' ?>">
					<div class="pat_notes">
						<table border='0' cellpadding="1" class="text">
						<?php if (count($portal_list) > 0) { ?>
							<tr>
								<td style="padding: 5px;">
									<div>
										<span>Total Active Messages: <b><?php echo $portalMsgCount['active'] ?></b></span>
									</div>
									<div>
										<span>Total Inactive Messages: <b><?php echo $portalMsgCount['inactive'] ?></b></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="padding: 5px;">
									<a href="#" class="change_activity" onclick="doUpdate('portal')" >
										<span><?php echo htmlspecialchars( xl('Update Active'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="#" onclick="doRefresh()">
										<span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="#" onclick="doPostalLetterRefresh()">
										<span><?php echo htmlspecialchars( xl('Refresh Postal Letters Status'), ENT_NOQUOTES); ?></span>
									</a>
								</td>
							</tr>
						</table>
	
						<table class="text" style="width:100%">
							<tr class="showborder_head">
								<th style='text-align:center'><?php echo xlt('Action'); ?></th>
								<th style='text-align:center'>
									<a href="#" onclick="return doSort('portal','<?php //echo generateOrderBy('activity', $portal_orderby) ?>')" style="color:#000;padding:0px 3px;"><?php echo xlt('Active'); ?></a>
								</th>
								<th style='text-align:center'>
									<a href="#" onclick="return doSort('portal','<?php //echo generateOrderBy('msgtime', $portal_orderby) ?>')" style="color:#000"><?php echo xlt('Date/Time'); ?></a>
								</th>
								<th style='text-align:center'><?php echo xlt('Author'); ?></th>
								<th style='text-align:center'><?php echo xlt('Topic'); ?></th>
								<th><?php echo xlt('Content'); ?></th>
							</tr>
							<?php
								// display all of the notes for the day, as well as others that are active
								// from previous dates, up to a certain number, $N

								$portal_count = 0;
								$evenodd = '#fff';
								foreach ($portal_list AS $portal_data) {
									$portal_count ++;
									$row_portal_id = $portal_data['id'];
									
									$checked = ($portal_data['activity'] == '1') ? "checked" : "";
									$evenodd = ($evenodd == '#eee') ? '#fff' : '#eee';
									$hilite = $evenodd;

									// highlight the row if it's been selected for updating
									if ($_REQUEST['noteid'] == $row_portal_id) {
										$hilite = 'yellow';
									}
									
									$user_name = $portal_data['user_name'];
									if (empty($portal_data['userid']) && $portal_data['msg_from'] == 'PATIENT') $user_name = '[&nbsp;PATIENT&nbsp;]';
									if (empty($user_name) && $portal_data['msg_from'] == 'CLINIC') $user_name = '[&nbsp;SYSTEM&nbsp;]';
									
									$msg_date = $portal_data['msg_time'];
									$datetime = strtotime($portal_data['msg_time']);
									if ($datetime) $msg_date = date('Y-m-d h:ia', $datetime);
							?>
							<tr id="<?php echo $row_portal_id ?>" class="noterow <?php echo $hilite ?>" style="background-color:<?php echo $hilite?>" >
								<td style='text-align:center;white-space:nowrap' class="actionTD">
									<a href='../../main/messages/portal_message.php?mode=edit&id=<?php echo $row_portal_id ?>' class='css_button_small notes_link' 
										title='<?php echo htmlspecialchars(xl('Portal Message'), ENT_QUOTES) ?>'>
											<span><?php echo htmlspecialchars(xl('Edit'), ENT_NOQUOTES) ?></span>
									</a>
									<a href="javascript:;" class='css_button_small notes_link' 
										title='<?php echo htmlspecialchars(xl('Portal Message'), ENT_QUOTES) ?>' onClick="replyPortalMSGButton(event,'<?php echo $portal_data['id']; ?>')">
											<span><?php echo htmlspecialchars(xl('Reply'), ENT_NOQUOTES) ?></span>
									</a>
									<?php 
											// if the user is an admin or if they are the author of the note, they can delete it
											/* if (($note_data['user'] == $_SESSION['authUser']) || (AclMain::aclCheckCore('admin', 'super', '', 'write'))) {
									?>
									<a href='#' class='css_button_small notes_link' onclick="doDelete('<?php echo $row_note_id ?>')" 
										title='<?php echo htmlspecialchars(xl('Delete this note'), ENT_QUOTES) ?>'>
											<span><?php echo htmlspecialchars(xl('Delete'), ENT_NOQUOTES) ?></span>
									</a>
									<?php 
											} */
									?>
								</td>
								<td class='text bold' style='text-align:center'>
									<input type='hidden' name='portal_act[<?php echo $row_portal_id ?>]' value='1' />
									<input type='checkbox' name='portal_chk[<?php echo $row_portal_id ?>]' <?php echo $checked ?>/>
								</td>
								<td class='text'>
									<?php echo $msg_date ?>
								</td>
								<td class='text' style="white-space:nowrap">
									<?php echo $user_name ?>
								</td>
								<td class='text bold'>
									<?php echo (empty($portal_data['topic_name']))? 'Phone Call' : $portal_data['topic_name'] ?>
								</td>
								<td class='notecell' style='padding:6px;'>
									<?php echo MessagesLib::displayMessageContent($portal_data['message']) ?>
								</td>
							</tr>
							<?php 
									$portal_count ++;
								}
	
							} else { // no results
								$notice = "No ";
								if ($_REQUEST['activity'] == '1') $notice .= 'Active ';
								if ($_REQUEST['activity'] == '0') $notice .= 'Inactive ';

								if ($_REQUEST['activity'] == '1' && $portalMsgCount['inactive'] > 0) $post_notice = '<br/>'.$portalMsgCount['inactive'].' Inactive Messages ';
								if ($_REQUEST['activity'] == '0' && $portalMsgCount['active'] > 0) $post_notice = '<br/>'.$portalMsgCount['active'].' Active Messages ';

								$notice .= "Portal Messages On File";
								print "<tr><td class='text' style='text-align:center;font-size:1.3em;font-weight:bold;padding-top:30px'>" . htmlspecialchars(xl($notice), ENT_NOQUOTES) . $post_notice . "</td></tr>\n";
							}
							?>
						</table>
					</div>
	
					<table style="display: none;">
						<tr>
							<td><br>
							<?php
								if ($portal_offset > ($N - 1)) {
									echo "   <a class='link' href='#' onclick='doRefresh(\"prev\")'>[ " . htmlspecialchars(xl('Previous'), ENT_NOQUOTES) . " ]</a>\n";
								}
							?>
							</td>
							<td align='right'><br>
							<?php
								if ($portal_count == $N) {
									echo "   <a class='link' href='#' onclick='doRefresh(\"next\")'>[ " . htmlspecialchars(xl('Next'), ENT_NOQUOTES) . " ]</a>\n";
								}
							?>
							</td>
						</tr>
					</table>
					<?php //generatePagination('portal', $portal_pageDetails, $portal_pageno); ?>
				</div>
				<?php } ?>
		
				<!-- SMS MESSAGES -->
	
				<div id='sms' class="tab <?php if ($active_tab == 'sms') echo 'current' ?>">
					<div class="pat_notes">
						<table border='0' cellpadding="1" class="text">
						<?php //if (count($sms_list) > 0) { ?>
							<tr>
								<td style="padding: 5px;">
									<div>
										<span><?php echo xl('Total Active Messages'); ?>: <b><span id="active_sms">0</span></b></span>
									</div>
									<div>
										<span><?php echo xl('Total Inactive Messages'); ?>: <b><span id="inactive_sms">0</span></b></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="padding: 5px;">
									<a href="javascript:;" class="change_activity" onclick="doUpdateAjax('sms')" >
										<span><?php echo htmlspecialchars( xl('Update Active'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doRefreshAjax()">
										<span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doAdddocAjax('sms')" >
										<span><?php echo htmlspecialchars( xl('Add to chart'), ENT_NOQUOTES); ?></span>
									</a>
								</td>
							</tr>
						</table>

						<table id="sms_table" class="text table tableRowHighLight" style="width:100%">
							<thead>
								<tr>
									<?php
										foreach ($smsColumnList as $clk => $cItem) {
											if($cItem["name"] == "dt_control") {
											?> <th><div class="dt-control text"></div></th> <?php
											} else {
											?> <th><?php echo $cItem["title"] ?></th> <?php
											}
										}
									 ?>
								</tr>
							</thead>
						</table>
					</div>
					<textarea name="selected_sms_list_data" style="display: none;"><?php echo isset($form_selected_sms_list_data) ? json_encode($form_selected_sms_list_data) : '[]'; ?></textarea>
				</div>

				<!-- FAX MESSAGES -->
				<div id='fax' class="tab <?php if ($active_tab == 'fax') echo 'current' ?>">
					<div class="pat_notes">
						<table border='0' cellpadding="1" class="text">
						<?php //if (count($fax_list) > 0) { ?>
							<tr>
								<td style="padding: 5px;">
									<div>
										<span><?php echo xl('Total Active Messages'); ?>: <b><span id="active_fax">0</span></b></span>
									</div>
									<div>
										<span><?php echo xl('Total Inactive Messages'); ?>: <b><span id="inactive_fax">0</span></b></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="padding: 5px;">
									<a href="javascript:;" class="change_activity" onclick="doUpdateAjax('fax')" >
										<span><?php echo htmlspecialchars( xl('Update Active'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doRefreshAjax()">
										<span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doFaxRefreshAjax()">
										<span><?php echo htmlspecialchars( xl('Refresh fax status'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doAdddocAjax('fax')" >
										<span><?php echo htmlspecialchars( xl('Add to chart'), ENT_NOQUOTES); ?></span>
									</a>
								</td>
							</tr>
						</table>

						<table id="fax_table" class="text table tableRowHighLight" style="width:100%">
							<thead>
								<tr>
									<?php
										foreach ($faxColumnList as $clk => $cItem) {
											if($cItem["name"] == "dt_control") {
											?> <th><div class="dt-control text"></div></th> <?php
											} else {
											?> <th><?php echo $cItem["title"] ?></th> <?php
											}
										}
									 ?>
								</tr>
							</thead>
						</table>
					</div>
					<textarea name="selected_fax_list_data" style="display: none;"><?php echo isset($form_selected_fax_list_data) ? json_encode($form_selected_fax_list_data) : '[]'; ?></textarea>
				</div>

				<!-- POSTAL LETTERS -->
				<div id='postal_letter' class="tab <?php if ($active_tab == 'postal_letter') echo 'current' ?>">
					<div class="pat_notes">
						<table border='0' cellpadding="1" class="text">
						<?php //if (count($postal_letter_list) > 0) { ?>
							<tr>
								<td style="padding: 5px;">
									<div>
										<span><?php echo xl('Total Active Postal Letters'); ?>: <b><span id="active_postal_letter">0</span></b></span>
									</div>
									<div>
										<span><?php echo xl('Total Inactive Postal Letters'); ?>: <b><span id="inactive_postal_letter">0</span></b></span>
									</div>
								</td>
							</tr>
							<tr>
								<td style="padding: 5px;">
									<a href="javascript:;" class="change_activity" onclick="doUpdateAjax('postal_letter')" >
										<span><?php echo htmlspecialchars( xl('Update Active'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doRefreshAjax()">
										<span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doPostalLetterRefreshAjax()">
										<span><?php echo htmlspecialchars( xl('Refresh postal letter status'), ENT_NOQUOTES); ?></span>
									</a>
									&nbsp;|&nbsp;
									<a href="javascript:;" onclick="doAdddocAjax('postal_letter')" >
										<span><?php echo htmlspecialchars( xl('Add to chart'), ENT_NOQUOTES); ?></span>
									</a>
								</td>
							</tr>
						</table>

						<table id="postal_letter_table" class="text table tableRowHighLight" style="width:100%">
							<thead>
								<tr>
									<?php
										foreach ($postalLetterColumnList as $clk => $cItem) {
											if($cItem["name"] == "dt_control") {
											?> <th><div class="dt-control text"></div></th> <?php
											} else {
											?> <th><?php echo $cItem["title"] ?></th> <?php
											}
										}
									 ?>
								</tr>
							</thead>
						</table>
						
					</div>
					<textarea name="selected_postal_letter_list_data" style="display: none;"><?php echo isset($form_selected_postal_letter_list_data) ? json_encode($form_selected_postal_letter_list_data) : '[]'; ?></textarea>
				</div>
	
			</div>
		</form>
			
		<script language='JavaScript'>

			<?php if ($_GET['set_pid']) { 
				$ndata = getPatientData($pid, "fname, lname, pubpid"); 
			?>
				parent.left_nav.setPatient(<?php echo "'" . addslashes($ndata['fname'] . " " . $ndata['lname']) . "'," . addslashes($pid) . ",'" . addslashes($ndata['pubpid']) . "',window.name"; ?>);
			<?php
			}

			// If this note references a new patient document, pop up a display
			// of that document.
			//
			if ($noteid /* && $title == 'New Document' */ ) {
				$prow = getPnoteById($noteid, 'body');
				if (preg_match('/New scanned document (\d+): [^\n]+\/([^\n]+)/', $prow['body'], $matches)) {
					$docid = $matches[1];
					$docname = $matches[2];
					?>
				window.open('../../../controller.php?document&retrieve&patient_id=<?php echo htmlspecialchars($pid, ENT_QUOTES); ?>&document_id=<?php echo htmlspecialchars($docid, ENT_QUOTES); ?>&<?php echo htmlspecialchars($docname, ENT_QUOTES);?>&as_file=true',
				  '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
			<?php } ?>
			<?php } ?>
		</script>

	</div>
	<!-- end outer 'messages' -->
</body>

<script type="text/javascript">
	var internal_note_table = null;
	var email_table = null;
	var sms_table = null;
	var fax_table = null;
	var postal_letter_table = null;

	$(document).ready(function() {
		let internalnotes_dataTable_format = function(d) {
            return '<div class="text"><table class="table text row-details-table mb-0"><tr><td>'+(d['content'] ? d['content'] : '')+'</td></tr></table></div>';
        }

	    internal_note_table = $('#internal_note_table').dataTable({
	      	'processing': true,
	      	'serverSide': true,
	      	'serverMethod': 'post',
	      	'ajax': {
	          	url:'ajax/messages_full_ajax.php?pid=<?php echo $pid; ?>&action=internal_msg',
	          	data: function(adata) {
          			if(typeof data !== 'undefined') {
	             		for (let key in data) {
	             			adata[key] = data[key];
	             		}
             		}

             		//Append Filter Value
             		//adata['filterVal'] = getFilterValues(id + "_filter");
             		adata['filterVal'] = {};
             		adata['filterVal']['active'] = '<?php echo $activity; ?>';
	            },
	            type: "POST",   // connection method (default: GET)
	      	},
	      	'initComplete': function(settings){
                let rowD = $(this).rowDetails({
                    format : internalnotes_dataTable_format,
                    api: this.api()
                });

                rowD.expandAllRow(true);
            },
            "drawCallback": function (settings) {
            	let jsonData = settings['json']; 
            	let extraData = jsonData['otherData'];

            	$('#notes_active_notes').html(extraData['activeCount'] ? extraData['activeCount'] : 0);
            	$('#notes_inactive_notes').html(extraData['inActiveCount'] ? extraData['inActiveCount'] : 0);

            	setTimeout(function(){
		    		$('#internal_note_table').find('.contentiFrame').iframereadmoretext();
		    	}, 0);
            },
           	'autoWidth': false,
	      	'columns': prepareColumns('<?php echo json_encode($internalNoteColumnList); ?>'),
	      	"columnDefs": [
		      	{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='notes_act["+row['id']+"]' value='1' /><input type='checkbox' name='notes_chk["+row['id']+"]' "+row['active']+"/></center>";
	                },
	                "targets": 1
		      	}
	      	],
	      	"order": [[ 2, "desc" ]],
	      	"lengthChange": false,
				"searching": false
	    });


	    let email_dataTable_format = function(d) {
            return '<div class="text"><table class="table text row-details-table mb-0"><tr><td style="width:50%;"><span><b><?php echo xlt('To/From'); ?>: </b> '+(d['to_from'] ? d['to_from'] : '<i>Empty</i>')+'</td><td><span><b><?php echo xlt('Status'); ?>: </b> '+(d['status'] ? d['status'] : '<i>Empty</i>')+'</td></tr><tr><td colspan="2"><span><b><?php echo xlt('Subject'); ?>: </b> '+(d['subject'] ? d['subject'] : '<i>Empty</i>')+'</span></td></tr><tr><td colspan="2">'+(d['content'] ? d['content'] : '<i>Empty</i>')+'</td></tr></div>';
        }

	    email_table = $('#email_table').dataTable({
	      	'processing': true,
	      	'serverSide': true,
	      	'serverMethod': 'post',
	      	'ajax': {
	          	url:'ajax/messages_full_ajax.php?pid=<?php echo $pid; ?>&action=email_msg',
	          	data: function(adata) {
          			if(typeof data !== 'undefined') {
	             		for (let key in data) {
	             			adata[key] = data[key];
	             		}
             		}

             		//Append Filter Value
             		//adata['filterVal'] = getFilterValues(id + "_filter");
             		adata['filterVal'] = {};
             		adata['filterVal']['active'] = '<?php echo $activity; ?>';
	            },
	            type: "POST",   // connection method (default: GET)
	      	},
	      	'initComplete': function(settings){
                let rowD = $(this).rowDetails({
                    format : email_dataTable_format,
                    api: this.api()
                });

                rowD.expandAllRow(true);
            },
            "drawCallback": function (settings) {
            	let jsonData = settings['json']; 
            	let extraData = jsonData['otherData'];

            	$('#active_email').html(extraData['activeCount'] ? extraData['activeCount'] : 0);
            	$('#inactive_email').html(extraData['inActiveCount'] ? extraData['inActiveCount'] : 0);

            	checkSelectedItem('email');

            	setTimeout(function(){
		    		$('#email_table').find('.contentiFrame').iframereadmoretext();
		    	}, 0);
            },
           	'autoWidth': false,
	      	'columns': prepareColumns('<?php echo json_encode($emailColumnList); ?>'),
	      	"columnDefs": [
	      		{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='selected_email_act["+row['id']+"]' value='1' /><input type='checkbox' name='selected_email_chk["+row['id']+"]' "+row['select']+" onClick='handleSelectCheckBox(this,"+row['id']+", \"email\")' /></center>";
	                },
	                "targets": 1
		      	},
		      	{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='email_act["+row['id']+"]' value='1' /><input type='checkbox' name='email_chk["+row['id']+"]' "+row['active']+"/></center>";
	                },
	                "targets": 2
		      	}
	      	],
	      	"order": [[ 3, "desc" ]],
	      	"lengthChange": false,
			"searching": false
	    });

	    let sms_dataTable_format = function(d) {
            return '<div class="text"><table class="table text row-details-table mb-0"><tr><td style="width:50%;"><span><b><?php echo xlt('To/From'); ?>: </b> '+(d['to_from'] ? d['to_from'] : '<i>Empty</i>')+'</td><td><span><b><?php echo xlt('Status'); ?>: </b> '+(d['status'] ? d['status'] : '<i>Empty</i>')+'</td></tr><tr><td colspan="2">'+(d['content'] ? d['content'] : '<i>Empty</i>')+'</td></tr></div>';
        }

	    sms_table = $('#sms_table').dataTable({
	      	'processing': true,
	      	'serverSide': true,
	      	'serverMethod': 'post',
	      	'ajax': {
	          	url:'ajax/messages_full_ajax.php?pid=<?php echo $pid; ?>&action=sms_msg',
	          	data: function(adata) {
          			if(typeof data !== 'undefined') {
	             		for (let key in data) {
	             			adata[key] = data[key];
	             		}
             		}

             		//Append Filter Value
             		//adata['filterVal'] = getFilterValues(id + "_filter");
             		adata['filterVal'] = {};
             		adata['filterVal']['active'] = '<?php echo $activity; ?>';
	            },
	            type: "POST",   // connection method (default: GET)
	      	},
	      	'initComplete': function(settings){
                let rowD = $(this).rowDetails({
                    format : sms_dataTable_format,
                    api: this.api()
                });

                rowD.expandAllRow(true);
            },
            "drawCallback": function (settings) {
            	let jsonData = settings['json']; 
            	let extraData = jsonData['otherData'];

            	$('#active_sms').html(extraData['activeCount'] ? extraData['activeCount'] : 0);
            	$('#inactive_sms').html(extraData['inActiveCount'] ? extraData['inActiveCount'] : 0);

            	checkSelectedItem('sms');

            	setTimeout(function(){
		    		$('#sms_table').find('.contentiFrame').iframereadmoretext();
		    	}, 0);
            },
           	'autoWidth': false,
	      	'columns': prepareColumns('<?php echo json_encode($smsColumnList); ?>'),
	      	"columnDefs": [
	      		{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='selected_sms_act["+row['id']+"]' value='1' /><input type='checkbox' name='selected_sms_chk["+row['id']+"]' "+row['select']+" onClick='handleSelectCheckBox(this,"+row['id']+", \"sms\")' /></center>";
	                },
	                "targets": 1
		      	},
		      	{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='sms_act["+row['id']+"]' value='1' /><input type='checkbox' name='sms_chk["+row['id']+"]' "+row['active']+"/></center>";
	                },
	                "targets": 2
		      	}
	      	],
	      	"order": [[ 3, "desc" ]],
	      	"lengthChange": false,
			"searching": false
	    });


	    let fax_dataTable_format = function(d) {
            return '<div class="text"><table class="table text row-details-table mb-0"><tr><td style="width:50%;"><span><b><?php echo xlt('To/From'); ?>: </b> '+(d['to_from'] ? d['to_from'] : '<i>Empty</i>')+'</td><td><span><b><?php echo xlt('Status'); ?>: </b> '+(d['status'] ? d['status'] : '<i>Empty</i>')+'</td></tr><tr><td colspan="2">'+(d['content'] ? d['content'] : '<i>Empty</i>')+'</td></tr></div>';
        }

	    fax_table = $('#fax_table').dataTable({
	      	'processing': true,
	      	'serverSide': true,
	      	'serverMethod': 'post',
	      	'ajax': {
	          	url:'ajax/messages_full_ajax.php?pid=<?php echo $pid; ?>&action=fax_msg',
	          	data: function(adata) {
          			if(typeof data !== 'undefined') {
	             		for (let key in data) {
	             			adata[key] = data[key];
	             		}
             		}

             		//Append Filter Value
             		//adata['filterVal'] = getFilterValues(id + "_filter");
             		adata['filterVal'] = {};
             		adata['filterVal']['active'] = '<?php echo $activity; ?>';
	            },
	            type: "POST",   // connection method (default: GET)
	      	},
	      	'initComplete': function(settings){
                let rowD = $(this).rowDetails({
                    format : fax_dataTable_format,
                    api: this.api()
                });

                rowD.expandAllRow(true);
            },
            "drawCallback": function (settings) {
            	let jsonData = settings['json']; 
            	let extraData = jsonData['otherData'];

            	$('#active_fax').html(extraData['activeCount'] ? extraData['activeCount'] : 0);
            	$('#inactive_fax').html(extraData['inActiveCount'] ? extraData['inActiveCount'] : 0);

            	checkSelectedItem('fax');

            	setTimeout(function(){
		    		$('#fax_table').find('.contentiFrame').iframereadmoretext();
		    	}, 0);
            },
           	'autoWidth': false,
	      	'columns': prepareColumns('<?php echo json_encode($faxColumnList); ?>'),
	      	"columnDefs": [
	      		{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='selected_fax_act["+row['id']+"]' value='1' /><input type='checkbox' name='selected_fax_chk["+row['id']+"]' "+row['select']+" onClick='handleSelectCheckBox(this,"+row['id']+", \"fax\")'/></center>";
	                },
	                "targets": 1
		      	},
		      	{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='fax_act["+row['id']+"]' value='1' /><input type='checkbox' name='fax_chk["+row['id']+"]' "+row['active']+"/></center>";
	                },
	                "targets": 2
		      	}
	      	],
	      	"order": [[ 3, "desc" ]],
	      	"lengthChange": false,
			"searching": false
	    });

	    let postal_letter_dataTable_format = function(d) {
            return '<div class="text"><table class="table text row-details-table mb-0"><tr><td style="width:50%;"><span><b><?php echo xlt('To/From'); ?>: </b> '+(d['to_from'] ? d['to_from'] : '<i>Empty</i>')+'</td><td><span><b><?php echo xlt('Status'); ?>: </b> '+(d['status'] ? d['status'] : '<i>Empty</i>')+'</td></tr><tr><td colspan="2">'+(d['content'] ? d['content'] : '<i>Empty</i>')+'</td></tr></div>';
        }

	    postal_letter_table = $('#postal_letter_table').dataTable({
	      	'processing': true,
	      	'serverSide': true,
	      	'serverMethod': 'post',
	      	'ajax': {
	          	url:'ajax/messages_full_ajax.php?pid=<?php echo $pid; ?>&action=postal_letter_msg',
	          	data: function(adata) {
          			if(typeof data !== 'undefined') {
	             		for (let key in data) {
	             			adata[key] = data[key];
	             		}
             		}

             		//Append Filter Value
             		//adata['filterVal'] = getFilterValues(id + "_filter");
             		adata['filterVal'] = {};
             		adata['filterVal']['active'] = '<?php echo $activity; ?>';
	            },
	            type: "POST",   // connection method (default: GET)
	      	},
	      	'initComplete': function(settings){
                let rowD = $(this).rowDetails({
                    format : postal_letter_dataTable_format,
                    api: this.api()
                });

                rowD.expandAllRow(true);
            },
            "drawCallback": function (settings) {
            	let jsonData = settings['json']; 
            	let extraData = jsonData['otherData'];

            	$('#active_postal_letter').html(extraData['activeCount'] ? extraData['activeCount'] : 0);
            	$('#inactive_postal_letter').html(extraData['inActiveCount'] ? extraData['inActiveCount'] : 0);

            	checkSelectedItem('postal_letter');

            	setTimeout(function(){
		    		$('#postal_letter_table').find('.contentiFrame').iframereadmoretext();
		    	}, 0);
            },
           	'autoWidth': false,
	      	'columns': prepareColumns('<?php echo json_encode($postalLetterColumnList); ?>'),
	      	"columnDefs": [
	      		{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='selected_postal_letter_act["+row['id']+"]' value='1' /><input type='checkbox' name='selected_postal_letter_chk["+row['id']+"]' "+row['select']+" onClick='handleSelectCheckBox(this,"+row['id']+", \"postal_letter\")'/></center>";
	                },
	                "targets": 1
		      	},
		      	{
		    		"render": function ( data, type, row ) {
	                    return "<center><input type='hidden' name='postal_letter_act["+row['id']+"]' value='1' /><input type='checkbox' name='postal_letter_chk["+row['id']+"]' "+row['active']+"/></center>";
	                },
	                "targets": 2
		      	}
	      	],
	      	"order": [[ 3, "desc" ]],
	      	"lengthChange": false,
			"searching": false
	    });


	    /* Readmore */
	    $('ul.tabNav li a').click(function(){
			$('.contentiFrame').iframereadmoretext({ reset:true });
		});

		$(internal_note_table).on('click', 'tbody td.dt-control', function () {
			setTimeout(function(){
				$('.contentiFrame').iframereadmoretext();
			}, 0);
		});

		$(email_table).on('click', 'tbody td.dt-control', function () {
			setTimeout(function(){
				$('.contentiFrame').iframereadmoretext();
			}, 0);
		});

		$(sms_table).on('click', 'tbody td.dt-control', function () {
			setTimeout(function(){
				$('.contentiFrame').iframereadmoretext();
			}, 0);
		});

		$(fax_table).on('click', 'tbody td.dt-control', function () {
			setTimeout(function(){
				$('.contentiFrame').iframereadmoretext();
			}, 0);
		});

		$(postal_letter_table).on('click', 'tbody td.dt-control', function () {
			setTimeout(function(){
				$('.contentiFrame').iframereadmoretext();
			}, 0);
		});

		/* End */

	});


	function getFormData() {
		return $("#msg_form").serialize();
	}

	async function handleAjaxOperation(queryStr = '') {
		let data = getFormData();
		let group = $('.tab.current').attr('id');
		let resStatus = true;
		let qStr = (queryStr != "") ? "&"+queryStr : "";


		await $.ajax({
            type: "POST",
            url: 'ajax/messages_full_ajax.php?pid=<?php echo $pid; ?>' + qStr,
            data: data,//only input
            success: async function(response) {
            	if(response != "") {
            		let resData = JSON.parse(response);

            		if(resData['status'] != undefined) {
            			resStatus = resData['status'];
            		} 

            		if(resData['message']) {
            			alert(resData['message']);
            		}
            		
            	}

            	if(resStatus === true) {
            		if(group == "notes") { await internal_note_table.fnStandingRedraw(); }
            		if(group == "email") { await email_table.fnStandingRedraw(); }
            		if(group == "sms") { await sms_table.fnStandingRedraw(); }
            		if(group == "fax") { await fax_table.fnStandingRedraw(); }
            		if(group == "postal_letter") { await postal_letter_table.fnStandingRedraw(); }
            	}
            }
        });
	}

	async function doUpdateAjax(group) {
		$('#mode').val(group + '_update');
    	await handleAjaxOperation();
    }

    function doDeleteAjax(noteid) {
    	let data = getFormData();
		<?php 
			$text = htmlspecialchars(xl('Are you sure you want to delete this note?'), ENT_QUOTES);
			$text .= '\n';
			$text .= htmlspecialchars(xl('This action CANNOT be undone.'), ENT_QUOTES);
		?>
	    if (confirm("<?php echo $text ?>")) {
		    $('#mode').val('note_delete');
			$('#noteid').val(noteid);

			handleAjaxOperation();
		    //top.restoreSession();
			//$('#msg_form').submit();
	    }
	}

	function doAssignAjax(assign, type, id, pid) {
		$('#set_id').val(id); // store for later
		$('#set_mpid').val(pid);

		var actiontype = assign;

		if(type == "notes") {
			actiontype = 'note_'+assign;
		} else if(assign == 'release') {
			actiontype = 'assign';
		}

		$('#tmode').val(actiontype);
		if (assign == 'release') {
			setuser(0,'','','');
		} else {
			dlgopen('<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/find_user_popup.php?page_action=assign', '_blank', 500, 400);
		}
	}

	function unlinkButtonAjax(group, messageId) {
		<?php $text = htmlspecialchars(xl('Are you sure you want to unlink this message?'), ENT_QUOTES); ?>
	    if (confirm("<?php echo $text ?>")) {
			var type = 'unlink';

			if(group == "notes") {
				type = 'note_unlink';
			}

			$('#set_id').val(messageId);
			$('#tmode').val(type);
			dlgopen('<?php echo $GLOBALS['rootdir']; ?>/main/calendar/find_patient_popup.php', '_blank', 800, 400);
		}
	}

	// This is for callback by the find-user popup.
	function setuser(uid, uname, username, status, noteType = '') {
		var tmode = $('#tmode').val();
		$('#set_uid').val(uid);
		$('#set_group').val('');
		$('#set_username').val(username);
		$('#set_note_type').val(noteType);
		if (uid == 0) $('#set_group').val(username);
		$('#mode').val(tmode);

		handleAjaxOperation();
		//$('#msg_form').submit();
	}

	// This is for callback by the find-patient popup.
	function setpatient(pid, lname, fname, dob) {
		var tmode = $('#tmode').val();
		$('#set_mpid').val(pid);
		$('#mode').val(tmode);
		
		handleAjaxOperation();
		//top.restoreSession();
		//$('#msg_form').submit();
	}

	function doRefreshAjax(paging = '') {
		var group = $('.tab.current').attr('id');
		$('#active_tab').val(group);

		if(group == "notes") { internal_note_table.fnDraw(); }
        if(group == "email") { email_table.fnDraw(); }
        if(group == "sms") { sms_table.fnDraw(); }
        if(group == "fax") { fax_table.fnDraw(); }
        if(group == "postal_letter") { postal_letter_table.fnDraw(); }
	}

	function doAdddocAjax(group) {
	    $('#mode').val(group+'_adddoc');
		$('#active_tab').val(group);

		add_doc_popup(group);
	}

	// This invokes the find-addressbook popup.
	function add_doc_popup(type) {
		var url = '<?php echo $GLOBALS['webroot']."/interface/main/messages/add_doc_popup.php?pid=". $pid; ?>&pagetype='+type;
	  	let title = '<?php echo xlt('Add to chart'); ?>';
	  	dlgopen(url, 'addEmailDocuments', 600, 400, '', title);
	}

	// This is for callback by the add-doc popup.
	function setSelectedDoc(category_id, destination_name, form_docdate) {
		$('#doc_destination_name').val(destination_name);
		$('#doc_category').val(category_id);
		$('#form_docdate').val(form_docdate);

		let group = $('#active_tab').val();
		
		handleAjaxOperation();
		
		$('textarea[name="selected_'+group+'_list_data"]').val('[]');

		doRefreshAjax();
		//top.restoreSession();
		//$('#msg_form').submit();
	}

	function doPostalLetterRefreshAjax() {
		var group = $('.tab.current').attr('id');
		$('#active_tab').val(group);
	    $('#mode').val('postal_letter_refresh');
	    $('#'+group+'_page_no').val(1);

	    handleAjaxOperation();
	    //top.restoreSession();
		//$('#msg_form').submit();
	}

	function doFaxRefreshAjax() {
		var group = $('.tab.current').attr('id');
		$('#active_tab').val(group);
	    $('#mode').val('fax_status_refresh');
	    $('#'+group+'_page_no').val(1);

	    handleAjaxOperation();
	    //top.restoreSession();
		//$('#msg_form').submit();
	}
</script>

<script>

// jQuery stuff to make the page a little easier to use
$(document).ready(function(){

	// enable tabs
    tabbify();

    $("#add_internal_button").click(async function(e) {
        top.restoreSession();
        e.preventDefault();e.stopPropagation();
	    openInternalNotesPopup('?pid=<?php echo $pid ?>');
    });

    $("#add_phone_call_button").click(function(e) {
        top.restoreSession();
        e.preventDefault();e.stopPropagation();
        openPhonePopup('?pid=<?php echo $pid ?>');
    });

    $("#add_email_button").click(async function(e) {
        top.restoreSession();
        e.preventDefault();e.stopPropagation();
	    openEmailPopup('?pid=<?php echo $pid ?>');
    });

    $("#add_portal_button").click(function(e) {
        top.restoreSession();
        e.preventDefault();e.stopPropagation();
        openPortalPopup('?pid=<?php echo $pid ?>');
    });

    $("#add_sms_button").click(function(e) {
        top.restoreSession();
        e.preventDefault();e.stopPropagation();
        openSMSPopup('?pid=<?php echo $pid ?>');
    });

    $("#add_fax_button").click(function(e) {
        top.restoreSession();
        e.preventDefault();e.stopPropagation();
        openFaxPopup('?pid=<?php echo $pid ?>');
    });

	$("#add_postal_letter_button").click(function(e) {
        top.restoreSession();
        e.preventDefault();e.stopPropagation();
		openPostalLetterPopup('?pid=<?php echo $pid ?>');
    });

    $("#printnote").click(function() { 
        top.restoreSession();
        window.open('messages_print.php?noteid=<?php echo $noteid ?>', '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
    });


});

async function resendButton(e, type, messageId) {
	top.restoreSession();
    e.preventDefault();e.stopPropagation();
    resendMsgPopup(type, '<?php echo $pid ?>', messageId);
}

function replyEmailButton(e, messageId) {
	$('#set_id').val(messageId);
    top.restoreSession();
    e.preventDefault();e.stopPropagation();
    openEmailPopup('?pid=<?php echo $pid ?>&msgId='+messageId+'&action=reply');
}

function replySMSButton(e, messageId) {
	$('#set_id').val(messageId);
    top.restoreSession();
    e.preventDefault();e.stopPropagation();
    openSMSPopup('?pid=<?php echo $pid ?>&msgId='+messageId+'&action=reply');
}

function handleSelectCheckBox(el, id, type = '') {
	let isChecked = $(el).is(":checked")
	let lData = $('textarea[name="selected_'+type+'_list_data"]').val();
	lData = JSON.parse(lData);

	if(isChecked === true) {
		lData.push(id);
	} else if(isChecked === false) {
		delete lData[id];
	}

	$('textarea[name="selected_'+type+'_list_data"]').val(JSON.stringify(lData));
}

function checkSelectedItem(type = '') {
	let lData = $('textarea[name="selected_'+type+'_list_data"]').val();
	lData = JSON.parse(lData);

	jQuery.each(lData, function(lindex, litem) {
	    $('input[name="selected_'+type+'_chk['+litem+']"]').prop('checked', true);
	});
}

function replyPortalMSGButton(e, messageId) {
    top.restoreSession();
    e.preventDefault();e.stopPropagation();
    openPortalPopup('?pid=<?php echo $pid ?>&msgId='+messageId+'&action=reply');
}

function doShow(active) {
	var group = $('.tab.current').attr('id');
	$('#active_tab').val(group);
	$('#activity').val(active);
    $('#mode').val('refresh');

    top.restoreSession();
	$('#msg_form').submit();
}

function doRefresh(paging) {
	if(paging == "reply") {
		setTimeout(function(){
			$getId = $('#set_id').val();
			$count = $("#replyinput"+$getId).data('count');
			if($count > 0) {
				var confirmRes = confirm("Do you want to inactive messages which are from the similar (email or phone)?");
				if(confirmRes == true) {
					$('#mode').val('makeallinactive');
					top.restoreSession();
					//handleAjaxOperation();
					//top.restoreSession();
					$('#msg_form').submit();

					return true;
				}
			}
		}, 100);
	}

	//var offset = parseInt( $('#' +group+ '_offset').val() );
	//if (paging == 'next') offset = offset + 15;
	//if (paging == 'prev') offset = offset - 15;
	//$('#' +group+ '_offset').val(offset);
	var group = $('.tab.current').attr('id');
	$('#active_tab').val(group);
    $('#mode').val('refresh');

    top.restoreSession();
	$('#msg_form').submit();
}

</script>

<script type="text/javascript">
	$(".docrow").click(function() { todocument(this.id); });

	$(document).ready(function(){
		$(".docrow").click(function() { todocument(this.id); });
	});

	function todocument(docid) {
	  h = '/controller.php?document&view&patient_id=<?php echo $pid ?>&doc_id=' + docid;
	  openPopUp(h);
	}

	function openPopUp(url) {
		// in a popup so load the opener window if it still exists
		if ( (window.opener) && (window.opener.setPatient) ) {
			window.opener.loadFrame('RTop', 'RTop', url);
		// inside an OpenEMR frame so replace current frame
		} else if ( (parent.left_nav) && (parent.left_nav.loadFrame) ) {
			<?php if($GLOBALS['new_tabs_layout']) { ?>
    top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>" + url;
			<?php } else { ?>
			  parent.left_nav.loadFrame('RTop', 'RTop', url);
			<?php } ?>
		// not in a frame and opener no longer exists, create a new window
		} else {
			var newwin = window.open('../main/main_screen.php?patientID=' + pid);
			newwin.focus();
		}
	}
</script>

</html>
