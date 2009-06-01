<?php
// Copyright (C) 2006-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is a report of receipts by payer or payment method.
//
// The payer option means an insurance company name or "Patient".
//
// The payment method option is most useful for sites using
// pos_checkout.php (e.g. weight loss clinics) because this plugs
// a payment method like Cash, Check, VISA, etc. into the "source"
// column of the SQL-Ledger acc_trans table or ar_session table.

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/sql-ledger.inc");
require_once("../../library/acl.inc");

// This controls whether we show pt name, policy number and DOS.
$showing_ppd = true;

$insarray = array();

function bucks($amount) {
  if ($amount)
    printf("%.2f", $amount);
}

function thisLineItem($patient_id, $encounter_id, $memo, $transdate,
  $rowmethod, $rowpayamount, $rowadjamount, $payer_type=0)
{
  global $form_report_by, $insarray, $grandpaytotal, $grandadjtotal;

  if ($form_report_by != '1') { // reporting by method or check number
    showLineItem($patient_id, $encounter_id, $memo, $transdate,
      $rowmethod, $rowpayamount, $rowadjamount, $payer_type);
    return;
  }

  // Reporting by payer.
  //
  if ($_POST['form_details']) { // details are wanted
    // Save everything for later sorting.
    $insarray[] = array($patient_id, $encounter_id, $memo, $transdate,
      $rowmethod, $rowpayamount, $rowadjamount, $payer_type);
  }
  else { // details not wanted
    if (empty($insarray[$rowmethod])) $insarray[$rowmethod] = array(0, 0);
    $insarray[$rowmethod][0] += $rowpayamount;
    $insarray[$rowmethod][1] += $rowadjamount;
    $grandpaytotal  += $rowpayamount;
    $grandadjtotal  += $rowadjamount;
  }
}

function showLineItem($patient_id, $encounter_id, $memo, $transdate,
  $rowmethod, $rowpayamount, $rowadjamount, $payer_type=0)
{
  global $paymethod, $paymethodleft, $methodpaytotal, $methodadjtotal,
    $grandpaytotal, $grandadjtotal, $showing_ppd;

  if (! $rowmethod) $rowmethod = 'Unknown';

  $invnumber = "$patient_id.$encounter_id";

  if ($paymethod != $rowmethod) {
    if ($paymethod) {
      // Print method total.
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="<?php echo $showing_ppd ? 7 : 4; ?>">
   <?php echo xl('Total for ') . $paymethod ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($methodadjtotal) ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($methodpaytotal) ?>
  </td>
 </tr>
<?php
    }
    $methodpaytotal = 0;
    $methodadjtotal  = 0;
    $paymethod = $rowmethod;
    $paymethodleft = $paymethod;
  }

  if ($_POST['form_details']) {
?>

 <tr>
  <td class="detail">
   <?php echo $paymethodleft; $paymethodleft = "&nbsp;" ?>
  </td>
  <td class="dehead">
   <?php echo $transdate ?>
  </td>
  <td class="detail">
   <?php echo $invnumber ?>
  </td>

<?php
    if ($showing_ppd) {
      $pferow = sqlQuery("SELECT p.fname, p.mname, p.lname, fe.date " .
        "FROM patient_data AS p, form_encounter AS fe WHERE " .
        "p.pid = '$patient_id' AND fe.pid = p.pid AND " .
        "fe.encounter = '$encounter_id' LIMIT 1");
      $dos = substr($pferow['date'], 0, 10);

      echo "  <td class='dehead'>\n";
      echo "   " . $pferow['lname'] . ", " . $pferow['fname'] . " " . $pferow['mname'];      
      echo "  </td>\n";

      echo "  <td class='dehead'>\n";
      if ($payer_type) {
        $ptarr = array(1 => 'primary', 2 => 'secondary', 3 => 'tertiary');
        $insrow = getInsuranceDataByDate($patient_id, $dos,
          $ptarr[$payer_type], "policy_number");
        echo "   " . $insrow['policy_number'];
      }
      echo "  </td>\n";

      echo "  <td class='dehead'>\n";
      echo "   $dos\n";
      echo "  </td>\n";
    }
?>

  <td class="dehead">
   <?php echo $memo ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($rowadjamount) ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($rowpayamount) ?>
  </td>
 </tr>
<?php
  }
  $methodpaytotal += $rowpayamount;
  $grandpaytotal  += $rowpayamount;
  $methodadjtotal += $rowadjamount;
  $grandadjtotal  += $rowadjamount;
}

// This is called by usort() when reporting by payer with details.
// Sorts by payer/date/patient/encounter.
function payerCmp($a, $b) {
  foreach (array(4,3,0,1) as $i) {
    if ($a[$i] < $b[$i]) return -1;
    if ($a[$i] > $b[$i]) return  1;
  }
  return 0;
}

if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

$INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;

if (!$INTEGRATED_AR) SLConnect();

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_use_edate = $_POST['form_use_edate'];
$form_facility  = $_POST['form_facility'];
$form_report_by = $_POST['form_report_by'];
$form_cptcode   = trim($_POST['form_cptcode']);
?>
<html>
<head>
<?php if (function_exists('html_header_show')) html_header_show(); ?>
<title><?xl('Receipts Summary','e')?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Receipts Summary','e') ?></h2>

<form method='post' action='receipts_by_method_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   Report by
<?php
echo "   <select name='form_report_by'>\n";
foreach (array(1 => 'Payer', 2 => 'Payment Method', 3 => 'Check Number') as $key => $value) {
  echo "    <option value='$key'";
  if ($key == $form_report_by) echo ' selected';
  echo ">" . xl($value) . "</option>\n";
}
echo "   </select>&nbsp;\n";

// Build a drop-down list of facilities.
//
$query = "SELECT id, name FROM facility ORDER BY name";
$fres = sqlStatement($query);
echo "   <select name='form_facility'>\n";
echo "    <option value=''>-- " . xl('All Facilities') . " --\n";
while ($frow = sqlFetchArray($fres)) {
  $facid = $frow['id'];
  echo "    <option value='$facid'";
  if ($facid == $form_facility) echo " selected";
  echo ">" . $frow['name'] . "\n";
}
echo "   </select>\n";
?>
   <?php if (!$GLOBALS['simplified_demographics']) echo '&nbsp;' . xl('CPT') . ':'; ?>
   <input type='text' name='form_cptcode' size='5' value='<?php echo $form_cptcode; ?>'
    title='<?php xl('Optional procedure code','e'); ?>'
    <?php if ($GLOBALS['simplified_demographics']) echo "style='display:none'"; ?> />
   &nbsp;
   <input type='checkbox' name='form_details' value='1'<?php if ($_POST['form_details']) echo " checked"; ?> /><?xl('Details','e')?>
  </td>
 </tr>

 <tr>
  <td>
   <select name='form_use_edate'>
    <option value='0'><?php xl('Payment Date','e'); ?></option>
    <option value='1'<?php if ($form_use_edate) echo ' selected' ?>><?php xl('Invoice Date','e'); ?></option>
   </select>
   &nbsp;<?xl('From:','e')?>
   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;<?php xl('To:','e'); ?>
   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?xl('Refresh','e')?>">
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   <?xl('Method','e')?>
  </td>
  <td class="dehead">
   <?xl('Date','e')?>
  </td>
  <td class="dehead">
   <?xl('Invoice','e')?>
  </td>
<?php if ($showing_ppd) { ?>
  <td class="dehead">
   <?xl('Patient','e')?>
  </td>
  <td class="dehead">
   <?xl('Policy','e')?>
  </td>
  <td class="dehead">
   <?xl('DOS','e')?>
  </td>
<?php } ?>
  <td class="dehead">
   <?xl('Procedure','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Adjustments','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Payments','e')?>
  </td>
 </tr>
<?php

if (!$INTEGRATED_AR) {
  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);
  $chart_id_income = SLQueryValue("select id from chart where accno = '$sl_income_acc'");
  if ($sl_err) die($sl_err);
}

if ($_POST['form_refresh']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;

  $paymethod   = "";
  $paymethodleft = "";
  $methodpaytotal = 0;
  $grandpaytotal  = 0;
  $methodadjtotal  = 0;
  $grandadjtotal  = 0;

  if ($INTEGRATED_AR) {

    // Get co-pays using the encounter date as the pay date.  These will
    // always be considered patient payments.  Ignored if selecting by
    // billing code.
    //
    if (!$form_cptcode) {
      $query = "SELECT b.fee, b.pid, b.encounter, b.code_type, " .
        "fe.date, fe.facility_id " .
        "FROM billing AS b " .
        "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
        "WHERE b.code_type = 'COPAY' AND b.activity = 1 AND b.fee != 0 AND " .
        "fe.date >= '$from_date 00:00:00' AND fe.date <= '$to_date 23:59:59'";
      // If a facility was specified.
      if ($form_facility) $query .= " AND fe.facility_id = '$form_facility'";
      $query .= " ORDER BY fe.date, b.pid, b.encounter, fe.id";
      //
      $res = sqlStatement($query);
      while ($row = sqlFetchArray($res)) {
        $rowmethod = $form_report_by == 1 ? 'Patient' : 'Co-Pay';
        thisLineItem($row['pid'], $row['encounter'], $row['code_text'],
          substr($row['date'], 0, 10), $rowmethod, 0 - $row['fee'], 0);
      }
    } // end if not form_cptcode

    // Get all other payments and adjustments and their dates, corresponding
    // payers and check reference data, and the encounter dates separately.
    //
    $query = "SELECT a.pid, a.encounter, a.post_time, a.pay_amount, " .
      "a.adj_amount, a.memo, a.session_id, a.code, a.payer_type, fe.id, fe.date, " .
      "s.deposit_date, s.payer_id, s.reference, i.name " .
      "FROM ar_activity AS a " .
      "JOIN form_encounter AS fe ON fe.pid = a.pid AND fe.encounter = a.encounter " .
      "JOIN forms AS f ON f.pid = a.pid AND f.encounter = a.encounter AND f.formdir = 'newpatient' " .
      "LEFT JOIN ar_session AS s ON s.session_id = a.session_id " .
      "LEFT JOIN insurance_companies AS i ON i.id = s.payer_id " .
      "WHERE ( a.pay_amount != 0 OR a.adj_amount != 0 )";
    //
    if ($form_use_edate) {
      $query .= " AND fe.date >= '$from_date 00:00:00' AND fe.date <= '$to_date 23:59:59'";
    } else {
      $query .= " AND ( ( s.deposit_date IS NOT NULL AND " .
        "s.deposit_date >= '$from_date' AND s.deposit_date <= '$to_date' ) OR " .
        "( s.deposit_date IS NULL AND a.post_time >= '$from_date 00:00:00' AND " .
        "a.post_time <= '$to_date 23:59:59' ) )";
    }
    // If a procedure code was specified.
    if ($form_cptcode) $query .= " AND a.code LIKE '$form_cptcode%'";
    // If a facility was specified.
    if ($form_facility) $query .= " AND fe.facility_id = '$form_facility'";
    //
    if ($form_use_edate) {
      $query .= " ORDER BY s.reference, fe.date, a.pid, a.encounter, fe.id";
    } else {
      $query .= " ORDER BY s.reference, s.deposit_date, a.post_time, a.pid, a.encounter, fe.id";
    }
    //
    $res = sqlStatement($query);
    while ($row = sqlFetchArray($res)) {
      if ($form_use_edate) {
        $thedate = substr($row['date'], 0, 10);
      } else if (!empty($row['deposit_date'])) {
        $thedate = $row['deposit_date'];
      } else {
        $thedate = substr($row['post_time'], 0, 10);
      }
      // Compute reporting key: insurance company name or payment method.
      if ($form_report_by == '1') {
        if (empty($row['payer_id'])) {
          $rowmethod = '';
        } else {
          if (empty($row['name'])) $rowmethod = xl('Unnamed insurance company');
          else $rowmethod = $row['name'];
        }
      }
      else {
        if (empty($row['session_id'])) {
          $rowmethod = trim($row['memo']);
        } else {
          $rowmethod = trim($row['reference']);
        }
        if ($form_report_by != '3') {
          // Extract only the first word as the payment method because any
          // following text will be some petty detail like a check number.
          $rowmethod = substr($rowmethod, 0, strcspn($rowmethod, ' /'));
        }
      }
      //
      thisLineItem($row['pid'], $row['encounter'], $row['code'], $thedate,
        $rowmethod, $row['pay_amount'], $row['adj_amount'], $row['payer_type']);
    }
  } // end $INTEGRATED_AR
  else {
    $query = "SELECT acc_trans.amount, acc_trans.transdate, acc_trans.memo, " .
      "replace(acc_trans.source, 'InvAdj ', '') AS source, " .
      "acc_trans.chart_id, ar.invnumber, ar.employee_id, ar.notes " .
      "FROM acc_trans, ar WHERE " .
      "( acc_trans.chart_id = $chart_id_cash OR " .
      "( acc_trans.chart_id = $chart_id_income AND " .
      "acc_trans.source LIKE 'InvAdj %' ) ) AND " .
      "ar.id = acc_trans.trans_id AND ";
    if ($form_cptcode) {
      $query .= "acc_trans.memo ILIKE '$form_cptcode%' AND ";
    }
    if ($form_use_edate) {
      $query .= "ar.transdate >= '$from_date' AND " .
      "ar.transdate <= '$to_date'";
    } else {
      $query .= "acc_trans.transdate >= '$from_date' AND " .
      "acc_trans.transdate <= '$to_date'";
    }
    $query .= " ORDER BY source, acc_trans.transdate, ar.invnumber, acc_trans.memo";

    // echo "<!-- $query -->\n";

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);

    for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
      $row = SLGetRow($t_res, $irow);
      list($patient_id, $encounter_id) = explode(".", $row['invnumber']);

      // If a facility was specified then skip invoices whose encounters
      // do not indicate that facility.
      if ($form_facility) {
        $tmp = sqlQuery("SELECT count(*) AS count FROM form_encounter WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
          "facility_id = '$form_facility'");
        if (empty($tmp['count'])) continue;
      }

      $rowpayamount = 0 - $row['amount'];
      $rowadjamount = 0;
      if ($row['chart_id'] == $chart_id_income) {
        $rowadjamount = $rowpayamount;
        $rowpayamount = 0;
      }

      // Compute reporting key: insurance company name or payment method.
      $payer_type = 0; // will be 0=pt, 1=ins1, 2=ins2 or 3=ins3
      if ($form_report_by == '1') {
        $rowmethod = '';
        $rowsrc = strtolower($row['source']);
        $insgot = strtolower($row['notes']);
        foreach (array('ins1', 'ins2', 'ins3') as $value) {
          if (strpos($rowsrc, $value) !== false) {
            $i = strpos($insgot, $value);
            if ($i !== false) {
              $j = strpos($insgot, "\n", $i);
              if (!$j) $j = strlen($insgot);
              $payer_type = 0 + substr($value, 3);
              $rowmethod = trim(substr($row['notes'], $i + 5, $j - $i - 5));
              break;
            }
          }
        } // end foreach
      } // end reporting by payer
      else {
        $rowmethod = trim($row['source']);
        if ($form_report_by != '3') {
          // Extract only the first word as the payment method because any
          // following text will be some petty detail like a check number.
          $rowmethod = substr($rowmethod, 0, strcspn($rowmethod, ' /'));
        }
      } // end reporting by method

      thisLineItem($patient_id, $encounter_id, $row['memo'], $row['transdate'],
        $rowmethod, $rowpayamount, $rowadjamount, $payer_type);
    } // end for
  } // end not $INTEGRATED_AR

  // Not payer summary.
  if ($form_report_by != '1' || $_POST['form_details']) {

    if ($form_report_by == '1') { // by payer with details
      // Sort and dump saved info.
      usort($insarray, 'payerCmp');
      foreach ($insarray as $a) {
        if (empty($a[4])) $a[4] = xl('Patient');
        showLineItem($a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6], $a[7]);
      }
    } // end by payer with details

    // Print last method total.
?>
 <tr bgcolor="#ddddff">
  <td class="detail" colspan="<?php echo $showing_ppd ? 7 : 4; ?>">
   <?echo xl('Total for ') . $paymethod ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($methodadjtotal) ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($methodpaytotal) ?>
  </td>
 </tr>
<?php
  }

  // Payer summary: need to sort and then print it all.
  else {
    ksort($insarray);
    foreach ($insarray as $key => $value) {
      if (empty($key)) $key = xl('Patient');
?>
 <tr bgcolor="#ddddff">
  <td class="detail" colspan="<?php echo $showing_ppd ? 7 : 4; ?>">
   <?php echo $key; ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($value[1]); ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($value[0]); ?>
  </td>
 </tr>
<?php
    } // end foreach
  } // end payer summary
?>
 <tr bgcolor="#ffdddd">
  <td class="detail" colspan="<?php echo $showing_ppd ? 7 : 4; ?>">
   <?php xl('Grand Total','e') ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($grandadjtotal) ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($grandpaytotal) ?>
  </td>
 </tr>

<?php
} // end form refresh
if (!$INTEGRATED_AR) SLClose();
?>

</table>
</form>
</center>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
