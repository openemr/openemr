<?php
/**
* Script to display results for a given procedure order.
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

require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/classes/Document.class.php");

function getListItem($listid, $value) {
  $lrow = sqlQuery("SELECT title FROM list_options " .
    "WHERE list_id = ? AND option_id = ?",
    array($listid, $value));
  $tmp = xl_list_label($lrow['title']);
  if (empty($tmp)) $tmp = (($value === '') ? '' : "($value)");
  return $tmp;
}

function myCellText($s) {
  $s = trim($s);
  if ($s === '') return '&nbsp;';
  return text($s);
}

// Check if the given string already exists in the $aNotes array.
// If not, stores it as a new entry.
// Either way, returns the corresponding key which is a small integer.
function storeNote($s) {
  global $aNotes;
  $key = array_search($s, $aNotes);
  if ($key !== FALSE) return $key;
  $key = count($aNotes);
  $aNotes[$key] = $s;
  return $key;
}

function generate_order_report($orderid, $input_form=false) {
  global $aNotes;

  // Check authorization.
  $thisauth = acl_check('patients', 'med');
  if (!$thisauth) return xl('Not authorized');

  $orow = sqlQuery("SELECT " .
    "po.procedure_order_id, po.date_ordered, " .
    "po.order_status, po.specimen_type, po.patient_id, " .
    "pd.pubpid, pd.lname, pd.fname, pd.mname, " .
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

  $patient_id = $orow['patient_id'];
?>

<style>

.labres tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
.labres tr.detail { font-size:10pt; }
.labres a, .labres a:visited, .labres a:hover { color:#0000cc; }

.labres table {
 border-style: solid;
 border-width: 1px 0px 0px 1px;
 border-color: black;
}

.labres td, .labres th {
 border-style: solid;
 border-width: 0px 1px 1px 0px;
 border-color: black;
}

</style>

<?php if ($input_form) { ?>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<?php } // end if input form ?>

<?php if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) { ?>
<script language="JavaScript">
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
// Called to show patient notes related to this order in the "other" frame.
function showpnotes(orderid) {
 // Look for the top or bottom frame that contains this document, return if none.
 var w;
 for (w = window; w.name != 'RTop' && w.name != 'RBot'; w = w.parent) {
  if (w.parent == w) return false;
 }
 var othername = (w.name == 'RTop') ? 'RBot' : 'RTop';
 w.parent.left_nav.forceDual();
 w.parent.left_nav.setRadio(othername, 'pno');
 w.parent.left_nav.loadFrame('pno1', othername, 'patient_file/summary/pnotes_full.php?orderid=' + orderid);
 return false;
}
</script>
<?php } // end if not patient report ?>

<?php if ($input_form) { ?>
<form method='post' action='single_order_results.php?orderid=<?php echo $orderid; ?>'>
<?php } // end if input form ?>

<div class='labres'>

<table width='100%' cellpadding='2' cellspacing='0'>
 <tr bgcolor='#cccccc'>
  <td width='5%' nowrap><?php echo xlt('Patient ID'); ?></td>
  <td width='45%'><?php echo myCellText($orow['pubpid']); ?></td>
  <td width='5%' nowrap><?php echo xlt('Order ID'); ?></td>
  <td width='45%'>
<?php
  if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
    echo "   <a href='" . $GLOBALS['webroot'];
    echo "/interface/orders/order_manifest.php?orderid=";
    echo attr($orow['procedure_order_id']);
    echo "' target='_blank' onclick='top.restoreSession()'>";
  }
  echo myCellText($orow['procedure_order_id']);
  if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
    echo "</a>\n";
  }
?>
  </td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Patient Name'); ?></td>
  <td><?php echo myCellText($orow['lname'] . ', ' . $orow['fname'] . ' ' . $orow['mname']); ?></td>
  <td nowrap><?php echo xlt('Ordered By'); ?></td>
  <td><?php echo myCellText($orow['ulname'] . ', ' . $orow['ufname'] . ' ' . $orow['umname']); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Order Date'); ?></td>
  <td><?php echo myCellText(oeFormatShortDate($orow['date_ordered'])); ?></td>
  <td nowrap><?php echo xlt('Print Date'); ?></td>
  <td><?php echo oeFormatShortDate(date('Y-m-d')); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Order Status'); ?></td>
  <td><?php echo myCellText($orow['order_status']); ?></td>
  <td nowrap><?php echo xlt('Encounter Date'); ?></td>
  <td><?php echo myCellText(oeFormatShortDate(substr($orow['date'], 0, 10))); ?></td>
 </tr>
 <tr bgcolor='#cccccc'>
  <td nowrap><?php echo xlt('Lab'); ?></td>
  <td><?php echo myCellText($orow['labname']); ?></td>
  <td nowrap><?php echo xlt('Specimen Type'); ?></td>
  <td><?php echo myCellText($orow['specimen_type']); ?></td>
 </tr>
</table>

&nbsp;<br />

<table width='100%' cellpadding='2' cellspacing='0'>

 <tr class='head'>
  <td rowspan='2' valign='middle'><?php echo xlt('Ordered Procedure'); ?></td>
  <td colspan='4'><?php echo xlt('Report'); ?></td>
  <td colspan='7'><?php echo xlt('Results'); ?></td>
 </tr>

 <tr class='head'>
  <td><?php echo xlt('Reported'); ?></td>
  <td><?php echo xlt('Specimen'); ?></td>
  <td><?php echo xlt('Status'); ?></td>
  <td><?php echo xlt('Note'); ?></td>
  <td><?php echo xlt('Code'); ?></td>
  <td><?php echo xlt('Name'); ?></td>
  <td><?php echo xlt('Abn'); ?></td>
  <td><?php echo xlt('Value'); ?></td>
  <td><?php echo xlt('Range'); ?></td>
  <td><?php echo xlt('Units'); ?></td>
  <td><?php echo xlt('Note'); ?></td>
 </tr>

<?php 
  $query = "SELECT " .
    "po.date_ordered, pc.procedure_order_seq, pc.procedure_code, " .
    "pc.procedure_name, " .
    "pr.procedure_report_id, pr.date_report, pr.date_collected, pr.specimen_num, " .
    "pr.report_status, pr.review_status, pr.report_notes " .
    "FROM procedure_order AS po " .
    "JOIN procedure_order_code AS pc ON pc.procedure_order_id = po.procedure_order_id " .
    "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id AND " .
    "pr.procedure_order_seq = pc.procedure_order_seq " .
    "WHERE po.procedure_order_id = ? " .
    "ORDER BY pc.procedure_order_seq, pr.procedure_report_id";

  $res = sqlStatement($query, array($orderid));

  $lastpoid = -1;
  $lastpcid = -1;
  $lastprid = -1;
  $encount = 0;
  $lino = 0;
  $extra_html = '';
  $aNotes = array();
  $sign_list = '';

  while ($row = sqlFetchArray($res)) {
    $order_type_id  = empty($row['order_type_id'      ]) ? 0 : ($row['order_type_id' ] + 0);
    $order_seq      = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
    $report_id      = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);
    $procedure_code = empty($row['procedure_code'  ]) ? '' : $row['procedure_code'];
    $procedure_name = empty($row['procedure_name'  ]) ? '' : $row['procedure_name'];
    $date_report    = empty($row['date_report'     ]) ? '' : $row['date_report'];
    $date_collected = empty($row['date_collected'  ]) ? '' : substr($row['date_collected'], 0, 16);
    $specimen_num   = empty($row['specimen_num'    ]) ? '' : $row['specimen_num'];
    $report_status  = empty($row['report_status'   ]) ? '' : $row['report_status']; 
    $review_status  = empty($row['review_status'   ]) ? 'received' : $row['review_status'];

    if ($review_status != 'reviewed' && $report_id) {
      if ($sign_list) $sign_list .= ',';
      $sign_list .= $report_id;
    }

    $report_noteid ='';
    if (!empty($row['report_notes'])) {
      $report_noteid = 1 + storeNote($row['report_notes']);
    }

    $query = "SELECT " .
      "ps.result_code, ps.result_text, ps.abnormal, ps.result, ps.range, " .
      "ps.result_status, ps.facility, ps.units, ps.comments, ps.document_id " .
      "FROM procedure_result AS ps " .
      "WHERE ps.procedure_report_id = ? " .
      "ORDER BY ps.result_code, ps.procedure_result_id";

    $rres = sqlStatement($query, array($report_id));
    $rrows = array();
    while ($rrow = sqlFetchArray($rres)) {
      $rrows[] = $rrow;
    }
    if (empty($rrows)) {
      $rrows[0] = array('result_code' => '');
    }

    foreach ($rrows as $rrow) {
      $result_code      = empty($rrow['result_code'     ]) ? '' : $rrow['result_code'];
      $result_text      = empty($rrow['result_text'     ]) ? '' : $rrow['result_text'];
      $result_abnormal  = empty($rrow['abnormal'        ]) ? '' : $rrow['abnormal'];
      $result_result    = empty($rrow['result'          ]) ? '' : $rrow['result'];
      $result_units     = empty($rrow['units'           ]) ? '' : $rrow['units'];
      $result_facility  = empty($rrow['facility'        ]) ? '' : $rrow['facility'];
      $result_comments  = empty($rrow['comments'        ]) ? '' : $rrow['comments'];
      $result_range     = empty($rrow['range'           ]) ? '' : $rrow['range'];
      $result_status    = empty($rrow['result_status'   ]) ? '' : $rrow['result_status'];
      $result_document_id = empty($rrow['document_id'   ]) ? '' : $rrow['document_id'];

      $result_comments = trim($result_comments);
      $result_noteid = '';
      if (!empty($result_comments)) {
        $result_noteid = 1 + storeNote($result_comments);
      }

      if ($lastpoid != $order_id || $lastpcid != $order_seq) {
        ++$encount;
      }
      $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

      echo " <tr class='detail' bgcolor='$bgcolor'>\n";

      if ($lastpcid != $order_seq) {
        $lastprid = -1; // force report fields on first line of each procedure
        echo "  <td>" . text("$procedure_code: $procedure_name") . "</td>\n";
      }
      else {
        echo "  <td style='background-color:transparent'>&nbsp;</td>";
      }

      // If this starts a new report or a new order, generate the report fields.
      if ($report_id != $lastprid) {
        echo "  <td>";
        echo myCellText(oeFormatShortDate($date_report));
        echo "</td>\n";

        echo "  <td>";
        echo myCellText($specimen_num);
        echo "</td>\n";

        echo "  <td title='" . xla('Check mark indicates reviewed') . "'>";
        echo myCellText(getListItem('proc_rep_status', $report_status));
        if ($row['review_status'] == 'reviewed') {
          echo " &#x2713;"; // unicode check mark character
        }
        echo "</td>\n";

        echo "  <td align='center'>";
        echo myCellText($report_noteid);
        echo "</td>\n";
      }
      else {
        echo "  <td colspan='4' style='background-color:transparent'>&nbsp;</td>\n";
      }

      if ($result_code !== '' || $result_document_id) {
        echo "  <td>";
        echo myCellText($result_code);
        echo "</td>\n";
        echo "  <td>";
        echo myCellText($result_text);
        echo "</td>\n";
        echo "  <td>";
        echo myCellText(getListItem('proc_res_abnormal', $result_abnormal));
        echo "</td>\n";
        //
        if ($result_document_id) {
          $d = new Document($result_document_id);
          echo "  <td colspan='3'>";
          if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
            echo "<a href='" . $GLOBALS['webroot'] . "/controller.php?document";
            echo "&retrieve&patient_id=$patient_id&document_id=$result_document_id' ";
            echo "onclick='top.restoreSession()'>";
          }
          echo $d->get_url_file();
          if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
            echo "</a>";
          }
          echo "</td>\n";
        }
        else {
          echo "  <td>";
          echo myCellText($result_result);
          echo "</td>\n";
          echo "  <td>";
          echo myCellText($result_range);
          echo "</td>\n";
          echo "  <td>";
          echo myCellText($result_units);
          echo "</td>\n";
        }
        echo "  <td align='center'>";
        echo myCellText($result_noteid);
        echo "</td>\n";
      }
      else {
        echo "  <td colspan='7' style='background-color:transparent'>&nbsp;</td>\n";
      }

      echo " </tr>\n";

      $lastpoid = $order_id;
      $lastpcid = $order_seq;
      $lastprid = $report_id;
      ++$lino;
    }
  }
?>

</table>

&nbsp;<br />
<table width='100%' style='border-width:0px;'>
 <tr>
  <td style='border-width:0px;'>
<?php
  if (!empty($aNotes)) {
    echo "<table cellpadding='3' cellspacing='0'>\n";
    echo " <tr bgcolor='#cccccc'>\n";
    echo "  <th align='center' colspan='2'>" . xlt('Notes') . "</th>\n";
    echo " </tr>\n";
    foreach ($aNotes as $key => $value) {
      echo " <tr>\n";
      echo "  <td valign='top'>" . ($key + 1) . "</td>\n";
      echo "  <td>" . nl2br(text($value)) . "</td>\n";
      echo " </tr>\n";
    }
    echo "</table>\n";
  }
?>
  </td>
  <td style='border-width:0px;' align='right' valign='top'>
<?php if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) { ?>
   <input type='button' value='<?php echo xla('Related Patient Notes'); ?>' 
    onclick='showpnotes(<?php echo $orderid; ?>)' />
<?php } ?>
<?php if ($input_form && $sign_list) { ?>
   &nbsp;
   <input type='hidden' name='form_sign_list' value='<?php echo attr($sign_list); ?>' />
   <input type='submit' name='form_sign' value='<?php echo xla('Sign Results'); ?>'
    title='<?php echo xla('Mark these reports as reviewed'); ?>' />
<?php } ?>
  </td>
 </tr>
</table>

</div>

<?php if ($input_form) { ?>
</form>
<?php } // end if input form ?>

<?php
} // end function generate_order_report
?>
