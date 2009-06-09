<?php
 require_once("../../globals.php");
 require_once("$srcdir/pnotes.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/options.inc.php");
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
  echo "<p>(" . xl('Notes not authorized') . ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>

<div id='pnotes'>

<?php if ($thisauth == 'write' || $thisauth == 'addonly'): ?>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="pnotes_full.php" onclick="top.restoreSession()">
<?php } else { ?>
<a href="pnotes_full.php" target="Main" onclick="top.restoreSession()">
<?php } ?>

<span class="title"><?php xl('Notes','e'); ?></span><span class=more><?php echo $tmore;?></span>
</a>
<?php endif; ?>

<br>

<table>

<?php
//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 15;

$conn = $GLOBALS['adodb']['db'];

// Get the billing note if there is one.
$billing_note = "";
$colorbeg = "";
$colorend = "";
$sql = "select genericname2, genericval2 " .
    "from patient_data where pid = '$pid' limit 1";
$resnote = $conn->Execute($sql);
if($resnote && !$resnote->EOF && $resnote->fields['genericname2'] == 'Billing') {
  $billing_note = $resnote->fields['genericval2'];
  $colorbeg = "<span style='color:red'>";
  $colorend = "</span>";
}

//Display what the patient owes
/*********************************************************************
require_once($GLOBALS['fileroot'] ."/library/classes/WSWrapper.class.php");
$customer_info['id'] = 0;
$sql = "SELECT foreign_id from integration_mapping as im LEFT JOIN patient_data as pd on im.local_id=pd.id where pd.pid = '" . $pid . "' and im.local_table='patient_data' and im.foreign_table='customer'";
$result = $conn->Execute($sql);
if($result && !$result->EOF) 
{
  $customer_info['id'] = $result->fields['foreign_id'];
}
$function['ezybiz.customer_balance'] = array(new xmlrpcval($customer_info,"struct"));
$ws = new WSWrapper($function);
if(is_numeric($ws->value)) {
  $formatted = sprintf('$%01.2f', $ws->value);
  echo " <tr>\n";
  echo "  <td>$colorbeg" . "Balance Due$colorend</td><td>$colorbeg$formatted$colorend</td>\n";
  echo " </tr>\n";
}
*********************************************************************/
$balance = get_patient_balance($pid);
if ($balance != "0") {
  $formatted = sprintf((xl('$').'%01.2f'), $balance);
  echo " <tr class='text billing'>\n";
  echo "  <td>".$colorbeg.xl('Balance Due').$colorend."</td><td>".$colorbeg.$formatted.$colorend."</td>\n";
  echo " </tr>\n";
}

if ($billing_note) {
  echo " <tr class='text billing'>\n";
  echo "  <td>".$colorbeg.xl('Billing Note').$colorend."</td><td>".$colorbeg.$billing_note.$colorend."</td>\n";
  echo " </tr>\n";
}

//retrieve all active notes
$result = getPnotesByDate("", 1, "id,date,body,user,title,assigned_to", $pid, "all", 0);

if ($result != null) {
  $notes_count = 0;//number of notes so far displayed
  foreach ($result as $iter) {

    if ($notes_count >= $N) {
      //we have more active notes to print, but we've reached our display maximum
      echo " <tr>\n";
      echo "  <td colspan='3' align='center'>\n";
      echo "   <a ";
      if (!$GLOBALS['concurrent_layout']) echo "target='Main' ";
      echo "href='pnotes_full.php?active=1' class='alert' onclick='top.restoreSession()'>";
      echo xl('Some notes were not displayed.','','',' ') . xl('Click here to view all.') . "</a>\n";
      echo "  </td>\n";
      echo " </tr>\n";
      break;
    }

    $body = $iter['body'];
    if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
      $body = nl2br($body);
    } else {
      $body = date('Y-m-d H:i', strtotime($iter['date'])) .
        ' (' . $iter['user'] . ') ' . nl2br($body);
    }

    echo " <tr class='text noterow' id='".$iter['id']."'>\n";
      
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
<?php if ($thisauth == 'write' || $thisauth == 'addonly'): ?>
    top.restoreSession();
    <?php if (!$GLOBALS['concurrent_layout']): ?>
    top.Main.location.href = "pnotes_full.php?noteid=" + note.id + "&active=1";
    <?php else: ?>
    location.href = "pnotes_full.php?noteid=" + note.id + "&active=1";
    <?php endif; ?>
<?php else: ?>
    // no-op
    alert("<?php xl('You do not have access to view/edit this note','e'); ?>");
<?php endif; ?>
}

</script>

</html>
