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
  if (empty($tmp)) $tmp = "($report_status)";
  return $tmp;
}

function myCellText($s) {
  if ($s === '') return '&nbsp;';
  return text($s);
}

function generate_order_summary($orderid) {
  $orow = sqlQuery("SELECT " .
    "po.procedure_order_id, po.patient_id, po.date_ordered, po.order_status, " .
    "po.date_collected, po.specimen_type, po.specimen_location, " .
    "pd.pubpid, pd.lname, pd.fname, pd.mname, pd.DOB, " .
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

</style>

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
  <td nowrap><?php echo xlt('Ins Name'); ?></td>
  <td><?php echo myCellText($ins_name); ?></td>
  <td nowrap><?php echo xlt('Specimen Type'); ?></td>
  <td><?php echo myCellText($orow['specimen_type']); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Ins Address'); ?></td>
  <td><?php echo myCellText("$ins_addr, $ins_city, $ins_state $ins_zip"); ?></td>
  <td nowrap><?php echo xlt('Collection Date'); ?></td>
  <td><?php echo myCellText(oeFormatShortDate($orow['date_collected'])); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Ins Policy'); ?></td>
  <td><?php echo myCellText($ins_policy); ?></td>
  <td nowrap><?php echo xlt('Specimen Location'); ?></td>
  <td><?php echo myCellText($orow['specimen_location']); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Ins Group'); ?></td>
  <td><?php echo myCellText($ins_group); ?></td>
  <td nowrap><?php echo xlt('Order Status'); ?></td>
  <td><?php echo myCellText($orow['order_status']); ?></td>
 </tr>
</table>

&nbsp;<br />

<table width='100%' cellpadding='2' cellspacing='0'>

 <tr class='head'>
  <td><?php echo xlt('Code'); ?></td>
  <td><?php echo xlt('Description'); ?></td>
  <td><?php echo xlt('Diagnoses'); ?></td>
 </tr>

<?php 
  $query = "SELECT " .
    "procedure_order_seq, procedure_code, procedure_name, diagnoses " .
    "FROM procedure_order_code WHERE " .
    "procedure_order_id =  ? ORDER BY procedure_order_seq";
  $res = sqlStatement($query, array($orderid));

  $encount = 0;

  while ($row = sqlFetchArray($res)) {
    $order_seq      = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
    $procedure_code = empty($row['procedure_code'  ]) ? '' : $row['procedure_code'];
    $procedure_name = empty($row['procedure_name'  ]) ? '' : $row['procedure_name'];
    $diagnoses      = empty($row['diagnoses'       ]) ? '' : $row['diagnoses'];
    ++$encount;
    $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
    echo " <tr class='detail' bgcolor='$bgcolor'>\n";
    echo "  <td>" . text("$procedure_code") . "</td>\n";
    echo "  <td>" . text("$procedure_name") . "</td>\n";
    echo "  <td>" . text("$diagnoses"     ) . "</td>\n";
    echo " </tr>\n";
  }
?>

</table>
</div>

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
