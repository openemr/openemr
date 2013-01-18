<?php
/**
* List procedure orders and reports, and fetch new reports and their results.
*
* Copyright (C) 2013 Rod Roark <rod@sunsetsystems.com>
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
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("./receive_hl7_results.inc.php");

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
 window.open('single_order_results.php?orderid=' + orderid);
}

</script>

</head>

<body class="body_top">
<form method='post' action='list_reports.php' enctype='multipart/form-data'
 onsubmit='return validate(this)'>

<?php
$messages = array();
$errmsg = poll_hl7_results($messages);
foreach ($messages as $message) {
  echo text($message) . "<br />\n";
}
if ($errmsg) {
  echo "<font color='red'>" . text($errmsg) . "</font><br />\n";
}

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

$form_from_date = empty($_POST['form_from_date']) ? '' : trim($_POST['form_from_date']);
$form_to_date = empty($_POST['form_to_date']) ? '' : trim($_POST['form_to_date']);
// if (empty($form_to_date)) $form_to_date = $form_from_date;

$form_reviewed = empty($_POST['form_reviewed']) ? 3 : intval($_POST['form_reviewed']);

$form_patient = !empty($_POST['form_patient']);
?>

<table>
 <tr>
  <td class='text'>
   &nbsp;<?php echo xlt('From'); ?>:
   <input type='text' size='8' name='form_from_date' id='form_from_date'
    value='<?php echo attr($form_from_date); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />

   &nbsp;<?php echo xlt('To'); ?>:
   <input type='text' size='8' name='form_to_date' id='form_to_date'
    value='<?php echo attr($form_to_date); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>' />

   &nbsp;
   <select name='form_reviewed'>
<?php
foreach (array('1' => xl('All'), '2' => xl('Reviewed'), '3' => xl('Unreviewed'),
  '4' => xl('Unreceived')) as $key => $value) {
  echo "<option value='$key'";
  if ($key == $form_reviewed) echo " selected";
  echo ">" . text($value) . "</option>\n";
}
?>
   </select>

   &nbsp;
   <input type='checkbox' name='form_patient' value='1'
    <?php if ($form_patient) echo 'checked '; ?>/>Current Patient Only

   &nbsp;
   <span title='<?php echo xla('You may optionally upload HL7 results from a file'); ?>'>
   <?php echo xlt('Upload'); ?>:
   <input type='hidden' name='MAX_FILE_SIZE' value='4000000' />
   <input type='file' name='userfile' size='8' />
   </span>

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
  "po.procedure_order_id, po.date_ordered, pc.procedure_order_seq, pc.procedure_code, " .
  "pc.procedure_name, " .
  "pr.procedure_report_id, pr.date_report, pr.report_status, pr.review_status";

$joins =
  "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id AND " .
  "pr.procedure_order_seq = pc.procedure_order_seq";

$orderby =
  "po.date_ordered, po.procedure_order_id, " .
  "pc.procedure_order_seq, pr.procedure_report_id";

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

if ($form_reviewed == 2) {
  $where .= " AND pr.procedure_report_id IS NOT NULL AND pr.review_status = 'reviewed'";
}
else if ($form_reviewed == 3) {
  $where .= " AND pr.procedure_report_id IS NOT NULL AND pr.review_status != 'reviewed'";
}
else if ($form_reviewed == 4) {
  $where .= " AND pr.procedure_report_id IS NULL";
}

$query = "SELECT po.patient_id, " .
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

while ($row = sqlFetchArray($res)) {
  $patient_id       = empty($row['patient_id'         ]) ? 0 : ($row['patient_id'         ] + 0);
  $order_id         = empty($row['procedure_order_id' ]) ? 0 : ($row['procedure_order_id' ] + 0);
  $order_seq        = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
  $date_ordered     = empty($row['date_ordered']) ? '' : $row['date_ordered'];
  $procedure_code   = empty($row['procedure_code']) ? '' : $row['procedure_code'];
  $procedure_name   = empty($row['procedure_name']) ? '' : $row['procedure_name'];
  $report_id        = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);
  $date_report      = empty($row['date_report']) ? '' : $row['date_report'];
  $report_status    = empty($row['report_status']) ? '' : $row['report_status']; 
  $review_status    = empty($row['review_status']) ? '' : $row['review_status'];

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
    echo "  <td>" . text($ptname) . "</td>\n";
    echo "  <td>" . text($row['pubpid']) . "</td>\n";
  }
  else {
    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
  }

  // Generate order columns.
  if ($lastpoid != $order_id) {
    $lastpcid = -1;
    echo "  <td><a href='javascript:openResults($order_id)'>";    
    echo text($date_ordered);
    echo "</a></td>\n";
    echo "  <td>" . text($order_id) . "</td>\n";
  }
  else {
    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
  }

  // Generate procedure columns.
  if ($order_seq && $lastpcid != $order_seq) {
    echo "  <td>" . text($procedure_code) . "</td>\n";
    echo "  <td>" . text($procedure_name) . "</td>\n";
  }
  else {
    echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
  }

  // Generate report columns.
  if ($report_id) {
    echo "  <td>" . text($date_report) . "</td>\n";

    // echo "  <td>" . text($report_status) . "</td>\n";
    // echo "  <td>" . text($review_status) . "</td>\n";

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
