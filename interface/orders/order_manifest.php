<?php
/**
* Script to display a summary of a given procedure order before it has been processed.
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
require_once("$srcdir/patient.inc");
require_once("$srcdir/classes/InsuranceCompany.class.php");

function getListItem($listid, $value) {
  $lrow = sqlQuery("SELECT title FROM list_options " .
    "WHERE list_id = ? AND option_id = ?",
    array($listid, $value));
  $tmp = xl_list_label($lrow['title']);
  if (empty($tmp)) $tmp = "($value)";
  return $tmp;
}

function myCellText($s) {
  if ($s === '') return '&nbsp;';
  return text($s);
}

function generate_order_summary($orderid) {

  // If requested, save checkbox selections as to which procedures are not sendable.
  if ($_POST['bn_save']) {
    sqlStatement("UPDATE procedure_order_code " .
      "SET do_not_send = 0 WHERE " .
      "procedure_order_id = ? AND " .
      "do_not_send != 0",
      array($orderid));
    if (!empty($_POST['form_omit'])) {
      foreach ($_POST['form_omit'] as $key) {
        sqlStatement("UPDATE procedure_order_code " .
          "SET do_not_send = 1 WHERE " .
          "procedure_order_id = ? AND " .
          "do_not_send = 0 AND " .
          "procedure_order_seq = ?",
          array($orderid, intval($key)));
      }
    }
  }

  $orow = sqlQuery("SELECT " .
    "po.procedure_order_id, po.patient_id, po.date_ordered, po.order_status, " .
    "po.date_collected, po.specimen_type, po.specimen_location, po.lab_id, po.clinical_hx, " .
    "pd.pubpid, pd.lname, pd.fname, pd.mname, pd.DOB, pd.sex, " .
    "fe.date, " .
    "pp.name AS labname, " .
    "u.lname AS ulname, u.fname AS ufname, u.mname AS umname " .
    "FROM procedure_order AS po " .
    "LEFT JOIN patient_data AS pd ON pd.pid = po.patient_id " .
    "LEFT JOIN procedure_providers AS pp ON pp.ppid = po.lab_id " .
    "LEFT JOIN users AS u ON u.id = po.provider_id " .
    "LEFT JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
    "WHERE po.procedure_order_id = ?",
    array($orderid));

  $lab_id = intval($orow['lab_id']);
  $patient_id = intval($orow['patient_id']);
  $encdate = substr($orow['date'], 0, 10);

  // Get insurance info.
  $ins_policy = '';
  $ins_group  = '';
  $ins_name   = '';
  $ins_addr   = '';
  $ins_city   = '';
  $ins_state  = '';
  $ins_zip    = '';
  $irow = getInsuranceDataByDate($patient_id, $encdate, 'primary',
    "insd.provider, insd.policy_number, insd.group_number");
  if (!empty($irow['provider'])) {
    $ins_policy = $irow['policy_number'];
    $ins_group  = $irow['group_number'];
    $insco = new InsuranceCompany($irow['provider']);
    if (!empty($insco)) {
      $ins_name  = $insco->get_name();
      $tmp       = $insco->get_address();
      $ins_addr  = $tmp->get_line1();
      $ins_city  = $tmp->get_city();
      $ins_state = $tmp->get_state();
      $ins_zip   = $tmp->get_zip();
    }
  }
?>

<style>

.ordsum tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
.ordsum tr.detail { font-size:10pt; }
.ordsum a, .ordsum a:visited, .ordsum a:hover { color:#0000cc; }

.ordsum table {
 border-style: solid;
 border-width: 1px 0px 0px 1px;
 border-color: black;
}

.ordsum td, .ordsum th {
 border-style: solid;
 border-width: 0px 1px 1px 0px;
 border-color: black;
}

/* specifically exclude from printing */
@media print {
 .unprintable {
  visibility: hidden;
  display: none;
 }
}

</style>

<form method='post' action='order_manifest.php?orderid=<?php echo $orderid; ?>'>

<div class='ordsum'>

<table width='100%' cellpadding='2' cellspacing='0'>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Patient Name'); ?></td>
  <td><?php echo myCellText($orow['lname'] . ', ' . $orow['fname'] . ' ' . $orow['mname']); ?></td>
  <td nowrap><?php echo xlt('Ordered By'); ?></td>
  <td><?php echo myCellText($orow['ulname'] . ', ' . $orow['ufname'] . ' ' . $orow['umname']); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td width='5%' nowrap><?php echo xlt('MRN (pid)'); ?></td>
  <td width='45%'><?php echo myCellText($patient_id); ?></td>
  <td width='5%' nowrap><?php echo xlt('Order ID'); ?></td>
  <td width='45%'><?php echo myCellText($orow['procedure_order_id']); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Encounter Date'); ?></td>
  <td><?php echo myCellText(oeFormatShortDate($encdate)); ?></td>
  <td nowrap><?php echo xlt('Order Date'); ?></td>
  <td><?php echo myCellText(oeFormatShortDate($orow['date_ordered'])); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Birth Date'); ?></td>
  <td><?php echo myCellText(oeFormatShortDate($orow['DOB'])); ?></td>
  <td nowrap><?php echo xlt('Lab'); ?></td>
  <td><?php echo myCellText($orow['labname']); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Sex'); ?></td>
  <td><?php echo myCellText(getListItem('sex', $orow['sex'])); ?></td>
  <td nowrap><?php echo xlt('Specimen Type'); ?></td>
  <td><?php echo myCellText($orow['specimen_type']); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Ins Name'); ?></td>
  <td><?php echo myCellText($ins_name); ?></td>
  <td nowrap><?php echo xlt('Collection Date'); ?></td>
  <td><?php echo myCellText(oeFormatShortDate($orow['date_collected'])); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Ins Address'); ?></td>
  <td><?php echo myCellText("$ins_addr, $ins_city, $ins_state $ins_zip"); ?></td>
  <td nowrap><?php echo xlt('Clinical History'); ?></td>
  <td><?php echo myCellText($orow['clinical_hx']); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Ins Policy'); ?></td>
  <td><?php echo myCellText($ins_policy); ?></td>
  <td nowrap><?php echo xlt('Order Status'); ?></td>
  <td><?php echo myCellText(getListItem('ord_status', $orow['order_status'])); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Ins Group'); ?></td>
  <td><?php echo myCellText($ins_group); ?></td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
 </tr>
</table>

&nbsp;<br />

<table width='100%' cellpadding='2' cellspacing='0'>

 <tr class='head'>
  <td><?php echo xlt('Omit'); ?></td>
  <td><?php echo xlt('Code'); ?></td>
  <td><?php echo xlt('Description'); ?></td>
  <td><?php echo xlt('Diagnoses'); ?></td>
  <td><?php echo xlt('Notes'); ?></td>
 </tr>

<?php 
  $query = "SELECT " .
    "procedure_order_seq, procedure_code, procedure_name, diagnoses, do_not_send " .
    "FROM procedure_order_code WHERE " .
    "procedure_order_id =  ? ";
  if (!empty($_POST['bn_show_sendable'])) {
    $query .= "AND do_not_send = 0 ";
  }
  $query .= "ORDER BY procedure_order_seq";
  $res = sqlStatement($query, array($orderid));

  $encount = 0;

  while ($row = sqlFetchArray($res)) {
    $order_seq      = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
    $procedure_code = empty($row['procedure_code'  ]) ? '' : $row['procedure_code'];
    $procedure_name = empty($row['procedure_name'  ]) ? '' : $row['procedure_name'];
    $diagnoses      = empty($row['diagnoses'       ]) ? '' : $row['diagnoses'];

    // Create a string of HTML representing the procedure answers.
    // This code cloned from gen_hl7_order.inc.php.
    // Should maybe refactor it into something like a ProcedureAnswer class.
    $qres = sqlStatement("SELECT " .
      "a.question_code, a.answer, q.fldtype, q.question_text " .
      "FROM procedure_answers AS a " .
      "LEFT JOIN procedure_questions AS q ON " .
      "q.lab_id = ? " .
      "AND q.procedure_code = ? AND " .
      "q.question_code = a.question_code " .
      "WHERE " .
      "a.procedure_order_id = ? AND " .
      "a.procedure_order_seq = ? " .
      "ORDER BY q.seq, a.answer_seq",
      array($lab_id, $procedure_code, $orderid, $order_seq));

    $notes='';
    while ($qrow = sqlFetchArray($qres)) {
      // Formatting of these answer values may be lab-specific and we'll figure
      // out how to deal with that as more labs are supported.
      $answer = trim($qrow['answer']);
      $fldtype = $qrow['fldtype'];
      if ($fldtype == 'G') {
        $weeks = intval($answer / 7);
        $days = $answer % 7;
        $answer = $weeks . 'wks ' . $days . 'days';
      }
      if ($notes) $notes .= '<br />';
      $notes .= text($qrow['question_text'] . ': ' . $answer);
    }
    if ($notes === '') $notes = '&nbsp;';

    ++$encount;
    $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
    echo " <tr class='detail' bgcolor='$bgcolor'>\n";
    echo "  <td><input type='checkbox' name='form_omit[$order_seq]' value='1'";
    if (!empty($row['do_not_send'])) echo " checked";
    echo " /></td>\n";
    echo "  <td>" . text("$procedure_code") . "</td>\n";
    echo "  <td>" . text("$procedure_name") . "</td>\n";
    echo "  <td>" . text("$diagnoses"     ) . "</td>\n";
    echo "  <td>$notes</td>\n";
    echo " </tr>\n";
  }
?>

</table>
</div>

<center>
<p class='unprintable'>
<input type='submit' name='bn_save' value='<?php echo xla('Save omission selections'); ?>' />
&nbsp;
<input type='submit' name='bn_show_all' value='<?php echo xla('Show all procedures'); ?>' />
&nbsp;
<input type='submit' name='bn_show_sendable' value='<?php echo xla('Show only procedures not omitted'); ?>' />
</p>
</center>

</form>

<?php
} // end function generate_order_summary

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) die(xl('Not authorized'));

$orderid = intval($_GET['orderid']);
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href='<?php echo $css_header; ?>' type='text/css'>
<title><?php echo xlt('Order Summary'); ?></title>
<style>
body {
 margin: 9pt;
 font-family: sans-serif; 
 font-size: 1em;
}
</style>
</head>
<body>
<?php
  generate_order_summary($orderid);
?>
</body>
</html>
