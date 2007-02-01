<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../library/sql-ledger.inc");
 include_once("../../library/invoice_summary.inc.php");
 include_once("../../custom/statement.inc.php");
 include_once("../../library/sl_eob.inc.php");

 $alertmsg = '';
 $bgcolor = "#aaaaaa";
 $export_patient_count = 0;
 $export_dollars = 0;

 function bucks($amount) {
  if ($amount)
   printf("%.2f", $amount);
 }

 $today = date("Y-m-d");

 $form_date      = fixDate($_POST['form_date'], "");
 $form_to_date   = fixDate($_POST['form_to_date'], "");
 $is_due_ins     = $_POST['form_category'] == xl('Due Ins');
 $is_due_pt      = $_POST['form_category'] == xl('Due Pt');


 $grand_total_charges     = 0;
 $grand_total_adjustments = 0;
 $grand_total_paid        = 0;

 SLConnect();

function endPatient($ptrow) {
  global $export_patient_count, $export_dollars, $bgcolor;
  global $grand_total_charges, $grand_total_adjustments, $grand_total_paid;
  global $is_due_ins;

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
        "genericval2 = 'IN COLLECTIONS " . date("Y-m-d") . "' " .
        "WHERE pid = '" . $ptrow['pid'] . "'");
    }
    $export_patient_count += 1;
    $export_dollars += $pt_balance;
  }
  else {
    if ($ptrow['count'] > 1) {
      echo " <tr bgcolor='$bgcolor'>\n";
      echo "  <td class='detail' colspan='" . ($is_due_ins ? '5' : '4') . "'>\n";
      echo "   &nbsp;\n";
      echo "  </td>\n";
      echo "  <td class='detotal' colspan='5'>\n";
      echo "   &nbsp;Total Patient Balance:\n";
      echo "  </td>\n";
      echo "  <td class='detotal' align='right'>\n";
      echo "   &nbsp;" . sprintf("%.2f", $pt_balance) . "&nbsp;\n";
      echo "  </td>\n";
      echo "  <td class='detail' colspan='2'>\n";
      echo "   &nbsp;\n";
      echo "  </td>\n";
      echo " </tr>\n";
    }
  }
  $grand_total_charges     += $ptrow['charges'];
  $grand_total_adjustments += $ptrow['adjustments'];
  $grand_total_paid        += $ptrow['paid'];
}
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

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1" colspan="4">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   <?xl('Svc Date:','e')?>
   <input type='text' name='form_date' size='10' value='<?php echo $_POST['form_date']; ?>'
    title='<?xl("Date of service mm/dd/yyyy","e")?>'>
  </td>
  <td>
   <?xl('To:','e')?>
   <input type='text' name='form_to_date' size='10' value='<?php echo $_POST['form_to_date']; ?>'
    title='<?xl("Ending DOS mm/dd/yyyy if you wish to enter a range","e")?>'>
  </td>
  <td>
   <select name='form_category'>
<?php
 foreach (array(xl('Open'), xl('Due Pt'), xl('Due Ins'), xl('Credits')) as $value) {
  echo "    <option value='$value'";
  if ($_POST['form_category'] == $value) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
  </td>
  <td>
   <input type='submit' name='form_search' value='<?xl("Search","e")?>'>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="4">
  </td>
 </tr>

</table>

<?php
  if ($_POST['form_search'] || $_POST['form_export']) {
    $where = "";

    if ($_POST['form_export']) {
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

    $query = "SELECT ar.id, ar.invnumber, ar.duedate, ar.amount, ar.paid, " .
      "ar.intnotes, ar.notes, ar.shipvia, " .
      "customer.id AS custid, customer.name, customer.address1, " .
      "customer.city, customer.state, customer.zipcode, customer.phone, " .
      "(SELECT SUM(invoice.fxsellprice) FROM invoice WHERE " .
      "invoice.trans_id = ar.id AND invoice.fxsellprice > 0) AS charges, " .
      "(SELECT SUM(invoice.fxsellprice) FROM invoice WHERE " .
      "invoice.trans_id = ar.id AND invoice.fxsellprice < 0) AS adjustments " .
      "FROM ar JOIN customer ON customer.id = ar.customer_id " .
      "WHERE ( $where ) ";
    if ($_POST['form_search']) {
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
      // else {
      //   if ($pt_balance < 0) continue;
      // }

      // $duncount was originally supposed to be the number of times that
      // the patient was sent a statement for this invoice.
      //
      $duncount = substr_count(strtolower($row['intnotes']), "statement sent");

      // But if we have not yet billed the patient, then compute $duncount as a
      // negative count of the number of insurance plans for which we have not
      // yet closed out insurance.  Here we also compute $insname as the name of
      // the insurance plan from which we are awaiting payment.
      //
      $insname = '';
      if (! $duncount) {
        $insgot = strtolower($row['notes']);
        $inseobs = strtolower($row['shipvia']);
        foreach (array('ins1', 'ins2', 'ins3') as $value) {
          $i = strpos($insgot, $value);
          if ($i !== false && strpos($inseobs, $value) === false) {
            --$duncount;
            if (!$insname && $is_due_ins) {
              $j = strpos($insgot, "\n", $i);
              if (!$j) $j = strlen($insgot);
              $insname = trim(substr($row['notes'], $i + 5, $j - $i - 5));
            }
          }
        }
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

      $pdrow = sqlQuery("SELECT pd.fname, pd.lname, pd.mname, pd.ss, " .
        "pd.genericname2, pd.genericval2 FROM " .
        "integration_mapping AS im, patient_data AS pd WHERE " .
        "im.foreign_id = " . $row['custid'] . " AND " .
        "im.foreign_table = 'customer' AND " .
        "pd.id = im.local_id");

      $row['ss'] = $pdrow['ss'];
      $row['billnote'] = ($pdrow['genericname2'] == 'Billing') ? $pdrow['genericval2'] : '';

      $ptname = $pdrow['lname'] . ", " . $pdrow['fname'];
      if ($pdrow['mname']) $ptname .= " " . substr($pdrow['mname'], 0, 1);

      // $rows[$ptname] = $row;
      $rows[$insname . '|' . $ptname . '|' . $encounter] = $row; // new
    }

    ksort($rows);

    if ($_POST['form_export']) {
      echo "<textarea rows='35' cols='100' readonly>";
    }
    else {
?>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
<?php if ($is_due_ins) { ?>
  <td class="dehead">
   &nbsp;<?xl('Insurance','e')?>
  </td>
<?php } ?>
  <td class="dehead">
   &nbsp;<?xl('Name','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('SSN','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Phone','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('City','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?php xl('Invoice','e') ?>
  </td>
  <td class="dehead">
   &nbsp;<?php xl('Svc Date','e') ?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Charge','e') ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php xl('Adjust','e') ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php xl('Paid','e') ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php xl('Balance','e') ?>&nbsp;
  </td>
  <td class="dehead" align="center">
   <?php xl('Prv','e') ?>
  </td>
  <td class="dehead" align="center">
   <?php xl('Sel','e') ?>
  </td>
 </tr>

<?php
    }

    $ptrow = array('insname' => '', 'pid' => 0);
    $orow = -1;

    foreach ($rows as $key => $row) {
      list($insname, $ptname, $trash) = explode('|', $key);
      list($pid, $encounter) = explode(".", $row['invnumber']);

      if ($insname != $ptrow['insname'] || $pid != $ptrow['pid']) {
        // For the report, this will write the patient totals.  For the
        // export this writes everything for the patient:
        endPatient($ptrow);
        $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
        $ptrow = array('insname' => $insname, 'ptname' => $ptname, 'pid' => $pid, 'count' => 1);
        foreach ($row as $key => $value) $ptrow[$key] = $value;
      } else {
        $ptrow['amount']      += $row['amount'];
        $ptrow['paid']        += $row['paid'];
        $ptrow['charges']     += $row['charges'];
        $ptrow['adjustments'] += $row['adjustments'];
        ++$ptrow['count'];
      }

      if (!$_POST['form_export']) {

        $in_collections = stristr($row['billnote'], 'IN COLLECTIONS') !== false;

?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
<?php
        if ($ptrow['count'] == 1) {
          if ($is_due_ins) {
            echo "  <td class='detail'>\n";
            echo "   &nbsp;$insname\n";
            echo "  </td>\n";
          }
          echo "  <td class='detail'>\n";
          echo "   &nbsp;$ptname\n";
          echo "  </td>\n";
          echo "  <td class='detail'>\n";
          echo "   &nbsp;" . $row['ss'] . "\n";
          echo "  </td>\n";
          echo "  <td class='detail'>\n";
          echo "   &nbsp;" . $row['phone'] . "\n";
          echo "  </td>\n";
          echo "  <td class='detail'>\n";
          echo "   &nbsp;" . $row['city'] . "\n";
          echo "  </td>\n";
        } else {
          echo "  <td class='detail' colspan='" . ($is_due_ins ? '5' : '4') . "'>\n";
          echo "   &nbsp;\n";
          echo "  </td>\n";
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
  <td class="detail" align="right">
   <?php bucks($row['charges'] + $row['adjustments'] - $row['paid']) ?>&nbsp;
  </td>
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
 </tr>
<?
      } // end not $form_export
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
    else {
      echo " <tr bgcolor='#ffffff'>\n";
      echo "  <td class='detail' colspan='" . ($is_due_ins ? '5' : '4') . "'>\n";
      echo "   &nbsp;\n";
      echo "  </td>\n";
      echo "  <td class='dehead' colspan='2'>\n";
      echo "   &nbsp;Report Totals:\n";
      echo "  </td>\n";
      echo "  <td class='dehead' align='right'>\n";
      echo "   &nbsp;" . sprintf("%.2f", $grand_total_charges) . "&nbsp;\n";
      echo "  </td>\n";
      echo "  <td class='dehead' align='right'>\n";
      echo "   &nbsp;" . sprintf("%.2f", $grand_total_adjustments) . "&nbsp;\n";
      echo "  </td>\n";
      echo "  <td class='dehead' align='right'>\n";
      echo "   &nbsp;" . sprintf("%.2f", $grand_total_paid) . "&nbsp;\n";
      echo "  </td>\n";
      echo "  <td class='dehead' align='right'>\n";
      echo "   " . sprintf("%.2f", $grand_total_charges +
           $grand_total_adjustments - $grand_total_paid) . "&nbsp;\n";
      echo "  </td>\n";
      echo "  <td class='detail' colspan='2'>\n";
      echo "   &nbsp;\n";
      echo "  </td>\n";
      echo " </tr>\n";
      echo "</table>\n";
    }
  } // end if form_search
  SLClose();
?>

<p>
<?php if (!$_POST['form_export']) { ?>
<input type='button' value='Select All' onclick='checkAll(true)' /> &nbsp;
<input type='button' value='Clear All' onclick='checkAll(false)' /> &nbsp;
<input type='submit' name='form_export' value='Export Selected to Collections' /> &nbsp;
<input type='checkbox' name='form_without' value='1' /> <?php xl('Without Update','e') ?>
<?php } ?>
</p>

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
