<?php
// Copyright (C) 2015 Tony McCormick <tony@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/sql-ledger.inc");
require_once("../../library/invoice_summary.inc.php");
require_once("../../library/sl_eob.inc.php");
require_once("../../library/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";

$alertmsg = '';
$bgcolor = "#aaaaaa";

$today = date("Y-m-d");

$form_date      = fixDate($_POST['form_date'], "");
$form_to_date   = fixDate($_POST['form_to_date'], "");
$form_aging_category = $_POST['form_aging_category'];
$is_ins_summary = false;
$is_due_ins     = $is_ins_summary;
$is_due_pt      = false;
$is_all         = false;

function getInsName($payerid) {
  $tmp = sqlQuery("SELECT name FROM insurance_companies WHERE id = ?", array($payerid));
  return $tmp['name'];
}

// In the case of CSV export only, a download will be forced.
if ($_POST['form_csvexport']) {
  $filename = "aging_report_" . $form_aging_category;
  if ( $form_date ) {
     $filename .= "_from-" . $form_date;
  }
  if ( $form_to_date ) {
     $filename .= "_to-" . $form_to_date;
  }
  $filename .= ".csv";
  $filename = strtolower($filename);
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=" . $filename );
  header("Content-Description: File Transfer");
}
else {
?>
<html>
<head>
<?php if (function_exists('html_header_show')) html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<title><?php echo xlt('Aging Report')?></title>
<style type="text/css">

@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results {
       margin-top: 30px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>

</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Aging'); ?></span>

<form method='post' action='aging_report.php' enctype='multipart/form-data' id='theform'>

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
 <tr>
  <td width='610px'>
	<div style='float:left'>

	<table class='text'>
		</tr>
			<td>
				<table>

					<tr>
						<td class='label'>
						   <?php echo xlt('Service Date'); ?>:
						</td>
						<td>
						   <input type='text' name='form_date' id="form_date" size='10' value='<?php echo $form_date ?>'
							onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
						   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
							title='<?php echo xla('Click here to choose a date'); ?>'>
						</td>
						<td class='label'>
						   <?php echo xlt('To'); ?>:
						</td>
						<td>
						   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
							onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
						   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
							title='<?php echo xla('Click here to choose a date'); ?>'>
						</td>
					</tr>

					<tr>
						<td class='label'>
						   <?php echo xlt('Category'); ?>:
						</td>
                                                <td>
                                                    <select name='form_aging_category'>
                                                        <?php
                                                         foreach (array('all' => xla('All'),'payor' => xla('Payor'), 'facility' => xla('Facility')) as $key => $value) {
                                                          echo "    <option value='$key'";
                                                          if ($_POST['form_aging_category'] == $key) echo " selected";
                                                          echo ">$value</option>\n";
                                                         }
                                                        ?>
                                                    </select>
						</td>
					</tr>

				</table>
			</td>
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php echo xlt('Submit'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php echo xlt('Print'); ?>
						</span>
					</a>
                                        <a href='#' class='css_button' onclick='export_csv()'>
						<span>
							<?php echo xlt('Export as CSV'); ?>
						</span>
					</a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>
</div>

<?php
} // end not form_csvexport

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
    $rows = array();
    $where = "";

    if ($form_date) {
      if ($where) $where .= " AND ";
      if ($form_to_date) {
        $where .= "f.date >= '$form_date 00:00:00' AND f.date <= '$form_to_date 23:59:59'";
      }
      else {
        $where .= "f.date >= '$form_date 00:00:00' AND f.date <= '$form_date 23:59:59'";
      }
    }

    if (! $where) {
      $where = "1 = 1";
    }

    $query = "SELECT f.facility, f.id, f.date, f.pid, f.encounter, f.last_level_billed, " .
      "f.last_level_closed, f.last_stmt_date, f.stmt_count, f.invoice_refno, " .
      "p.fname, p.mname, p.lname, p.street, p.city, p.state, " .
      "p.postal_code, p.phone_home, p.ss, p.genericname2, p.genericval2, " .
      "p.pubpid, p.DOB, CONCAT(u.lname, ', ', u.fname) AS referrer, " .
      "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
      "b.pid = f.pid AND b.encounter = f.encounter AND " .
      "b.activity = 1 AND b.code_type != 'COPAY' ) AS charges, " .
      "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
      "b.pid = f.pid AND b.encounter = f.encounter AND " .
      "b.activity = 1 AND b.code_type = 'COPAY' ) AS copays, " .
      "( SELECT SUM(s.fee) FROM drug_sales AS s WHERE " .
      "s.pid = f.pid AND s.encounter = f.encounter ) AS sales, " .
      "( SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE " .
      "a.pid = f.pid AND a.encounter = f.encounter ) AS payments, " .
      "( SELECT SUM(a.adj_amount) FROM ar_activity AS a WHERE " .
      "a.pid = f.pid AND a.encounter = f.encounter ) AS adjustments " .
      "FROM form_encounter AS f " .
      "JOIN patient_data AS p ON p.pid = f.pid " .
      "LEFT OUTER JOIN users AS u ON u.id = p.ref_providerID " .
      "WHERE $where " .
      "ORDER BY f.pid, f.encounter";    
    $eres = sqlStatement($query);

    while ($erow = sqlFetchArray($eres)) {
      $patient_id = $erow['pid'];
      $encounter_id = $erow['encounter'];
      $pt_balance = $erow['charges'] + $erow['sales'] + $erow['copays'] - $erow['payments'] - $erow['adjustments'];
      $pt_balance = 0 + sprintf("%.2f", $pt_balance); // yes this seems to be necessary
      $svcdate = substr($erow['date'], 0, 10);

      if ($_POST['form_refresh'] && ! $is_all) {
        if ($pt_balance == 0) continue;
      }

      // If we have not yet billed the patient, then compute $duncount as a
      // negative count of the number of insurance plans for which we have not
      // yet closed out insurance.  Here we also compute $insname as the name of
      // the insurance plan from which we are awaiting payment, and its sequence
      // number $insposition (1-3).
      $last_level_closed = $erow['last_level_closed'];
      $duncount = $erow['stmt_count'];
      $payerids = array();
      $insposition = 0;
      $insname = '';
      $facility = '';
      if (! $duncount) {
        for ($i = 1; $i <= 3; ++$i) {
          $tmp = arGetPayerID($patient_id, $svcdate, $i);
          if (empty($tmp)) break;
          $payerids[] = $tmp;
        }
        $duncount = $last_level_closed - count($payerids);
        if ($duncount < 0) {
          if (!empty($payerids[$last_level_closed])) {
            $insname = getInsName($payerids[$last_level_closed]);
            $insposition = $last_level_closed + 1;
          }
        }
      }

      // Skip invoices not in the desired "Due..." category.
      //
      if ($is_due_ins && $duncount >= 0) continue;
      if ($is_due_pt  && $duncount <  0) continue;

      // echo "<!-- " . $erow['encounter'] . ': ' . $erow['charges'] . ' + ' . $erow['sales'] . ' + ' . $erow['copays'] . ' - ' . $erow['payments'] . ' - ' . $erow['adjustments'] . "  -->\n"; // debugging

      // An invoice is due from the patient if money is owed and we are
      // not waiting for insurance to pay.
      $isduept = ($duncount >= 0) ? " checked" : "";

      $row = array();

      $row['id']        = $erow['id'];
      $row['invnumber'] = "$patient_id.$encounter_id";
      $row['custid']    = $patient_id;
      $row['name']      = $erow['fname'] . ' ' . $erow['lname'];
      $row['address1']  = $erow['street'];
      $row['city']      = $erow['city'];
      $row['state']     = $erow['state'];
      $row['zipcode']   = $erow['postal_code'];
      $row['phone']     = $erow['phone_home'];
      $row['duncount']  = $duncount;
      $row['dos']       = $svcdate;
      $row['ss']        = $erow['ss'];
      $row['DOB']       = $erow['DOB'];
      $row['pubpid']    = $erow['pubpid'];
      $row['billnote']  = ($erow['genericname2'] == 'Billing') ? $erow['genericval2'] : '';
      $row['referrer']  = $erow['referrer'];
      $row['irnumber']  = $erow['invoice_refno'];
      $row['facility']  = $erow['facility'];

      $facility = $row['facility'];

      // Also get the primary insurance company name whenever there is one.
      $row['ins1'] = '';
      if ($insposition == 1) {
        $row['ins1'] = $insname;
      } else {
        if (empty($payerids)) {
          $tmp = arGetPayerID($patient_id, $svcdate, 1);
          if (!empty($tmp)) $payerids[] = $tmp;
        }
        if (!empty($payerids)) {
          $row['ins1'] = getInsName($payerids[0]);
        }
      }

      // This computes the invoice's total original charges and adjustments,
      // date of last activity, and determines if insurance has responded to
      // all billing items.
      $invlines = ar_get_invoice_summary($patient_id, $encounter_id, true);

      $row['charges'] = 0;
      $row['adjustments'] = 0;
      $row['paid'] = 0;
      $ins_seems_done = true;
      $ladate = $svcdate;
      foreach ($invlines as $key => $value) {
        $row['charges'] += $value['chg'] + $value['adj'];
        $row['adjustments'] += 0 - $value['adj'];
        $row['paid'] += $value['chg'] - $value['bal'];
        foreach ($value['dtl'] as $dkey => $dvalue) {
          $dtldate = trim(substr($dkey, 0, 10));
          if ($dtldate && $dtldate > $ladate) $ladate = $dtldate;
        }
        $lckey = strtolower($key);
        if ($lckey == 'co-pay' || $lckey == 'claim') continue;
        if (count($value['dtl']) <= 1) $ins_seems_done = false;
      }

      // Simulating ar.amount in SQL-Ledger which is charges with adjustments:
      $row['amount'] = $row['charges'] + $row['adjustments'];

      $row['ladate'] = $ladate;

      // Compute number of days since last activity.
      $latime = mktime(0, 0, 0, substr($ladate, 5, 2),
        substr($ladate, 8, 2), substr($ladate, 0, 4));
      $row['inactive_days'] = floor((time() - $latime) / (60 * 60 * 24));

      $ptname = $erow['lname'] . ", " . $erow['fname'];
      if ($erow['mname']) $ptname .= " " . substr($erow['mname'], 0, 1);

      if (!$is_due_ins ) $insname = '';
      $rows[$insname . '|' . $ptname . '|' . $encounter_id . '|' . $facility] = $row;
    } // end while

    ksort($rows);

    if ($_POST['form_csvexport']) {
      // CSV headers:
      if (true) {
        echo '"Name",';
        echo '"Average age (days) - Date of Service"' . ",";
        echo '"Average age (days) - Last Activity Date"' . "\n";
      }
    } else {
?>

<div id="report_results">
<table>

 <thead>
    <th>&nbsp;<?php echo xlt('Name')?></th>
    <th>&nbsp;<?php echo xlt('Average age (days) - Date of Service')?></th>
    <th>&nbsp;<?php echo xlt('Average age (days) - Last Activity Date')?></th>
</thead>

<?php
} // end not export

$ptrow = array('insname' => '', 'pid' => 0);
$orow = -1;

$allAccumulator = array( 'count' => 0, 'days' => 0);
$payorAccumulator = array();
$facilityAccumulator = array();

/////////////////////////
// detail compute
/////////////////////////
foreach ($rows as $key => $row) {
    list($insname, $ptname, $trash, $facility) = explode('|', $key);

    // Compute invoice balance
    $balance = $row['charges'] + $row['adjustments'] - $row['paid'];
    if ($balance <= 0 ) {
        continue;
    }

    $agedateDOS = $row['dos'];
    $agetimeDOS = mktime(0, 0, 0, substr($agedateDOS, 5, 2),
        substr($agedateDOS, 8, 2), substr($agedateDOS, 0, 4));
    $daysDOS = floor((time() - $agetimeDOS) / (60 * 60 * 24));
    
    $agedateLAD = $row['ladate'];
    $agetimeLAD = mktime(0, 0, 0, substr($agedateLAD, 5, 2),
        substr($agedateLAD, 8, 2), substr($agedateLAD, 0, 4));
    $daysLAD = floor((time() - $agetimeLAD) / (60 * 60 * 24));
   
    // all accumulator
    if ($form_aging_category == 'all' ) {
      $allAccumulator['count'] += 1;

      $allAccumulator['daysDOS'] += $daysDOS;
      $allAccumulator['averageDOS'] = floor($allAccumulator['daysDOS'] / $allAccumulator['count']);
      $allAccumulator['daysLAD'] += $daysLAD;
      $allAccumulator['averageLAD'] = floor($allAccumulator['daysLAD'] / $allAccumulator['count']);
    }

    // payor accumulator
    if ($form_aging_category == 'payor' ) {
        if ( isset($row['ins1']) ) {
          $thisInsurance = $row['ins1'];
          $thisPayorAccumulator = $payorAccumulator[$thisInsurance];
          if ( !isset($thisPayorAccumulator ) ) {
              $thisPayorAccumulator = array( 'count' => 0, 'daysDOS' => 0, 'daysLAD' => 0  );
              $payorAccumulator[$thisInsurance] = $thisPayorAccumulator;
          }
          $payorAccumulator[$thisInsurance]['count'] += 1;

          $payorAccumulator[$thisInsurance]['daysDOS'] += $daysDOS;
          $payorAccumulator[$thisInsurance]['averageDOS'] = floor($payorAccumulator[$thisInsurance]['daysDOS'] / $payorAccumulator[$thisInsurance]['count']);
          $payorAccumulator[$thisInsurance]['daysLAD'] += $daysLAD;
          $payorAccumulator[$thisInsurance]['averageLAD'] = floor($payorAccumulator[$thisInsurance]['daysLAD'] / $payorAccumulator[$thisInsurance]['count']);
        }
    }

    // facility accumulator
    if ($form_aging_category == 'facility' ) {
      $facilityAccumulator[$facility]['count'] += 1;
 
      $facilityAccumulator[$facility]['daysDOS'] += $daysDOS;
      $facilityAccumulator[$facility]['averageDOS'] = floor($facilityAccumulator[$facility]['daysDOS'] / $facilityAccumulator[$facility]['count']);
      $facilityAccumulator[$facility]['daysLAD'] += $daysLAD;
      $facilityAccumulator[$facility]['averageLAD'] = floor($facilityAccumulator[$facility]['daysLAD'] / $facilityAccumulator[$facility]['count']);
    }
} // end loop
  
/////////////////////////
// detail rendering
/////////////////////////
if ( $form_aging_category == 'all' ) {
  if ($_POST['form_csvexport']) {
      echo '"' . 'All'                          . '",';
      echo '"' . $allAccumulator['averageDOS']     . '"' . ",";
      echo '"' . $allAccumulator['averageLAD']     . '"' . "\n";
  } else {
    ?><tr bgcolor='<?php echo $bgcolor ?>'>
          <td class='detail'><?php echo xlt('All')?></td>
          <td class='detail'><?php echo text($allAccumulator['averageDOS']) ?></td>
          <td class='detail'><?php echo text($allAccumulator['averageLAD']) ?></td>
    </tr><?php   
  }
}

if ( $form_aging_category == 'payor' ) {
  ksort($payorAccumulator);
  foreach ($payorAccumulator as $payor => $payorInfo) {
      if ($_POST['form_csvexport']) {
            echo '"' . $payor                          . '",';
            echo '"' . $payorInfo['averageDOS']     . '"' . ",";
            echo '"' . $payorInfo['averageLAD']     . '"' . "\n";
      } else {
          $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
          ?><tr bgcolor='<?php echo $bgcolor ?>'>
              <td class='detail'><?php echo text($payor) ?></td>
              <td class='detail'><?php echo text($payorInfo['averageDOS']) ?></td>
              <td class='detail'><?php echo text($payorInfo['averageLAD']) ?></td>
          </tr><?php    
      }
  }
}

if ( $form_aging_category == 'facility' ) {
  ksort($facilityAccumulator);
  foreach ($facilityAccumulator as $facility => $facilityInfo) {
      if ($_POST['form_csvexport']) {
          echo '"' . $facility                          . '",';
          echo '"' . $facilityInfo['averageDOS']     . '"' . ",";
          echo '"' . $facilityInfo['averageLAD']     . '"' . "\n";
      } else {
          $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
          ?><tr bgcolor='<?php echo $bgcolor ?>'>
              <td class='detail'><?php echo $facility ?></td>
              <td class='detail'><?php echo text($facilityInfo['averageDOS']) ?></td>
              <td class='detail'><?php echo text($facilityInfo['averageLAD']) ?></td>
          </tr><?php    
      }
  }
}

if (!$_POST['form_csvexport']) {
    echo "</table>\n";
    echo "</div>\n";
}

} // end if form_refresh

if (!$_POST['form_csvexport']) {

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
<script>
    function export_csv() {
        $("#form_csvexport").attr("value","true"); $("#theform").submit()
    }
</script>
</body>
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</html>
<?php
} // end not form_csvexport
?>
