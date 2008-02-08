<?php
// Copyright (C) 2006-2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../globals.php");
include_once("../../library/patient.inc");
include_once("../../library/sql-ledger.inc");
include_once("../../library/invoice_summary.inc.php");
// include_once("../../custom/statement.inc.php");
// include_once("../../library/sl_eob.inc.php");

$alertmsg = '';
$bgcolor = "#aaaaaa";
$export_patient_count = 0;
$export_dollars = 0;

$today = date("Y-m-d");

$form_date      = fixDate($_POST['form_date'], "");
$form_to_date   = fixDate($_POST['form_to_date'], "");
$is_due_ins     = $_POST['form_category'] == xl('Due Ins');
$is_due_pt      = $_POST['form_category'] == xl('Due Pt');
$is_all         = $_POST['form_category'] == xl('All');

if ($_POST['form_search'] || $_POST['form_export'] || $_POST['form_csvexport']) {
  $form_cb_ssn      = $_POST['form_cb_ssn']      ? true : false;
  $form_cb_dob      = $_POST['form_cb_dob']      ? true : false;
  $form_cb_policy   = $_POST['form_cb_policy']   ? true : false;
  $form_cb_phone    = $_POST['form_cb_phone']    ? true : false;
  $form_cb_city     = $_POST['form_cb_city']     ? true : false;
  $form_cb_ins1     = $_POST['form_cb_ins1']     ? true : false;
  $form_cb_referrer = $_POST['form_cb_referrer'] ? true : false;
  $form_cb_idays    = $_POST['form_cb_idays']    ? true : false;
  $form_cb_err      = $_POST['form_cb_err']      ? true : false;
} else {
  $form_cb_ssn      = true;
  $form_cb_dob      = false;
  $form_cb_policy   = false;
  $form_cb_phone    = true;
  $form_cb_city     = false;
  $form_cb_ins1     = false;
  $form_cb_referrer = false;
  $form_cb_idays    = false;
  $form_cb_err      = false;
}
$form_age_cols = (int) $_POST['form_age_cols'];
$form_age_inc  = (int) $_POST['form_age_inc'];
if ($form_age_cols > 0 && $form_age_cols < 50) {
  if ($form_age_inc <= 0) $form_age_inc = 30;
} else {
  $form_age_cols = 0;
  $form_age_inc  = 0;
}

$initial_colspan = 1;
if ($is_due_ins      ) ++$initial_colspan;
if ($form_cb_ssn     ) ++$initial_colspan;
if ($form_cb_dob     ) ++$initial_colspan;
if ($form_cb_policy  ) ++$initial_colspan;
if ($form_cb_phone   ) ++$initial_colspan;
if ($form_cb_city    ) ++$initial_colspan;
if ($form_cb_ins1    ) ++$initial_colspan;
if ($form_cb_referrer) ++$initial_colspan;

$grand_total_charges     = 0;
$grand_total_adjustments = 0;
$grand_total_paid        = 0;
$grand_total_agedbal = array();
for ($c = 0; $c < $form_age_cols; ++$c) $grand_total_agedbal[$c] = 0;

SLConnect();

function bucks($amount) {
  if ($amount)
   printf("%.2f", $amount);
}

function endPatient($ptrow) {
  global $export_patient_count, $export_dollars, $bgcolor;
  global $grand_total_charges, $grand_total_adjustments, $grand_total_paid;
  global $grand_total_agedbal, $is_due_ins, $form_age_cols;
  global $initial_colspan, $form_cb_idays, $form_cb_err;

  if (!$ptrow['pid']) return;

  $pt_balance = $ptrow['amount'] - $ptrow['paid'];

  if ($_POST['form_export']) {
    // This is a fixed-length format used by Transworld Systems.  Your
    // needs will surely be different, so consider this just an example.
    //
    echo "1896H"; // client number goes here
    echo "000";   // filler
    echo sprintf("%-30s", substr($ptrow['ptname'], 0, 30));
    echo sprintf("%-30s", " ");
    echo sprintf("%-30s", substr($ptrow['address1'], 0, 30));
    echo sprintf("%-15s", substr($ptrow['city'], 0, 15));
    echo sprintf("%-2s", substr($ptrow['state'], 0, 2));
    echo sprintf("%-5s", $ptrow['zipcode'] ? substr($ptrow['zipcode'], 0, 5) : '00000');
    echo "1";                      // service code
    echo sprintf("%010.0f", $ptrow['pid']); // transmittal number = patient id
    echo " ";                      // filler
    echo sprintf("%-15s", substr($ptrow['ss'], 0, 15));
    echo substr($ptrow['dos'], 5, 2) . substr($ptrow['dos'], 8, 2) . substr($ptrow['dos'], 2, 2);
    echo sprintf("%08.0f", $pt_balance * 100);
    echo sprintf("%-9s\n", " ");

    if (!$_POST['form_without']) {
      sqlStatement("UPDATE patient_data SET " .
        "genericname2 = 'Billing', " .
        "genericval2 = CONCAT('IN COLLECTIONS " . date("Y-m-d") . "', genericval2) " .
        "WHERE pid = '" . $ptrow['pid'] . "'");
    }
    $export_patient_count += 1;
    $export_dollars += $pt_balance;
  }
  else if ($_POST['form_csvexport']) {
    $export_patient_count += 1;
    $export_dollars += $pt_balance;
  }
  else {
    if ($ptrow['count'] > 1) {
      echo " <tr bgcolor='$bgcolor'>\n";
      echo "  <td class='detail' colspan='$initial_colspan'>";
      echo "&nbsp;</td>\n";
      echo "  <td class='detotal' colspan='5'>&nbsp;Total Patient Balance:</td>\n";
      if ($form_age_cols) {
        for ($c = 0; $c < $form_age_cols; ++$c) {
          echo "  <td class='detotal' align='right'>&nbsp;" .
            sprintf("%.2f", $ptrow['agedbal'][$c]) . "&nbsp;</td>\n";
        }
      }
      else {
        echo "  <td class='detotal' align='right'>&nbsp;" .
          sprintf("%.2f", $pt_balance) . "&nbsp;</td>\n";
      }
      if ($form_cb_idays) echo "  <td class='detail'>&nbsp;</td>\n";
      echo "  <td class='detail' colspan='2'>&nbsp;</td>\n";
      if ($form_cb_err) echo "  <td class='detail'>&nbsp;</td>\n";
      echo " </tr>\n";
    }
  }
  $grand_total_charges     += $ptrow['charges'];
  $grand_total_adjustments += $ptrow['adjustments'];
  $grand_total_paid        += $ptrow['paid'];
  for ($c = 0; $c < $form_age_cols; ++$c) {
    $grand_total_agedbal[$c] += $ptrow['agedbal'][$c];
  }
}

// In the case of CSV export only, a download will be forced.
if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=collections_report.csv");
  header("Content-Description: File Transfer");
}
else {
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?xl('Collections Report','e')?></title>
<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
 .detotal   { color:#996600; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

<script language="JavaScript">

function checkAll(checked) {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  if (ename.indexOf('form_cb[') == 0)
   f.elements[i].checked = checked;
 }
}

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<form method='post' action='collections_report.php' enctype='multipart/form-data'>

<table border='0' cellpadding='5' cellspacing='0' width='98%'>

 <tr>
  <td height="1">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td align='center'>
   <input type='checkbox' name='form_cb_ssn'<?php if ($form_cb_ssn) echo ' checked'; ?>>
   <?php xl('SSN','e') ?>&nbsp;
   <input type='checkbox' name='form_cb_dob'<?php if ($form_cb_dob) echo ' checked'; ?>>
   <?php xl('DOB','e') ?>&nbsp;
   <input type='checkbox' name='form_cb_policy'<?php if ($form_cb_policy) echo ' checked'; ?>>
   <?php xl('Policy','e') ?>&nbsp;
   <input type='checkbox' name='form_cb_phone'<?php if ($form_cb_phone) echo ' checked'; ?>>
   <?php xl('Phone','e') ?>&nbsp;
   <input type='checkbox' name='form_cb_city'<?php if ($form_cb_city) echo ' checked'; ?>>
   <?php xl('City','e') ?>&nbsp;
   <input type='checkbox' name='form_cb_ins1'<?php if ($form_cb_ins1) echo ' checked'; ?>>
   <?php xl('Primary Ins','e') ?>&nbsp;
   <input type='checkbox' name='form_cb_referrer'<?php if ($form_cb_referrer) echo ' checked'; ?>>
   <?php xl('Referrer','e') ?>&nbsp;
   <input type='checkbox' name='form_cb_idays'<?php if ($form_cb_idays) echo ' checked'; ?>>
   <?php xl('Inactive Days','e') ?>&nbsp;
   <input type='checkbox' name='form_cb_err'<?php if ($form_cb_err) echo ' checked'; ?>>
   <?php xl('Errors','e') ?>
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td align='center'>
   <?php xl('Age Cols:','e') ?>
   <input type='text' name='form_age_cols' size='2' value='<?php echo $form_age_cols; ?>'>
   &nbsp;
   <?php xl('Age Increment:','e') ?>
   <input type='text' name='form_age_inc' size='3' value='<?php echo $form_age_inc; ?>'>
   &nbsp;
   <?xl('Svc Date:','e')?>
   <input type='text' name='form_date' size='10' value='<?php echo $_POST['form_date']; ?>'
    title='<?xl("Date of service mm/dd/yyyy","e")?>'>
   &nbsp;
   <?xl('To:','e')?>
   <input type='text' name='form_to_date' size='10' value='<?php echo $_POST['form_to_date']; ?>'
    title='<?xl("Ending DOS mm/dd/yyyy if you wish to enter a range","e")?>'>
   &nbsp;
   <select name='form_category'>
<?php
 foreach (array(xl('Open'), xl('Due Pt'), xl('Due Ins'), xl('Credits'), xl('All')) as $value) {
  echo "    <option value='$value'";
  if ($_POST['form_category'] == $value) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
   &nbsp;
   <input type='submit' name='form_search' value='<?xl("Search","e")?>'>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php

} // end not form_csvexport

  if ($_POST['form_search'] || $_POST['form_export'] || $_POST['form_csvexport']) {
    $where = "";

    if ($_POST['form_export'] || $_POST['form_csvexport']) {
      $where = "( 1 = 2";
      foreach ($_POST['form_cb'] as $key => $value) $where .= " OR ar.customer_id = $key";
      $where .= ' )';
    }

    if ($form_date) {
      if ($where) $where .= " AND ";
      $date1 = substr($form_date, 0, 4) . substr($form_date, 5, 2) .
        substr($form_date, 8, 2);
      if ($form_to_date) {
        $date2 = substr($form_to_date, 0, 4) . substr($form_to_date, 5, 2) .
          substr($form_to_date, 8, 2);
        $where .= "((CAST (substring(ar.invnumber from position('.' in ar.invnumber) + 1 for 8) AS integer) " .
          "BETWEEN '$date1' AND '$date2')";
        $tmp = "date >= '$form_date' AND date <= '$form_to_date'";
      }
      else {
        // This catches old converted invoices where we have no encounters:
        $where .= "(ar.invnumber LIKE '%.$date1'";
        $tmp = "date = '$form_date'";
      }
      // Pick out the encounters from MySQL with the desired DOS:
      $rez = sqlStatement("SELECT pid, encounter FROM form_encounter WHERE $tmp");
      while ($row = sqlFetchArray($rez)) {
        $where .= " OR ar.invnumber = '" . $row['pid'] . "." . $row['encounter'] . "'";
      }
      $where .= ")";
    }

    if (! $where) {
      $where = "1 = 1";
    }

    // TBD: Instead of the subselects in the following query, we will call
    // get_invoice_summary() in order to get data at the procedure level and
    // thus decide if insurance appears to be done with each invoice.

    $query = "SELECT ar.id, ar.invnumber, ar.duedate, ar.amount, ar.paid, " .
      "ar.intnotes, ar.notes, ar.shipvia, " .
      "customer.id AS custid, customer.name, customer.address1, " .
      "customer.city, customer.state, customer.zipcode, customer.phone " .
      // ", (SELECT SUM(invoice.fxsellprice) FROM invoice WHERE " .
      // "invoice.trans_id = ar.id AND invoice.fxsellprice > 0) AS charges, " .
      // "(SELECT SUM(invoice.fxsellprice) FROM invoice WHERE " .
      // "invoice.trans_id = ar.id AND invoice.fxsellprice < 0) AS adjustments " .
      "FROM ar JOIN customer ON customer.id = ar.customer_id " .
      "WHERE ( $where ) ";
    if ($_POST['form_search'] && ! $is_all) {
      $query .= "AND ar.amount != ar.paid ";
    }
    $query .= "ORDER BY ar.invnumber";

    // echo "<!-- $query -->\n"; // debugging

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);
    $num_invoices = SLRowCount($t_res);

    //////////////////////////////////////////////////////////////////

    $rows = array();
    for ($irow = 0; $irow < $num_invoices; ++$irow) {
      $row = SLGetRow($t_res, $irow);
      $pt_balance = sprintf("%.2f",$row['amount']) - sprintf("%.2f",$row['paid']);

      if ($_POST['form_category'] == 'Credits') {
        if ($pt_balance > 0) continue;
      }

      // $duncount was originally supposed to be the number of times that
      // the patient was sent a statement for this invoice.
      //
      $duncount = substr_count(strtolower($row['intnotes']), "statement sent");

      // But if we have not yet billed the patient, then compute $duncount as a
      // negative count of the number of insurance plans for which we have not
      // yet closed out insurance.  Here we also compute $insname as the name of
      // the insurance plan from which we are awaiting payment, and its sequence
      // number $insposition (1-3).
      //
      $insname = '';
      $insposition = 0;
      $inseobs = strtolower($row['shipvia']);
      $insgot = strtolower($row['notes']);
      if (! $duncount) {
        foreach (array('ins1', 'ins2', 'ins3') as $value) {
          $i = strpos($insgot, $value);
          if ($i !== false && strpos($inseobs, $value) === false) {
            --$duncount;
            if (!$insname && $is_due_ins) {
              $j = strpos($insgot, "\n", $i);
              if (!$j) $j = strlen($insgot);
              $insname = trim(substr($row['notes'], $i + 5, $j - $i - 5));
              $insposition = substr($value, 3); // 1, 2 or 3
            }
          }
        }
      }

      // Also get the primary insurance company name whenever there is one.
      $row['ins1'] = '';
      $i = strpos($insgot, 'ins1');
      if ($i !== false) {
        $j = strpos($insgot, "\n", $i);
        if (!$j) $j = strlen($insgot);
        $row['ins1'] = trim(substr($row['notes'], $i + 5, $j - $i - 5));
      }

      // An invoice is now due from the patient if money is owed and we are
      // not waiting for insurance to pay.  We no longer look at the due date
      // for this.
      //
      $isduept = ($duncount >= 0) ? " checked" : "";

      // Skip invoices not in the desired "Due..." category.
      //
      if ($is_due_ins && $duncount >= 0) continue;
      if ($is_due_pt  && $duncount <  0) continue;

      $row['duncount'] = $duncount;

      // Determine the date of service.  An 8-digit encounter number is
      // presumed to be a date of service imported during conversion.
      // Otherwise look it up in the form_encounter table.
      //
      $svcdate = "";
      list($pid, $encounter) = explode(".", $row['invnumber']);
      if (strlen($encounter) == 8) {
        $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
          "-" . substr($encounter, 6, 2);
      }
      else if ($encounter) {
        $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
          "encounter = $encounter");
        $svcdate = substr($tmp['date'], 0, 10);
      }

      $row['dos'] = $svcdate;

      // This computes the invoice's total original charges and adjustments,
      // date of last activity, and determines if insurance has responded to
      // all billing items.
      //
      $invlines = get_invoice_summary($row['id'], true);
      $row['charges'] = 0;
      $row['adjustments'] = 0;
      $ins_seems_done = true;
      $ladate = $svcdate;
      // echo "\n<!-- $ladate * -->\n"; // debugging
      foreach ($invlines as $key => $value) {
        $row['charges'] += $value['chg'] + $value['adj'];
        $row['adjustments'] += 0 - $value['adj'];
        foreach ($value['dtl'] as $dkey => $dvalue) {
          $dtldate = trim(substr($dkey, 0, 10));
          // echo "\n<!-- $dtldate -->\n"; // debugging
          if ($dtldate && $dtldate > $ladate) $ladate = $dtldate;
        }
        $lckey = strtolower($key);
        if ($lckey == 'co-pay' || $lckey == 'claim') continue;
        if (count($value['dtl']) <= 1) $ins_seems_done = false;
      }
      $row['billing_errmsg'] = '';
      if ($is_due_ins && strpos($inseobs, 'ins1') === false && $ins_seems_done)
        $row['billing_errmsg'] = 'Ins1 seems done';
      else if (strpos($inseobs, 'ins1') !== false && !$ins_seems_done)
        $row['billing_errmsg'] = 'Ins1 seems not done';

      // Compute number of days since last activity.
      $latime = mktime(0, 0, 0, substr($ladate, 5, 2),
        substr($ladate, 8, 2), substr($ladate, 0, 4));
      $row['inactive_days'] = floor((time() - $latime) / (60 * 60 * 24));

      $pdrow = sqlQuery("SELECT pd.fname, pd.lname, pd.mname, pd.ss, " .
        "pd.genericname2, pd.genericval2, pd.pid, pd.DOB, " .
        "CONCAT(u.lname, ', ', u.fname) AS referrer FROM " .
        "integration_mapping AS im, patient_data AS pd " .
        "LEFT OUTER JOIN users AS u ON u.id = pd.providerID " .
        "WHERE im.foreign_id = " . $row['custid'] . " AND " .
        "im.foreign_table = 'customer' AND " .
        "pd.id = im.local_id");

      $row['ss'] = $pdrow['ss'];
      $row['DOB'] = $pdrow['DOB'];
      $row['billnote'] = ($pdrow['genericname2'] == 'Billing') ? $pdrow['genericval2'] : '';
      $row['referrer'] = $pdrow['referrer'];

      $ptname = $pdrow['lname'] . ", " . $pdrow['fname'];
      if ($pdrow['mname']) $ptname .= " " . substr($pdrow['mname'], 0, 1);

      // Look up insurance policy number if we need it.
      if ($form_cb_policy) {
        $patient_id = $pdrow['pid'];
        $instype = ($insposition == 2) ? 'secondary' : (($insposition == 3) ? 'tertiary' : 'primary');
        $insrow = sqlQuery("SELECT policy_number FROM insurance_data WHERE " .
          "pid = '$patient_id' AND type = '$instype' AND date <= '$svcdate' " .
          "ORDER BY date DESC LIMIT 1");
        $row['policy'] = $insrow['policy_number'];
      }

      // $rows[$ptname] = $row;
      $rows[$insname . '|' . $ptname . '|' . $encounter] = $row;
    }

    ksort($rows);

    if ($_POST['form_export']) {
      echo "<textarea rows='35' cols='100' readonly>";
    }
    else if ($_POST['form_csvexport']) {
      // CSV headers:
      if (true) {
        echo '"Insurance",';
        echo '"Name",';
        echo '"Invoice",';
        echo '"DOS",';
        echo '"Referrer",';
        echo '"Charge",';
        echo '"Adjust",';
        echo '"Paid",';
        echo '"Balance",';
        echo '"IDays"' . "\n";
      }
    }
    else {
?>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
<?php if ($is_due_ins) { ?>
  <td class="dehead">&nbsp;<?php xl('Insurance','e')?></td>
<?php } ?>
  <td class="dehead">&nbsp;<?php xl('Name','e')?></td>
<?php if ($form_cb_ssn) { ?>
  <td class="dehead">&nbsp;<?php xl('SSN','e')?></td>
<?php } ?>
<?php if ($form_cb_dob) { ?>
  <td class="dehead">&nbsp;<?php xl('DOB','e')?></td>
<?php } ?>
<?php if ($form_cb_policy) { ?>
  <td class="dehead">&nbsp;<?php xl('Policy','e')?></td>
<?php } ?>
<?php if ($form_cb_phone) { ?>
  <td class="dehead">&nbsp;<?php xl('Phone','e')?></td>
<?php } ?>
<?php if ($form_cb_city) { ?>
  <td class="dehead">&nbsp;<?php xl('City','e')?></td>
<?php } ?>
<?php if ($form_cb_ins1) { ?>
  <td class="dehead">&nbsp;<?php xl('Primary Ins','e')?></td>
<?php } ?>
<?php if ($form_cb_referrer) { ?>
  <td class="dehead">&nbsp;<?php xl('Referrer','e')?></td>
<?php } ?>
  <td class="dehead">&nbsp;<?php xl('Invoice','e') ?></td>
  <td class="dehead">&nbsp;<?php xl('Svc Date','e') ?></td>
  <td class="dehead" align="right"><?php xl('Charge','e') ?>&nbsp;</td>
  <td class="dehead" align="right"><?php xl('Adjust','e') ?>&nbsp;</td>
  <td class="dehead" align="right"><?php xl('Paid','e') ?>&nbsp;</td>
<?php
      // Generate aging headers if appropriate, else balance header.
      if ($form_age_cols) {
        for ($c = 0; $c < $form_age_cols;) {
          echo "  <td class='dehead' align='right'>";
          echo $form_age_inc * $c;
          if (++$c < $form_age_cols) {
            echo "-" . ($form_age_inc * $c - 1);
          } else {
            echo "+";
          }
          echo "</td>\n";
        }
      }
      else {
?>
  <td class="dehead" align="right"><?php xl('Balance','e') ?>&nbsp;</td>
<?php
      }
?>
<?php if ($form_cb_idays) { ?>
  <td class="dehead" align="right"><?php xl('IDays','e')?>&nbsp;</td>
<?php } ?>
  <td class="dehead" align="center"><?php xl('Prv','e') ?></td>
  <td class="dehead" align="center"><?php xl('Sel','e') ?></td>
<?php if ($form_cb_err) { ?>
  <td class="dehead">&nbsp;<?php xl('Error','e')?></td>
<?php } ?>
 </tr>

<?php
    } // end not export

    $ptrow = array('insname' => '', 'pid' => 0);
    $orow = -1;

    foreach ($rows as $key => $row) {
      list($insname, $ptname, $trash) = explode('|', $key);
      list($pid, $encounter) = explode(".", $row['invnumber']);

      if ($insname != $ptrow['insname'] || $pid != $ptrow['pid']) {
        // For the report, this will write the patient totals.  For the
        // collections export this writes everything for the patient:
        endPatient($ptrow);
        $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
        $ptrow = array('insname' => $insname, 'ptname' => $ptname, 'pid' => $pid, 'count' => 1);
        foreach ($row as $key => $value) $ptrow[$key] = $value;
        $ptrow['agedbal'] = array();
      } else {
        $ptrow['amount']      += $row['amount'];
        $ptrow['paid']        += $row['paid'];
        $ptrow['charges']     += $row['charges'];
        $ptrow['adjustments'] += $row['adjustments'];
        ++$ptrow['count'];
      }

      if (!$_POST['form_export'] && !$_POST['form_csvexport']) {
        $in_collections = stristr($row['billnote'], 'IN COLLECTIONS') !== false;
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
<?php
        if ($ptrow['count'] == 1) {
          if ($is_due_ins) {
            echo "  <td class='detail'>&nbsp;$insname</td>\n";
          }
          echo "  <td class='detail'>&nbsp;$ptname</td>\n";
          if ($form_cb_ssn) {
            echo "  <td class='detail'>&nbsp;" . $row['ss'] . "</td>\n";
          }
          if ($form_cb_dob) {
            echo "  <td class='detail'>&nbsp;" . $row['DOB'] . "</td>\n";
          }
          if ($form_cb_policy) {
            echo "  <td class='detail'>&nbsp;" . $row['policy'] . "</td>\n";
          }
          if ($form_cb_phone) {
            echo "  <td class='detail'>&nbsp;" . $row['phone'] . "</td>\n";
          }
          if ($form_cb_city) {
            echo "  <td class='detail'>&nbsp;" . $row['city'] . "</td>\n";
          }
          if ($form_cb_ins1) {
            echo "  <td class='detail'>&nbsp;" . $row['ins1'] . "</td>\n";
          }
          if ($form_cb_referrer) {
            echo "  <td class='detail'>&nbsp;" . $row['referrer'] . "</td>\n";
          }
        } else {
          echo "  <td class='detail' colspan='$initial_colspan'>";
          echo "&nbsp;</td>\n";
        }
?>
  <td class="detail">
   &nbsp;<a href="../billing/sl_eob_invoice.php?id=<?php echo $row['id'] ?>"
    target="_blank"><?php echo $row['invnumber'] ?></a>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['dos']; ?>
  </td>
  <td class="detail" align="right">
   <?php bucks($row['charges']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php bucks($row['adjustments']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php bucks($row['paid']) ?>&nbsp;
  </td>

<?php
        $balance = $row['charges'] + $row['adjustments'] - $row['paid'];
        if ($form_age_cols) {
          $dostime = mktime(0, 0, 0, substr($row['dos'], 5, 2),
            substr($row['dos'], 8, 2), substr($row['dos'], 0, 4));
          $days = floor((time() - $dostime) / (60 * 60 * 24));
          $colno = min($form_age_cols - 1, max(0, floor($days / $form_age_inc)));
          $ptrow['agedbal'][$colno] += $balance;
          for ($c = 0; $c < $form_age_cols; ++$c) {
            echo "  <td class='detail' align='right'>";
            if ($c == $colno) {
              bucks($balance);
            }
            echo "&nbsp;</td>\n";
          }
        }
        else {
?>
  <td class="detail" align="right"><?php bucks($balance) ?>&nbsp;</td>
<?php
        } // end else
?>
<?php
        if ($form_cb_idays) {
          echo "  <td class='detail' align='right'>";
          echo $row['inactive_days'] . "&nbsp;</td>\n";
        }
?>
  <td class="detail" align="center">
   <?php echo $row['duncount'] ? $row['duncount'] : "&nbsp;" ?>
  </td>
  <td class="detail" align="center">
<?php
        if ($ptrow['count'] == 1) {
          if ($in_collections) {
            echo "   <b><font color='red'>IC</font></b>\n";
          } else {
            echo "   <input type='checkbox' name='form_cb[" . $row['custid'] . "]' />\n";
          }
        } else {
          echo "   &nbsp;\n";
        }
?>
  </td>
<?php
        if ($form_cb_err) {
          echo "  <td class='detail'>&nbsp;";
          echo $row['billing_errmsg'] . "</td>\n";
        }
?>
 </tr>
<?
      } // end not export

      else if ($_POST['form_csvexport']) {
        // The CSV detail line is written here.
        $balance = $row['charges'] + $row['adjustments'] - $row['paid'];
        // echo '"' . $insname                             . '",';
        echo '"' . $row['ins1']                         . '",';
        echo '"' . $ptname                              . '",';
        echo '"' . $row['invnumber']                    . '",';
        echo '"' . $row['dos']                          . '",';
        echo '"' . $row['referrer']                     . '",';
        echo '"' . sprintf('%.2f', $row['charges'])     . '",';
        echo '"' . sprintf('%.2f', $row['adjustments']) . '",';
        echo '"' . sprintf('%.2f', $row['paid'])        . '",';
        echo '"' . sprintf('%.2f', $balance)            . '",';
        echo '"' . $row['inactive_days']                . '"' . "\n";
      } // end $form_csvexport

    } // end loop

    endPatient($ptrow);

    if ($_POST['form_export']) {
      echo "</textarea>\n";
      $alertmsg .= "$export_patient_count patients representing $" .
        sprintf("%.2f", $export_dollars) . " have been exported ";
      if ($_POST['form_without']) {
        $alertmsg .= "but NOT flagged as in collections.";
      } else {
        $alertmsg .= "AND flagged as in collections.";
      }
    }
    else if ($_POST['form_csvexport']) {
      // echo "</textarea>\n";
      // $alertmsg .= "$export_patient_count patients representing $" .
      //   sprintf("%.2f", $export_dollars) . " have been exported.";
    }
    else {
      echo " <tr bgcolor='#ffffff'>\n";
      echo "  <td class='detail' colspan='$initial_colspan'>\n";
      echo "   &nbsp;</td>\n";
      echo "  <td class='dehead' colspan='2'>&nbsp;Report Totals:</td>\n";
      echo "  <td class='dehead' align='right'>&nbsp;" .
        sprintf("%.2f", $grand_total_charges) . "&nbsp;</td>\n";
      echo "  <td class='dehead' align='right'>&nbsp;" .
        sprintf("%.2f", $grand_total_adjustments) . "&nbsp;</td>\n";
      echo "  <td class='dehead' align='right'>&nbsp;" .
        sprintf("%.2f", $grand_total_paid) . "&nbsp;</td>\n";
      if ($form_age_cols) {
        for ($c = 0; $c < $form_age_cols; ++$c) {
          echo "  <td class='dehead' align='right'>" .
            sprintf("%.2f", $grand_total_agedbal[$c]) . "&nbsp;</td>\n";
        }
      }
      else {
        echo "  <td class='dehead' align='right'>" .
          sprintf("%.2f", $grand_total_charges +
          $grand_total_adjustments - $grand_total_paid) . "&nbsp;</td>\n";
      }
      if ($form_cb_idays) echo "  <td class='detail'>&nbsp;</td>\n";
      echo "  <td class='detail' colspan='2'>&nbsp;</td>\n";
      echo " </tr>\n";
      if ($form_cb_err) echo "  <td class='detail'>&nbsp;</td>\n";
      echo "</table>\n";
    }
  } // end if form_search
  SLClose();

if (!$_POST['form_csvexport']) {
  if (!$_POST['form_export']) {
?>
<p>
<input type='button' value='Select All' onclick='checkAll(true)' /> &nbsp;
<input type='button' value='Clear All' onclick='checkAll(false)' /> &nbsp;
<input type='submit' name='form_csvexport' value='Export Selected as CSV' /> &nbsp; &nbsp;
<input type='submit' name='form_export' value='Export Selected to Collections' /> &nbsp;
<input type='checkbox' name='form_without' value='1' /> <?php xl('Without Update','e') ?>
</p>
<?php
  } // end not export
?>
</form>
</center>
<script language="JavaScript">
<?php
  if ($alertmsg) {
    echo "alert('" . htmlentities($alertmsg) . "');\n";
  }
?>
</script>
</body>
</html>
<?php
} // end not form_csvexport
?>