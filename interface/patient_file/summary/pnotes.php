<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

 require_once("../../globals.php");
 require_once("$srcdir/pnotes.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/classes/Document.class.php");

 // form parameter docid can be passed to restrict the display to a document.
 $docid = empty($_REQUEST['docid']) ? 0 : 0 + $_REQUEST['docid'];
?>
<html>
<head>
<?php html_header_show();?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_bottom">

<?php
 $thisauth = acl_check('patients', 'notes');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(" . htmlspecialchars( xl('Notes not authorized'), ENT_NOQUOTES) . ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>

<div id='pnotes'>

<?php if ( acl_check('patients', 'notes','',array('write','addonly') )): ?>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="pnotes_full.php?docid=<?php echo htmlspecialchars( $docid, ENT_QUOTES); ?>" onclick="top.restoreSession()">
<?php } else { ?>
<a href="pnotes_full.php?docid=<?php echo htmlspecialchars( $docid, ENT_QUOTES); ?>" target="Main" onclick="top.restoreSession()">
<?php } ?>

<span class="title"><?php echo htmlspecialchars( xl('Notes'), ENT_NOQUOTES); ?>
<?php
  if ($docid) {
    echo " " . htmlspecialchars( xl("linked to document"), ENT_NOQUOTES) . " ";
    $d = new Document($docid);	
    echo $d->get_url_file();
  }
?>
</span>
<span class=more><?php echo htmlspecialchars( $tmore, ENT_NOQUOTES);?></span>
</a>
<?php endif; ?>

<br>

<table>

<?php
//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 15;

// Get the billing note if there is one.
$billing_note = "";
$colorbeg = "";
$colorend = "";
$sql = "select genericname2, genericval2 " .
    "from patient_data where pid = ? limit 1";
$resnote = sqlQuery($sql, array($pid) );
if($resnote && $resnote['genericname2'] == 'Billing') {
  $billing_note = $resnote['genericval2'];
  $colorbeg = "<span style='color:red'>";
  $colorend = "</span>";
}

//Display what the patient owes
$balance = get_patient_balance($pid);
if ($balance != "0") {
  $formatted = sprintf((xl('$').'%01.2f'), $balance);
  echo " <tr class='text billing'>\n";
  echo "  <td>" . $colorbeg . htmlspecialchars( xl('Balance Due'), ENT_NOQUOTES) .
    $colorend . "</td><td>" . $colorbeg . 
    htmlspecialchars( $formatted, ENT_NOQUOTES) . $colorend."</td>\n";
  echo " </tr>\n";
}

if ($billing_note) {
  echo " <tr class='text billing'>\n";
  echo "  <td>" . $colorbeg . htmlspecialchars( xl('Billing Note'), ENT_NOQUOTES) .
    $colorend . "</td><td>" . $colorbeg .
    htmlspecialchars( $billing_note, ENT_NOQUOTES) . $colorend . "</td>\n";
  echo " </tr>\n";
}

//retrieve all active notes
$result = getPnotesByDate("", 1, "id,date,body,user,title,assigned_to",
  $pid, "all", 0, '', $docid);

if ($result != null) {
  $notes_count = 0;//number of notes so far displayed
  foreach ($result as $iter) {

    if ($notes_count >= $N) {
      //we have more active notes to print, but we've reached our display maximum
      echo " <tr>\n";
      echo "  <td colspan='3' align='center'>\n";
      echo "   <a ";
      if (!$GLOBALS['concurrent_layout']) echo "target='Main' ";
      echo "href='pnotes_full.php?active=1&docid=" .
	htmlspecialchars( $docid, ENT_QUOTES) . 
	"' class='alert' onclick='top.restoreSession()'>";
      echo htmlspecialchars( xl('Some notes were not displayed.','','',' '), ENT_NOQUOTES) .
        htmlspecialchars( xl('Click here to view all.'), ENT_NOQUOTES) . "</a>\n";
      echo "  </td>\n";
      echo " </tr>\n";
      break;
    }

    $body = $iter['body'];
    if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
      $body = nl2br(htmlspecialchars( $body, ENT_NOQUOTES));
    } else {
      $body = htmlspecialchars( date('Y-m-d H:i', strtotime($iter['date'])), ENT_NOQUOTES) .
        ' (' . htmlspecialchars( $iter['user'], ENT_NOQUOTES) . ') ' . nl2br(htmlspecialchars( $body, ENT_NOQUOTES));
    }

    echo " <tr class='text noterow' id='".htmlspecialchars( $iter['id'], ENT_QUOTES)."'>\n";
      
    // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings  
    echo "  <td valign='top' class='bold'>";
    echo generate_display_field(array('data_type'=>'1','list_id'=>'note_type'), $iter['title']);
    echo "</td>\n";
      
    echo "  <td valign='top'>$body</td>\n";
    echo " </tr>\n";

    $notes_count++;
  }
}
?>

</table>

</div> <!-- end pnotes -->

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".noterow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".noterow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".noterow").click(function() { EditNote(this); });
});

var EditNote = function(note) {
<?php if ( acl_check('patients', 'notes','',array('write','addonly') )): ?>
    top.restoreSession();
    <?php if (!$GLOBALS['concurrent_layout']): ?>
    top.Main.location.href = "pnotes_full.php?docid=<?php echo $docid; ?>&noteid=" + note.id + "&active=1";
    <?php else: ?>
    location.href = "pnotes_full.php?docid=<?php echo $docid; ?>&noteid=" + note.id + "&active=1";
    <?php endif; ?>
<?php else: ?>
    // no-op
    alert("<?php echo htmlspecialchars( xl('You do not have access to view/edit this note'), ENT_QUOTES); ?>");
<?php endif; ?>
}

</script>

</html>
