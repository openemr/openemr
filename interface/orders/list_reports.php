<?php
/**
* List procedure orders and reports, and fetch new reports and their results.
*
* Copyright (C) 2013-2014 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once("$srcdir/log.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/classes/Document.class.php");
require_once("./receive_hl7_results.inc.php");
require_once("./gen_hl7_order.inc.php");

/**
 * Get a list item title, translating if required.
 *
 * @param  string  $listid  List identifier.
 * @param  string  $value   List item identifier.
 * @return string  The item's title.
 */
function getListItem($listid, $value) {
  $lrow = sqlQuery("SELECT title FROM list_options " .
    "WHERE list_id = ? AND option_id = ?",
    array($listid, $value));
  $tmp = xl_list_label($lrow['title']);
  if (empty($tmp)) $tmp = "($report_status)";
  return $tmp;
}

/**
 * Adapt text to be suitable as the contents of a table cell.
 *
 * @param  string  $s  Input text.
 * @return string  Output text.
 */
function myCellText($s) {
  if ($s === '') return '&nbsp;';
  return text($s);
}

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) die(xlt('Not authorized'));

$errmsg = '';

// Send selected unsent orders if requested. This does not support downloading
// very well as it will only send the first of those.
if ($_POST['form_xmit']) {
  foreach ($_POST['form_cb'] as $formid) {
    $row = sqlQuery("SELECT lab_id FROM procedure_order WHERE " .
      "procedure_order_id = ?", array($formid));
    $ppid = intval($row['lab_id']);
    $hl7 = '';
    $errmsg = gen_hl7_order($formid, $hl7);
    if (empty($errmsg)) {
      $errmsg = send_hl7_order($ppid, $hl7);
    }
    if ($errmsg) break;
    sqlStatement("UPDATE procedure_order SET date_transmitted = NOW() WHERE " .
      "procedure_order_id = ?", array($formid));
  }
}
?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<title><?php echo xlt('Procedure Orders and Reports'); ?></title>

<style>

tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }

</style>

<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>

<script language="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function openResults(orderid) {
 top.restoreSession();
 // Open results in a new window. The options parameter serves to defeat Firefox's
 // "open windows in a new tab", which is what we want because the doc may want to
 // see the results concurrently with other stuff like related patient notes.
 // Opening in the other frame is not good enough because if they then do related
 // patients notes it will wipe out this script's list. We need 3 viewports.
 window.open('single_order_results.php?orderid=' + orderid, '_blank', 'toolbar=0,location=0,menubar=0,scrollbars=yes');
 //
 // To open results in the same frame:
 // document.location.href = 'single_order_results.php?orderid=' + orderid;
 //
 // To open results in the "other" frame:
 // var w = window;
 // var othername = (w.name == 'RTop') ? 'RBot' : 'RTop';
 // w.parent.left_nav.forceDual();
 // w.parent.left_nav.setRadio(othername, 'ore');
 // w.parent.left_nav.loadFrame('ore1', othername, 'orders/single_order_results.php?orderid=' + orderid);
}

// Invokes the patient matching dialog.
// args is a string of URL arguments, see the calling logic for that.
// The dialog script will directly insert the selected pid value, or 0,
// into the value of the form field named "[select][$key1][$key2]".
//
function openPtMatch(args) {
 top.restoreSession();
 window.open('patient_match_dialog.php?' + args, '_blank', 'toolbar=0,location=0,menubar=0,scrollbars=yes');
}

function openPatient(pid) {
 top.restoreSession();
 document.location.href = "../patient_file/summary/demographics.php?set_pid=" + pid;
}

</script>

</head>

<body class="body_top">
<form method='post' action='list_reports.php' enctype='multipart/form-data'
 onsubmit='return validate(this)'>

<!-- This might be set by the results window: -->
<input type='hidden' name='form_external_refresh' value='' />

<?php
if ($errmsg) {
  echo "<font color='red'>" . text($errmsg) . "</font><br />\n";
}

$info = array();

// We skip match/delete processing if this is just a refresh, because that
// might be a nasty surprise.
if (empty($_POST['form_external_refresh'])) {
  // Get patient matching selections from this form if there are any.
  if (is_array($_POST['select'])) {
    foreach ($_POST['select'] as $selkey => $selval) {
      // Note that $selval is an array of the values to match on.
      $info[$selkey] = array('select' => $selval);
    }
  }
  // Get file delete requests from this form if there are any.
  if (is_array($_POST['delete'])) {
    foreach ($_POST['delete'] as $delkey => $dummy) {
      $info[$delkey] = array('delete' => true);
    }
  }
}

// Attempt to post any incoming results.
$errmsg = poll_hl7_results($info);

// Display a row for each required patient matching decision or message.
$s = '';
$matchreqs = false;
foreach ($info as $infokey => $infoval) {
  $count = 0;
  if (is_array($infoval['match'])) {
    foreach ($infoval['match'] as $matchkey => $matchval) {
      $matchreqs = true;
      $s .= " <tr class='detail' bgcolor='#ccccff'>\n";
      if (!$count++) {
        $s .= "  <td align='center'><input type='checkbox' name='delete[" .
          attr($infokey) . "]' value='1' /></td>\n";
        $s .= "  <td>" . text($infokey) . "</td>\n";
      }
      else {
        $s .= "  <td>&nbsp;</td>\n";
        $s .= "  <td>&nbsp;</td>\n";
      }
      $s .= "  <td><a href='javascript:openPtMatch(\"" .
        "key1="   . urlencode($infokey ) .
        "&key2="  . urlencode($matchkey) .
        "&ss="    . urlencode($matchval['ss'   ]) .
        "&fname=" . urlencode($matchval['fname']) .
        "&lname=" . urlencode($matchval['lname']) .
        "&DOB="   . urlencode($matchval['DOB'  ]) .
        "\")'>";
      $s .= xlt('Click to match patient') . ' "' . text($matchval['lname']) .
        ', ' . text($matchval['fname']) . '"';
      $s .= "</a>";
      $s .= "</td>\n";
      $s .= "  <td style='width:1%'><input type='text' name='select[" .
        attr($infokey) . "][" . attr($matchkey) . "]' size='3' value='' " .
        "style='background-color:transparent' readonly /></td>\n";
      $s .= " </tr>\n";
    }
  }
  if (is_array($infoval['mssgs'])) {
    foreach ($infoval['mssgs'] as $message) {
      $s .= " <tr class='detail' bgcolor='#ccccff'>\n";
      if (!$count++) {
        $s .= "  <td><input type='checkbox' name='delete[" . attr($infokey) . "]' value='1' /></td>\n";
        $s .= "  <td>" . text($infokey) . "</td>\n";
      }
      else {
        $s .= "  <td>&nbsp;</td>\n";
        $s .= "  <td>&nbsp;</td>\n";
      }
      $s .= "  <td colspan='2' style='color:red'>". text($message) . "</td>\n";
      $s .= " </tr>\n";
    }
  }
}
if ($s) {
  echo "<p class='bold' style='color:#008800'>";
  echo xlt('Incoming results requiring attention:');
  echo "</p>\n";
  echo "<table width='100%'>\n";
  echo " <tr class='head'>\n";
  echo "  <td>" . xlt('Delete'  ) . "</th>\n";
  echo "  <td>" . xlt('Lab/File') . "</th>\n";
  echo "  <td>" . xlt('Message' ) . "</th>\n";
  echo "  <td>" . xlt('Match'   ) . "</th>\n";
  echo " </tr>\n";
  echo $s;
  echo "</table>\n";
  echo "<p class='bold' style='color:#008800'>";
  if ($matchreqs) {
    echo xlt('Click where indicated above to match the patient.') . ' ';
    echo xlt('After that the Match column will show the selected patient ID, or 0 to create.') . ' ';
    echo xlt('If you do not select a match the patient will be created.') . ' ';
  }
  echo xlt('Checkboxes above indicate if you want to reject and delete the HL7 file.') . ' ';
  echo xlt('When done, click Submit (below) to apply your choices.');
  echo "</p>\n";
}

// If there was a fatal error display that.
if ($errmsg) {
  echo "<font color='red'>" . text($errmsg) . "</font><br />\n";
}

// Upload support removed because it is awkward to handle and of dubious
// value.  Note we can now get results from the local filesystem.
//
/*********************************************************************
// Process uploaded file if there is one.
if (!empty($_FILES['userfile']['name'])) { // if upload was attempted
  if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    $hl7 = file_get_contents($_FILES['userfile']['tmp_name']);
    $msg = receive_hl7_results($hl7);
    $message = xl('Upload processed successfully');
    if ($msg) {
      $message = xl('Error processing upload') . ": " . $msg;
    }
    echo text($message) . "<br />\n";
  }
  else {
    echo "<font color='red'>" . xlt('Upload failed!') . "</font><br />\n";
  }
}
*********************************************************************/

$form_from_date = empty($_POST['form_from_date']) ? '' : trim($_POST['form_from_date']);
$form_to_date = empty($_POST['form_to_date']) ? '' : trim($_POST['form_to_date']);
// if (empty($form_to_date)) $form_to_date = $form_from_date;

$form_reviewed = empty($_POST['form_reviewed']) ? 3 : intval($_POST['form_reviewed']);

$form_patient = !empty($_POST['form_patient']);

$form_provider = empty($_POST['form_provider']) ? '' : intval($_POST['form_provider']);
?>

<table width='100%'>
 <tr>
  <td class='text' align='center'>
   &nbsp;<?php echo xlt('From'); ?>:
   <input type='text' size='6' name='form_from_date' id='form_from_date'
    value='<?php echo attr($form_from_date); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />

   &nbsp;<?php echo xlt('To'); ?>:
   <input type='text' size='6' name='form_to_date' id='form_to_date'
    value='<?php echo attr($form_to_date); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />

   &nbsp;
   <input type='checkbox' name='form_patient' value='1'
    <?php if ($form_patient) echo 'checked '; ?>/><?php echo xlt('Current Pt Only'); ?>

   &nbsp;
   <select name='form_reviewed'>
<?php
foreach (array(
  '1' => xl('All'),
  '2' => xl('Reviewed'),
  '3' => xl('Received, unreviewed'),
  '4' => xl('Sent, not received'),
  '5' => xl('Not sent'),
  ) as $key => $value) {
  echo "<option value='$key'";
  if ($key == $form_reviewed) echo " selected";
  echo ">" . text($value) . "</option>\n";
}
?>
   </select>

   &nbsp;
<?php
 generate_form_field(array('data_type' => 10, 'field_id' => 'provider',
   'empty_title' => '-- All Providers --'), $form_provider);
?>

   &nbsp;
   <input type='submit' name='form_refresh' value=<?php echo xla('Submit'); ?>>
  </td>
 </tr>
</table>

<table width='100%' cellpadding='1' cellspacing='2'>

 <tr class='head'>
  <td colspan='2'><?php echo xlt('Patient'  ); ?></td>
  <td colspan='2'><?php echo xlt('Order'    ); ?></td>
  <td colspan='2'><?php echo xlt('Procedure'); ?></td>
  <td colspan='2'><?php echo xlt('Report'   ); ?></td>
 </tr>

 <tr class='head'>
  <td><?php echo xlt('Name'       ); ?></td>
  <td><?php echo xlt('ID'         ); ?></td>
  <td><?php echo xlt('Date'       ); ?></td>
  <td><?php echo xlt('ID'         ); ?></td>
  <td><?php echo xlt('Code'       ); ?></td>
  <td><?php echo xlt('Description'); ?></td>
  <td><?php echo xlt('Date'       ); ?></td>
  <td><?php echo xlt('Status'     ); ?></td>
  <!-- <td><?php echo xlt('Reviewed'   ); ?></td> -->
 </tr>

<?php 
$selects =
  "po.patient_id, po.procedure_order_id, po.date_ordered, po.date_transmitted, " .
  "pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, pc.do_not_send, " .
  "pr.procedure_report_id, pr.date_report, pr.report_status, pr.review_status";

$joins =
  "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id AND " .
  "pr.procedure_order_seq = pc.procedure_order_seq";

$orderby =
  "po.date_ordered, po.procedure_order_id, " .
  "pc.do_not_send, pc.procedure_order_seq, pr.procedure_report_id";

$where = "1 = 1";
$sqlBindArray = array();

if (!empty($form_from_date)) {
  $where .= " AND po.date_ordered >= ?";
  $sqlBindArray[] = $form_from_date;
}
if (!empty($form_to_date)) {
  $where .= " AND po.date_ordered <= ?";
  $sqlBindArray[] = $form_to_date;
}

if ($form_patient) {
  $where .= " AND po.patient_id = ?";
  $sqlBindArray[] = $pid;
}

if ($form_provider) {
  $where .= " AND po.provider_id = ?";
  $sqlBindArray[] = $form_provider;
}

if ($form_reviewed == 2) {
  $where .= " AND pr.procedure_report_id IS NOT NULL AND pr.review_status = 'reviewed'";
}
else if ($form_reviewed == 3) {
  $where .= " AND pr.procedure_report_id IS NOT NULL AND pr.review_status != 'reviewed'";
}
else if ($form_reviewed == 4) {
  $where .= " AND po.date_transmitted IS NOT NULL AND pr.procedure_report_id IS NULL";
}
else if ($form_reviewed == 5) {
  $where .= " AND po.date_transmitted IS NULL AND pr.procedure_report_id IS NULL";
}

$query = "SELECT " .
  "pd.fname, pd.mname, pd.lname, pd.pubpid, $selects " .
  "FROM procedure_order AS po " .
  "LEFT JOIN procedure_order_code AS pc ON pc.procedure_order_id = po.procedure_order_id " .
  "LEFT JOIN patient_data AS pd ON pd.pid = po.patient_id $joins " .
  "WHERE $where " .
  "ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, $orderby";

$res = sqlStatement($query, $sqlBindArray);

$lastptid = -1;
$lastpoid = -1;
$lastpcid = -1;
$encount = 0;
$lino = 0;
$extra_html = '';
$num_checkboxes = 0;

while ($row = sqlFetchArray($res)) {
  $patient_id       = empty($row['patient_id'         ]) ? 0 : ($row['patient_id'         ] + 0);
  $order_id         = empty($row['procedure_order_id' ]) ? 0 : ($row['procedure_order_id' ] + 0);
  $order_seq        = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
  $date_ordered     = empty($row['date_ordered'       ]) ? '' : $row['date_ordered'];
  $date_transmitted = empty($row['date_transmitted'   ]) ? '' : $row['date_transmitted'];
  $procedure_code   = empty($row['procedure_code'     ]) ? '' : $row['procedure_code'];
  $procedure_name   = empty($row['procedure_name'     ]) ? '' : $row['procedure_name'];
  $report_id        = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);
  $date_report      = empty($row['date_report'        ]) ? '' : $row['date_report'];
  $report_status    = empty($row['report_status'      ]) ? '' : $row['report_status']; 
  $review_status    = empty($row['review_status'      ]) ? '' : $row['review_status'];

  // Sendable procedures sort first, so this also applies to the order on an order ID change.
  $sendable = isset($row['procedure_order_seq']) && $row['do_not_send'] == 0;

  $ptname = $row['lname'];
  if ($row['fname'] || $row['mname'])
    $ptname .= ', ' . $row['fname'] . ' ' . $row['mname'];

  if ($lastpoid != $order_id || $lastpcid != $order_seq) {
    ++$encount;
  }
  $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

  echo " <tr class='detail' bgcolor='$bgcolor'>\n";

  // Generate patient columns.
  if ($lastptid != $patient_id) {
    $lastpoid = -1;
    echo "  <td onclick='openPatient($patient_id)' style='cursor:pointer;color:blue'>";
    echo text($ptname);
    echo "</td>\n";
    echo "  <td>" . text($row['pubpid']) . "</td>\n";
  }
  else {
    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
  }

  // Generate order columns.
  if ($lastpoid != $order_id) {
    $lastpcid = -1;
    echo "  <td>";
    // Checkbox to support sending unsent orders, disabled if sent.
    echo "<input type='checkbox' name='form_cb[$order_id]' value='$order_id' ";
    if ($date_transmitted || !$sendable) {
      echo "disabled";
    } else {
      echo "checked";
      ++$num_checkboxes;
    }
    echo " />";
    // Order date comes with a link to open results in the same frame.
    echo "<a href='javascript:openResults($order_id)' ";
    echo "title='" . xla('Click for results') . "'>";    
    echo text($date_ordered);
    echo "</a></td>\n";
    echo "  <td>";
    // Order ID comes with a link to open the manifest in a new window/tab.
    echo "<a href='" . $GLOBALS['webroot'];
    echo "/interface/orders/order_manifest.php?orderid=";
    echo attr($order_id);
    echo "' target='_blank' onclick='top.restoreSession()' ";
    echo "title='" . xla('Click for order summary') . "'>";
    echo text($order_id);
    echo "</a></td>\n";
  }
  else {
    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
  }

  // Generate procedure columns.
  if ($order_seq && $lastpcid != $order_seq) {
    if ($sendable) {
      echo "  <td>" . text($procedure_code) . "</td>\n";
      echo "  <td>" . text($procedure_name) . "</td>\n";
    }
    else {
      echo "  <td><strike>" . text($procedure_code) . "</strike></td>\n";
      echo "  <td><strike>" . text($procedure_name) . "</strike></td>\n";
    }
  }
  else {
    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
  }

  // Generate report columns.
  if ($report_id) {
    echo "  <td>" . text($date_report) . "</td>\n";
    echo "  <td title='" . xla('Check mark indicates reviewed') . "'>";
    echo myCellText(getListItem('proc_rep_status', $report_status));
    if ($review_status == 'reviewed') {
      echo " &#x2713;"; // unicode check mark character
    }
    echo "</td>\n";
  }
  else {
    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
  }

  echo " </tr>\n";

  $lastptid = $patient_id;
  $lastpoid = $order_id;
  $lastpcid = $order_seq;
  ++$lino;
}
?>

</table>

<?php if ($num_checkboxes) { ?>
<center><p>
<input type='submit' name='form_xmit' value='<?php echo xla('Transmit Selected Orders'); ?>' />
</p></center>
<?php } ?>

<script language='JavaScript'>

// Initialize calendar widgets for "from" and "to" dates.
Calendar.setup({inputField:'form_from_date', ifFormat:'%Y-%m-%d',
 button:'img_from_date'});
Calendar.setup({inputField:'form_to_date', ifFormat:'%Y-%m-%d',
 button:'img_to_date'});

</script>

</form>
</body>
</html>
