<?php
 require_once("../../globals.php");
 require_once("$srcdir/pnotes.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/patient.inc");
?>
<html>
<head>
<?php html_header_show();?>

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
  echo "<p>(Notes not authorized)</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>

<table border='0' cellspacing='0' cellpadding='0' height='100%'>
<tr>

<td valign='top'>

<?php if ($thisauth == 'write' || $thisauth == 'addonly') { ?>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="pnotes_full.php" onclick="top.restoreSession()">
<?php } else { ?>
<a href="pnotes_full.php" target="Main" onclick="top.restoreSession()">
<?php } ?>

<font class="title"><?php xl('Notes','e'); ?></font><font class=more><?php echo $tmore;?></font>
</a>
<?php } ?>

<br>

<table border='0'>

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
  $colorbeg = "<font color='red'>";
  $colorend = "<font>";
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
if($balance) {
  $formatted = sprintf('$%01.2f', $balance);
  echo " <tr>\n";
  echo "  <td>$colorbeg" . "Balance Due$colorend</td><td>$colorbeg$formatted$colorend</td>\n";
  echo " </tr>\n";
}

if($billing_note) {
  echo " <tr>\n";
  echo "  <td>$colorbeg" . "Billing Note$colorend</td><td>$colorbeg$billing_note$colorend</td>\n";
  echo " </tr>\n";
}

//retrieve all active notes
if ($result = getPnotesByDate("", 1, "id,date,body,user,title,assigned_to",
  $pid, "all", 0))
{
  $notes_count = 0;//number of notes so far displayed
  foreach ($result as $iter) {

    if ($notes_count >= $N) {
      //we have more active notes to print, but we've reached our display maximum
      echo " <tr>\n";
      echo "  <td colspan='3' align='center'>\n";
      echo "   <a ";
      if (!$GLOBALS['concurrent_layout']) echo "target='Main' ";
      echo "href='pnotes_full.php?active=1' class='alert' onclick='top.restoreSession()'>";
      echo "Some notes were not displayed. Click here to view all.</a>\n";
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

    echo " <tr>\n";
    echo "  <td valign='top'>\n";
    echo "   <a href='pnotes_full.php?noteid=" . $iter['id'] . "&active=1'";
    if (!$GLOBALS['concurrent_layout']) echo " target='Main'";
    echo " class='bold' onclick='top.restoreSession()'>" . $iter['title'] . "</a>\n";
    echo "  </td>\n";
    echo "  <td valign='top'>\n";
    echo "   <font class='text'>$body</font>\n";
    echo "  </td>\n";
    echo " </tr>\n";

    $notes_count++;
  }
}
?>

</table>

</td>
</tr>
</table>

</body>
</html>
