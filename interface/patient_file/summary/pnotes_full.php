<?
 include_once("../../globals.php");
 include_once("$srcdir/pnotes.inc");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");

 // Check authorization.
 $thisauth = acl_check('patients', 'notes');
 if ($thisauth != 'write' && $thisauth != 'addonly')
  die("Not authorized.");
 $tmp = getPatientData($pid, "squad");
 if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  die("Not authorized for this squad.");

//the number of records to display per screen
$N = 25;

$note_types = array(
  'Unassigned',
  'Chart Note',
  'Insurance',
  'New Document',
  'Pharmacy',
  'Prior Auth',
  'Referral',
  'Test Scheduling',
  'Other');

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
  $prow = getPnoteById($noteid, 'title,assigned_to');
  $title = $prow['title'];
  $assigned_to = $prow['assigned_to'];
}

// Get the users list.
$ures = sqlStatement("SELECT username, fname, lname FROM users " .
 "ORDER BY lname, fname");
?>
<html>
<head>

<link rel='stylesheet' href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<form border='0' method='post' name='new_note' action='pnotes_full.php'>

<a href="../summary/patient_summary.php" target="Main">
<font class='title'>Patient Notes</font><font class='back'>(Back)</font>
</a>
<br>

<input type='hidden' name='mode' value="new">
<input type='hidden' name='offset' value="<? echo $offset ?>">
<input type='hidden' name='active' value="<? echo $active ?>">
<input type='hidden' name='noteid' value="<? echo $noteid ?>">

<center>

<table border='0' cellspacing='8'>
 <tr>
  <td class='text' align='center'>
<?php
 if ($noteid) {
   echo "<b>Amend Existing Note &quot;$title&quot;</b>\n";
 } else {
   echo "<b>Add New Note</b>\n";
 }
?>
  </td>
 </tr>
 <tr>
  <td class='text' align='center'>
   <b>Type:</b>
   <select name='title'>
<?
 foreach ($note_types as $value) {
  echo "    <option value='$value'";
  if ($value == $title) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
   &nbsp; &nbsp;
   <b>To:</b>
   <select name='assigned_to'>
<?
 while ($urow = sqlFetchArray($ures)) {
  echo "    <option value='" . $urow['username'] . "'";
  if ($urow['username'] == $assigned_to) echo " selected";
  echo ">" . $urow['lname'];
  if ($urow['fname']) echo ", " . $urow['fname'];
  echo "</option>\n";
 }
?>
    <option value=''>** Close **</option>
   </select>
  </td>
 </tr>
 <tr>
  <td>
   <textarea name='note' rows='4' cols='80' wrap='virtual'></textarea>
  </td>
 </tr>
</table>

<a href="javascript:document.new_note.submit();" class='link_submit'>
<?php if ($noteid) { ?>
[Append to This Note]
<?php } else { ?>
[Add New Note]
<?php } ?>
</a>
<br>
</form>

<form border='0' method='post' name='update_activity' action="pnotes_full.php">

<?//change the view on the current mode, whether all, active, or inactive
$all_class = "link"; $active_class = "link"; $inactive_class = "link";
if ($active=="all") {
  $all_class="link_selected";
} elseif ($active==1) {
  $active_class="link_selected";
} elseif ($active==0) {
  $inactive_class="link_selected";
}
?>

<br>
<font class='text'>View: </font> 
<a href="pnotes_full.php?offset=0&active=all" class='<?echo $all_class;?>'>[All]</a>
<a href="pnotes_full.php?offset=0&active=1" class='<?echo $active_class;?>'>[Only Active]</a>
<a href="pnotes_full.php?offset=0&active=0" class='<?echo $inactive_class;?>'>[Only Inactive]</a>

<input type='hidden' name='mode' value="update">
<input type='hidden' name='offset' value="<?echo $offset;?>">
<input type='hidden' name='active' value="<?echo $active;?>">
<input type='hidden' name='noteid' value="0">

<table border='0'>
 <tr>
  <td colspan='3' align='left'>
   <a href="javascript:document.update_activity.submit();" class='link_submit'>[Change Activity]</a>
  </td>
 </tr>
<?
//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N

//retrieve all notes

if ($result = getPnotesByDate("", $active,
  'id,date,body,user,activity,title,assigned_to', $pid, $N, $offset))
{
  $result_count = 0;
  foreach ($result as $iter) {
    $result_count++;

//  if (getdate() == strtotime($iter{"date"})) {
//    $date_string = "Today, " . date( "D F jS" ,strtotime($iter{"date"}));
//  } else {
//    $date_string = date( "D F jS" ,strtotime($iter{"date"}));
//  }

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

    echo " <tr>\n";
    echo "  <td valign='top'>\n";
    echo "   <input type='hidden' name='act" . $iter{"id"} . "' value='1'>\n";
    echo "   <input type='checkbox' name='chk" . $iter{"id"} . "' $checked>\n";

    echo "   <a href='javascript:document.forms[1].noteid.value=" .
         $iter['id'] . ";document.update_activity.submit();' " .
         "class='link_submit'>" . $iter['title'] . "</a>\n";
    echo "  </td>\n";
    echo "  <td valign='top'>\n";
    echo "   <font class='text'>$body</font>\n";
    echo "  </td>\n";
    echo " </tr>\n";

    $notes_count++;
  }
} else {
  //no results
  print "<tr><td></td><td></td></tr>\n";
}

?>
 <tr>
  <td colspan='3' align='left'>
   <a href="javascript:document.update_activity.submit();" class='link_submit'>[Change Activity]</a>
  </td>
 </tr>

</table>
</form>

<table width='400' border='0' cellpadding='0' cellspacing='0'>
 <tr>
  <td>
<?
if ($offset > ($N-1)) {
  echo "   <a class='link' href='pnotes_full.php?active=" . $active . "&offset=" . ($offset-$N) . "'>[Previous]</a>\n";
}
?>
  </td>
  <td align='right'>
<?
if ($result_count == $N) {
  echo "   <a class='link' href='pnotes_full.php?active=" . $active . "&offset=" . ($offset+$N) . "'>[Next]</a>\n";
}
?>
  </td>
 </tr>
</table>

</center>

<?php
// If this note references a new patient document, pop up a display
// of that document.
//
if ($noteid && $title == 'New Document') {
  $prow = getPnoteById($noteid, 'body');
  if (preg_match('/New scanned document (\d+): [^\n]+\/([^\n]+)/', $prow['body'], $matches)) {
    $docid = $matches[1];
    $docname = $matches[2];
?>
<script language="JavaScript">
 window.open('../../../controller.php?document&retrieve&patient_id=<?php echo $pid ?>&document_id=<?php echo $docid ?>&<?php echo $docname?>&as_file=true',
  '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
</script>
<?php
  }
}
?>

</body>
</html>
