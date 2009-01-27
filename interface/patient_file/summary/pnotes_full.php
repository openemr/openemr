<?php
include_once("../../globals.php");
include_once("$srcdir/pnotes.inc");
include_once("$srcdir/patient.inc");
include_once("$srcdir/acl.inc");

if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) {
    include_once("$srcdir/pid.inc");
    setpid($_GET['set_pid']);
}

// Check authorization.
$thisauth = acl_check('patients', 'notes');
if ($thisauth != 'write' && $thisauth != 'addonly')
    die("Not authorized.");
$tmp = getPatientData($pid, "squad");
if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
    die("Not authorized for this squad.");

//the number of records to display per screen
$N = 25;

$mode   = $_REQUEST['mode'];
$offset = $_REQUEST['offset'];
$active = $_REQUEST['active'];
$noteid = $_REQUEST['noteid'];

if (!isset($offset)) $offset = 0;
if (!isset($active)) $active = "all";

// this code handles changing the state of activity tags when the user updates
// them through the interface
if (isset($mode)) {
  if ($mode == "update") {
    foreach ($_POST as $var => $val) {
      if (strncmp($var, 'act', 3) == 0) {
        $id = str_replace("act", "", $var);
        if ($_POST["chk$id"]) {
          reappearPnote($id);
        } else {
          disappearPnote($id);
        }
      }
    }
  }
  elseif ($mode == "new") {
    $note = $_POST['note'];
    // The subroutine will do its own addslashes().
    if (get_magic_quotes_gpc()) $note = stripslashes($note);
    if ($noteid) {
      updatePnote($noteid, $note, $_POST['title'], $_POST['assigned_to']);
      $noteid = '';
    }
    else {
      addPnote($pid, $note, $userauthorized, '1', $_POST['title'],
        $_POST['assigned_to']);
    }
  }
}

$title = '';
$assigned_to = $_SESSION['authUser'];
if ($noteid) {
  $prow = getPnoteById($noteid, 'title,assigned_to,body');
  $title = $prow['title'];
  $assigned_to = $prow['assigned_to'];
}

// Get the users list.  The "Inactive" test is a kludge, we should create
// a separate column for this.
$ures = sqlStatement("SELECT username, fname, lname FROM users " .
 "WHERE username != '' AND active = 1 AND " .
 "( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
 "ORDER BY lname, fname");

//retrieve all notes
$result = getPnotesByDate("", $active, 'id,date,body,user,activity,title,assigned_to', $pid, $N, $offset);

?>

<html>
<head>
<?php html_header_show();?>

<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

</head>
<body class="body_top">

<div id="pnotes"> <!-- large outer DIV -->

<form border='0' method='post' name='new_note' id="new_note" action='pnotes_full.php'>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="pnotes.php" onclick="top.restoreSession()">
<font class='title'><?php xl('Patient Notes','e'); ?></font>
<font class='back'>(<?php xl('Back','e'); ?>)</font></a>
<?php } else { ?>
<a href="../summary/patient_summary.php" target="Main" onclick="top.restoreSession()">
<font class='title'><?php xl('Patient Notes','e'); ?></font>
<font class='back'>(<?php xl('Back','e'); ?>)</font></a>
<?php } ?>

<br>

<input type='hidden' name='mode' id="mode" value="new">
<input type='hidden' name='offset' id="offset" value="<?php echo $offset ?>">
<input type='hidden' name='active' id="active" value="<?php echo $active ?>">
<input type='hidden' name='noteid' id="noteid" value="<?php echo $noteid ?>">

<center>

<table border='0' cellspacing='8'>
 <tr>
  <td class='text' align='center'>
<?php
 if ($noteid) {
   echo "<b>".xl('Amend Existing Note')." &quot;$title&quot;</b>\n";
 } else {
   echo "<b>".xl('Add New Note')."</b>\n";
 }
?>
  </td>
 </tr>
 <tr>
  <td class='text' align='center'>
   <b><?php xl('Type','e'); ?>:</b>
   <select name='title'>
<?php
 foreach ($patient_note_types as $value) {
  echo "    <option value='$value'";
  if ($value == $title) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
   &nbsp; &nbsp;
   <b><?php xl('To','e'); ?>:</b>
   <select name='assigned_to'>
    <option value=''>** <?php xl('Close','e'); ?> **</option>
<?php
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
  <td>
<?php
if ($noteid) {
    $body = $prow['body'];
    $body = nl2br($body);
    echo "<div class='text' style='background-color:white; color: gray; border:1px solid #999; padding: 5px;'>".$body."</div>";
}
?>
   <textarea name='note' id='note' rows='4' cols='80'></textarea>
  </td>
 </tr>
</table>

<?php if ($noteid) { ?>

<a href="javascript:top.restoreSession();document.new_note.submit();" class='link_submit'>
[<?php xl('Append to This Note','e'); ?>]
</a>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href='pnotes_print.php?noteid=<?php echo $noteid; ?>' class='link_submit'
 target='_blank' onclick='top.restoreSession()'>
[<?php xl('Print This Note','e'); ?>]
</a>

<?php } else { ?>

<a href="javascript:top.restoreSession();document.new_note.submit();" class='link_submit'>
[<?php xl('Add New Note','e'); ?>]
</a>

<?php } ?>

<br>
</form>

<form border='0' method='post' name='update_activity' id='update_activity' action="pnotes_full.php">

<?php //change the view on the current mode, whether all, active, or inactive
$all_class = "link"; $active_class = "link"; $inactive_class = "link";
if ($active=="all") {
  $all_class="link_selected";
} elseif ($active==1) {
  $active_class="link_selected";
} elseif ($active==0) {
  $inactive_class="link_selected";
}

?>

<!-- start of previous notes DIV -->
<div style="border-top: 1px dashed black; padding-top: 10px;">

<font class='text'><?php xl('View','e'); ?>: </font> 
<a href="pnotes_full.php?offset=0&active=all" class='<?php echo $all_class;?>'
 onclick='top.restoreSession()'>[<?php xl('All','e'); ?>]</a>
<a href="pnotes_full.php?offset=0&active=1" class='<?php echo $active_class;?>'
 onclick='top.restoreSession()'>[<?php xl('Only Active','e'); ?>]</a>
<a href="pnotes_full.php?offset=0&active=0" class='<?php echo $inactive_class;?>'
 onclick='top.restoreSession()'>[<?php xl('Only Inactive','e'); ?>]</a>

<input type='hidden' name='mode' value="update">
<input type='hidden' name='offset' id='noteid' value="<?php echo $offset;?>">
<input type='hidden' name='active' id='noteid' value="<?php echo $active;?>">
<input type='hidden' name='noteid' id='noteid' value="0">

<table border='0' style="border-collapse:collapse;">
<?php if ($result != ""): ?>
 <tr>
  <td colspan='3' style="border-bottom: 1px solid black; padding: 5px;">
   <input type="button" class="change_activity" value="<?php xl('Change Activity','e'); ?>" />
  </td>
 </tr>
<?php endif; ?>
<?
//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N


if ($result != "") {
  $result_count = 0;
  foreach ($result as $iter) {
    $result_count++;

    $body = $iter['body'];
    if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
      $body = nl2br($body);
    } else {
      $body = date('Y-m-d H:i', strtotime($iter['date'])) .
        ' (' . $iter['user'] . ') ' . nl2br($body);
    }

    if ($iter{"activity"}) {
      $checked = "checked";
    } else {
      $checked = "";
    }

    // highlight the row if it's been selected for updating
    if ($_REQUEST['noteid'] == $iter['id']) {
        echo " <tr class='noterow highlightcolor' id='".$iter['id']."'>\n";
    }
    else {
        echo " <tr class='noterow' id='".$iter['id']."'>\n";
    }
    echo "  <td class='text bold'>\n";
    echo "   <input type='hidden' name='act" . $iter{"id"} . "' value='1'>\n";
    echo "   <input type='checkbox' name='chk" . $iter{"id"} . "' $checked>\n";
    echo "  </td><td class='text bold notecell' id='".$iter['id']."'>\n";

//    echo "   <a href='javascript:document.forms[1].noteid.value=" .
//         $iter['id'] . ";top.restoreSession();document.update_activity.submit();' " .
//         "class='link_submit'>" . $iter['title'] . "</a>\n";
    echo $iter['title'];
    echo "  </td>\n";
    echo "  <td class='text notecell' id='".$iter['id']."'>\n";
    echo "   $body";
    echo "  </td>\n";
    echo " </tr>\n";

    $notes_count++;
  }
} else {
  //no results
  print "<tr><td colspan='3' class='text'><br>No notes</td></tr>\n";
}

?>

<?php if ($result != ""): ?>
 <tr>
  <td colspan='3' align='left' style="padding: 5px;">
   <input type="button" class="change_activity" value="<?php xl('Change Activity','e'); ?>" />
  </td>
 </tr>
<?php endif; ?>

</table>
</form>

<table width='400' border='0' cellpadding='0' cellspacing='0'>
 <tr>
  <td>
<?php
if ($offset > ($N-1)) {
  echo "   <a class='link' href='pnotes_full.php?active=" . $active .
    "&offset=" . ($offset-$N) . "' onclick='top.restoreSession()'>[" .
    xl('Previous') . "]</a>\n";
}
?>
  </td>
  <td align='right'>
<?php
if ($result_count == $N) {
  echo "   <a class='link' href='pnotes_full.php?active=" . $active .
    "&offset=" . ($offset+$N) . "' onclick='top.restoreSession()'>[" .
    xl('Next') . "]</a>\n";
}
?>
  </td>
 </tr>
</table>

</div> <!-- close the previous-notes DIV -->

</center>

<script language='JavaScript'>

<?php
if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) {
  $ndata = getPatientData($pid, "fname, lname, pubpid");
?>
 parent.left_nav.setPatient(<?php echo "'" . addslashes($ndata['fname']) . " " . addslashes($ndata['lname']) . "',$pid,'" . addslashes($ndata['pubpid']) . "',window.name"; ?>);
 parent.left_nav.setRadio(window.name, 'pno');
<?php
}

// If this note references a new patient document, pop up a display
// of that document.
//
if ($noteid /* && $title == 'New Document' */ ) {
  $prow = getPnoteById($noteid, 'body');
  if (preg_match('/New scanned document (\d+): [^\n]+\/([^\n]+)/', $prow['body'], $matches)) {
    $docid = $matches[1];
    $docname = $matches[2];
?>
 window.open('../../../controller.php?document&retrieve&patient_id=<?php echo $pid ?>&document_id=<?php echo $docid ?>&<?php echo $docname?>&as_file=true',
  '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
<?php
  }
}
?>

</script>

</div> <!-- end outer 'pnotes' -->

</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".append").click(function() { top.restoreSession(); document.my_form.submit(); });
    $(".savenew").click(function() { $("noteid").value(""); });
//    $(".printform").click(function() { PrintForm(); });
    $(".change_activity").click(function() { top.restoreSession(); $("#update_activity").submit(); });
    
    $(".noterow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".noterow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".notecell").click(function() { EditNote(this); });

    $("#note").focus();
});

var EditNote = function(note) {
    top.restoreSession();
    $("#noteid").val(note.id);
    $("#mode").val("");
    //alert($("#noteid").val());
    $("#new_note").submit(); 
}

</script>

</html>
