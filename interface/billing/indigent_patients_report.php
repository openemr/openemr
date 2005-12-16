<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This is the Indigent Patients Report.  It displays a summary of
 // encounters within the specified time period for patients without
 // insurance.

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../library/sql-ledger.inc");

 $alertmsg = '';

 function bucks($amount) {
  if ($amount) return sprintf("%.2f", $amount);
  return "";
 }

 $form_start_date = fixDate($_POST['form_start_date'], "2005-10-01");
 $form_end_date   = fixDate($_POST['form_end_date'], date("Y-m-d"));

 SLConnect();
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?xl('Indigent Patients Report','e')?></title>

<script language="JavaScript">

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h3>Indigent Patients Report</h3>

<form method='post' action='indigent_patients_report.php'>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   <?xl('Start Date:','e')?>
  </td>
  <td>
   <input type='text' name='form_start_date' size='10' value='<? echo $form_start_date ?>'
    title='<?xl("Beginning date of service yyyy-mm-dd","e")?>'>
  </td>
  <td>
   <?xl('End Date:','e')?>
  </td>
  <td>
   <input type='text' name='form_end_date' size='10' value='<? echo $form_end_date ?>'
    title='<?xl("Ending date of service yyyy-mm-dd","e")?>'>
  </td>
  <td>
   <input type='submit' name='form_search' value='<?xl("Search","e")?>'>
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
   &nbsp;<?xl('Patient','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('SSN','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Invoice','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Svc Date','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Due Date','e')?>
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
 </tr>
<?
  if ($_POST['form_search']) {

    $where = "";

    if ($form_start_date) {
      $where .= " AND e.date >= '$form_start_date'";
    }
    if ($form_end_date) {
      $where .= " AND e.date <= '$form_end_date'";
    }

    $rez = sqlStatement("SELECT " .
      "e.date, e.encounter, p.pid, p.lname, p.fname, p.mname, p.ss " .
      "FROM form_encounter AS e, patient_data AS p, insurance_data AS i " .
      "WHERE p.pid = e.pid AND i.pid = e.pid AND i.type = 'primary' " .
      "AND i.provider = ''$where " .
      "ORDER BY p.lname, p.fname, p.mname, p.pid, e.date"
    );

    $total_amount = 0;
    $total_paid   = 0;

    for ($irow = 0; $row = sqlFetchArray($rez); ++$irow) {
      $invnumber = $row['pid'] . "." . $row['encounter'];

      $ares = SLQuery("SELECT duedate, amount, paid FROM ar WHERE " .
        "ar.invnumber = '$invnumber'");
      if ($sl_err) die($sl_err);
      $arow = SLGetRow($ares, 0);

      $total_amount += bucks($arow['amount']);
      $total_paid   += bucks($arow['paid']);

      $bgcolor = (($irow & 1) ? "#ffdddd" : "#ddddff");
?>
 <tr bgcolor='<? echo $bgcolor ?>'>
  <td class="detail">
   &nbsp;<? echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] ?>
  </td>
  <td class="detail">
   &nbsp;<? echo $row['ss'] ?>
  </td>
  <td class="detail">
   &nbsp;<? echo $invnumber ?></a>
  </td>
  <td class="detail">
   &nbsp;<? echo substr($row['date'], 0, 10) ?>
  </td>
  <td class="detail">
   &nbsp;<? echo $arow['duedate'] ?>
  </td>
  <td class="detail" align="right">
   <? echo bucks($arow['amount']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <? echo bucks($arow['paid']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <? echo bucks($arow['amount'] - $arow['paid']) ?>&nbsp;
  </td>
 </tr>
<?
    }
?>
 <tr bgcolor='#dddddd'>
  <td class="detail">
   &nbsp;Totals
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail" align="right">
   <? echo bucks($total_amount) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <? echo bucks($total_paid) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <? echo bucks($total_amount - $total_paid) ?>&nbsp;
  </td>
 </tr>
<?
  }
  SLClose();
?>

</table>

</form>
</center>
<script>
<?
	if ($alertmsg) {
		echo "alert('$alertmsg');\n";
	}
?>
</script>
</body>
</html>
