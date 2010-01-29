<?php
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
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/gprelations.inc.php");

if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) {
    require_once("$srcdir/pid.inc");
    setpid($_GET['set_pid']);
}
// Check authorization.
$thisauth = acl_check('patients', 'notes');
if ($thisauth != 'write' && $thisauth != 'addonly')
    die(xl('Not authorized'));
$tmp = getPatientData($pid, "squad");
if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
    die(xl('Not authorized for this squad.'));

//the number of records to display per screen
$N = 25;

$mode   = $_REQUEST['mode'];
$offset = $_REQUEST['offset'];
$form_active = $_REQUEST['form_active'];
$form_inactive = $_REQUEST['form_inactive'];
$noteid = $_REQUEST['noteid'];
$form_doc_only = isset($_POST['mode']) ? (empty($_POST['form_doc_only']) ? 0 : 1) : 1;

if (!isset($offset)) $offset = 0;

// if (!isset($active)) $active = "all";

$active = 'all';
if ($form_active) {
  if (!$form_inactive) $active = '1';
}
else {
  if ($form_inactive)
    $active = '0';
  else
    $form_active = $form_inactive = '1';
}

// form parameter docid can be passed to restrict the display to a document.
$docid = empty($_REQUEST['docid']) ? 0 : 0 + $_REQUEST['docid'];

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
        if ($docid) {
          setGpRelation(1, $docid, 6, $id, !empty($_POST["lnk$id"]));
        }
      }
    }
  }
  elseif ($mode == "new") {
    $note = $_POST['note'];
    // The subroutine will do its own addslashes().
    if (get_magic_quotes_gpc()) $note = stripslashes($note);
    if ($noteid) {
      updatePnote($noteid, $note, $_POST['form_note_type'], $_POST['assigned_to']);
      $noteid = '';
    }
    else {
      addPnote($pid, $note, $userauthorized, '1', $_POST['form_note_type'],
        $_POST['assigned_to']);
    }
  }
  elseif ($mode == "delete") {
    if ($noteid) {
        deletePnote($noteid);
        newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], "pnotes: id ".$noteid);
    }
    $noteid = '';
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
$result = getPnotesByDate("", $active, 'id,date,body,user,activity,title,assigned_to',
  $pid, $N, $offset);
?>

<html>
<head>
<?php html_header_show();?>

<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript">
function submitform(attr) {
if (attr="newnote")
document.forms[0].submit();
}
</script>
</head>
<body class="body_top">

<div id="pnotes"> <!-- large outer DIV -->

<form border='0' method='post' name='new_note' id="new_note" target="_parent" action='pnotes_full.php?docid=<?php echo $docid; ?>'>

    <div>
        <div style='float:left; margin-right: 5px'>
            <span class="title"><?php xl('Patient Note','e'); ?></span>
        </div>
        <div>
            <?php if ($noteid) { ?>
            <!-- existing note -->
            <a href="#" class="css_button" id="printnote"><span><?php xl('Print','e'); ?></span></a>
            <?php } ?>
            <a class="css_button large_button" id='cancel' href='javascript:;'>
            <span class='css_button_span large_button_span'><?php xl('Cancel','e');?></span>
            </a>
        </div>
    </div>

    <br/>

<?php
$title_docname = "";
if ($docid) {
  $title_docname = " " . xl("linked to document") . " ";
  $d = new Document($docid);
  $title_docname .= $d->get_url_file();
}
?>
<input type='hidden' name='mode' id="mode" value="new">
<input type='hidden' name='trigger' id="trigger" value="add">
<input type='hidden' name='offset' id="offset" value="<?php echo $offset ?>">
<input type='hidden' name='form_active' id="form_active" value="<?php echo $form_active ?>">
<input type='hidden' name='form_inactive' id="form_inactive" value="<?php echo $form_inactive ?>">
<input type='hidden' name='noteid' id="noteid" value="<?php echo $noteid ?>">
<input type='hidden' name='form_doc_only' id="form_doc_only" value="<?php echo $form_doc_only ?>">
<table border='0' cellspacing='8'>
 <tr>
  <td class='text'>
    <?php
     if ($noteid) {
       // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
       echo xl('Amend Existing Note')."<b> &quot;" . generate_display_field(array('data_type'=>'1','list_id'=>'note_type'), $title) . "&quot;</b>\n";
     } else {
       echo xl('Add New Note')."\n";
     }
    ?>
  </td>
 </tr>
 <tr>
  <td class='text'>
    <br/>

   <b><?php xl('Type','e'); ?>:</b>
   <?php
   // Added 6/2009 by BM to incorporate the patient notes into the list_options listings
    generate_form_field(array('data_type'=>1,'field_id'=>'note_type','list_id'=>'note_type','empty_title'=>'SKIP'), $title);
   ?>
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
    echo "<div class='text'>".$body."</div>";
}
?>
    <br/>
   <textarea name='note' id='note' rows='4' cols='58'></textarea>

    <?php if ($noteid) { ?>
    <!-- existing note -->
    <a href="#" class="css_button" id="newnote" title="<?php xl('Add as a new note','e'); ?>" ><span><?php xl('Save as new note','e'); ?></span></a>
    <a href="#" class="css_button" id="appendnote" title="<?php xl('Append to the existing note','e'); ?>"><span><?php xl('Append this note','e'); ?></span></a>
    <?php } else { ?>
    <a href="#" class="css_button" id="newnote" title="<?php xl('Add as a new note','e'); ?>" ><span><?php xl('Save as new note','e'); ?></span></a>
    <?php } ?>

  </td>
 </tr>
</table>
<br>
</form>
<form border='0' method='post' name='update_activity' id='update_activity'
 action="pnotes_full.php?docid=<?php echo $docid; ?>">

<!-- start of previous notes DIV -->
<div class=pat_notes>


<input type='hidden' name='mode' value="update">
<input type='hidden' name='offset' id='noteid' value="<?php echo $offset;?>">
<input type='hidden' name='noteid' id='noteid' value="0">
</form>

<table width='400' border='0' cellpadding='0' cellspacing='0'>
 <tr>
  <td>
<?php
if ($offset > ($N-1)) {
  echo "   <a class='link' href='pnotes_full.php" .
    "?docid=$docid" .
    "&form_active=$form_active" .
    "&form_inactive=$form_inactive" .
    "&form_doc_only=$form_doc_only" .
    "&offset=" . ($offset-$N) . "' onclick='top.restoreSession()'>[" .
    xl('Previous') . "]</a>\n";
}
?>
  </td>
  <td align='right'>
<?php
if ($result_count == $N) {
  echo "   <a class='link' href='pnotes_full.php" .
    "?docid=$docid" .
    "&form_active=$form_active" .
    "&form_inactive=$form_inactive" .
    "&form_doc_only=$form_doc_only" .
    "&offset=" . ($offset+$N) . "' onclick='top.restoreSession()'>[" .
    xl('Next') . "]</a>\n";
}
?>
  </td>
 </tr>
</table>

</div> <!-- close the previous-notes DIV -->

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
    $("#appendnote").click(function() { AppendNote(); });
    $("#newnote").click(function() { NewNote(); });
    $("#printnote").click(function() { PrintNote(); });

    $(".change_activity").click(function() { top.restoreSession(); $("#update_activity").submit(); });

    $(".deletenote").click(function() { DeleteNote(this); });

    $(".noterow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".noterow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".notecell").click(function() { EditNote(this); });

    $("#note").focus();

    var EditNote = function(note) {
        top.restoreSession();
        $("#noteid").val(note.id);
        $("#mode").val("");
        $("#new_note").submit();
    }

    var NewNote = function () {
        top.restoreSession();
        $("#noteid").val('');
        $("#new_note").submit();
    }

    var AppendNote = function () {
        top.restoreSession();
        $("#new_note").submit();
    }

    var PrintNote = function () {
        top.restoreSession();
        window.open('pnotes_print.php?noteid=<?php echo $noteid; ?>', '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
    }

    var DeleteNote = function(note) {
        if (confirm("<?php xl('Are you sure you want to delete this note?','e','','\n ') . xl('This action CANNOT be undone.','e'); ?>")) {
            top.restoreSession();
            // strip the 'del' part of the object's ID
            $("#noteid").val(note.id.replace(/del/, ""));
            $("#mode").val("delete");
            $("#new_note").submit();
        }
    }

});
$(document).ready(function(){
    $("#cancel").click(function() {
		  parent.$.fn.fancybox.close();
	 });

});
</script>

</html>
