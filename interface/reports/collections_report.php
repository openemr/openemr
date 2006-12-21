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

 $DEBUG = 0; // set to 0 for production, 1 to test

 $alertmsg = '';
 $where = '';

 function bucks($amount) {
  if ($amount)
   printf("%.2f", $amount);
 }

 $today = date("Y-m-d");

 $form_date      = fixDate($_POST['form_date'], "");
 $form_to_date   = fixDate($_POST['form_to_date'], "");
 $form_export    = $_POST['form_export'];
 $form_minimum   = sprintf("%.2f",$_POST['form_minimum']);

 SLConnect();
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?xl('Collections Report','e')?></title>

<script language="JavaScript">

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<form method='post' action='collections_report.php' enctype='multipart/form-data'>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1" colspan="6">
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
   <?xl('Minimum:','e')?>
   <input type='text' name='form_minimum' size='4' value='<?php echo $form_minimum; ?>'
    title='<?xl("Minimum balance to include","e")?>'>
  </td>
  <td>
   <select name='form_category'>
<?php
 foreach (array(xl('Open'), xl('Due Pt'), xl('Due Ins')) as $value) {
  echo "    <option value='$value'";
  if ($_POST['form_category'] == $value) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
  </td>
  <td>
   <?xl('Export:','e')?>
   <input type='checkbox' name='form_export' value='1'
    title='<?xl("To display in export format","e")?>'
    <?php if ($form_export) echo 'checked '; ?>/>
  </td>
  <td>
   <input type='submit' name='form_search' value='<?xl("Search","e")?>'>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="6">
  </td>
 </tr>

</table>

<?php
  if ($_POST['form_search']) {
    $where = "";

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
      "customer.city, customer.state, customer.zipcode, customer.phone " .
      "FROM ar, customer WHERE ( $where ) AND customer.id = ar.customer_id ";
    if ($_POST['form_category'] != 'All') {
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

      if ((sprintf("%.2f",$row['amount']) - sprintf("%.2f",$row['paid']))
        < ($form_minimum + 0)) continue;

      // $duncount was originally supposed to be the number of times that
      // the patient was sent a statement for this invoice.
      //
      $duncount = substr_count(strtolower($row['intnotes']), "statement sent");

      // But if we have not yet billed the patient, then compute $duncount as a
      // negative count of the number of insurance plans for which we have not
      // yet closed out insurance.
      //
      if (! $duncount) {
        $insgot = strtolower($row['notes']);
        $inseobs = strtolower($row['shipvia']);
        foreach (array('ins1', 'ins2', 'ins3') as $value) {
          if (strpos($insgot, $value) !== false &&
              strpos($inseobs, $value) === false)
            --$duncount;
        }
      }

      // An invoice is now due from the patient if money is owed and we are
      // not waiting for insurance to pay.  We no longer look at the due date
      // for this.
      //
      $isduept = ($duncount >= 0) ? " checked" : "";

      // Skip invoices not in the desired "Due..." category.
      //
      if ($_POST['form_category'] == 'Due Ins' && $duncount >= 0) continue;
      if ($_POST['form_category'] == 'Due Pt'  && $duncount <  0) continue;

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

      $pdrow = sqlQuery("SELECT pd.fname, pd.lname, pd.mname, pd.ss FROM " .
        "integration_mapping AS im, patient_data AS pd WHERE " .
        "im.foreign_id = " . $row['custid'] . " AND " .
        "im.foreign_table = 'customer' AND " .
        "pd.id = im.local_id");

      $row['ss'] = $pdrow['ss'];

      $ptname = $pdrow['lname'] . ", " . $pdrow['fname'];
      if ($pdrow['mname']) $ptname .= " " . substr($pdrow['mname'], 0, 1);

      $rows[$ptname] = $row;
    }

    ksort($rows);

    if ($form_export) {
      echo "<textarea rows='35' cols='100' readonly>";
    }
    else {
?>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
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
   &nbsp;<?xl('Street','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('City','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('State','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Zip','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Invoice','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Svc Date','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Amount','e')?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?xl('Paid','e')?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?xl('Balance','e')?>&nbsp;
  </td>
  <td class="dehead" align="center">
   <?xl('Prv','e')?>
  </td>
 </tr>

<?php
    }

    $orow = -1;
    foreach ($rows as $ptname => $row) {

      $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
      list($pid, $encounter) = explode(".", $row['invnumber']);

      if ($form_export) {

        // This is a fixed-length format used by Transworld Systems.  Your
        // needs will surely be different, so consider this just an example.
        //
        echo "9999X"; // client number goes here
        echo "000";   // filler
        echo sprintf("%-30s", substr($ptname, 0, 30));
        echo sprintf("%-30s", " ");
        echo sprintf("%-30s", substr($row['address1'], 0, 30));
        echo sprintf("%-15s", substr($row['city'], 0, 15));
        echo sprintf("%-2s", substr($row['state'], 0, 2));
        echo sprintf("%-5s", $row['zipcode'] ? substr($row['zipcode'], 0, 5) : '00000');
        echo "0";                      // service code
        echo sprintf("%010.0f", $pid); // transmittal number = patient id
        echo " ";                      // filler
        echo sprintf("%-15s", substr($row['ss'], 0, 15));
        echo substr($row['dos'], 5, 2) . substr($row['dos'], 8, 2) . substr($row['dos'], 2, 2);
        echo sprintf("%08.0f", ($row['amount'] - $row['paid']) * 100);
        echo sprintf("%-9s\n", " ");

      } else {
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td class="detail">
   &nbsp;<?php echo $ptname; ?>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['ss'] ?>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['phone'] ?>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['address1'] ?>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['city'] ?>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['state'] ?>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['zipcode'] ?>
  </td>
  <td class="detail">
   &nbsp;<a href="../billing/sl_eob_invoice.php?id=<?php echo $row['id'] ?>"
    target="_blank"><?php echo $row['invnumber'] ?></a>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['dos']; ?>
  </td>
  <td class="detail" align="right">
   <?php bucks($row['amount']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php bucks($row['paid']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php bucks($row['amount'] - $row['paid']) ?>&nbsp;
  </td>
  <td class="detail" align="center">
   <?php echo $row['duncount'] ? $row['duncount'] : "&nbsp;" ?>
  </td>
 </tr>
<?
      } // end not $form_export
    } // end loop

    if ($form_export) {
      echo "</textarea>\n";
    }
    else {
      echo "</table>\n";
    }
  } // end if form_search
  SLClose();
?>

<p>

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
