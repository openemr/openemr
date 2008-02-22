<?
  // This module was written for one of my clients to report on cash
  // receipts by practitioner.  It is not as complete as it should be
  // but I wanted to make the code available to the project because
  // many other practices have this same need. - rod@sunsetsystems.com

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/sql-ledger.inc");
  include_once("../../library/acl.inc");

  // This determines if a particular procedure code corresponds to receipts
  // for the "Clinic" column as opposed to receipts for the practitioner.  Each
  // practice will have its own policies in this regard, so you'll probably
  // have to customize this function.  If you use the "fee sheet" encounter
  // form then the code below may work for you.
  //
  include_once("../forms/fee_sheet/codes.php");
  function is_clinic($code) {
    global $bcodes;
    return ($bcodes['CPT4'][xl('Lab')][$code]     ||
      $bcodes['CPT4'][xl('Immunizations')][$code] ||
      $bcodes['HCPCS'][xl('Therapeutic Injections')][$code]);
  }

  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

  SLConnect();

  $form_use_edate = $_POST['form_use_edate'];
  $form_cptcode = trim($_POST['form_cptcode']);
  $form_icdcode = trim($_POST['form_icdcode']);
?>
<html>
<head>
<? html_header_show();?>
<title><?xl('Receipts for Medical Services','e')?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?xl('Cash Receipts','e')?></h2>

<form method='post' action='sl_receipts_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
<?
	if (acl_check('acct', 'rep_a')) {
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
   &nbsp;<select name='form_use_edate'>
    <option value='0'><?php xl('Payment Date','e'); ?></option>
    <option value='1'<?php if ($form_use_edate) echo ' selected' ?>><?php xl('Invoice Date','e'); ?></option>
   </select>
   &nbsp;<?xl('From:','e')?>
   <input type='text' name='form_from_date' size='10' value='<? echo $_POST['form_from_date']; ?>' title='MM/DD/YYYY'>
   &nbsp;To:
   <input type='text' name='form_to_date' size='10' value='<? echo $_POST['form_to_date']; ?>' title='MM/DD/YYYY'>
   &nbsp;CPT:
   <input type='text' name='form_cptcode' size='5' value='<? echo $form_cptcode; ?>'
    title='<?php xl('Optional procedure code','e'); ?>'>
   &nbsp;ICD:
   <input type='text' name='form_icdcode' size='5' value='<? echo $form_icdcode; ?>'
    title='<?php xl('Enter a diagnosis code to exclude all invoices not containing it','e'); ?>'>
   &nbsp;
   <input type='checkbox' name='form_details' value='1'<? if ($_POST['form_details']) echo " checked"; ?>><?xl('Details','e')?>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?xl('Refresh','e')?>">
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
   <?xl('Practitioner','e')?>
  </td>
  <td class="dehead">
   <?xl('Date','e')?>
  </td>
  <td class="dehead">
   <?xl('Invoice','e')?>
  </td>
<?php if ($form_cptcode) { ?>
  <td class="dehead" align='right'>
   <?xl('InvAmt','e')?>
  </td>
<?php } ?>
<?php if ($form_cptcode) { ?>
  <td class="dehead">
   <?xl('Insurance','e')?>
  </td>
<?php } ?>
  <td class="dehead">
   <?xl('Procedure','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Prof.','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Clinic','e')?>
  </td>
 </tr>
<?
  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);

  if ($_POST['form_refresh']) {
    $form_doctor = $_POST['form_doctor'];
    $from_date = fixDate($_POST['form_from_date']);
    $to_date   = fixDate($_POST['form_to_date']);

    if ($form_cptcode) {
      $query = "SELECT acc_trans.amount, acc_trans.transdate, " .
        "acc_trans.memo, acc_trans.project_id, acc_trans.trans_id, " .
        "ar.invnumber, ar.employee_id, invoice.fxsellprice " .
        "FROM acc_trans, ar, invoice WHERE " .
        "acc_trans.chart_id = $chart_id_cash AND " .
        "acc_trans.memo ILIKE '$form_cptcode' AND " .
        "ar.id = acc_trans.trans_id AND " .
        "invoice.trans_id = acc_trans.trans_id AND " .
        "invoice.serialnumber ILIKE acc_trans.memo AND " .
        "invoice.fxsellprice >= 0.00 AND " .
        "invoice.fxsellprice >= 0.00 AND " .
        "( invoice.description ILIKE 'CPT%' OR invoice.description ILIKE 'Proc%' ) AND ";
    }
    else {
      $query = "select acc_trans.amount, acc_trans.transdate, " .
        "acc_trans.memo, acc_trans.trans_id, " .
        "ar.invnumber, ar.employee_id from acc_trans, ar where " .
        "acc_trans.chart_id = $chart_id_cash and " .
        "ar.id = acc_trans.trans_id and ";
    }

    if ($form_use_edate) {
      $query .= "ar.transdate >= '$from_date' and " .
      "ar.transdate <= '$to_date'";
    } else {
      $query .= "acc_trans.transdate >= '$from_date' and " .
      "acc_trans.transdate <= '$to_date'";
    }

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
    $last_trans_id = 0;
    $skipping      = false;

    for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
      $row = SLGetRow($t_res, $irow);

      // If a diagnosis code was given then skip any invoices without
      // that diagnosis.
      if ($form_icdcode) {
        if ($row['trans_id'] == $last_trans_id) {
          if ($skipping) continue;
          // same invoice and not skipping, do nothing.
        } else { // new invoice
          $skipping = false;
          if (!SLQueryValue("SELECT count(*) FROM invoice WHERE " .
            "invoice.trans_id = '" . $row['trans_id'] . "' AND " .
            "( invoice.description ILIKE 'ICD9:$form_icdcode %' OR " .
            "invoice.serialnumber ILIKE 'ICD9:$form_icdcode' )"))
          {
            $skipping = true;
            continue;
          }
        }
      }

      // Get insurance company name
      $insconame = '';
      if ($form_cptcode && $row['project_id']) {
        $tmp = sqlQuery("SELECT name FROM insurance_companies WHERE " .
          "id = '" . $row['project_id'] . "'");
        $insconame = $tmp['name'];
      }

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
  <td class="detail" colspan="<?php echo $form_cptcode ? '6' : '4'; ?>">
   <? echo xl('Totals for ') . $docname ?>
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
  <td class="detail">
   <? echo $row['transdate'] ?>
  </td>
  <td class="detail">
   <? echo $row['invnumber'] ?>
  </td>
<?php if ($form_cptcode) { ?>
  <td class="detail" align='right'>
   <?php bucks($row['fxsellprice']) ?>
  </td>
<?php } ?>
<?php if ($form_cptcode) { ?>
  <td class="detail">
   <?php echo $insconame ?>
  </td>
<?php } ?>
  <td class="detail">
   <? echo $row['memo'] ?>
  </td>
  <td class="detail" align="right">
   <? bucks($amount1) ?>
  </td>
  <td class="detail" align="right">
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
  <td class="detail" colspan="<?php echo $form_cptcode ? '6' : '4'; ?>">
   <?echo xl('Totals for ') . $docname ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($doctotal1) ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($doctotal2) ?>
  </td>
 </tr>

 <tr bgcolor="#ffdddd">
  <td class="detail" colspan="<?php echo $form_cptcode ? '6' : '4'; ?>">
   <?xl('Grand Totals','e')?>
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
