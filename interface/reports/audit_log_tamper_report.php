<?php
/**
 * Audit Log Tamper Report.
 *
 * Copyright (C) 2014 Ensoftek
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Anil N <aniln@ensoftek.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../globals.php");
include_once("$srcdir/log.inc");
include_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href='<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css' type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
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
if ($_GET["start_date"])
$start_date = $_GET['start_date'];

if ($_GET["end_date"])
$end_date = $_GET['end_date'];

if ($_GET["form_patient"])
$form_patient = $_GET['form_patient'];

/*
 * Start date should not be greater than end date - Date Validation
 */
if ($start_date && $end_date)
{
	if($start_date > $end_date){
		echo "<table><tr class='alert'><td colspan=7>"; echo xlt('Start Date should not be greater than End Date');
		echo "</td></tr></table>"; 
		$err_message=1;	
	}
}

?>
<?php
$form_user = $_REQUEST['form_user'];
$form_pid = $_REQUEST['form_pid'];
if ($form_patient == '' ) $form_pid = '';

$get_sdate=$start_date ? $start_date : date("Y-m-d H:i:s");
$get_edate=$end_date ? $end_date : date("Y-m-d H:i:s");

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
<input type="text" size="18" name="start_date" id="start_date" value="<?php echo $start_date ? $start_date : date('Y-m-d H:i:s'); ?>" title="<?php echo xla('yyyy-mm-dd H:m Start date'); ?>" onkeyup="datekeyup(this,mypcc,true)" onblur="dateblur(this,mypcc,true)" />
<img src="../pic/show_calendar.gif" align="absbottom" width="24" height="22" id="img_begin_date" border="0" alt="[?]" style="cursor: pointer; cursor: hand" title="<?php echo xla('Click here to choose date time'); ?>">&nbsp;
</td>
<td>
<span class="text"><?php echo xlt('End Date'); ?>: </span>
</td><td>
<input type="text" size="18" name="end_date" id="end_date" value="<?php echo $end_date ? $end_date : date('Y-m-d H:i:s'); ?>" title="<?php echo xla('yyyy-mm-dd H:m End date'); ?>" onkeyup="datekeyup(this,mypcc,true)" onblur="dateblur(this,mypcc,true)" />
<img src="../pic/show_calendar.gif" align="absbottom" width="24" height="22" id="img_end_date" border="0" alt="[?]" style="cursor: pointer; cursor: hand" title="<?php echo xla('Click here to choose date time'); ?>">&nbsp;
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

$check_sum = $_GET['check_sum'];
?>
<input type="checkbox" name="check_sum" " <?php if ($check_sum == 'on') echo "checked";  ?>"></input>
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
 <?php  if($check_sum) {?>
  <th id="sortby_newchecksum" class="text" title="<?php xla('Sort by New Checksum'); ?>"><?php  xlt('Tampered Checksum'); ?></th>
  <th id="sortby_oldchecksum" class="text" title="<?php xla('Sort by Old Checksum'); ?>"><?php  xlt('Original Checksum'); ?></th>
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
if($eventname != "" && $type_event != ""){
	$getevent=$eventname."-".$type_event;
}
      
if(($eventname == "") && ($type_event != "")){	
	$tevent=$type_event;   	
}else if($type_event =="" && $eventname != ""){
	$gev=$eventname;
}else if ($eventname == ""){
	$gev = "";
}else{
	$gev = $getevent;
}

$dispArr = array();
$icnt = 1;
if ($ret = getEvents(array('sdate' => $get_sdate,'edate' => $get_edate, 'user' => $form_user, 'patient' => $form_pid, 'sortby' => $_GET['sortby'], 'levent' =>$gev, 'tevent' =>$tevent))) {
  foreach ($ret as $iter) {
    //translate comments
    $patterns = array ('/^success/','/^failure/','/ encounter/');
	$replace = array ( xl('success'), xl('failure'), xl('encounter','',' '));
	
	$dispCheck = false;
	$log_id = $iter['id'];
	$commentEncrStatus = "No";
	$logEncryptData = logCommentEncryptData($log_id);
	
	if(count($logEncryptData) > 0){
		$commentEncrStatus = $logEncryptData['encrypt'];
		$checkSumOld = $logEncryptData['checksum'];
		$concatLogColumns = $iter['date'].$iter['event'].$iter['user'].$iter['groupname'].$iter['comments'].$iter['patient_id'].$iter['success'].$iter['checksum'].$iter['crt_user'];
		$checkSumNew = sha1($concatLogColumns);
		
		if($checkSumOld != $checkSumNew){
			$dispCheck = true;
		}else{
			$dispCheck = false;
			continue;
		}
	}else{
		continue;
	}
	
	if($commentEncrStatus == "Yes"){
		$decrypt_comment =  trim(aes256Decrypt($iter["comments"]));
		$trans_comments = preg_replace($patterns, $replace, $decrypt_comment);
	}else{
		$comments = trim($iter["comments"]);
		$trans_comments = preg_replace($patterns, $replace, $comments);
	}
	
	//Alter Checksum value records only display here
	if($dispCheck){
		$dispArr[] = $icnt++;
?>
	 <TR class="oneresult">
		  <TD class="text tamperColor"><?php echo oeFormatShortDate(substr($iter["date"], 0, 10)) . substr($iter["date"], 10) ?></TD>
		  <TD class="text tamperColor"><?php echo text($iter["user"]); ?></TD>
		  <TD class="text tamperColor"><?php echo text($iter["patient_id"]);?></TD>
		  <TD class="text tamperColor"><?php echo text($trans_comments);?></TD>
		  <?php  if($check_sum) { ?>
		  <TD class="text tamperColor"><?php echo text($checkSumNew);?></TD>
		  <TD class="text tamperColor"><?php echo text($checkSumOld);?></TD>
		  <?php } ?>
	 </TR>
<?php
      }
    }
  }
  
  if( count($dispArr) == 0 ){?>
	 <TR class="oneresult">
		 <?php 
			$colspan = 4;
			if($check_sum) $colspan=6;
		 ?>
		<TD class="text" colspan="<?php echo $colspan;?>" align="center"><?php echo xlt('No audit log tampering detected in the selected date range.'); ?></TD>
	 </TR>
<?php
  }else{?>
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
});


/* required for popup calendar */
Calendar.setup({inputField:"start_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_begin_date", showsTime:true});
Calendar.setup({inputField:"end_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_end_date", showsTime:true});

</script>

</html>

