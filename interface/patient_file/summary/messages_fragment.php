<?php

use OpenEMR\Core\Header;

require_once("../../globals.php");
require_once($GLOBALS['srcdir'] . '/patient.inc');
require_once($GLOBALS['srcdir'] . '/options.inc.php');
require_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');
require_once('./messages_fragment_columns.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\PostalLetter;


//$twig = new TwigContainer(null, $GLOBALS['kernel']);

// Check authorization
if (!AclMain::aclCheckCore('patients', 'notes', '', array ('write', 'addonly'))) {
	die (htmlspecialchars(xl('You are not authorized to access this information'), ENT_NOQUOTES));
}

?>

<style type="text/css">
	table.table.msg-table td.no-padding {
		padding: 0px !important;
	}

	.row-details-table.table tr:first-child td{
		border-top: 1px solid #fff!important;
	}

	.msg-tab-container {
		display: grid;
    	grid-template-rows: 1fr auto;
	}
</style>

<div class="clearfix pt-2">
	<ul class="tabNav">
		<li class="current"><a id="header_tab_notes" href="#"><?php echo htmlspecialchars(xl('Internal Notes'),ENT_NOQUOTES); ?></a></li>
		<?php if (isset($phone_list)) { ?>
		<li><a id="header_tab_phone" href="#"><?php echo htmlspecialchars(xl('Phone Calls'),ENT_NOQUOTES); ?></a></li>
		<?php } ?>
		<li><a id="header_tab_email" href="#"><?php echo htmlspecialchars(xl('Email'),ENT_NOQUOTES); ?></a></li>
		<?php if (isset($portal_list)) { ?>
		<li><a id="header_tab_portal" href="#"><?php echo htmlspecialchars(xl('Portal Messages'),ENT_NOQUOTES); ?></a></li>
		<?php } ?>
		<li><a id="header_tab_sms" href="#"><?php echo htmlspecialchars(xl('SMS'),ENT_NOQUOTES); ?></a></li>
		<li><a id="header_tab_fax" href="#"><?php echo htmlspecialchars(xl('Fax'),ENT_NOQUOTES); ?></a></li>
		<li><a id="header_tab_postal_letter" href="#"><?php echo htmlspecialchars(xl('Postal Letters'),ENT_NOQUOTES); ?></a></li>
	</ul>

	<div class='tabContainer'>
		<!-- INTERNAL MESSAGES -->
		<div id='notes' class="tab current mb-0 px-0">
			<div class="msg-tab-container">
				<table id="internal_note_table" class="text table table-sm msg-table tableRowHighLight" style="width:100%">
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

				<div class="mt-2 text">
	 				<a href="javascript:;" id="add_internal_button" class="btn btn-primary btn-sm notes_link" style="margin-left:0" title='Patient Note'>
						<span><?php echo xlt('Add Internal Note'); ?></span>
					</a>
				</div>
			</div>
		</div>

		<!-- PHONE MESSAGES -->
		<?php if (isset($phone_list)) { ?>
		<div id='phone' class="tab">
			<?php if (count($phone_list) > 0) { ?>
	  			<table class="table table-sm msg-table table-hover table-remove-top-border" id="recent_phone_msg" style="width:100%;margin-top: 0px !important;">
	 				<thead>
	 					<tr>
	 						<th><div class="dt-control text"></div></th>
	 						<th class="text" width="30"><?php echo xlt('Active'); ?></th>
							<th class="text"><?php echo xlt('Date/Time'); ?></th>
							<th class="text"><?php echo xlt('Author'); ?></th>
							<th class="text"><?php echo xlt('Topic'); ?></th>
							<th class="text"><?php echo xlt('Content'); ?></th>
	 					</tr>
	 				</thead>
	 				<tbody>
	 					<?php
						// display all of the notes for the day, as well as others that are active
						// from previous dates, up to a certain number, $N

						$phone_count = 0;
						$evenodd = '';
						foreach ($phone_list AS $phone_data) {
							$row_phone_id = $phone_data['id'];
							
							//$checked = ($phone_data['activity'] == '1') ? "<i class='fa fa-check'/>" : "";
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


							if ($phone_data['activity'] == '1') {
								$checked = "<i class='fa fa-check-circle' style='font-size: 18px;vertical-align: middle;color:#059862;' />";
							} else {
								$checked = "<i class='fa fa-times-circle' style='font-size: 18px;vertical-align: middle;color:#F44336;' />";
							}
						?>
						<tr id="<?php echo $row_phone_id ?>" class="noterow <?php echo $hilite ?>" >
							<td class="dt-control text"></td>
							<td class='text bold' style='text-align:center;padding:3px'>
								<?php echo $checked ?>
							</td>
							<td class='text'>
								<?php echo $msg_date ?>
							</td>
							<td class='text' style="white-space:nowrap;">
								<?php echo $user_name ?>
							</td>
							<td class='text bold'>
								<?php echo $phone_data['topic'] ?>
							</td>
							<td class='notecell'>
								<?php echo MessagesLib::displayMessageContent($phone_data['message']) ?>
							</td>
						</tr>
						<?php 
							$phone_count ++;
						}
						?>
	 				</tbody>
	 			</table>

	 			<div class="mt-2 text">
	 				<?php echo htmlspecialchars(xl('Displaying the following number of most recent phone calls:'), ENT_NOQUOTES) ?><b><?php echo $phone_count;?></b><br>
					<a href='messages_full.php?active_tab=phone&form_active=1' onclick='top.restoreSession()'>
					<?php echo htmlspecialchars(xl("Click here to view all phone call notes"),ENT_NOQUOTES) ?>
									</a>
				</div>
	  		<?php } else { ?>
	 			<div class="mt-3 text">
	 				<?php echo htmlspecialchars(xl('No Phone Calls On File'), ENT_NOQUOTES) ?><br>
					<a href='<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/messages_full.php?active_tab=phone&form_active=1' onclick='top.restoreSession()'>
					<?php echo htmlspecialchars(xl("Click here to document a phone call"),ENT_NOQUOTES) ?>
	 			</div>
	 		<?php } ?>
		</div>
		<?php } ?>

		<!-- EMAIL MESSAGES -->
		<div id='email' class="tab mb-0 px-0">
			<div class="msg-tab-container">
				<table id="email_table" class="text table table-sm msg-table tableRowHighLight" style="width:100%">
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

				<div class="mt-2 text">
	 				<a href="javascript:'" id="add_email_button" class="btn btn-primary btn-sm notes_link" title='Email Message'>
						<span><?php echo xlt('Send Email Message'); ?></span>
					</a>

					<div class="mt-1">
						<?php echo htmlspecialchars(xl('Displaying the following number of most recent messages:'), ENT_NOQUOTES) ?><b><span id="email_count">0</span></b><br>
						<a href='<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/messages_full.php?active_tab=email&form_active=1' onclick='top.restoreSession()'>
							<?php echo htmlspecialchars(xl("Click here to view all email messages"),ENT_NOQUOTES) ?>
						</a>

					</div>
	 			</div>
 			</div>
		</div>

		<!-- PORTAL MESSAGES -->
		<?php if (isset($portal_list)) { ?>
		<div id='portal' class="tab">
			<?php if (count($portal_list) > 0) { ?>
	  			<table class="table table-sm text msg-table tableRowHighLight table-remove-top-border" id="recent_portal_msg" style="width:100%;margin-top: 0px !important;">
	 				<thead>
	 					<tr>
	 						<th style="width: 30px"><div class="dt-control text" width="40"></div></th>
	 						<th class="text" style="width: 35px"><?php echo xlt('Active'); ?></th>
							<th class="text" style="width: 140px"><?php echo xlt('Date/Time'); ?></th>
							<th class="text"><?php echo xlt('Author'); ?></th>
							<th class="text"><?php echo xlt('Topic'); ?></th>
							<th class="text"><?php echo xlt('Content'); ?></th>
	 					</tr>
	 				</thead>
	 				<tbody>
	 					<?php
							// display all of the notes for the day, as well as others that are active
							// from previous dates, up to a certain number, $N

							$portal_count = 0;
							$evenodd = '';
							foreach ($portal_list AS $portal_data) {
								$row_portal_id = $portal_data['id'];

								if ($portal_data['activity'] == '1') {
									$checked = "<i class='fa fa-check-circle' style='font-size: 18px;vertical-align: middle;color:#059862;' title='Active' />";
								} else {
									$checked = "<i class='fa fa-times-circle' style='font-size: 18px;vertical-align: middle;color:#F44336;' title='Inactive' />";
								}

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
							<tr id="<?php echo $row_portal_id ?>" class="noterow" >
								<td class="dt-control text"></td>
								<td class='text bold'><?php echo $checked ?></td>
								<td class='text'><?php echo $msg_date ?></td>
								<td class='text'><?php echo $user_name ?></td>
								<td class='text bold'><?php echo $portal_data['topic'] ?></td>
								<td class='notecell'>
									<table class="table table-sm text table-borderless mt-2 mb-2">
										<tr>
											<td class="p-0">
												<span><b><?php echo xlt('Topic'); ?>: </b> <?php echo $portal_data['topic'] ?>
												</span>
											</td>
										</tr>
										<tr>
											<td class="p-0"><?php echo MessagesLib::displayMessageContent($portal_data['message']) ?></td>
										</tr>
									</table>
								</td>
							</tr>
						<?php 
								$portal_count ++;
							}
						?>
	 				</tbody>
	 			</table>

	 			<div class="mt-2 text">
	 				<?php echo htmlspecialchars(xl('Displaying the following number of most recent messages:'), ENT_NOQUOTES) ?><b><?php echo $portal_count;?></b><br>
					<a href='<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/messages_full.php?active_tab=portal&form_active=1' onclick='top.restoreSession()'>
						<?php echo htmlspecialchars(xl("Click here to view all portal messages"),ENT_NOQUOTES) ?>
					</a>
	 			</div>
	 		<?php } else { ?>
	 			<div class="mt-3 text">
	 				<?php echo htmlspecialchars(xl('No Portal Messages On File'), ENT_NOQUOTES) ?><br>
					<a href='<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/messages_full.php?active_tab=portal&form_active=1' onclick='top.restoreSession()'>
						<?php echo htmlspecialchars(xl("Click here to send a portal message"),ENT_NOQUOTES) ?>
					</a>
	 			</div>
	 		<?php } ?>
		</div>
		<?php } ?>

		<!-- SMS MESSAGES -->
		<div id='sms' class="tab mb-0 px-0">
			<div class="msg-tab-container">
				<table id="sms_table" class="text table table-sm msg-table tableRowHighLight" style="width:100%">
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

				<div class="mt-2 text">
	  				<a href="javascript:;" id="add_sms_button" class="btn btn-primary btn-sm notes_link" title='SMS Message'>
						<span><?php echo xlt('Open SMS Chat Form'); ?></span>
					</a>

					<div class="mt-1">
						<?php echo htmlspecialchars(xl('Displaying the following number of most recent messages:'), ENT_NOQUOTES) ?><b><span id="sms_count">0</span></b><br>
						<a href='<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/messages_full.php?active_tab=sms&form_active=1' onclick='top.restoreSession()'>
						<?php echo htmlspecialchars(xl("Click here to view all SMS messages"),ENT_NOQUOTES) ?>
						</a>
					</div>
	  			</div>
  			</div>
		</div>

		<!-- FAX MESSAGES -->
		<div id='fax' class="tab mb-0 px-0">
			<div class="msg-tab-container">
				<table id="fax_table" class="text table table-sm msg-table tableRowHighLight" style="width:100%">
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

				<div class="mt-2 text">
	  				<a href="javascript:;" id="add_fax_button" class="btn btn-primary btn-sm notes_link" title='Fax Message'>
						<span><?php echo xlt('Send Fax Message'); ?></span>
					</a>

					<div class="mt-1">
						<?php echo htmlspecialchars(xl('Displaying the following number of most recent messages:'), ENT_NOQUOTES) ?><b><span id="fax_count">0</span></b><br>
						<a href='<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/messages_full.php?active_tab=fax&form_active=1' onclick='top.restoreSession()'>
						<?php echo htmlspecialchars(xl("Click here to view all Fax messages"),ENT_NOQUOTES) ?>
						</a>
					</div>
	  			</div>
  			</div>
		</div>

		<!-- POSTAL LETTERS -->
		<div id='postal_letter' class="tab mb-0 px-0">
			<div class="msg-tab-container">
				<table id="postal_letter_table" class="text table table-sm msg-table tableRowHighLight" style="width:100%">
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

				<div class="mt-2 text">
	  				<a href="javascript:;" id="add_postal_letter_button" class="btn btn-primary btn-sm notes_link" title='Postal Letter'>
						<span><?php echo xlt('Send Postal Letter'); ?></span>
					</a>

	  				<div class="mt-1">
	  					<?php echo htmlspecialchars(xl('Displaying the following number of most recent messages:'), ENT_NOQUOTES) ?><b><span id="postal_letter_count">0</span></b><br>
						<a href='<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/messages_full.php?active_tab=postal_letter&form_active=1' onclick='top.restoreSession()'>
						<?php echo htmlspecialchars(xl("Click here to view all Postal Letters"),ENT_NOQUOTES) ?>
						</a>
	  				</div>
	  			</div>
  			</div>
		</div>
	</div>
</div>
