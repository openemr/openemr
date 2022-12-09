<?php

use OpenEMR\Core\Header;

require_once("../../globals.php");
require_once ($GLOBALS['srcdir'] . '/patient.inc');
require_once ($GLOBALS['srcdir'] . '/options.inc.php');
require_once($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\Common\Acl\AclMain;

//$twig = new TwigContainer(null, $GLOBALS['kernel']);

// Check authorization
if (!AclMain::aclCheckCore('patients', 'notes', '', array ('write', 'addonly'))) {
	die (htmlspecialchars(xl('You are not authorized to access this information'), ENT_NOQUOTES));
}

// Records to display per screen
$N = 3;

// Retrieve all notes
$notes_list = array();
$sql = "SELECT p.*, CONCAT(LEFT(u.`fname`,1), '. ',u.`lname`) AS 'user_name' FROM `pnotes` p LEFT JOIN `users` u ON p.`assigned_to` LIKE u.`username` AND p.`assigned_to` != '' WHERE p.`pid` = ? AND p.`deleted` != 1 ORDER BY p.`date` DESC LIMIT " . $N;
  
$notes_result = sqlStatementNoLog($sql, array($pid));
while ($notes_data = sqlFetchArray($notes_result)) {
	$note_name = isset($notes_data['user_name']) ? $notes_data['user_name'] : "";
	if(empty($note_name)) {
		$notelist_options = sqlQueryNoLog("SELECT * FROM list_options WHERE (SUBSTRING(?,5) = list_options.`option_id` AND list_options.`list_id` = 'Messaging_Groups') ", array($notes_data['assigned_to']));
		if(!empty($notelist_options['title'])) {
			$note_name = $notelist_options['title'];
		}
	}

	$notes_data['note_name'] = $note_name;
	$notes_list[] = $notes_data;
}

// Retrieve all portal messages
$portal_list = array();
$sql = "SELECT ml.*, ol.`title` AS 'topic', CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name' FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `list_options` ol ON ol.`list_id` LIKE 'Portal_Subject' AND ml.`event` = ol.`option_id` WHERE ml.`pid` = ? AND `type` LIKE 'PORTAL' ORDER BY ml.`id` DESC LIMIT " . $N;

$portal_result = sqlStatementNoLog($sql, array($pid));
while ($portal_data = sqlFetchArray($portal_result)) {
	$portal_list[] = $portal_data;
}

// Retrieve email messages
$email_list = array();
$sql = "SELECT ml.*, CONCAT(LEFT(us.`fname`,1), '. ',us.`lname`) AS 'user_name', CONCAT(LEFT(pd.`fname`,1), '. ',pd.`lname`) AS 'patient_name', CONCAT(LEFT(u1.`fname`,1), '. ',u1.`lname`) AS 'assign_user_name' FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `patient_data` pd ON ml.`pid` = pd.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` WHERE ml.`pid` = ? AND `type` LIKE 'EMAIL' ORDER BY ml.`id` DESC LIMIT " . $N;

$email_result = sqlStatementNoLog($sql, array($pid));
while ($email_data = sqlFetchArray($email_result)) {
	$email_list[] = $email_data;
}

// Retrieve all sms messages
$sms_list = array();
$sql = "SELECT ml.*, CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name', CONCAT(LEFT(u1.`fname`,1), '. ',u1.`lname`) AS 'assign_user_name' FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` WHERE ml.`pid`=? AND ml.`type` LIKE 'SMS' ORDER BY `id` DESC LIMIT " . $N;

$sms_result = sqlStatementNoLog($sql, array($pid));
while ($sms_data = sqlFetchArray($sms_result)) {
	$sms_list[] = $sms_data;
}

// Retrieve all phone messages
$phone_list = array();
$sql = "SELECT ml.*, ol.`title` AS 'topic', CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name' FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `list_options` ol ON ol.`list_id` LIKE 'Portal_Subject' AND ml.`event` = ol.`option_id` WHERE ml.`pid`=? AND ml.`type` LIKE 'PHONE' ORDER BY `id` DESC LIMIT " . $N;

$phone_result = sqlStatementNoLog($sql, array($pid));
while ($phone_data = sqlFetchArray($phone_result)) {
	$phone_list[] = $phone_data;
}

// Retrieve all fax messages
$fax_list = array();
$sql = "SELECT ml.*, fm.`description`, fm.`file_name`, fm.`url`, fm.`receivers_name`, CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name', CONCAT(LEFT(u1.`fname`,1), '. ',u1.`lname`) AS 'assign_user_name' FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `fax_messages` fm ON ml.`id` = fm.`message_id` WHERE ml.`pid`=? AND ml.`type` LIKE 'FAX' ORDER BY `id` DESC LIMIT " . $N;

$fax_result = sqlStatementNoLog($sql, array($pid));
while ($fax_data = sqlFetchArray($fax_result)) {
	$fax_list[] = $fax_data;
}

// Retrieve all postal letters
$postal_letter_list = array();
$sql = "SELECT ml.*, pl.`description`, pl.`file_name`, pl.`url`, CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name', CONCAT(LEFT(u1.`fname`,1), '. ',u1.`lname`) AS 'assign_user_name' FROM `message_log` ml LEFT JOIN `users` us ON ml.`userid` = us.`id` LEFT JOIN `users` u1 ON ml.`assigned` IS NOT NULL AND ml.`assigned` = u1.`id` LEFT JOIN `postal_letters` pl ON ml.`id` = pl.`message_id` WHERE ml.`pid`=? AND ml.`type` LIKE 'P_LETTER' ORDER BY `id` DESC LIMIT " . $N;

$postal_letter_result = sqlStatementNoLog($sql, array($pid));
while ($postal_letter_data = sqlFetchArray($postal_letter_result)) {
	$postal_letter_list[] = $postal_letter_data;
}

function getNoteTypeGroup($row) {
	$notelist_options = sqlQueryNoLog( "SELECT * FROM list_options WHERE (? = list_options.`option_id` AND list_options.`list_id` = 'Messaging_Groups') ", array($row['assign_group']));
	
	if(!empty($notelist_options['title'])) {
		return $notelist_options['title'];
	}
	return '';
}

function getassigned($type, $row) {
	if (!empty($row['assigned']) || $row['assigned'] != 0) {
		echo $row['assign_user_name'] ."&nbsp;";
	} else if ($row['assign_group']) {
		echo "<span>" . getNoteTypeGroup($row) . "</span>";
	} else {
		echo "<span style='color:red'>Unassigned</span>";
	}
}

?>
<!-- <html>
<head>
	<title><?php //echo htmlspecialchars( xl('Messages'), ENT_NOQUOTES); ?></title>
	<link rel="stylesheet" href='<?php //echo $css_header ?>' type='text/css' />
	<?php //Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-dt', 'datatables-bs', 'oemr_ad']);  ?>
</head>
<body> -->
	<div class="clearfix pt-2">
		<nav>
		  <div class="nav nav-tabs" id="nav-tab" role="tablist">
		    <a class="nav-item nav-link active" id="nav-internalnotes-tab" data-toggle="tab" href="#nav-internalnotes" role="tab" aria-controls="nav-internalnotes" aria-selected="true"><?php echo htmlspecialchars(xl('Internal Notes'),ENT_NOQUOTES); ?></a>
		    <a style="display:none;" class="nav-item nav-link" id="nav-phone-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-phone" aria-selected="false"><?php echo htmlspecialchars(xl('Phone Calls'),ENT_NOQUOTES); ?></a>
		    <a class="nav-item nav-link" id="nav-emails-tab" data-toggle="tab" href="#nav-emails" role="tab" aria-controls="nav-emails" aria-selected="true"><?php echo htmlspecialchars(xl('Email'),ENT_NOQUOTES); ?></a>
		    <a class="nav-item nav-link" id="nav-sms-tab" data-toggle="tab" href="#nav-sms" role="tab" aria-controls="nav-sms" aria-selected="true"><?php echo htmlspecialchars(xl('SMS'),ENT_NOQUOTES); ?></a>
		    <a class="nav-item nav-link" id="nav-fax-tab" data-toggle="tab" href="#nav-fax" role="tab" aria-controls="nav-fax" aria-selected="true"><?php echo htmlspecialchars(xl('FAX'),ENT_NOQUOTES); ?></a>
		    <a class="nav-item nav-link" id="nav-postalletter-tab" data-toggle="tab" href="#nav-postalletter" role="tab" aria-controls="nav-postalletter" aria-selected="true"><?php echo htmlspecialchars(xl('Postal Letter'),ENT_NOQUOTES); ?></a>
		  </div>
		</nav>
		<div class="tab-content" id="nav-tabContent">
		 	<div class="tab-pane fade show active" id="nav-internalnotes" role="tabpanel" aria-labelledby="nav-internalnotes-tab">
		 		<?php if (count($notes_list) > 0) { ?>
		 			<table class="table table-sm table-hover">
		 				<thead>
		 					<tr>
		 						<th width="50"><?php echo xlt('Active'); ?></th>
								<th><?php echo xlt('Date/Time'); ?></th>
								<th><?php echo xlt('Assigned'); ?></th>
								<th><?php echo xlt('Note Type'); ?></th>
								<th><?php echo xlt('Content'); ?></th>
		 					</tr>
		 				</thead>
		 				<tbody>
		 					<?php 
		 					$notes_count = 0;
							foreach ($notes_list AS $note_data) {
								$row_note_id = $note_data['id'];
		
								$body = $note_data['body'];
								if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
									$body = nl2br(oeFormatPatientNote($body));
								} else {
									$body = htmlspecialchars(oeFormatSDFT(strtotime($note_data['date'])) . date(' H:i', strtotime($note_data['date'])), ENT_NOQUOTES) . ' (' . htmlspecialchars($note_data['user'], ENT_NOQUOTES) . ') ' .'<br>'. nl2br(oeFormatPatientNote($body));
								}
								$body = preg_replace('/(\sto\s)-patient-(\))/', '${1}' . $patientname . '${2}', $body);

								if (($note_data{"activity"}) && ($note_data['message_status'] != "Done")) {
									$checked = "<i class='fa fa-check'/>";
								} else {
									$checked = "<i class='fa fa-times'/>";
								}
							}
		 					?>
		 					<tr id="<?php echo $row_note_id ?>" class="noterow">
		 						<td class='text'><center><?php echo $checked ?></center></td>
								<td class='text'><?php echo $note_data['date'] ?></td>
								<td class='text'><?php echo $note_data['note_name'] ?></td>
								<td class='text'>
								<?php 
										echo generate_display_field (
											array (
												'data_type' => '1',
												'list_id' => 'note_type' 
											), 
											$note_data['title']
										);
								?>
								</td>
								<td>
									<div class="tooltip-container" data-toggle='tooltip' title='Demo'><span>...</span><div class="hidden_content">Hidden Tooltip Content</div></div>
									<?php echo $body ?>
								</td>
		 					</tr>
		 				</tbody>
		 			</table>
		 		<?php } ?>
			</div>
		  <div class="tab-pane fade" id="nav-phone" role="tabpanel" aria-labelledby="nav-phone-tab">Phone</div>
		  <div class="tab-pane fade" id="nav-emails" role="tabpanel" aria-labelledby="nav-emails-tab">Emails</div>
		  <div class="tab-pane fade" id="nav-sms" role="tabpanel" aria-labelledby="nav-sms-tab">SMS</div>
		  <div class="tab-pane fade" id="nav-fax" role="tabpanel" aria-labelledby="nav-fax-tab">FAX</div>
		  <div class="tab-pane fade" id="nav-postalletter" role="tabpanel" aria-labelledby="nav-postalletter-tab">Postal Lettter</div>
		</div>
	</div>
<script type="text/javascript">
		//alert('gggg');
		// $(document).ready(function() {
			// jQuery('[data-toggle="tooltip"]').tooltip({
		 //        content: function(){
		 //          var element = $(this);
		 //          console.log('dfdfd');
		 //          return element.find('.hidden_content').html();
		 //        },
		 //        track: true
		 //    });
		// });
	</script>
<!-- </body>
</html> -->
