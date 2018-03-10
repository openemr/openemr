<?php
/**
 * Log Viewer.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/log.inc");
require_once("$srcdir/crypto.php");
?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

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
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

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
<font class="title"><?php echo xlt('Logs Viewer'); ?></font>
<br>
<?php
$err_message=0;

$start_date = (!empty($_GET["start_date"])) ? DateTimeToYYYYMMDDHHMMSS($_GET["start_date"]) : date("Y-m-d") . " 00:00";
$end_date = (!empty($_GET["end_date"])) ? DateTimeToYYYYMMDDHHMMSS($_GET["end_date"]) : date("Y-m-d") . " 23:59";
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
    $form_patient = isset($_GET["form_patient"]) ? $_GET["form_patient"] : "";
}

?>
<?php
$form_user = isset($_REQUEST['form_user']) ? $_REQUEST['form_user'] : '';
$form_pid = isset($_REQUEST['form_pid']) ? $_REQUEST['form_pid'] : '';

if ($form_patient == '') {
    $form_pid = '';
}

$res = sqlStatement("select distinct LEFT(date,10) as date from log order by date desc limit 30");
for ($iter=0; $row=sqlFetchArray($res); $iter++) {
    $ret[$iter] = $row;
}

// Get the users list.
$sqlQuery = "SELECT username, fname, lname FROM users " .
  "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) ";

$ures = sqlStatement($sqlQuery);
?>

<br>
<FORM METHOD="GET" name="theform" id="theform">
<?php
$sortby = isset($_GET['sortby']) ? $_GET['sortby'] : '';
$direction = isset($_GET['direction']) ? $_GET['direction'] : '';

?>
<input type="hidden" name="direction" id="direction" value="<?php echo !empty($direction) ? attr($direction) : 'asc'; ?>">
<input type="hidden" name="sortby" id="sortby" value="<?php echo attr($sortby); ?>">
<input type=hidden name=csum value="">
<table>
<tr><td>
<span class="text"><?php echo xlt('Start Date'); ?>: </span>
</td><td>
<input class="datetimepicker" type="text" size="18" name="start_date" id="start_date" value="<?php echo attr(oeFormatDateTime($start_date, 0)); ?>" title="<?php echo xla('Start Date'); ?>" />
</td>
<td>
<span class="text"><?php  xl('End Date', 'e'); ?>: </span>
</td><td>
<input class="datetimepicker" type="text" size="18" name="end_date" id="end_date" value="<?php echo attr(oeFormatDateTime($end_date, 0)); ?>" title="<?php echo xla('End Date'); ?>" />
</td>
<!--VicarePlus :: Feature For Generating Log For The Selected Patient --!>
<td>
&nbsp;&nbsp;<span class='text'><?php echo xlt('Patient'); ?>: </span>
</td>
<td>
<input type='text' size='20' name='form_patient' style='width:100%;cursor:pointer;cursor:hand' value='<?php echo $form_patient ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
<input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
</td>
</tr>
<tr><td>
<span class='text'><?php echo xlt('User'); ?>: </span>
</td>
<td>
<?php
echo "<select name='form_user'>\n";
echo " <option value=''>" . xlt('All') . "</option>\n";
while ($urow = sqlFetchArray($ures)) {
    if (!trim($urow['username'])) {
        continue;
    }

    echo " <option value='" . attr($urow['username']) . "'";
    if ($urow['username'] == $form_user) {
        echo " selected";
    }

    echo ">" . text($urow['lname']);
    if ($urow['fname']) {
        echo ", " . text($urow['fname']);
    }

    echo "</option>\n";
}

echo "</select>\n";
?>
</td>
<td>
<!-- list of events name -->
<span class='text'><?php echo xlt('Name of Events'); ?>: </span>
</td>
<td>
<?php
$eventname = isset($_GET['eventname']) ? $_GET['eventname'] : '';
$res = sqlStatement("select distinct event from log order by event ASC");
$ename_list=array();
$j=0;
while ($erow = sqlFetchArray($res)) {
    if (!trim($erow['event'])) {
        continue;
    }

     $data = explode('-', $erow['event']);
     $data_c = count($data);
     $ename=$data[0];
    for ($i=1; $i<($data_c-1); $i++) {
        $ename.="-".$data[$i];
    }

    $ename_list[$j]=$ename;
    $j=$j+1;
}

$res1 = sqlStatement("select distinct event from extended_log order by event ASC");
// $j=0; // This can't be right!  -- Rod 2013-08-23
while ($row = sqlFetchArray($res1)) {
    if (!trim($row['event'])) {
        continue;
    }

         $new_event = explode('-', $row['event']);
         $no = count($new_event);
         $events=$new_event[0];
    for ($i=1; $i<($no-1); $i++) {
        $events.="-".$new_event[$i];
    }

    if ($events=="disclosure") {
        $ename_list[$j]=$events;
    }

        $j=$j+1;
}

$ename_list=array_unique($ename_list);
$ename_list=array_merge($ename_list);
$ecount=count($ename_list);
echo "<select name='eventname' onchange='eventTypeChange(this.options[this.selectedIndex].value);'>\n";
echo " <option value=''>" . xlt('All') . "</option>\n";
for ($k=0; $k<$ecount; $k++) {
    echo " <option value='" . attr($ename_list[$k]) . "'";
    if ($ename_list[$k] == $eventname && $ename_list[$k]!= "") {
        echo " selected";
    }

    echo ">" . text($ename_list[$k]);
    echo "</option>\n";
}

echo "</select>\n";
?>
</td>
<!-- type of events ends  -->
<td>
&nbsp;&nbsp;<span class='text'><?php echo xlt('Type of Events'); ?>: </span>
</td><td>
<?php
$type_event = isset($_GET['type_event']) ? $_GET['type_event'] : '';
$event_types=array("select", "update", "insert", "delete", "replace");
$lcount=count($event_types);
if ($eventname=="disclosure") {
    echo "<select name='type_event' disabled='disabled'>\n";
    echo " <option value=''>" . xlt('All') . "</option>\n";
    echo "</option>\n";
} else {
    echo "<select name='type_event'>\n";
}

  echo " <option value=''>" . xlt('All') . "</option>\n";
for ($k=0; $k<$lcount; $k++) {
    echo " <option value='" .attr($event_types[$k]). "'";
    if ($event_types[$k] == $type_event && $event_types[$k]!= "") {
        echo " selected";
    }

    echo ">" . text(preg_replace('/^select$/', 'Query', $event_types[$k])); // Convert select to Query for MU2 requirement
    echo "</option>\n";
}

echo "</select>\n";
?>
</td>
<tr><td>
<span class='text'><?php echo xlt('Include Checksum'); ?>: </span>
</td><td>
<?php
$check_sum = isset($_GET['check_sum']) ? $_GET['check_sum'] : '';
?>
<input type="checkbox" name="check_sum" <?php echo ($check_sum == 'on') ? "checked" : ""; ?>></input>
</td>
<td>
<input type=hidden name="event" value="<?php echo attr($event); ?>">
<a href="javascript:document.theform.submit();" class='link_submit'>[<?php echo xlt('Refresh'); ?>]</a>
</td>
<td>
<div id='valid_button'>
<input type=button id='validate_log' onclick='validatelog();' value='<?php echo xla('Validate Log'); ?>'></input>
</div>
<div id='log_loading' style="display: none">
<img src='../../images/loading.gif'/>
</div>
</td>
</tr>
</table>
</FORM>


<?php if ($start_date && $end_date && $err_message!=1) { ?>
<div id="logview">
<table>
 <tr>
  <!-- <TH><?php echo xlt('Date'); ?><TD> -->
  <th id="sortby_date" class="text sortby" title="<?php echo xla('Sort by date/time'); ?>"><?php echo xlt('Date'); ?></th>
  <th id="sortby_event" class="text sortby" title="<?php echo xla('Sort by Event'); ?>"><?php echo xlt('Event'); ?></th>
  <th id="sortby_category" class="text sortby" title="<?php echo xla('Sort by Category'); ?>"><?php echo xlt('Category'); ?></th>
  <th id="sortby_user" class="text sortby" title="<?php echo xla('Sort by User'); ?>"><?php echo xlt('User'); ?></th>
  <th id="sortby_cuser" class="text sortby" title="<?php echo xla('Sort by Crt User'); ?>"><?php echo xlt('Certificate User'); ?></th>
  <th id="sortby_group" class="text sortby" title="<?php echo xla('Sort by Group'); ?>"><?php echo xlt('Group'); ?></th>
  <th id="sortby_pid" class="text sortby" title="<?php echo xla('Sort by PatientID'); ?>"><?php echo xlt('PatientID'); ?></th>
  <th id="sortby_success" class="text sortby" title="<?php echo xla('Sort by Success'); ?>"><?php echo xlt('Success'); ?></th>
  <th id="sortby_comments" class="text sortby" title="<?php echo xla('Sort by Comments'); ?>"><?php echo xlt('Comments'); ?></th>
    <?php  if ($check_sum) {?>
  <th id="sortby_checksum" class="text sortby" title="<?php echo xla('Sort by Checksum'); ?>"><?php echo xlt('Checksum'); ?></th>
    <?php } ?>
 </tr>
<?php
?>
<input type="hidden" name="event" value="<?php echo attr($eventname) . "-" . attr($type_event) ?>">
<?php

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

if ($ret = getEvents(array('sdate' => $start_date,'edate' => $end_date, 'user' => $form_user, 'patient' => $form_pid, 'sortby' => $_GET['sortby'], 'levent' =>$gev, 'tevent' =>$tevent,'direction' => $_GET['direction']))) {
    foreach ($ret as $iter) {
        //translate comments
        $patterns = array ('/^success/','/^failure/','/ encounter/');
        $replace = array ( xl('success'), xl('failure'), xl('encounter', '', ' '));

        $log_id = $iter['id'];
        $commentEncrStatus = "No";
        $encryptVersion = 0;
        $logEncryptData = logCommentEncryptData($log_id);
        if (count($logEncryptData) > 0) {
            $commentEncrStatus = $logEncryptData['encrypt'];
            $encryptVersion = $logEncryptData['version'];
        }

        //July 1, 2014: Ensoftek: Decrypt comment data if encrypted
        if ($commentEncrStatus == "Yes") {
            if ($encryptVersion == 1) {
                // Use new openssl method
                if (extension_loaded('openssl')) {
                    $trans_comments = preg_replace($patterns, $replace, aes256Decrypt($iter["comments"]));
                } else {
                    $trans_comments = xl("Unable to decrypt these comments since the PHP openssl module is not installed.");
                }
            } else { //$encryptVersion == 0
                // Use old mcrypt method
                if (extension_loaded('mcrypt')) {
                    $trans_comments = preg_replace($patterns, $replace, aes256Decrypt_mycrypt($iter["comments"]));
                } else {
                    $trans_comments = xl("Unable to decrypt these comments since the PHP mycrypt module is not installed.");
                }
            }
        } else {
            $trans_comments = preg_replace($patterns, $replace, $iter["comments"]);
        }
        ?>
       <TR class="oneresult">
        <TD class="text"><?php echo text(oeFormatDateTime($iter["date"])); ?></TD>
    <TD class="text"><?php echo text(preg_replace('/select$/', 'Query', $iter["event"])); //Convert select term to Query for MU2 requirements ?></TD>
    <TD class="text"><?php echo text($iter["category"]); ?></TD>
    <TD class="text"><?php echo text($iter["user"]); ?></TD>
    <TD class="text"><?php echo text($iter["crt_user"]); ?></TD>
    <TD class="text"><?php echo text($iter["groupname"]); ?></TD>
    <TD class="text"><?php echo text($iter["patient_id"]); ?></TD>
    <TD class="text"><?php echo text($iter["success"]); ?></TD>
    <TD class="text"><?php echo nl2br(text(preg_replace('/^select/i', 'Query', $trans_comments))); //Convert select term to Query for MU2 requirements ?></TD>
    <?php  if ($check_sum) { ?>
  <TD class="text"><?php echo text($iter["checksum"]); ?></TD>
    <?php } ?>
     </TR>

    <?php
    }
}

if (($eventname=="disclosure") || ($gev == "")) {
    $eventname="disclosure";
    if ($ret = getEvents(array('sdate' => $start_date,'edate' => $end_date, 'user' => $form_user, 'patient' => $form_pid, 'sortby' => $_GET['sortby'], 'event' =>$eventname))) {
        foreach ($ret as $iter) {
            $comments=xl('Recipient Name').":".$iter["recipient"].";".xl('Disclosure Info').":".$iter["description"];
            ?>
            <TR class="oneresult">
              <TD class="text"><?php echo text(oeFormatDateTime($iter["date"])); ?></TD>
          <TD class="text"><?php echo xlt($iter["event"]); ?></TD>
          <TD class="text"><?php echo xlt($iter["category"]); ?></TD>
          <TD class="text"><?php echo text($iter["user"]); ?></TD>
          <TD class="text"><?php echo text($iter["crt_user"]); ?></TD>
          <TD class="text"><?php echo text($iter["groupname"]); ?></TD>
          <TD class="text"><?php echo text($iter["patient_id"]); ?></TD>
          <TD class="text"><?php echo text($iter["success"]); ?></TD>
          <TD class="text"><?php echo text($comments); ?></TD>
            <?php  if ($check_sum) { ?>
  <TD class="text"><?php echo text($iter["checksum"]); ?></TD>
    <?php } ?>
     </TR>
    <?php
        }
    }
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
    $('.sortby')
    $("#sortby_date").click(function() { set_sort_direction(); $("#sortby").val("date"); $("#theform").submit(); });
    $("#sortby_event").click(function() { set_sort_direction(); $("#sortby").val("event"); $("#theform").submit(); });
    $("#sortby_category").click(function() { set_sort_direction(); $("#sortby").val("category"); $("#theform").submit(); });
    $("#sortby_user").click(function() { set_sort_direction(); $("#sortby").val("user"); $("#theform").submit(); });
    $("#sortby_cuser").click(function() { set_sort_direction(); $("#sortby").val("user"); $("#theform").submit(); });
    $("#sortby_group").click(function() { set_sort_direction(); $("#sortby").val("groupname"); $("#theform").submit(); });
    $("#sortby_pid").click(function() { set_sort_direction(); $("#sortby").val("patient_id"); $("#theform").submit(); });
    $("#sortby_success").click(function() { set_sort_direction(); $("#sortby").val("success"); $("#theform").submit(); });
    $("#sortby_comments").click(function() { set_sort_direction(); $("#sortby").val("comments"); $("#theform").submit(); });
    $("#sortby_checksum").click(function() { set_sort_direction(); $("#sortby").val("checksum"); $("#theform").submit(); });

    $('.datetimepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

function set_sort_direction(){
    if($('#direction').val() == 'asc')
        $('#direction').val('desc');
    else
        $('#direction').val('asc');
}

function validatelog(){
     var img = document.getElementById('log_loading');
     var btn = document.getElementById('valid_button');
     if(img){
         if(img.style.display == "block"){
             return false;
         }
         img.style.display = "block";
        if(btn){btn.style.display = "none"}
     }
     $.ajax({
            url:"../../library/log_validation.php",
            asynchronous : true,
            method: "post",
            success :function(response){
                    if(img){
                            img.style.display="none";
                            if(btn){btn.style.display="block";}
                    }
                    alert(response);
                    },
            failure :function(){
                    if(img){
                            img.style.display="none";
                            if(btn){btn.style.display="block";}
                    }
                    alert('<?php echo xls("Audit Log Validation Failed"); ?>');
            }
     });

}
</script>

</html>
