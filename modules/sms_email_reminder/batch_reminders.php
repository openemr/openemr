<?php

// Copyright (C) 2010 OpenEMR Support LLC
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// comment below exit if plan to use this script
exit;

$backpic = "";
$ignoreAuth = 1;

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once($GLOBALS['srcdir'] . "/maviq_phone_api.php");
require_once($GLOBALS['srcdir'] . "/reminders.php");

use OpenEMR\Core\Header;

?>

<html>
<head>
<?php Header::setupHeader(); ?>
<link rel="stylesheet" href="batchcom.css">
</head>
<body class="body_top">
<span class="title"><?php echo xlt('Patient Reminder Batch Job'); ?></span>

<?php
// Collect the sender information
// TODO
// $sender_name
// $email_address
//
?>

<table>
 <tr>
  <td class='text' align='left' colspan="3"><br />

    <?php $update_rem_log = update_reminders(); ?>

    <span class="text"><?php echo xlt('The patient reminders have been updated') . ":"?></span><br />
      <span class="text"><?php echo xlt('Total active actions') . ": " . text($update_rem_log['total_active_actions']); ?></span><br />
      <span class="text"><?php echo xlt('Total active reminders before update') . ": " . text($update_rem_log['total_pre_active_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total unsent reminders before update') . ": " . text($update_rem_log['total_pre_unsent_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total active reminders after update') . ": " . text($update_rem_log['total_post_active_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total unsent reminders after update') . ": " . text($update_rem_log['total_post_unsent_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total new reminders') . ": " . text($update_rem_log['number_new_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total updated reminders') . ": " . text($update_rem_log['number_updated_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total inactivated reminders') . ": " . text($update_rem_log['number_inactivated_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total unchanged reminders') . ": " . text($update_rem_log['number_unchanged_reminders']) ;?></span><br />

    <?php $send_rem_log = send_reminders(); ?>

    <br /><span class="text"><?php echo xlt('The patient reminders have been sent') . ":"?></span><br />
      <span class="text"><?php echo xlt('Total unsent reminders before sending process') . ": " . text($send_rem_log['total_pre_unsent_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total unsent reminders after sending process') . ": " . text($send_rem_log['total_post_unsent_reminders']); ?></span><br />
      <span class="text"><?php echo xlt('Total successful reminders sent via email') . ": " . text($send_rem_log['number_success_emails']); ?></span><br />
      <span class="text"><?php echo xlt('Total failed reminders sent via email') . ": " . text($send_rem_log['number_failed_emails']); ?></span><br />
      <span class="text"><?php echo xlt('Total successful reminders sent via phone') . ": " . text($send_rem_log['number_success_calls']); ?></span><br />
      <span class="text"><?php echo xlt('Total failed reminders sent via phone') . ": " . text($send_rem_log['number_unchanged_reminders']); ?></span><br />

    <br /><span class="text"><?php echo xlt('(Email delivery is immediate, while automated VOIP is sent to the service provider for further processing.)'); ?></span><br /><br />
  </td>
 </tr>
</table>
<br /><br />
</body>
</html>

