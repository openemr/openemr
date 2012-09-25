<?php
// Copyright (C) 2010 OpenEMR Support LLC
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once(dirname(__FILE__)."/../../interface/globals.php");
require_once ($GLOBALS['srcdir'] . "/classes/postmaster.php");
require_once ($GLOBALS['srcdir'] . "/maviq_phone_api.php");
require_once($GLOBALS['srcdir'] . "/reminders.php");
require_once($GLOBALS['srcdir'] . "/report_database.inc");

// This is only pertinent for users of php versions less than 5.2
//  (ie. this wrapper is only loaded when php version is less than
//   5.2; otherwise the native php json functions are used)
require_once "$srcdir/jsonwrapper/jsonwrapper.php";

//To improve performance and not freeze the session when running this
// report, turn off session writing. Note that php session variables
// can not be modified after the line below. So, if need to do any php
// session work in the future, then will need to remove this line.
session_write_close();

//Remove time limit, since script can take many minutes
set_time_limit(0);

// If report_id, then just going to show the report log
$report_id = ($_GET['report_id']) ? $_GET['report_id'] : "";

// Set the "nice" level of the process for this script when. When the "nice" level
// is increased, this cpu intensive script will have less affect on the performance
// of other server activities, albeit it may negatively impact the performance
// of this script (note this is only applicable for linux).
if (empty($report_id) && !empty($GLOBALS['pat_rem_clin_nice'])) {
  proc_nice($GLOBALS['pat_rem_clin_nice']);
}
?>

<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="batchcom.css" type="text/css">
</head>
<body class="body_top">
<span class="title"><?php echo htmlspecialchars(xl('Patient Reminder Batch Job'), ENT_NOQUOTES)?></span>

<?php
// Collect the sender information
// TODO
// $sender_name
// $email_address
//
?>

<table>
 <tr>
  <td class='text' align='left' colspan="3"><br>

   <?php if ($report_id) {
     // collect log from a previous run to show
     $results_log = collectReportDatabase($report_id);
     $data_log = json_decode($results_log['data'],TRUE);
     $update_rem_log = $data_log[0];
     if ($results_log['type'] == "process_send_reminders") {
       $send_rem_log = $data_log[1];
     }
     echo "<span class='text'>" . xlt("Date of Report") . ": " . text($results_log['date_report']) . "</span><br><br>";
   }
   else {
     $update_rem_log = update_reminders_batch_method();
     $send_rem_log = send_reminders();
   }
   ?>

    <span class="text"><?php echo htmlspecialchars(xl('The patient reminders have been updated'), ENT_NOQUOTES) . ":"?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total active actions'), ENT_NOQUOTES) . ": " . $update_rem_log['total_active_actions'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total active reminders before update'), ENT_NOQUOTES) . ": " . $update_rem_log['total_pre_active_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total unsent reminders before update'), ENT_NOQUOTES) . ": " . $update_rem_log['total_pre_unsent_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total active reminders after update'), ENT_NOQUOTES) . ": " . $update_rem_log['total_post_active_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total unsent reminders after update'), ENT_NOQUOTES) . ": " . $update_rem_log['total_post_unsent_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total new reminders'), ENT_NOQUOTES) . ": " . $update_rem_log['number_new_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total updated reminders'), ENT_NOQUOTES) . ": " . $update_rem_log['number_updated_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total inactivated reminders'), ENT_NOQUOTES) . ": " . $update_rem_log['number_inactivated_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total unchanged reminders'), ENT_NOQUOTES) . ": " . $update_rem_log['number_unchanged_reminders'];?></span><br>

   <?php if ($results_log['type'] != "process_reminders") { ?> 
    <br><span class="text"><?php echo htmlspecialchars(xl('The patient reminders have been sent'), ENT_NOQUOTES) . ":"?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total unsent reminders before sending process'), ENT_NOQUOTES) . ": " . $send_rem_log['total_pre_unsent_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total unsent reminders after sending process'), ENT_NOQUOTES) . ": " . $send_rem_log['total_post_unsent_reminders'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total successful reminders sent via email'), ENT_NOQUOTES) . ": " . $send_rem_log['number_success_emails'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total failed reminders sent via email'), ENT_NOQUOTES) . ": " . $send_rem_log['number_failed_emails'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total successful reminders sent via phone'), ENT_NOQUOTES) . ": " . $send_rem_log['number_success_calls'];?></span><br>
      <span class="text"><?php echo htmlspecialchars(xl('Total failed reminders sent via phone'), ENT_NOQUOTES) . ": " . $send_rem_log['number_unchanged_reminders'];?></span><br>

    <br><span class="text"><?php echo htmlspecialchars(xl('(Email delivery is immediate, while automated VOIP is sent to the service provider for further processing.)'), ENT_NOQUOTES)?></span><br>
   <?php } // end of ($results_log['type'] != "process_reminders") ?>

   <?php if (report_id) { ?>
     <br><input type="button" value="<?php echo htmlspecialchars(xl('Back'), ENT_QUOTES); ?>" onClick="top.restoreSession(); window.open('../reports/report_results.php','_self',false)"><br><br><br>
   <?php } else { ?>
     <br><input type="button" value="<?php echo htmlspecialchars(xl('Close'), ENT_QUOTES); ?>" onClick="window.close()"><br><br><br>
   <?php } ?>

  </td>
 </tr>
</table>
<br><br>
</body>
</html>

