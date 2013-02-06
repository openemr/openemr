<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/log.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/gprelations.inc.php");
require_once("$srcdir/formatting.inc.php");

if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) {
    require_once("$srcdir/pid.inc");
    setpid($_GET['set_pid']);
}

// Check authorization.
if (!acl_check('patients','notes','',array('write','addonly') ))
    die(htmlspecialchars( xl('Not authorized'), ENT_NOQUOTES));
$tmp = getPatientData($pid, "squad");
if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
    die(htmlspecialchars( xl('Not authorized for this squad.'), ENT_NOQUOTES));

//the number of records to display per screen
$N = 15;
$M = 15;

$mode   = $_REQUEST['mode'];
$offset = $_REQUEST['offset'];
$offset_sent = $_REQUEST['offset_sent'];
$form_active = $_REQUEST['form_active'];
$form_inactive = $_REQUEST['form_inactive'];
$noteid = $_REQUEST['noteid'];
$form_doc_only = isset($_POST['mode']) ? (empty($_POST['form_doc_only']) ? 0 : 1) : 1;
if($_REQUEST['s'] == '1'){
  $inbox = "";
  $outbox = "current";
  $inbox_style = "style='display:none;border:5px solid #FFFFFF;'";
  $outbox_style = "style='border:5px solid #FFFFFF;'";
}else{
  $inbox = "current";
  $outbox = "";
  $inbox_style = "style='border:5px solid #FFFFFF;'";;
  $outbox_style = "style='display:none;border:5px solid #FFFFFF;'";
}

if (!isset($offset)) $offset = 0;
if (!isset($offset_sent)) $offset_sent = 0;

// Collect active variable and applicable html code for links
if ($form_active) {
  $active = '1';
  $activity_string_html = 'form_active=1';
}
else if ($form_inactive) {
  $active = '0';
  $activity_string_html = 'form_inactive=1';
}
else {
  $active = 'all';
  $activity_string_html = '';
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
        newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "pnotes: id ".$noteid);
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

$pres = sqlQuery("SELECT lname, fname " .
 "FROM patient_data WHERE pid = ?", array($pid) );
$patientname = $pres['lname'] . ", " . $pres['fname'];

//retrieve all notes
$result = getPnotesByDate("", $active, 'id,date,body,user,activity,title,assigned_to,message_status',
  $pid, $N, $offset);
$result_sent = getSentPnotesByDate("", $active, 'id,date,body,user,activity,title,assigned_to,message_status',
  $pid, $M, $offset_sent);
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
/// todo, move this to a common library

$(document).ready(function(){

    $("#dem_view").click( function() {
        toggle( $(this), "#DEM" );
    });

    // load divs
    $("#stats_div").load("stats.php");
    $("#notes_div").load("pnotes_fragment.php");

    // fancy box
    enable_modals();
    tabbify();
});
function show_div(name){
  if(name == 'inbox'){
    document.getElementById('inbox_div').style.display = '';
    document.getElementById('outbox_div').style.display = 'none';
  }else{
    document.getElementById('inbox_div').style.display = 'none';
    document.getElementById('outbox_div').style.display = '';
  }
}
</script>
</head>
<body class="body_top">

<div id="pnotes"> <!-- large outer DIV -->

<form border='0' method='post' name='new_note' id="new_note" action='pnotes_full.php?docid=<?php echo htmlspecialchars( $docid, ENT_QUOTES); ?>&<?php echo attr($activity_string_html);?>'>


    <div>
        <span class="title"><?php echo htmlspecialchars( xl('Patient Notes'), ENT_NOQUOTES); ?></span>
    </div>
    <div style='float:left;margin-right:10px'>
        <?php echo htmlspecialchars( xl('for'), ENT_NOQUOTES);?>&nbsp;<span class="title">
	<a href="../summary/demographics.php" onclick="top.restoreSession()"><?php echo htmlspecialchars( getPatientName($pid), ENT_NOQUOTES); ?></a></span>
    </div>
    <div>
        <a href="pnotes_full_add.php" class="css_button iframe"><span><?php echo htmlspecialchars( xl('Add'), ENT_NOQUOTES); ?></span></a>
        <a href="demographics.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?> class="css_button" onclick="top.restoreSession()">
            <span><?php echo htmlspecialchars( xl('View Patient'), ENT_NOQUOTES);?></span>
        </a>
    </div>
    <br/>
    <br/>
    <div>
    <?php if ($active == "all") { ?>
      <span><?php echo xlt('Show All'); ?></span>
    <?php } else { ?>
      <a href="pnotes_full.php" class="link" onclick="top.restoreSession()"><span><?php echo xlt('Show All'); ?></span></a>
    <?php } ?>
    |
    <?php if ($active == '1') { ?>
      <span><?php echo xlt('Show Active'); ?></span>
    <?php } else { ?>
      <a href="pnotes_full.php?form_active=1" class="link" onclick="top.restoreSession()"><span><?php echo xlt('Show Active'); ?></span></a>
    <?php } ?>
    |
    <?php if ($active == '0') { ?>
      <span><?php echo xlt('Show Inactive'); ?></span>
    <?php } else { ?>
      <a href="pnotes_full.php?form_inactive=1" class="link" onclick="top.restoreSession()"><span><?php echo xlt('Show Inactive'); ?></span></a>
    <?php } ?>
    </div>

    <?php
    $title_docname = "";
    if ($docid) {
      $title_docname = " " . htmlspecialchars( xl("linked to document"), ENT_NOQUOTES) . " ";
      $d = new Document($docid);
      $title_docname .= $d->get_url_file();
    }
    ?>
    <input type='hidden' name='mode' id="mode" value="new">
    <input type='hidden' name='offset' id="offset" value="<?php echo $offset; ?>">
    <input type='hidden' name='offset_sent' id="offset_sent" value="<?php echo $offset_sent; ?>">
    <input type='hidden' name='form_active' id="form_active" value="<?php echo htmlspecialchars( $form_active, ENT_QUOTES); ?>">
    <input type='hidden' name='form_inactive' id="form_inactive" value="<?php echo htmlspecialchars( $form_inactive, ENT_QUOTES); ?>">
    <input type='hidden' name='noteid' id="noteid" value="<?php echo htmlspecialchars( $noteid, ENT_QUOTES); ?>">
    <input type='hidden' name='form_doc_only' id="form_doc_only" value="<?php echo htmlspecialchars( $form_doc_only, ENT_QUOTES); ?>">
</form>


<?php
// Get the billing note if there is one.
$billing_note = "";
$colorbeg = "";
$colorend = "";
$resnote = getPatientData($pid, "genericname2, genericval2");
if($resnote && $resnote['genericname2'] == 'Billing') {
  $billing_note = $resnote['genericval2'];
  $colorbeg = "<span style='color:red'>";
  $colorend = "</span>";
}

//Display what the patient owes
$balance = get_patient_balance($pid);
?>

<?php if ($billing_note || $balance ) { ?>

<div style='margin-top:3px'>
<table width='80%'>
<?php
if ($balance != "0") {
  // $formatted = sprintf((xl('$').'%01.2f'), $balance);
  $formatted = oeFormatMoney($balance);
  echo " <tr class='text billing'>\n";
  echo "  <td>".$colorbeg . htmlspecialchars( xl('Balance Due'), ENT_NOQUOTES) .
    $colorend."&nbsp;".$colorbeg. htmlspecialchars( $formatted, ENT_NOQUOTES) .
    $colorend."</td>\n";
  echo " </tr>\n";
}

if ($billing_note) {
  echo " <tr class='text billing'>\n";
  echo "  <td>".$colorbeg . htmlspecialchars( xl('Billing Note'), ENT_NOQUOTES) .
    $colorend."&nbsp;".$colorbeg . htmlspecialchars( $billing_note, ENT_NOQUOTES) .
    $colorend."</td>\n";
  echo " </tr>\n";
}
?>
</table>
</div>
<br>
<?php } ?>
<ul class="tabNav">
  <li class="<?php echo $inbox; ?>" ><a onclick="show_div('inbox')" href="#"><?php echo htmlspecialchars(xl('Inbox'),ENT_NOQUOTES); ?></a></li>
  <li class="<?php echo $outbox; ?>" ><a onclick="show_div('outbox')" href="#"><?php echo htmlspecialchars(xl('Sent Items'),ENT_NOQUOTES); ?></a></li>
</ul>
<div class='tabContainer' >
  <div id='inbox_div' <?php echo $inbox_style; ?> >
<form border='0' method='post' name='update_activity' id='update_activity'
 action="pnotes_full.php?docid=<?php echo htmlspecialchars( $docid, ENT_QUOTES); ?>&<?php echo attr($activity_string_html);?>">
<!-- start of previous notes DIV -->
<div class=pat_notes>
<input type='hidden' name='mode' value="update">
<input type='hidden' name='offset' id='offset' value="<?php echo $offset; ?>">
<input type='hidden' name='offset_sent' id='offset_sent' value="<?php echo $offset_sent; ?>">
<input type='hidden' name='noteid' id='noteid' value="0">
<table border='0' cellpadding="1" class="text">
<?php if ($result != ""): ?>
 <tr>
  <td colspan='5' style="padding: 5px;" >
    <a href="#" class="change_activity" ><span><?php echo htmlspecialchars( xl('Update Active'), ENT_NOQUOTES); ?></span></a>
    |
    <a href="pnotes_full.php?<?php echo attr($activity_string_html);?>" class="" id='Submit'><span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span></a>
  </td>
 </tr></table>
<?php endif; ?>

<table border='0' cellpadding="1"  class="text" width = "80%">
<?php
// display all of the notes for the day, as well as others that are active
// from previous dates, up to a certain number, $N

if ($result != "") {
  echo " <tr class=showborder_head align='left'>\n";
  echo "  <th style='width:100px';>&nbsp;</th>\n";
  echo "  <th>" . htmlspecialchars( xl('Active'), ENT_NOQUOTES) . "&nbsp;</th>\n";
  echo "  <th>" . ($docid ? htmlspecialchars( xl('Linked'), ENT_NOQUOTES) : '') . "</th>\n";
  echo "  <th>" . htmlspecialchars( xl('Type'), ENT_NOQUOTES) . "</th>\n";
  echo "  <th>" . htmlspecialchars( xl('Content'), ENT_NOQUOTES) . "</th>\n";
  echo " </tr>\n";

  $result_count = 0;
  foreach ($result as $iter) {
    $result_count++;
    $row_note_id = $iter['id'];

    $linked = "";
    if ($docid) {
      if (isGpRelation(1, $docid, 6, $row_note_id)) {
        $linked = "checked";
      }
      else {
        // Skip unlinked notes if that is requested.
        if ($form_doc_only) continue;
      }
    }

    $body = $iter['body'];
    if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
      $body = nl2br(htmlspecialchars( oeFormatPatientNote($body), ENT_NOQUOTES));
    } else {
      $body = htmlspecialchars( oeFormatSDFT(strtotime($iter['date'])).date(' H:i', strtotime($iter['date'])), ENT_NOQUOTES) .
        ' (' . htmlspecialchars( $iter['user'], ENT_NOQUOTES) . ') ' . nl2br(htmlspecialchars( oeFormatPatientNote($body), ENT_NOQUOTES));
    }
    $body = preg_replace('/(\sto\s)-patient-(\))/','${1}'.$patientname.'${2}',$body);
    if ( ($iter{"activity"}) && ($iter['message_status'] != "Done") ) {
      $checked = "checked";
    } else {
      $checked = "";
    }

    // highlight the row if it's been selected for updating
    if ($_REQUEST['noteid'] == $row_note_id) {
        echo " <tr height=20 class='noterow highlightcolor' id='".htmlspecialchars( $row_note_id, ENT_QUOTES)."'>\n";
    }
    else {
        echo " <tr class='noterow' id='".htmlspecialchars( $row_note_id, ENT_QUOTES)."'>\n";
    }


	echo "  <td><a href='pnotes_full_add.php?trigger=edit&noteid=".htmlspecialchars( $row_note_id, ENT_QUOTES).
	  "' class='css_button_small iframe'><span>". htmlspecialchars( xl('Edit'), ENT_NOQUOTES) ."</span></a>\n";

    // display, or not, a button to delete the note
    // if the user is an admin or if they are the author of the note, they can delete it
    if (($iter['user'] == $_SESSION['authUser']) || (acl_check('admin','super','','write'))) {
	  echo " <a href='#' class='deletenote css_button_small' id='del" . htmlspecialchars( $row_note_id, ENT_QUOTES) .
	    "' title='" . htmlspecialchars( xl('Delete this note'), ENT_QUOTES) . "'><span>" .
	    htmlspecialchars( xl('Delete'), ENT_NOQUOTES) . "</span>\n";
    }
    echo "  </td>\n";


    echo "  <td class='text bold'>\n";
    echo "   <input type='hidden' name='act".htmlspecialchars( $row_note_id, ENT_QUOTES)."' value='1' />\n";
    echo "   <input type='checkbox' name='chk".htmlspecialchars( $row_note_id, ENT_QUOTES)."' $checked />\n";
    echo "  </td>\n";

    echo "  <td class='text bold'>\n";
    if ($docid) {
      echo "   <input type='checkbox' name='lnk".htmlspecialchars( $row_note_id, ENT_QUOTES)."' $linked />\n";
    }
    echo "  </td>\n";

    echo "  <td class='bold notecell' id='".htmlspecialchars( $row_note_id, ENT_QUOTES)."'>" .
      "<a href='pnotes_full_add.php?trigger=edit&noteid=".htmlspecialchars( $row_note_id, ENT_QUOTES)."' class='iframe'>\n";
    // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
    echo generate_display_field(array('data_type'=>'1','list_id'=>'note_type'), $iter['title']);
    echo "  </a></td>\n";

    echo "  <td class='notecell' id='".htmlspecialchars( $row_note_id, ENT_QUOTES)."'>\n";
    echo "   $body";
    echo "  </td>\n";
    echo " </tr>\n";

    $notes_count++;
  }
} else {
  //no results
  print "<tr><td colspan='3' class='text'>" . htmlspecialchars( xl('None'), ENT_NOQUOTES) . ".</td></tr>\n";
}

?>

</table>
</div>
</form>

<table width='400' border='0' cellpadding='0' cellspacing='0'>
 <tr>
  <td>
<?php
if ($offset > ($N-1)) {
  echo "   <a class='link' href='pnotes_full.php" .
    "?docid=" . htmlspecialchars( $docid, ENT_QUOTES) .
    "&form_active=" . htmlspecialchars( $form_active, ENT_QUOTES) .
    "&form_inactive=" . htmlspecialchars( $form_inactive, ENT_QUOTES) .
    "&form_doc_only=" . htmlspecialchars( $form_doc_only, ENT_QUOTES) .
    "&offset=" . ($offset-$N) . "&" . attr($activity_string_html) . "' onclick='top.restoreSession()'>[" .
    htmlspecialchars( xl('Previous'), ENT_NOQUOTES) . "]</a>\n";
}
?>
  </td>
  <td align='right'>
<?php
if ($result_count == $N) {
  echo "   <a class='link' href='pnotes_full.php" .
    "?docid=" . htmlspecialchars( $docid, ENT_QUOTES) .
    "&form_active=" . htmlspecialchars( $form_active, ENT_QUOTES) .
    "&form_inactive=" . htmlspecialchars( $form_inactive, ENT_QUOTES) .
    "&form_doc_only=" . htmlspecialchars( $form_doc_only, ENT_QUOTES) .
    "&offset=" . ($offset+$N) . "&" . attr($activity_string_html) . "' onclick='top.restoreSession()'>[" .
    htmlspecialchars( xl('Next'), ENT_NOQUOTES) . "]</a>\n";
}
?>
  </td>
 </tr>
</table>

</div>
  <div id='outbox_div' <?php echo $outbox_style; ?> >
<table border='0' cellpadding="1" class="text">
<?php if ($result_sent != ""): ?>
 <tr>
  <td colspan='5' style="padding: 5px;" >
    <a href="pnotes_full.php?s=1&<?php echo attr($activity_string_html);?>" class="" id='Submit'><span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span></a>
  </td>
 </tr></table>
<?php endif; ?>

<table border='0' cellpadding="1"  class="text" width = "80%">
<?php
// display all of the notes for the day, as well as others that are active
// from previous dates, up to a certain number, $N

if ($result_sent != "") {
  echo " <tr class=showborder_head align='left'>\n";
  echo "  <th style='width:100px';>&nbsp;</th>\n";
  echo "  <th>" . htmlspecialchars( xl('Active'), ENT_NOQUOTES) . "&nbsp;</th>\n";
  echo "  <th>" . ($docid ? htmlspecialchars( xl('Linked'), ENT_NOQUOTES) : '') . "</th>\n";
  echo "  <th>" . htmlspecialchars( xl('Type'), ENT_NOQUOTES) . "</th>\n";
  echo "  <th>" . htmlspecialchars( xl('Content'), ENT_NOQUOTES) . "</th>\n";
  echo " </tr>\n";

  $result_sent_count = 0;
  foreach ($result_sent as $iter) {
    $result_sent_count++;
    $row_note_id = $iter['id'];

    $linked = "";
    if ($docid) {
      if (isGpRelation(1, $docid, 6, $row_note_id)) {
        $linked = "checked";
      }
      else {
        // Skip unlinked notes if that is requested.
        if ($form_doc_only) continue;
      }
    }

    $body = $iter['body'];
    if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
      $body = nl2br(htmlspecialchars( oeFormatPatientNote($body), ENT_NOQUOTES));
    } else {
      $body = htmlspecialchars( oeFormatSDFT(strtotime($iter['date'])).date(' H:i', strtotime($iter['date'])), ENT_NOQUOTES) .
        ' (' . htmlspecialchars( $iter['user'], ENT_NOQUOTES) . ') ' . nl2br(htmlspecialchars( oeFormatPatientNote($body), ENT_NOQUOTES));
    }
    $body = preg_replace('/(:\d{2}\s\()'.$pid.'(\sto\s)/','${1}'.$patientname.'${2}',$body);
    if (($iter{"activity"}) && ($iter['message_status'] != "Done") ) {
      $checked = "checked";
    } else {
      $checked = "";
    }

    // highlight the row if it's been selected for updating
    if ($_REQUEST['noteid'] == $row_note_id) {
        echo " <tr height=20 class='noterow highlightcolor' id='".htmlspecialchars( $row_note_id, ENT_QUOTES)."'>\n";
    }
    else {
        echo " <tr class='noterow' id='".htmlspecialchars( $row_note_id, ENT_QUOTES)."'>\n";
    }


	echo "  <td><a href='pnotes_full_add.php?trigger=edit&noteid=".htmlspecialchars( $row_note_id, ENT_QUOTES).
	  "' class='css_button_small iframe'><span>". htmlspecialchars( xl('Edit'), ENT_NOQUOTES) ."</span></a>\n";

    // display, or not, a button to delete the note
    // if the user is an admin or if they are the author of the note, they can delete it
    if (($iter['user'] == $_SESSION['authUser']) || (acl_check('admin','super','','write'))) {
	  echo " <a href='#' class='deletenote css_button_small' id='del" . htmlspecialchars( $row_note_id, ENT_QUOTES) .
	    "' title='" . htmlspecialchars( xl('Delete this note'), ENT_QUOTES) . "'><span>" .
	    htmlspecialchars( xl('Delete'), ENT_NOQUOTES) . "</span>\n";
    }
    echo "  </td>\n";


    echo "  <td class='text bold'>\n";
    echo "   <input type='hidden' name='act".htmlspecialchars( $row_note_id, ENT_QUOTES)."' value='1' />\n";
    echo "   <input type='checkbox' name='chk".htmlspecialchars( $row_note_id, ENT_QUOTES)."' $checked />\n";
    echo "  </td>\n";

    echo "  <td class='text bold'>\n";
    if ($docid) {
      echo "   <input type='checkbox' name='lnk".htmlspecialchars( $row_note_id, ENT_QUOTES)."' $linked />\n";
    }
    echo "  </td>\n";

    echo "  <td class='bold notecell' id='".htmlspecialchars( $row_note_id, ENT_QUOTES)."'>" .
      "<a href='pnotes_full_add.php?trigger=edit&noteid=".htmlspecialchars( $row_note_id, ENT_QUOTES)."' class='iframe'>\n";
    // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
    echo generate_display_field(array('data_type'=>'1','list_id'=>'note_type'), $iter['title']);
    echo "  </a></td>\n";

    echo "  <td class='notecell' id='".htmlspecialchars( $row_note_id, ENT_QUOTES)."'>\n";
    echo "   $body";
    echo "  </td>\n";
    echo " </tr>\n";

    $notes_sent_count++;
  }
} else {
  //no results
  print "<tr><td colspan='3' class='text'>" . htmlspecialchars( xl('None'), ENT_NOQUOTES) . ".</td></tr>\n";
}

?>

</table>

<table width='400' border='0' cellpadding='0' cellspacing='0'>
 <tr>
  <td>
<?php
if ($offset_sent > ($M-1)) {
  echo "   <a class='link' href='pnotes_full.php" .
    "?docid=" . htmlspecialchars( $docid, ENT_QUOTES) .
    "&s=1" .
    "&form_active=" . htmlspecialchars( $form_active, ENT_QUOTES) .
    "&form_inactive=" . htmlspecialchars( $form_inactive, ENT_QUOTES) .
    "&form_doc_only=" . htmlspecialchars( $form_doc_only, ENT_QUOTES) .
    "&offset_sent=" . ($offset_sent-$M) . "&" . attr($activity_string_html) . "' onclick='top.restoreSession()'>[" .
    htmlspecialchars( xl('Previous'), ENT_NOQUOTES) . "]</a>\n";
}
?>
  </td>
  <td align='right'>
<?php
if ($result_sent_count == $M) {
  echo "   <a class='link' href='pnotes_full.php" .
    "?docid=" . htmlspecialchars( $docid, ENT_QUOTES) .
    "&s=1" .
    "&form_active=" . htmlspecialchars( $form_active, ENT_QUOTES) .
    "&form_inactive=" . htmlspecialchars( $form_inactive, ENT_QUOTES) .
    "&form_doc_only=" . htmlspecialchars( $form_doc_only, ENT_QUOTES) .
    "&offset_sent=" . ($offset_sent+$M) . "&" . attr($activity_string_html) . "' onclick='top.restoreSession()'>[" .
    htmlspecialchars( xl('Next'), ENT_NOQUOTES) . "]</a>\n";
}
?>
  </td>
 </tr>
</table>

  </div>
</div>
<script language='JavaScript'>

<?php
if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) {
  $ndata = getPatientData($pid, "fname, lname, pubpid");
?>
 parent.left_nav.setPatient(<?php echo "'" . htmlspecialchars( $ndata['fname']." ".$ndata['lname'], ENT_QUOTES) . "'," .
   htmlspecialchars( $pid, ENT_QUOTES) . ",'" . htmlspecialchars( $ndata['pubpid'], ENT_QUOTES) . "',window.name"; ?>);
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
 window.open('../../../controller.php?document&retrieve&patient_id=<?php echo htmlspecialchars( $pid, ENT_QUOTES); ?>&document_id=<?php echo htmlspecialchars( $docid, ENT_QUOTES); ?>&<?php echo htmlspecialchars( $docname, ENT_QUOTES);?>&as_file=true',
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
        window.open('pnotes_print.php?noteid=<?php echo htmlspecialchars( $noteid, ENT_QUOTES); ?>', '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
    }

    var DeleteNote = function(note) {
        if (confirm("<?php echo htmlspecialchars( xl('Are you sure you want to delete this note?','','','\n '), ENT_QUOTES) .
	  htmlspecialchars( xl('This action CANNOT be undone.'), ENT_QUOTES); ?>")) {
            top.restoreSession();
            // strip the 'del' part of the object's ID
            $("#noteid").val(note.id.replace(/del/, ""));
            $("#mode").val("delete");
            $("#new_note").submit();
        }
    }

});

</script>


</html>
