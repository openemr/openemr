<?
  // This module was written for one of my clients to report on cash
  // receipts by practitioner.  It is not as complete as it should be
  // but I wanted to make the code available to the project because
  // many other practices have this same need. - rod@sunsetsystems.com

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/sql-ledger.inc");

  // This determines if a particular procedure code corresponds to receipts
  // for the "Clinic" column as opposed to receipts for the practitioner.  Each
  // practice will have its own policies in this regard, so you'll probably
  // have to customize this function.  If you use the "fee sheet" encounter
  // form then you might choose to uncomment the stuff below.
  //
  // include_once("../forms/fee_sheet/codes.php");
  function is_clinic($code) {
    // global $cpt, $hcpcs;
    // return ($cpt['Lab'][$code] || $cpt['Immunizations'][$code] ||
    //   $hcpcs['Therapeutic Injections'][$code]);
    return false;
  }

  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  SLConnect();
?>
<html>
<head>
<title>Receipts for Medical Services</title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2>Cash Receipts</h2>

<form method='post' action='sl_receipts_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
<?
	if (SLIsAdmin($_SESSION['authUser'])) {
		// Build a drop-down list of providers.
		//
		$query = "select id, lname, fname from users where " .
			"authorized = 1 order by lname, fname";
		$res = sqlStatement($query);
		echo "   <select name='form_doctor'>\n";
		echo "    <option value=''>All Providers\n";
		while ($row = sqlFetchArray($res)) {
			$provid = $row['id'];
			echo "    <option value='$provid'";
			if ($provid == $_POST['form_doctor']) echo " selected";
			echo ">" . $row['lname'] . ", " . $row['fname'] . "\n";
		}
		echo "   </select>\n";
	} else {
		echo "<input type='hidden' name='form_doctor' value='" . $_SESSION['authUserID'] . "'>";
	}
?>
   &nbsp;From:
   <input type='text' name='form_from_date' size='10' value='<? echo $_POST['form_from_date']; ?>' title='MM/DD/YYYY'>
   &nbsp;To:
   <input type='text' name='form_to_date' size='10' value='<? echo $_POST['form_to_date']; ?>' title='MM/DD/YYYY'>
   &nbsp;
   <input type='checkbox' name='form_details' value='1'<? if ($_POST['form_details']) echo " checked"; ?>>Details
   &nbsp;
   <input type='submit' name='form_refresh' value='Refresh'>
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
   Practitioner
  </td>
  <td class="dehead">
   Date
  </td>
  <td class="dehead">
   Invoice
  </td>
  <td class="dehead">
   Procedure
  </td>
  <td class="dehead" align="right">
   Prof.
  </td>
  <td class="dehead" align="right">
   Clinic
  </td>
 </tr>
<?
  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);

  if ($_POST['form_refresh']) {
    $form_doctor = $_POST['form_doctor'];
    $from_date = fixDate($_POST['form_from_date']);
    $to_date   = fixDate($_POST['form_to_date']);

    $query = "select acc_trans.amount, acc_trans.transdate, acc_trans.memo, " .
      "ar.invnumber, ar.employee_id from acc_trans, ar where " .
      "acc_trans.chart_id = $chart_id_cash and " .
      "acc_trans.transdate >= '$from_date' and " .
      "acc_trans.transdate <= '$to_date' and " .
      "ar.id = acc_trans.trans_id";

    if ($form_doctor) {
      $tmp = sqlQuery("select foreign_id from integration_mapping where " .
        "foreign_table = 'salesman' and local_id = $form_doctor");
      // $emplid = SLQueryValue("select id from employee where employeenumber = " .
      //   $tmp['foreign_id']);
      $emplid = $tmp['foreign_id'];
      $query .= " and ar.employee_id = $emplid";
    }

    $query .= " order by ar.employee_id, acc_trans.transdate, ar.invnumber, acc_trans.memo";

    echo "<!-- $query -->\n";

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);

    $docname     = "";
    $docnameleft = "";
    $docid       = 0;
    $doctotal1   = 0;
    $grandtotal1 = 0;
    $doctotal2   = 0;
    $grandtotal2 = 0;

    for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
      $row = SLGetRow($t_res, $irow);
      $amount1 = 0;
      $amount2 = 0;
      if (is_clinic($row['memo']))
        $amount2 -= $row['amount'];
      else
        $amount1 -= $row['amount'];

      if ($docid != $row['employee_id']) {
        if ($docid) {
          // Print doc totals.
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="4">
   Totals for <? echo $docname ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($doctotal1) ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($doctotal2) ?>
  </td>
 </tr>
<?
        }
        $doctotal1 = 0;
        $doctotal2 = 0;
        $docid = $row['employee_id'];
        $docname = SLQueryValue("select name from employee where id = $docid");
        $docnameleft = $docname;
      }

      if ($_POST['form_details']) {
?>

 <tr>
  <td class="detail">
   <? echo $docnameleft; $docnameleft = "&nbsp;" ?>
  </td>
  <td class="dehead">
   <? echo $row['transdate'] ?>
  </td>
  <td class="detail">
   <? echo $row['invnumber'] ?>
  </td>
  <td class="dehead">
   <? echo $row['memo'] ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($amount1) ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($amount2) ?>
  </td>
 </tr>
<?
      }
      $doctotal1   += $amount1;
      $doctotal2   += $amount2;
      $grandtotal1 += $amount1;
      $grandtotal2 += $amount2;
    }
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="4">
   Totals for <? echo $docname ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($doctotal1) ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($doctotal2) ?>
  </td>
 </tr>

 <tr bgcolor="#ffdddd">
  <td class="detail" colspan="4">
   Grand Totals
  </td>
  <td class="dehead" align="right">
   <? bucks($grandtotal1) ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($grandtotal2) ?>
  </td>
 </tr>

<?
  }
  SLClose();
?>

</table>
</form>
</center>
</body>
</html>
