<?php

/**
 * Used for adding dated reminders.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Craig Bezuidenhout <http://www.tajemo.co.za/>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 tajemo.co.za <http://www.tajemo.co.za/>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
  */

require_once("../../globals.php");
require_once("$srcdir/dated_reminder_functions.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$dateRanges = array();
// $dateranges = array ( number_period => text to display ) == period is always in the singular
// eg. $dateRanges['4_week'] = '4 Weeks From Now';
$dateRanges['1_day'] =  xl('1 Day From Now');
$dateRanges['2_day'] = xl('2 Days From Now');
$dateRanges['3_day'] = xl('3 Days From Now');
$dateRanges['4_day'] = xl('4 Days From Now');
$dateRanges['5_day'] = xl('5 Days From Now');
$dateRanges['6_day'] = xl('6 Days From Now');
$dateRanges['1_week'] = xl('1 Week From Now');
$dateRanges['2_week'] = xl('2 Weeks From Now');
$dateRanges['3_week'] = xl('3 Weeks From Now');
$dateRanges['4_week'] = xl('4 Weeks From Now');
$dateRanges['5_week'] = xl('5 Weeks From Now');
$dateRanges['6_week'] = xl('6 Weeks From Now');
$dateRanges['1_month'] = xl('1 Month From Now');
$dateRanges['2_month'] = xl('2 Months From Now');
$dateRanges['3_month'] = xl('3 Months From Now');
$dateRanges['4_month'] = xl('4 Months From Now');
$dateRanges['5_month'] = xl('5 Months From Now');
$dateRanges['6_month'] = xl('6 Months From Now');
$dateRanges['7_month'] = xl('7 Months From Now');
$dateRanges['8_month'] = xl('8 Months From Now');
$dateRanges['9_month'] = xl('9 Months From Now');
$dateRanges['1_year'] = xl('1 Year From Now');
$dateRanges['2_year'] = xl('2 Years From Now');

// --- need to add a check to ensure the post is being sent from the correct location ???

// default values for $this_message
$this_message = array('message' => '','message_priority' => 3,'dueDate' => '');
$forwarding = false;

// default values for Max words to input in a reminder
$max_reminder_words = 160;

// ---------------- FOR FORWARDING MESSAGES ------------->
if (isset($_GET['mID']) and is_numeric($_GET['mID'])) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $forwarding = true;
    $this_message = getReminderById($_GET['mID']);
}

// ---------------END FORWARDING MESSAGES ----------------



// --- add reminders
if ($_POST) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

// --- initialize $output as blank
    $output = '';
    $output = '<div><fieldset id="error_info" class="bg-danger text-white font-weight-bod" style="border: 1px solid var(--danger) !important; font-family: sans-serif; border-radius: 5px; padding: 20px 5px !important;">';// needs in-line styling because stylesheets not yet initialized
 // ------ fills an array with all recipients
    $sendTo = $_POST['sendTo'];

  // for incase of data error, this allows the previously entered data to re-populate the boxes
    $this_message['message'] = (isset($_POST['message']) ? $_POST['message'] : '');
    $this_message['priority'] = (isset($_POST['priority']) ? $_POST['priority'] : '');
    $this_message['dueDate'] = (isset($_POST['dueDate']) ? $_POST['dueDate'] : '');


// --------------------------------------------------------------------------------------------------------------------------
// --- check for the post, if it is valid, commit to the database, close this window and run opener.Handeler
    if (
// ------- check sendTo is not empty
        !empty($sendTo) and
// ------- check dueDate, only allow valid dates, todo -> enhance date checker
        isset($_POST['dueDate']) and preg_match('/\d{4}[-]\d{2}[-]\d{2}/', DateToYYYYMMDD($_POST['dueDate'])) and
// ------- check priority, only allow 1-3
        isset($_POST['priority']) and intval($_POST['priority']) <= 3 and
// ------- check message, only up to 160 characters limited by Db
        isset($_POST['message']) and mb_strlen($_POST['message']) <= $max_reminder_words and mb_strlen($_POST['message']) > 0 and
// ------- check if PatientID is set and in numeric
        isset($_POST['PatientID']) and is_numeric($_POST['PatientID'])
    ) {
        $dueDate = DateToYYYYMMDD($_POST['dueDate']);
        $priority = intval($_POST['priority']);
        $message = $_POST['message'];
        $fromID = $_SESSION['authUserID'];
        $patID = $_POST['PatientID'];
        if (isset($_POST['sendSeperately']) and $_POST['sendSeperately']) {
            foreach ($sendTo as $st) {
                $ReminderSent = sendReminder(array($st), $fromID, $message, $dueDate, $patID, $priority);
            }
        } else {
      // -------- Send the reminder
            $ReminderSent = sendReminder($sendTo, $fromID, $message, $dueDate, $patID, $priority);
        }

// --------------------------------------------------------------------------------------------------------------------------
        if (!$ReminderSent) {
            $output .= '<div class="text-center">* ' . xlt('Please select a valid recipient') . '</div> ';
        } else {
      // --------- echo javascript
            echo '<html><body>'
            . "<script src=\"" . $webroot . "/interface/main/tabs/js/include_opener.js\"></script>"
            . '<script>';
      // ------------ 1) refresh parent window this updates if sent to self
            echo '  if (opener && !opener.closed && opener.updateme) opener.updateme("new");';
      // ------------ 2) communicate with user
            echo '   alert("' . xls('Reminder Sent') . '");';
      // ------------ 3) close this window
            echo '  dlgclose();';
            echo '</script></body></html>';
      // --------- stop script from executing further
            exit;
        }

// --------------------------------------------------------------------------------------------------------------------------
    } else {
// ------- if POST error
        $output .= '<div class="text-center">* ' . xlt('Data Error') . '</div> ';
    }
    $output .= '</fieldset></div>';
// ------- if any errors, communicate with the user
    echo $output;
}

   // end add reminders

// get current patient, first check if this is a forwarded message, if it is use the original pid
if (isset($this_message['pid'])) {
    $patientID = (isset($this_message['pid']) ? $this_message['pid'] : 0);
    $reminder_title = xl("Forward this Reminder");
} else {
    $patientID = (isset($pid) ? $pid : 0);
    $reminder_title = xl("Send a Reminder");
}
?>
<html>
  <head>

    <title><?php echo xlt('Send a Reminder') ?></title>

    <?php Header::setupHeader(['datetime-picker', 'opener' ,'topdialog', 'common', 'moment']); ?>

    <script>
      $(function () {

        $('#timeSpan').change(function(){
          var value = $(this).val();
          var arr = value.split('_');
          var span = arr[1];
          var period = parseInt(arr[0]);
          var d=new Date();
          if(span == 'day'){
            d.setDate(d.getDate()+period);
          }
          else if(span == 'week'){
            var weekInDays = period * 7;
            d.setDate(d.getDate()+weekInDays);
          }
          else if(span == 'month'){
            d.setMonth(d.getMonth()+period);
          }
          else if(span == 'year'){
            var yearsInMonths = period * 12;
            d.setMonth(d.getMonth()+yearsInMonths);
          }
          var curr_date = d.getDate().toString();
          if(curr_date.length == 1){
            curr_date = '0'+curr_date;
          }
          var curr_month = d.getMonth() + 1; //months are zero based
          curr_month = curr_month.toString();
          if(curr_month.length == 1){
            curr_month = '0'+curr_month;
          }
          var curr_year = d.getFullYear();
          var fullDate = curr_year + "-" + curr_month + "-" + curr_date;
          $('#dueDate').val(moment(fullDate).format(<?php echo js_escape(DateFormatRead('validateJS'))?>));
        });


        $("#sendButton").click(function(){
          $('#errorMessage').html('');
          errorMessage = '';
          var PatientID = $('#PatientID').val();
          var dueDate = $('#dueDate').val();
          var priority = $('#priority:checked').val();
          var message = $("#message").val();
          // todo : check if PatientID is numeric , no rush as this is checked in the php after the post

          // check to see if a recipient has been set
          // todo : check if they are all numeric , no rush as this is checked in the php after the post

          if (!$("#sendTo option:selected").length){
             errorMessage = errorMessage + '* <?php echo xla('Please Select A Recipient') ?><br />';
          }


          // Check if Date is set
          // todo : add check to see if dueDate is a valid date , no rush as this is checked in the php after the post
          if(dueDate == ''){
             errorMessage = errorMessage + '* <?php echo xla('Please enter a due date') ?><br />';
          }

          // check if message is set
          if(message == ''){
             errorMessage = errorMessage + '* <?php echo xla('Please enter a message') ?><br />';
          }

          if(errorMessage != ''){
            // handle invalid queries
            $('#errorMessage').html(errorMessage);
            $('#error-info').show();
          }
          else{
            // handle valid queries
            // post the form to self
            top.restoreSession();
            $("#addDR").submit();
          }
          return false;
        })

        $("#removePatient").click(function(){
          $("#PatientID").val("0");
          $("#patientName").val("<?php echo xla('Click to select patient'); ?>");
          $(this).hide();
          return false;
        })
          // update word counter
          var messegeTextarea=$("#message")[0];
          limitText(messegeTextarea.form.message,messegeTextarea.form.countdown,<?php echo attr($max_reminder_words); ?>);

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
      })

        function sel_patient(){
           dlgopen('../../main/calendar/find_patient_popup.php', '_newDRPat', 650, 400, '', '');
        }

        function setpatient(pid, lname, fname, dob){
              $("#patientName").val(fname +' '+ lname)
              $("#PatientID").val(pid);
              $("#removePatient").show();
              return false;
        }

        function limitText(limitField, limitCount, limitNum) {
            if (limitField.value.length > limitNum) {
                limitField.value = limitField.value.substring(0, limitNum);
            } else {
                limitCount.value = limitNum - limitField.value.length;
            }
        }

        function selectAll(){
          $("#sendTo").each(function(){$("#sendTo option").prop("selected",true); });
        }
    </script>
    <style>
        @media only screen and (max-width: 680px) {
            [class*="col-"] {
                width: 100%;
                text-align: left !important;
            }
        }
        .oe-error-modal {
            border: 1px solid var(--danger) !important;
            background-color: var(--danger) !important;
            color: var(--white) !important;
            font-weight: bold;
            font-family: sans-serif;
            border-radius: 5px;
            padding: 20px 5px !important;
        }
    </style>

  </head>
  <body>
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

    <div class="container">
        <h4><?php echo attr($reminder_title) ?></h4>
        <form id="addDR"  class="form-horizontal" id="newMessage" method="post" onsubmit="return top.restoreSession()">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

            <fieldset id='error-info' class='oe-error-modal' style="display: none">
                <div class="text-center" id="errorMessage"></div>
            </fieldset>


            <div class="form-group mt-3">
                <label for="patientName"><?php echo xlt('Link To Patient') ?>: <i id="link-tooltip" class="fa fa-info-circle text-primary" aria-hidden="true" data-original-title="" title=""></i></label>
                <input type='text' id='patientName' name='patientName' class='form-control' value='<?php echo ($patientID > 0 ? attr(getPatName($patientID)) : xla('Click to select patient')); ?>' onclick='sel_patient()' title='<?php xla('Click to select patient'); ?>' readonly />
                <input type="hidden" name="PatientID" id="PatientID" value="<?php echo (isset($patientID) ? attr($patientID) : 0) ?>" />
                <button type="button" class="btn btn-secondary btn-undo mt-2" <?php echo ($patientID > 0 ? '' : 'style="display:none"') ?> id="removePatient"><?php echo xlt('unlink patient') ?></button>
            </div>

            <div class="form-group mt-3">
                <label for="patientName">
                    <?php echo xlt('Send to') ?>:
                    <br />
                    <?php echo xlt('([ctrl] + click to select multiple recipients)'); ?>
                </label>
                <select class="form-control" id="sendTo" name="sendTo[]" multiple="multiple">
                    <option value="<?php echo attr(intval($_SESSION['authUserID'])); ?>"><?php echo xlt('Myself') ?></option>
                        <?php //
                        $uSQL = sqlStatement('SELECT id, fname,	mname, lname  FROM  `users` WHERE  `active` = 1 AND `facility_id` > 0 AND id != ?', array(intval($_SESSION['authUserID'])));
                        for ($i = 2; $uRow = sqlFetchArray($uSQL); $i++) {
                            echo '<option value="' . attr($uRow['id']) . '">' . text($uRow['fname'] . ' ' . $uRow['mname'] . ' ' . $uRow['lname']) . '</option>';
                        }
                        ?>
                </select>
                <a class="btn btn-secondary btn-save mt-2" style="cursor: pointer" onclick="selectAll();"><?php echo xlt('Select all') ?></a>
            </div>

            <div class="form-group mt-3">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="sendSeperately" id="sendSeperately" title="<?php echo xla('Selecting this will create a message that needs to be processed by each recipient individually (this is not a group task).') ?>" />  <i id="select-tooltip" class="fa fa-info-circle text-primary" aria-hidden="true" data-original-title="" title=""></i> <?php echo xlt('Each recipient must set their own messages as completed.') ?>
                    </label>
                </div>
            </div>

            <div class="form-group row mt-3">
                <div class="col-5">
                    <label for="dueDate"><?php echo xlt('Due Date') ?>:</label>
                    <input type='text' class='datepicker form-control' name='dueDate' id="dueDate" value="<?php echo ($this_message['dueDate'] == '' ? oeFormatShortDate() : attr(oeFormatShortDate($this_message['dueDate']))); ?>" title='<?php echo attr(DateFormatRead('validateJS')) ?>'>
                </div>
                <div class="col-2">
                    <label for="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                    <div class="text-center"><?php echo xlt('OR') ?></div>
                </div>
                <div class="col-5">
                    <label for="timeSpan"><?php echo xlt('Select a Time Span') ?>:</label>
                    <select id="timeSpan" class="form-control">
                        <option value="__BLANK__"> -- <?php echo xlt('Select a Time Span') ?> -- </option>
                        <?php
                        $optionTxt = '';
                        foreach ($dateRanges as $val => $txt) {
                            $optionTxt .= '<option value="' . attr($val) . '">' . text($txt) . '</option>';
                        }
                        echo $optionTxt;
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group row mt-3">
                <div class="col-2 text-right">
                    <label for="priority"><?php echo xlt('Priority') ?>:</label>
                </div>
                <div class="col-10">
                    <label class="radio-inline"><input <?php echo ($this_message['message_priority'] == 3 ? 'checked="checked"' : '') ?> type="radio" name="priority" id="priority_3" value='3'><strong><?php echo xlt('Low{{Priority}}') ?></strong>
                    </label>
                    <label class="radio-inline"><input type="radio" name="optradio" class="d-none" /><input <?php echo ($this_message['message_priority'] == 2 ? 'checked="checked"' : '') ?> type="radio" name="priority" id="priority_2" value='2'><strong><?php echo xlt('Medium{{Priority}}') ?></strong>
                    </label>
                    <label class="radio-inline"><input type="radio" name="optradio" class="d-none" /><input <?php echo ($this_message['message_priority'] == 1 ? 'checked="checked"' : '') ?> type="radio" name="priority" id="priority_1" value='1'><strong><?php echo xlt('High{{Priority}}') ?></strong>
                    </label>
                </div>
            </div>

            <div class="form-group mt-3">
                <label class="text-right" for="message"><?php echo xlt('Type Your message here');?>:</label>
                <textarea onKeyDown="limitText(this.form.message,this.form.countdown,<?php echo attr(addslashes($max_reminder_words)); ?>);" onKeyUp="limitText(this.form.message,this.form.countdown,<?php echo attr(addslashes($max_reminder_words)); ?>);" class="form-control text-left" rows="5" name="message" id="message" placeholder="<?php echo xla('Maximum characters') ?> : <?php echo attr($max_reminder_words); ?>"><?php echo text($this_message['dr_message_text'] ?? '');?></textarea>
            </div>

            <div class="form-row mt-3">
                <label class="col-form-label col" for="countdown"><?php echo xlt('Characters Remaining') ?>:</label>
                <div class="col-2">
                    <input class="form-control" readonly type="text" name="countdown" id="countdown" value="<?php echo attr($max_reminder_words); ?>" />
                </div>
            </div>
            <button type='submit' class='btn btn-primary btn-send-msg' name="sendButton" id="sendButton" value="<?php echo xla('Send This Message');?>"  onclick='return this.clicked = true;'><?php echo xlt('Send This Message'); ?></button>
        </form>
    <div class="table-responsive">
    <?php
        $_GET['sentBy'] = array($_SESSION['authUserID']);
        $_GET['sd'] = oeFormatShortDate();
        $TempRemindersArray = logRemindersArray();
        $remindersArray = array();
    foreach ($TempRemindersArray as $RA) {
        $remindersArray[$RA['messageID']]['messageID'] = $RA['messageID'];
        $remindersArray[$RA['messageID']]['ToName'] = ((!empty($remindersArray[$RA['messageID']]['ToName'])) ? $remindersArray[$RA['messageID']]['ToName'] . ', ' . ($RA['ToName'] ?? '') : ($RA['ToName'] ?? ''));
        $remindersArray[$RA['messageID']]['PatientName'] = $RA['PatientName'];
        $remindersArray[$RA['messageID']]['message'] = $RA['message'];
        $remindersArray[$RA['messageID']]['dDate'] = $RA['dDate'];
    }

        echo '<h4>' . xlt('Messages You have sent Today') . '</h4>';
        echo '<table class="table table-bordered table-hover" id="logTable">
                <thead>
                  <tr>
                    <th>' . xlt('ID') . '</th>
                    <th>' . xlt('To{{Destination}}') . '</th>
                    <th>' . xlt('Patient') . '</th>
                    <th>' . xlt('Message') . '</th>
                    <th>' . xlt('Due Date') . '</th>
                  </tr>
                </thead>
                <tbody>';

    foreach ($remindersArray as $RA) {
        echo '<tr class="heading">
                  <td>' . text($RA['messageID']) . '</td>
                  <td>' . text($RA['ToName']) . '</td>
                  <td>' . text($RA['PatientName']) . '</td>
                  <td>' . text($RA['message']) . '</td>
                  <td>' . text(oeFormatShortDate($RA['dDate'])) . '</td>
                </tr>';
    }

        echo '</tbody></table></fieldset><div>';
    ?>
    </div><!--end of container div-->
  <script>
    $(function () {
        $('#link-tooltip').tooltip({title: "<?php echo xla('This message need not necessarily be linked to a patient'); ?>"});
        $('#select-tooltip').tooltip({title: "<?php echo xla('If the checkbox is checked then each individual of a group receiving this message will have to sign off by clicking the Set As Completed button'); ?>"});
    });
  </script>
</body>
</html>
