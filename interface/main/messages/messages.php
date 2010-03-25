<?php
// Copyright (C) 2010 OpenEMR Support LLC
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/log.inc");
require_once("$srcdir/options.inc.php");
include_once("$srcdir/formdata.inc.php");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/gprelations.inc.php");

?>

<html>
<head>

<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
</head>

<body class="body_top">
<?php
// Check to see if the user has Admin rights, and if so, allow access to See All.
$showall = formData('showall', 'G');
if ($showall == "yes") {
    $show_all = $showall;
}
if (acl_check('admin', 'super'    )) {
if ($show_all=='yes') {
    $showall = "yes";
    $lnkvar="'messages.php?show_all=no' name='Just Mine' onclick=\"top.restoreSession()\"> (".xl('Just Mine').")";
}
else {
    $showall = "no";
    $lnkvar="'messages.php?show_all=yes' name='See All' onclick=\"top.restoreSession()\"> (".xl('See All').")";
}
}
?>
<table><tr><td><span class="title"><?php xl('Messages','e'); ?></span> <a class='more' href=<?php echo $lnkvar; ?></a></td></tr></table><br>
<?php
switch($task) {
    case "add" :
    {
        // Add a new message for a specific patient; the message is documented in Patient Notes.
        // Add a new message; it's treated as a new note in Patient Notes.
        $note = strip_escape_custom($_POST['note']);
        $noteid = formData("noteid");
        $form_note_type = formData("form_note_type");
        $assigned_to = formData("assigned_to");
        $form_message_status = formData("form_message_status");
        $reply_to = formData("reply_to");
        $userauthorized = formData("userauthorized");
        if ($noteid) {
          updatePnote($noteid, $note, $form_note_type, $assigned_to);
          sqlQuery("update pnotes set message_status='".$form_message_status."' where id = '".$noteid."'");
          $noteid = '';
        }
        else {
          $noteid = addPnote($reply_to, $note, $userauthorized, '1', $form_note_type, $assigned_to);
          sqlQuery("update pnotes set message_status='".$form_message_status."' where id = '$noteid'");
        }
    } break;
    case "save" : {
        // Update alert.
        $noteid = formData("noteid");
        $form_message_status = formData("form_message_status");
        sqlQuery("update pnotes set message_status='".$form_message_status."' where id = '".$noteid."'");
        $task = "edit";
        $note = formData("note");
        $title = formData("form_note_type");
        $assigned_to = formData("assigned_to");
        $reply_to = formData("reply_to");
    }
    case "edit" : {
        if ($noteid == "") {
            $noteid = formData('noteid', 'G');
        }
        // Update the message if it already exists; it's appended to an existing note in Patient Notes.
        $sql = "select title, assigned_to, body, pid, message_status from pnotes where id='$noteid'";
        $result = sqlStatement($sql);
        if ($myrow = sqlFetchArray($result)) {
            if ($title == ""){
                $title = $myrow['title'];
            }
            if ($assigned_to == ""){
                $assigned_to = $myrow['assigned_to'];
            }
            $body = $myrow['body'];
            if ($reply_to == ""){
                $reply_to = $myrow['pid'];
            }
            $form_message_status = $myrow['message_status'];
        }
    } break;
    case "delete" : {
        // Delete selected message(s) from the Messages box (only).
        $delete_id = $_POST['delete_id'];
        for($i = 0; $i < count($delete_id); $i++) {
            sqlQuery("delete from pnotes where id='$delete_id[$i]'");
        }
    } break;
}

if($task == "addnew" or $task == "edit") {
 // Display the Messages page layout.
echo "
<form name=new_note id=new_note action=\"messages.php?showall=$showall&sortby=$sortby&sortorder=$sortorder&begin=$begin\" method=post>
<input type=hidden name=noteid id=noteid value=$noteid>
<input type=hidden name=task id=task value=add>";
?>
<div id="pnotes"><center>
<table border='0' cellspacing='8'>
 <tr>
  <td class='text' align='center'>
   <b><?php xl('Type','e'); ?>:</b>
   <?php
   if ($title == "") {
       $title = "New Document";
   }
   // Added 6/2009 by BM to incorporate the patient notes into the list_options listings.
    generate_form_field(array('data_type'=>1,'field_id'=>'note_type','list_id'=>'note_type','empty_title'=>'SKIP','order_by'=>'title'), $title);
   ?>
   &nbsp; &nbsp;
   <b><?php xl('To','e'); ?>:</b>
   <select name='assigned_to'>

<?php

$ures = sqlStatement("SELECT username, fname, lname FROM users " .
 "WHERE username != '' AND active = 1 AND " .
 "( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
 "ORDER BY lname, fname");
 while ($urow = sqlFetchArray($ures)) {
  echo "    <option value='" . $urow['username'] . "'";
  if ($urow['username'] == $assigned_to) echo " selected";
  echo ">" . $urow['lname'];
  if ($urow['fname']) echo ", " . $urow['fname'];
  echo "</option>\n";
 }

?>
   </select>
  </td>
</tr>
<tr>
  <td class='text' align='center'>
   <b class='<?php echo ($task=="addnew"?"required":"") ?>'><?php xl('Patient','e'); ?>:</b><?php
 if ($reply_to) {
  $prow = sqlQuery("SELECT lname, fname " .
   "FROM patient_data WHERE pid = '" . $reply_to . "'");
  $patientname = $prow['lname'] . ", " . $prow['fname'];
 }
   if ($patientname == "") {
       $patientname = xl('Click to select');
   } ?>
   <input type='text' size='10' name='form_patient' style='width:150px;<?php echo ($task=="addnew"?"cursor:pointer;cursor:hand;":"") ?>' value='<?php echo htmlspecialchars($patientname, ENT_QUOTES); ?>' <?php echo ($task=="addnew"?"onclick='sel_patient()' readonly":"disabled") ?> title='<?php ($task=="addnew"?xl('Click to select patient','e'):"") ?>'  />
   <input type='hidden' name='reply_to' value='<?php echo $reply_to ?>' />
   &nbsp; &nbsp;
   <b><?php xl('Status','e'); ?>:</b>
    <?php
   if ($form_message_status == "") {
       $form_message_status = 'New';
   }
    generate_form_field(array('data_type'=>1,'field_id'=>'message_status','list_id'=>'message_status','empty_title'=>'SKIP','order_by'=>'title'), $form_message_status); ?>
  </td>
 </tr>
 <tr>
  <td>

<?php

if ($noteid) {
    $body = nl2br($body);
    echo "<div class='text' style='background-color:white; color: gray; border:1px solid #999; padding: 5px; width: 640px;'>".$body."</div>";
}

?>
   <textarea name='note' id='note' rows='8' style="width: 660px; "><?php echo $note ?></textarea>
  </td>
 </tr>
</table>

<?php if ($noteid) { ?>
<!-- This is for displaying an existing note. -->
<input type="button" id="newnote" value="<?php xl('Send message','e'); ?>">
<input type="button" id="printnote" value="<?php xl('Print message','e'); ?>">
<input type="button" id="cancel" value="<?php xl('Cancel','e'); ?>">
<?php } else { ?>
<!-- This is for displaying a new note. -->
<input type="button" id="newnote" value="<?php xl('Send message','e'); ?>">
<input type="button" id="cancel" value="<?php xl('Cancel','e'); ?>">
<?php } ?>

<br>
</form></center></div>
<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#newnote").click(function() { NewNote(); });
    $("#printnote").click(function() { PrintNote(); });
    obj = document.getElementById("form_message_status");
    obj.onchange = function(){SaveNote();};
    $("#cancel").click(function() { CancelNote(); });
    $("#note").focus();

    var NewNote = function () {
        top.restoreSession();
      if (document.forms[0].reply_to.value.length == 0) {
       alert('<?php xl('Please choose a value for Re!', 'e') ?>');
      }
      else
      {
        $("#new_note").submit();
      }
    }

    var PrintNote = function () {
        top.restoreSession();
        window.open('../../patient_file/summary/pnotes_print.php?noteid=<?php echo $noteid; ?>', '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
    }

    var SaveNote = function () {
    <?php if ($noteid) { ?>
        top.restoreSession();
        $("#task").val("save");
        $("#new_note").submit();
        <?php } ?>
    }

    var CancelNote = function () {
        top.restoreSession();
        $("#task").val("");
        $("#new_note").submit();
    }
});
 // This is for callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.forms[0];
  f.form_patient.value = lname + ', ' + fname;
  f.reply_to.value = pid;
 }

 // This invokes the find-patient popup.
 function sel_patient() {
  dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 500, 400);
 }
</script><?php
}
else {
    $sortby = formData('sortby','R');
    $sortorder = formData('sortorder','R');
    $begin = formData('begin','R');
    // This is for sorting the records.
    $sort = array("users.lname", "patient_data.lname", "pnotes.title", "pnotes.date", "pnotes.message_status");
    $sortby = formData('sortby','R');
    $sortorder = formData('sortorder','R');
    $begin = formData('begin','R');
    if($sortby == "") {
        $sortby = $sort[0];
    }
    if($sortorder == "") {
        $sortorder = "asc";
    }
    for($i = 0; $i < count($sort); $i++) {
        $sortlink[$i] = "<a href=\"messages.php?showall=$showall&sortby=$sort[$i]&sortorder=asc\" onclick=\"top.restoreSession()\"><img src=\"../../../images/sortdown.gif\" border=0 alt=\"".xl('Sort Up')."\"></a>";
    }
    for($i = 0; $i < count($sort); $i++) {
        if($sortby == $sort[$i]) {
            switch($sortorder) {
                case "asc"      : $sortlink[$i] = "<a href=\"messages.php?showall=$showall&sortby=$sortby&sortorder=desc\" onclick=\"top.restoreSession()\"><img src=\"../../../images/sortup.gif\" border=0 alt=\"".xl('Sort Up')."\"></a>"; break;
                case "desc"     : $sortlink[$i] = "<a href=\"messages.php?showall=$showall&sortby=$sortby&sortorder=asc\" onclick=\"top.restoreSession()\"><img src=\"../../../images/sortdown.gif\" border=0 alt=\"".xl('Sort Down')."\"></a>"; break;
            } break;
        }
    }
    // Manage page numbering and display beneath the Messages table.
    $listnumber = 25;
    $show_all=='yes' ? $usrvar='_%' : $usrvar=$_SESSION['authUser'] ;
    $sql = "select pnotes.id, pnotes.user, pnotes.pid, pnotes.title, pnotes.date, pnotes.message_status, users.fname, users.lname, patient_data.fname, patient_data.lname FROM ((pnotes JOIN users ON pnotes.user = users.username) JOIN patient_data ON pnotes.pid = patient_data.pid) where pnotes.message_status != 'Done' and pnotes.assigned_to LIKE '$usrvar'";
    $result = sqlStatement($sql);
    if(sqlNumRows($result) != 0) {
        $total = sqlNumRows($result);
    }
    else {
        $total = 0;
    }
    if($begin == "" or $begin == 0) {
        $begin = 0;
    }
    $prev = $begin - $listnumber;
    $next = $begin + $listnumber;
    $start = $begin + 1;
    $end = $listnumber + $start - 1;
    if($end >= $total) {
        $end = $total;
    }
    if($end < $start) {
        $start = 0;
    }
    if($prev >= 0) {
        $prevlink = "<a href=\"messages.php?showall=$showall&sortby=$sortby&sortorder=$sortorder&begin=$prev\" onclick=\"top.restoreSession()\"><<</a>";
    }
    else {
        $prevlink = "<<";
    }

    if($next < $total) {
        $nextlink = "<a href=\"messages.php?showall=$showall&sortby=$sortby&sortorder=$sortorder&begin=$next\" onclick=\"top.restoreSession()\">>></a>";
    }
    else {
        $nextlink = ">>";
    }
    // Display the Messages table header.
    echo "
    <table width=100%><tr><td><table border=0 cellpadding=1 cellspacing=0 width=90%  style=\"border-left: 1px #000000 solid; border-right: 1px #000000 solid; border-top: 1px #000000 solid;\">
    <form name=wikiList action=\"messages.php?showall=$showall&sortby=$sortby&sortorder=$sortorder&begin=$begin\" method=post>
    <input type=hidden name=task value=delete>
        <tr height=\"24\" style=\"background:lightgrey\">
            <td align=\"center\" width=\"25\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\"><input type=checkbox id=\"checkAll\" onclick=\"selectAll()\"></td>
            <td width=\"20%\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\" class=bold>&nbsp;<b>".xl('From')."</b> $sortlink[0]</td>
            <td width=\"20%\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\" class=bold>&nbsp;<b>".xl('Patient')."</b> $sortlink[1]</td>
            <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\" class=bold>&nbsp;<b>".xl('Type')."</b> $sortlink[2]</td>
            <td width=\"15%\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\" class=bold>&nbsp;<b>".xl('Date')."</b> $sortlink[3]</td>
            <td width=\"15%\" style=\"border-bottom: 1px #000000 solid; \" class=bold>&nbsp;<b>".xl('Status')."</b> $sortlink[4]</td>
        </tr>";
        // Display the Messages table body.
        $count = 0;
        $show_all=='yes' ? $usrvar='_%' : $usrvar=$_SESSION['authUser'] ;
        $sql = "select pnotes.id, pnotes.user, pnotes.pid, pnotes.title, pnotes.date, pnotes.message_status, users.fname AS users_fname, users.lname AS users_lname, patient_data.fname AS patient_data_fname, patient_data.lname AS patient_data_lname FROM ((pnotes JOIN users ON pnotes.user = users.username) JOIN patient_data ON pnotes.pid = patient_data.pid) where pnotes.message_status != 'Done' and pnotes.assigned_to LIKE '$usrvar' order by $sortby $sortorder limit $begin, $listnumber";
        $result = sqlStatement($sql);
        while ($myrow = sqlFetchArray($result)) {
            $name = $myrow['user'];
            $name = $myrow['users_lname'];
            if ($myrow['users_fname']) {
                $name .= ", " . $myrow['users_fname'];
            }
            $patient = $myrow['pid'];
            $patient = $myrow['patient_data_lname'];
            if ($myrow['patient_data_fname']) {
                $patient .= ", " . $myrow['patient_data_fname'];
            }
            $count++;
            echo "
            <tr id=\"row$count\" style=\"background:white\" height=\"24\">
                <td align=\"center\" style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\"><input type=checkbox id=\"check$count\" name=\"delete_id[]\" value=\"".$myrow['id']."\" onclick=\"if(this.checked==true){ selectRow('row$count'); }else{ deselectRow('row$count'); }\"></td>
                <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\"><table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\">$name</td><td width=5></td></tr></table></td>
                <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\"><table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\"><a href=\"messages.php?showall=$showall&sortby=$sortby&sortorder=$sortorder&begin=$begin&task=edit&noteid=".$myrow['id']."\" onclick=\"top.restoreSession()\">$patient</a></td><td width=5></td></tr></table></td>
                <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\"><table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\">".$myrow['title']."</td><td width=5></td></tr></table></td>
                <td style=\"border-bottom: 1px #000000 solid; border-right: 1px #000000 solid;\"><table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\">".substr($myrow['date'], 0, strpos($myrow['date'], " "))."</td><td width=5></td></tr></table></td>
                <td style=\"border-bottom: 1px #000000 solid;\"><table cellspacing=0 cellpadding=0 width=100%><tr><td width=5></td><td class=\"text\">".$myrow['message_status']."</td><td width=5></td></tr></table></td>
            </tr>";
        }
    // Display the Messages table footer.
    echo "
    </form></table>
    <table border=0 cellpadding=5 cellspacing=0 width=90%>
        <tr>
            <td class=\"text\"><a href=\"messages.php?showall=$showall&sortby=$sortby&sortorder=$sortorder&begin=$begin&task=addnew\" onclick=\"top.restoreSession()\">".xl('Add New')."</a> &nbsp; <a href=\"javascript:confirmDeleteSelected()\" onclick=\"top.restoreSession()\">".xl('Delete')."</a></td>
            <td align=right class=\"text\">$prevlink &nbsp; $end of $total &nbsp; $nextlink</td>
        </tr>
    </table></td></tr></table><br>"; ?>
<script language="javascript">
// This is to confirm delete action.
function confirmDeleteSelected() {
    if(confirm("<?php xl('Do you really want to delete the selection?', 'e') ?>")) {
        document.wikiList.submit();
    }
}
// This is to allow selection of all items in Messages table for deletion.
function selectAll() {
    if(document.getElementById("checkAll").checked==true) {
        document.getElementById("checkAll").checked=true;<?php
        for($i = 1; $i <= $count; $i++) {
            echo "document.getElementById(\"check$i\").checked=true; document.getElementById(\"row$i\").style.background='#E7E7E7';  ";
        } ?>
    }
    else {
        document.getElementById("checkAll").checked=false;<?php
        for($i = 1; $i <= $count; $i++) {
            echo "document.getElementById(\"check$i\").checked=false; document.getElementById(\"row$i\").style.background='#F7F7F7';  ";
        } ?>
    }
}
// The two functions below are for managing row styles in Messages table.
function selectRow(row) {
    document.getElementById(row).style.background = "#E7E7E7";
}
function deselectRow(row) {
    document.getElementById(row).style.background = "#F7F7F7";
}
</script><?php
}
?>

</body>
</html>
