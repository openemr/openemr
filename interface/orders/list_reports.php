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

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("./receive_hl7_results.inc.php");

function getListItem($listid, $value) {
  $lrow = sqlQuery("SELECT title FROM list_options " .
    "WHERE list_id = ? AND option_id = ?",
    array($listid, $value));
  $tmp = xl_list_label($lrow['title']);
  if (empty($tmp)) $tmp = "($report_status)";
  return $tmp;
}

function myCellText($s) {
  if ($s === '') return '&nbsp;';
  return text($s);
}

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) die(xl('Not authorized'));
?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<title><?php  xl('Procedure Orders and Reports','e'); ?></title>

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
<form method='post' action='list_reports.php'
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

$form_from_date = formData('form_from_date','P',true);
$form_to_date   = formData('form_to_date','P',true);
if (empty($form_to_date)) $form_to_date = $form_from_date;

$form_reviewed = 0 + formData('form_reviewed');
if (!$form_reviewed) $form_reviewed = 3;

$form_patient = !empty($_POST['form_patient']);
?>

<table>
 <tr>
  <td class='text'>
   &nbsp;<?php xl('From','e'); ?>:
   <input type='text' size='10' name='form_from_date' id='form_from_date'
    value='<?php echo $form_from_date ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />

   &nbsp;<?php xl('To','e'); ?>:
   <input type='text' size='10' name='form_to_date' id='form_to_date'
    value='<?php echo $form_to_date ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />

   &nbsp;
   <select name='form_reviewed'>
<?php
foreach (array('1' => xlt('All'), '2' => xlt('Reviewed'), '3' => xlt('Unreviewed'),
  '4' => xlt('Unreceived')) as $key => $value) {
  echo "<option value='$key'";
  if ($key == $form_reviewed) echo " selected";
  echo ">$value</option>\n";
}
?>
   </select>

   &nbsp;
   <input type='checkbox' name='form_patient' value='1'
    <?php if ($form_patient) echo 'checked '; ?>/>Current Patient Only

   &nbsp;
   <input type='submit' name='form_refresh' value=<?php xl('Refresh','e'); ?>>
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
if (!empty($form_from_date)) {
  $where .= " AND po.date_ordered >= '$form_from_date'";
}
if (!empty($form_to_date)) {
  $where .= " AND po.date_ordered <= '$form_to_date'";
}

if ($form_patient) {
  $where .= " AND po.patient_id = '$pid'";
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

$res = sqlStatement($query);

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
