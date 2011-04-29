<?php
// Copyright (C) 2010 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Functions are kept here that will support reminders.

require_once(dirname(__FILE__) . "/clinical_rules.php");


// Display the patient reminder widget.
// Parameters:
//   $patient_id - pid of selected patient
//   $dateTarget - target date. If blank then will test with current date as target.
function patient_reminder_widget($patient_id,$dateTarget='') {

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Update reminders for patient
  update_reminders($dateTarget, $patient_id);

  // Fetch the active reminders
  $listReminders = fetch_reminders($patient_id);  

  if (empty($listReminders)) {
    // No reminders to show.
    echo htmlspecialchars( xl('No active patient reminders.'), ENT_NOQUOTES);
    return;
  }

  echo "<table cellpadding='0' cellspacing='0'>";
  foreach ($listReminders as $reminder) {
    echo "<tr><td style='padding:0 1em 0 1em;'><span class='small'>";
      // show reminder label
      echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_action_category'),$reminder['category']) .
        ": " . generate_display_field(array('data_type'=>'1','list_id'=>'rule_action'),$reminder['item']);
    echo "</span></td><td style='padding:0 1em 0 1em;'><span class='small'>";
      // show reminder due status
      echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_reminder_due_opt'),$reminder['due_status']);
    echo "</span></td><td style='padding:0 1em 0 1em;'><span class='small'>";
      // show reminder sent date
      if (empty($reminder['date_sent'])) {
        echo htmlspecialchars( xl('Reminder Not Sent Yet'), ENT_NOQUOTES);
      }
      else {
        echo htmlspecialchars( xl('Reminder Sent On').": ".$reminder['date_sent'], ENT_NOQUOTES);
      }
    echo "</span></td></tr>";
  } 
  echo "</table>";
}

// Function to update reminders.
// Parameters:
//   $dateTarget - target date. If blank then will test with current date as target.
//   $patient_id - pid of patient. If blank then will check all patients.
// Return:
//   Returns a array with following element:
//     'total_active_actions'         - Number of active actions.
//     'total_pre_active_reminders'   - Number of active reminders before processing.
//     'total_pre_unsent_reminders'   - Number of unsent reminders before processing.
//     'total_post_active_reminders'  - Number of active reminders after processing.
//     'total_post_unsent_reminders'   - Number of unsent reminders after processing.
//     'number_new_reminders'         - Number of new reminders
//     'number_updated_reminders'     - Number of updated reminders (due_status change)
//     'number_inactivated_reminders' - Number of inactivated reminders.
//     'number_unchanged_reminders'   - Number of unchanged reminders.
function update_reminders($dateTarget='', $patient_id='') {

  $logging = array();

  // Set date to current if not set
  $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect reminders (note that this function removes redundant and keeps the most distant
  //   reminder (ie. prefers 'past_due' over 'due' over 'soon_due')
  $collectedReminders = test_rules_clinic('','patient_reminder',$dateTarget,'reminders',$patient_id);
  $logging['total_active_actions'] = count($collectedReminders);

  // For logging purposes only:
  //  Collect number active of active and unsent reminders
  $logging['total_pre_active_reminders'] = count(fetch_reminders($patient_id));
  $logging['total_pre_unsent_reminders'] = count(fetch_reminders($patient_id, 'unsent'));

  // Migrate reminders into the patient_reminders table
  $logging['number_new_reminders'] = 0;
  $logging['number_updated_reminders'] = 0;
  $logging['number_unchanged_reminders'] = 0;
  foreach ($collectedReminders as $reminder) {

    // See if a reminder already exist
    $sql = "SELECT `id`, `pid`, `due_status`, `category`, `item` FROM `patient_reminders` WHERE " .
      "`active`='1' AND `pid`=? AND `category`=? AND `item`=?";
    $result = sqlQuery($sql, array($reminder['pid'], $reminder['category'], $reminder['item']) );

    if (empty($result)) {
      // It does not yet exist, so add a new reminder
      $sql = "INSERT INTO `patient_reminders` (`pid`, `due_status`, `category`, `item`, `date_created`) " .
        "VALUES (?, ?, ?, ?, NOW())";
      sqlStatement($sql, array($reminder['pid'], $reminder['due_status'], $reminder['category'], $reminder['item']) );
      $logging['number_new_reminders']++;
    }
    else {
      // It already exist (see if if needs to be updated via adding a new reminder)
      if ($reminder['due_status'] == $result['due_status']) {
        // No change in due status, so no need to update
        $logging['number_unchanged_reminders']++;
        continue;
      }
      else {
        // Change in due status, so inactivate current reminder and create a new one
        // First, inactivate the previous reminder
        $sql = "UPDATE `patient_reminders` SET `active` = '0', `reason_inactivated` = 'due_status_update', " .
          "`date_inactivated` = NOW() WHERE `id`=?";
        sqlStatement($sql, array($result['id']) );
        // Then, add the new reminder
        $sql = "INSERT INTO `patient_reminders` (`pid`, `due_status`, `category`, `item`, `date_created`) " .
          "VALUES (?, ?, ?, ?, NOW())";
        sqlStatement($sql, array($reminder['pid'], $reminder['due_status'], $reminder['category'], $reminder['item']) );
      }
    }
  }

  // Inactivate reminders that no longer exist
  // Go through each active reminder and ensure it is in the current list
  $sqlReminders = fetch_reminders($patient_id);
  $logging['number_inactivated_reminders'] = 0;
  foreach ( $sqlReminders as $row ) {
    $inactivateFlag = true;
    foreach ($collectedReminders as $reminder) {
      if ( ($row['pid'] == $reminder['pid']) &&
           ($row['category'] == $reminder['category']) &&
           ($row['item'] == $reminder['item']) &&
           ($row['due_status'] == $reminder['due_status']) ) {
        // The sql reminder has been confirmed, so do not inactivate it
        $inactivateFlag = false;
        break;
      }
    }
    if ($inactivateFlag) {
      // The sql reminder was not confirmed, so inactivate it
      $sql = "UPDATE `patient_reminders` SET `active` = '0', `reason_inactivated` = 'auto', " .
        "`date_inactivated` = NOW() WHERE `id`=?";
      sqlStatement($sql, array($row['id']) );
      $logging['number_inactivated_reminders'] = 0;
    }
  }

  // For logging purposes only:
  //  Collect number of active and unsent reminders
  $logging['total_post_active_reminders'] = count(fetch_reminders($patient_id));
  $logging['total_post_unsent_reminders'] = count(fetch_reminders($patient_id, 'unsent'));

  return $logging;

}

// Function to send reminders
// Return:
//   Returns a array with following element:
//     'total_pre_unsent_reminders'  - Number of reminders before processing.
//     'total_post_unsent_reminders' - Number of reminders after processing.
//     'number_success_emails'       - Number of successfully sent email reminders.
//     'number_failed_emails'        - Number of failed sent email reminders.
//     'number_success_calls'        - Number of successfully call reminders.
//     'number_failed_calls'         - Number of failed call reminders.
function send_reminders() {

  $logging = array();

  // Collect active reminders that have not yet been sent.
  $active_unsent_reminders = fetch_reminders($patient_id, 'unsent');
  $logging['total_pre_unsent_reminders'] = count($active_unsent_reminders);

  // Send the unsent reminders
  $logging['number_success_emails'] = 0;
  $logging['number_failed_emails'] = 0;
  $logging['number_success_calls'] = 0;
  $logging['number_failed_calls'] = 0;
  foreach ( $active_unsent_reminders as $reminder ) {

    // Collect patient information that reminder is going to.
    $sql = "SELECT `fname`, `lname`, `email`, `phone_home`, `hipaa_voice`, `hipaa_allowemail` from `patient_data` where `pid`=?";
    $result = sqlQuery($sql, array($reminder['pid']) );
	$patientfname = $result['fname'];
    $patientlname = $result['lname'];
	$patientemail = $result['email'];
    $patientphone = $result['phone_home'];
    $hipaa_voice = $result['hipaa_voice'];
    $hipaa_allowemail = $result['hipaa_allowemail'];

    // Email to patient if Allow Email and set reminder sent flag.
    if ($hipaa_allowemail == "YES") {
      $mail = new MyMailer();
	  $sender_name = $GLOBALS['patient_reminder_sender_name'];
      $email_address = $GLOBALS['patient_reminder_sender_email'];
      $mail->FromName = $sender_name;  // required
      $mail->Sender = $email_address;    // required
      $mail->From = $email_address;    // required
      $mail->AddAddress($patientemail, $patientfname.", ".$patientlname);   // required
      $mail->AddReplyTo($email_address,$sender_name);  // required
      $category_title = generate_display_field(array('data_type'=>'1','list_id'=>'rule_action_category'),$reminder['category']);
      $item_title = generate_display_field(array('data_type'=>'1','list_id'=>'rule_action'),$reminder['item']);
	  $mail->Body = "Dear ".$patientfname.", This is a message from your clinic to remind you of your ".$category_title.": ".$item_title;
      $mail->Subject = "Clinic Reminder";
      if ($mail->Send()) {
        // deal with and keep track of this successful email
        sqlStatement("UPDATE `patient_reminders` SET `email_status`='1', `date_sent`=NOW() WHERE id=?", array($reminder['id']) );
        $logging['number_success_emails']++;
      }
      else {
        // deal with and keep track of this unsuccesful email
        $logging['number_failed_emails']++;
      }
    }

    // Call to patient if Allow Voice Message and set reminder sent flag.
    if ($hipaa_voice == "YES") {
      // Automated VOIP service provided by Maviq. Please visit http://signup.maviq.com for more information.
      $siteId = $GLOBALS['phone_gateway_username'];
      $token = $GLOBALS['phone_gateway_password'];
      $endpoint = $GLOBALS['phone_gateway_url'];
      $client = new MaviqClient($siteId, $token, $endpoint);
      //Set up params.
      $data = array(
        "firstName" => $patientfname,
        "lastName" => $patientlname,
        "phone" => $patientphone,
        //"apptDate" => "$scheduled_date[1]/$scheduled_date[2]/$scheduled_date[0]",
        "timeRange" => "10-18",
        "type" => "reminder",
        "timeZone" => date('P'),
        "greeting" => str_replace("[[sender]]", $sender_name, str_replace("[[patient_name]]", $patientfname, $myrow['reminder_content']))
      );

      // Make the call.
      $response = $client->sendRequest("appointment", "POST", $data);

      if ($response->IsError) {
        // deal with and keep track of this unsuccessful call
        $logging['number_failed_calls']++;
      }
      else {
        // deal with and keep track of this succesful call
        sqlStatement("UPDATE `patient_reminders` SET `voice_status`='1', `date_sent`=NOW() WHERE id=?", array($reminder['id']) );
        $logging['number_success_calls']++;
      }
    }
  }

  // For logging purposes only:
  //  Collect active reminders that have not yet been sent.
  $logging['total_post_unsent_reminders'] = count(fetch_reminders($patient_id, 'unsent'));

  return $logging;
}

// Function to fetch reminders
// Parameters:
//   $patient_id - pid of patient. If blank then will check all patients.
//   $type       - unsent (unsent) vs all active (BLANK) reminders
//   $due_status - due status of reminders (soon_due,due,past_due). If blank,
//                   then will return all.
//   $select     - Select component of select statement. If blank, then
//                   will return all columns.
// Return:
//   Returns a array of reminders
function fetch_reminders($patient_id='',$type='',$due_status='',$select='*') {

  $arraySqlBind = array();

  if (!empty($patient_id)) {
    $where = "`pid`=? AND ";
    array_push($arraySqlBind,$patient_id);
  }

  if (!empty($due_status)) {
    $where .= "`due_status`=? AND ";
    array_push($arraySqlBind,$due_status);
  }

  if (empty($type)) {
    $where .= "`active`='1'";
  }
  else { // $type == 'unsent'
    $where .= "`active`='1' AND `date_sent` IS NULL";
  }

  $order = "`due_status`, `date_created`";

  $sql = "SELECT " . $select . " FROM `patient_reminders` WHERE " .
    $where . " ORDER BY " . $order;
  $rez = sqlStatement($sql, $arraySqlBind);

  for($iter=0; $row=sqlFetchArray($rez); $iter++)
    $returnval[$iter]=$row;

  return $returnval;
}

?>

