<?
  // This is the first of two pages to support posting of EOBs.
  // The second is sl_eob_invoice.php.

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/sql-ledger.inc");

  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  SLConnect();
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title>EOB Posting - Search</title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<form method='post' action='sl_eob_search.php'>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

 <tr>
  <td colspan='2'>
   &nbsp;
  </td>
  <td>
   Source:
  </td>
  <td>
   <input type='text' name='form_source' size='10' value='<? echo $_POST['form_source']; ?>'
    title='A check number or claim number to identify the payment'>
  </td>
  <td>
   Pay Date:
  </td>
  <td>
   <input type='text' name='form_paydate' size='10' value='<? echo $_POST['form_paydate']; ?>'
    title='Date of payment mm/dd/yyyy'>
  </td>
  <td>
   Amount:
  </td>
  <td>
   <input type='text' name='form_amount' size='10' value='<? echo $_POST['form_amount']; ?>'
    title='Paid amount that you will allocate'>
  </td>
  <td colspan='2' align='right'>
   <a href='sl_eob_help.php' target='_blank'>Help</a>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   Name:
  </td>
  <td>
   <input type='text' name='form_name' size='10' value='<? echo $_POST['form_name']; ?>'
    title='Any part of the patient name'>
  </td>
  <td>
   Chart ID:
  </td>
  <td>
   <input type='text' name='form_pid' size='10' value='<? echo $_POST['form_pid']; ?>'
    title='Patient chart ID'>
  </td>
  <td>
   Encounter:
  </td>
  <td>
   <input type='text' name='form_encounter' size='10' value='<? echo $_POST['form_encounter']; ?>'
    title='Encounter number'>
  </td>
  <td>
   Svc Date:
  </td>
  <td>
   <input type='text' name='form_date' size='10' value='<? echo $_POST['form_date']; ?>'
    title='Date of service mm/dd/yyyy'>
  </td>
  <td>
   <input type='checkbox' name='form_closed' value='1'
    title='Include closed invoices'<? if ($_POST['form_closed']) echo " checked"; ?>>Closed
  </td>
  <td>
   <input type='submit' name='form_search' value='Search'>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   Patient
  </td>
  <td class="dehead">
   Invoice
  </td>
  <td class="dehead">
   Svc Date
  </td>
  <td class="dehead" align="right">
   Amount
  </td>
  <td class="dehead" align="right">
   Paid
  </td>
 </tr>
<?
  if ($_POST['form_search']) {
    $form_name      = trim($_POST['form_name']);
    $form_pid       = trim($_POST['form_pid']);
    $form_encounter = trim($_POST['form_encounter']);
    $form_date      = fixDate($_POST['form_date'], "");

    $where = "";

    if ($form_name) {
      if ($where) $where .= " AND ";
      $where .= "customer.name ILIKE '%$form_name%'";
    }

    if ($form_pid && $form_encounter) {
      if ($where) $where .= " AND ";
      $where .= "ar.invnumber = '$form_pid.$form_encounter'";
    }
    else if ($form_pid) {
      if ($where) $where .= " AND ";
      $where .= "ar.invnumber LIKE '$form_pid.%'";
    }
    else if ($form_encounter) {
      if ($where) $where .= " AND ";
      $where .= "ar.invnumber like '%.$form_encounter'";
    }

    if ($form_date) {
      if ($where) $where .= " AND ";
      $where .= "(ar.invnumber LIKE '%." . substr($form_date, 0, 4) . substr($form_date, 5, 2) .
        substr($form_date, 8, 2) . "'";
      $rez = sqlStatement("SELECT pid, encounter FROM form_encounter WHERE date = '$form_date'");
      while ($row = sqlFetchArray($rez)) {
        $where .= " OR ar.invnumber = '" . $row['pid'] . "." . $row['encounter'] . "'";
      }
      $where .= ")";
    }

    if (! $where) die("At least one search parameter is required.");

    $query = "SELECT ar.id, ar.invnumber, ar.amount, ar.paid, customer.name " .
      "FROM ar, customer WHERE $where AND customer.id = ar.customer_id ";
    if (! $_POST['form_closed'])
      $query .= "AND ar.amount != ar.paid ";
    $query .= "ORDER BY customer.name, ar.invnumber";

    // echo "<!-- $query -->\n"; // debugging

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);

    for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
      $row = SLGetRow($t_res, $irow);

      // Determine the date of service.  If this was a search parameter
      // then we already know it.  Or an 8-digit encounter number is
      // presumed to be a date of service imported during conversion.
      // Otherwise look it up in the form_encounter table.
      //
      list($pid, $encounter) = explode(".", $row['invnumber']);
      if ($form_date) {
        $svcdate = $form_date;
      }
      else if (strlen($encounter) == 8) {
        $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
          "-" . substr($encounter, 6, 2);
      }
      else {
        $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
          "encounter = $encounter");
        $svcdate = substr($tmp['date'], 0, 10);
      }
?>
 <tr>
  <td class="detail">
   <? echo $row['name'] ?>
  </td>
  <td class="detail">
   <a href="sl_eob_invoice.php?id=<? echo $row['id'] ?>"
    target="_blank"><? echo $row['invnumber'] ?></a>
  </td>
  <td class="detail">
   <? echo $svcdate ?>
  </td>
  <td class="detail" align="right">
   <? bucks($row['amount']) ?>
  </td>
  <td class="detail" align="right">
   <? bucks($row['paid']) ?>
  </td>
 </tr>
<?
    }
  }
  SLClose();
?>

</table>
</form>
</center>
</body>
</html>
