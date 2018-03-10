<?php
/**
 * Audit Log Tamper Report.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Anil N <aniln@ensoftek.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Ensoftek
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/log.inc");
require_once("$srcdir/crypto.php");

?>
<html>
<head>

<title><?php echo xlt("Audit Log Tamper Report"); ?></title>

<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<style>
#logview {
    width: 100%;
}
#logview table {
    width:100%;
    border-collapse: collapse;
}
#logview th {
    background-color: #cccccc;
    cursor: pointer; cursor: hand;
    padding: 5px 5px;
    align: left;
    text-align: left;
}

#logview td {
    background-color: #ffffff;
    border-bottom: 1px solid #808080;
    cursor: default;
    padding: 5px 5px;
    vertical-align: top;
}
.highlight {
    background-color: #336699;
    color: #336699;
}
.tamperColor{
    color:red;
}
</style>
<script>
//function to disable the event type field if the event name is disclosure
function eventTypeChange(eventname)
{
         if (eventname == "disclosure") {
            document.theform.type_event.disabled = true;
          }
         else {
            document.theform.type_event.disabled = false;
         }
}

// VicarePlus :: This invokes the find-patient popup.
 function sel_patient() {
  dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
 }

// VicarePlus :: This is for callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.theform;
  f.form_patient.value = lname + ', ' + fname;
  f.form_pid.value = pid;
 }

</script>
</head>
<body class="body_top">
<font class="title"><?php echo xlt('Audit Log Tamper Report'); ?></font>
<br>
<?php
$err_message=0;

$start_date = (!empty($_GET["start_date"])) ? DateTimeToYYYYMMDDHHMMSS($_GET["start_date"]) : date("Y-m-d") . " 00:00:00";
$end_date = (!empty($_GET["end_date"])) ? DateTimeToYYYYMMDDHHMMSS($_GET["end_date"]) : date("Y-m-d") . " 23:59:59";
/*
 * Start date should not be greater than end date - Date Validation
 */
if ($start_date > $end_date) {
    echo "<table><tr class='alert'><td colspan=7>";
    echo xlt('Start Date should not be greater than End Date');
    echo "</td></tr></table>";
    $err_message=1;
}

if ($_GET["form_patient"]) {
    $form_patient = $_GET['form_patient'];
}

?>
<?php
$form_user = $_REQUEST['form_user'];
$form_pid = $_REQUEST['form_pid'];
if ($form_patient == '') {
    $form_pid = '';
}

?>
<br>
<FORM METHOD="GET" name="theform" id="theform" onSubmit='top.restoreSession()'>
<?php

$sortby = $_GET['sortby'];
?>
<input type="hidden" name="sortby" id="sortby" value="<?php echo attr($sortby); ?>">
<input type=hidden name=csum value="">
<table>
<tr><td>
<span class="text"><?php echo xlt('Start Date'); ?>: </span>
</td><td>
<input type="text" size="18" class="datetimepicker" name="start_date" id="start_date" value="<?php echo attr(oeFormatDateTime($start_date, 0, true)); ?>" title="<?php echo xla('Start date'); ?>" />
</td>
<td>
<span class="text"><?php echo xlt('End Date'); ?>: </span>
</td><td>
<input type="text" size="18" class="datetimepicker" name="end_date" id="end_date" value="<?php echo attr(oeFormatDateTime($end_date, 0, true)); ?>" title="<?php echo xla('End date'); ?>" />
</td>

<td>
&nbsp;&nbsp;<span class='text'><?php echo xlt('Patient'); ?>: </span>
</td>
<td>
<input type='text' size='20' name='form_patient' style='width:100%;cursor:pointer;cursor:hand' value='<?php echo attr($form_patient) ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xlt('Click to select patient'); ?>' />
<input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
</td>
</tr>

<tr><td>
<span class='text'><?php echo xlt('Include Checksum'); ?>: </span>
</td><td>
<?php

$check_sum = isset($_GET['check_sum']);
?>
<input type="checkbox" name="check_sum" <?php echo ($check_sum) ? "checked" : ""; ?>>
</td>
<td>
<input type=hidden name="event" value=<?php echo attr($event) ; ?>>
<a href="javascript:document.theform.submit();" class='link_submit'>[<?php echo xlt('Refresh'); ?>]</a>
</td>
</tr>
</table>
</FORM>


<?php if ($start_date && $end_date && $err_message!=1) { ?>
<div id="logview">
<span class="text" id="display_tamper" style="display:none;"><?php echo xlt('Following rows in the audit log have been tampered'); ?></span>
<table>
 <tr>
  <th id="sortby_date" class="text" title="<?php echo xla('Sort by Tamper date/time'); ?>"><?php echo xlt('Tamper Date'); ?></th>
  <th id="sortby_user" class="text" title="<?php echo xla('Sort by User'); ?>"><?php echo xlt('User'); ?></th>
  <th id="sortby_pid" class="text" title="<?php echo xla('Sort by PatientID'); ?>"><?php echo xlt('PatientID'); ?></th>
  <th id="sortby_comments" class="text" title="<?php echo  xla('Sort by Comments'); ?>"><?php echo xlt('Comments'); ?></th>
    <?php  if ($check_sum) {?>
  <th id="sortby_newchecksum" class="text" title="<?php xla('Sort by New Checksum'); ?>"><?php echo xlt('Tampered Checksum'); ?></th>
  <th id="sortby_oldchecksum" class="text" title="<?php xla('Sort by Old Checksum'); ?>"><?php echo xlt('Original Checksum'); ?></th>
    <?php } ?>
 </tr>
<?php

$eventname = $_GET['eventname'];
$type_event = $_GET['type_event'];
?>
<input type=hidden name=event value=<?php echo attr($eventname)."-".attr($type_event) ?>>
<?php
$type_event = "update";
$tevent="";
$gev="";
if ($eventname != "" && $type_event != "") {
    $getevent=$eventname."-".$type_event;
}

if (($eventname == "") && ($type_event != "")) {
    $tevent=$type_event;
} else if ($type_event =="" && $eventname != "") {
    $gev=$eventname;
} else if ($eventname == "") {
    $gev = "";
} else {
    $gev = $getevent;
}

$dispArr = array();
$icnt = 1;
if ($ret = getEvents(array('sdate' => $start_date,'edate' => $end_date, 'user' => $form_user, 'patient' => $form_pid, 'sortby' => $_GET['sortby'], 'levent' =>$gev, 'tevent' =>$tevent))) {
    foreach ($ret as $iter) {
        //translate comments
        $patterns = array ('/^success/','/^failure/','/ encounter/');
        $replace = array ( xl('success'), xl('failure'), xl('encounter', '', ' '));

        $dispCheck = false;
        $log_id = $iter['id'];
        $commentEncrStatus = "No";
        $encryptVersion = 0;
        $logEncryptData = logCommentEncryptData($log_id);

        if (count($logEncryptData) > 0) {
            $commentEncrStatus = $logEncryptData['encrypt'];
            $checkSumOld = $logEncryptData['checksum'];
            $encryptVersion = $logEncryptData['version'];
            $concatLogColumns = $iter['date'].$iter['event'].$iter['user'].$iter['groupname'].$iter['comments'].$iter['patient_id'].$iter['success'].$iter['checksum'].$iter['crt_user'];
            $checkSumNew = sha1($concatLogColumns);

            if ($checkSumOld != $checkSumNew) {
                $dispCheck = true;
            } else {
                $dispCheck = false;
                continue;
            }
        } else {
            continue;
        }

        if ($commentEncrStatus == "Yes") {
            if ($encryptVersion == 1) {
                // Use new openssl method
                if (extension_loaded('openssl')) {
                    $trans_comments = preg_replace($patterns, $replace, trim(aes256Decrypt($iter["comments"])));
                } else {
                    $trans_comments = xl("Unable to decrypt these comments since the PHP openssl module is not installed.");
                }
            } else { //$encryptVersion == 0
                // Use old mcrypt method
                if (extension_loaded('mcrypt')) {
                    $trans_comments = preg_replace($patterns, $replace, trim(aes256Decrypt_mycrypt($iter["comments"])));
                } else {
                    $trans_comments = xl("Unable to decrypt these comments since the PHP mycrypt module is not installed.");
                }
            }
        } else {
            $trans_comments = preg_replace($patterns, $replace, trim($iter["comments"]));
        }

        //Alter Checksum value records only display here
        if ($dispCheck) {
            $dispArr[] = $icnt++;
        ?>
     <TR class="oneresult">
          <TD class="text tamperColor"><?php echo text(oeFormatDateTime($iter["date"], "global", true)); ?></TD>
          <TD class="text tamperColor"><?php echo text($iter["user"]); ?></TD>
          <TD class="text tamperColor"><?php echo text($iter["patient_id"]);?></TD>
          <TD class="text tamperColor"><?php echo text($trans_comments);?></TD>
            <?php  if ($check_sum) { ?>
          <TD class="text tamperColor"><?php echo text($checkSumNew);?></TD>
          <TD class="text tamperColor"><?php echo text($checkSumOld);?></TD>
            <?php } ?>
     </TR>
<?php
        }
    }
}

if (count($dispArr) == 0) {?>
     <TR class="oneresult">
            <?php
            $colspan = 4;
            if ($check_sum) {
                $colspan=6;
            }
            ?>
        <TD class="text" colspan="<?php echo $colspan;?>" align="center"><?php echo xlt('No audit log tampering detected in the selected date range.'); ?></TD>
     </TR>
<?php
} else {?>
    <script type="text/javascript">$('#display_tamper').css('display', 'block');</script>
    <?php
}

?>
</table>
</div>
<?php } ?>
</body>
<script language="javascript">

// jQuery stuff to make the page a little easier to use
$(document).ready(function(){
    // funny thing here... good learning experience
    // the TR has TD children which have their own background and text color
    // toggling the TR color doesn't change the TD color
    // so we need to change all the TR's children (the TD's) just as we did the TR
    // thus we have two calls to toggleClass:
    // 1 - for the parent (the TR)
    // 2 - for each of the children (the TDs)
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); $(this).children().toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); $(this).children().toggleClass("highlight"); });

    // click-able column headers to sort the list
    $("#sortby_date").click(function() { $("#sortby").val("date"); $("#theform").submit(); });
    $("#sortby_event").click(function() { $("#sortby").val("event"); $("#theform").submit(); });
    $("#sortby_user").click(function() { $("#sortby").val("user"); $("#theform").submit(); });
    $("#sortby_cuser").click(function() { $("#sortby").val("user"); $("#theform").submit(); });
    $("#sortby_group").click(function() { $("#sortby").val("groupname"); $("#theform").submit(); });
    $("#sortby_pid").click(function() { $("#sortby").val("patient_id"); $("#theform").submit(); });
    $("#sortby_success").click(function() { $("#sortby").val("success"); $("#theform").submit(); });
    $("#sortby_comments").click(function() { $("#sortby").val("comments"); $("#theform").submit(); });
    $("#sortby_oldchecksum").click(function() { $("#sortby").val("checksum"); $("#theform").submit(); });
    $("#sortby_newchecksum").click(function() { $("#sortby").val("checksum"); $("#theform").submit(); });

    $('.datetimepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = true; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>

</html>

