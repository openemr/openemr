<?php
/**
 * This is a report of sales by item description. It's driven from
 * SQL-Ledger so as to include all types of invoice items.
 *
 * Copyright (C) 2015 Terry Hill <terry@lillysystems.com>
 * Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */
$sanitize_all_escapes=true;
$fake_register_globals=false;
 
require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/sql-ledger.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";

$form_provider  = $_POST['form_provider'];
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $form_details  = $_POST['form_details']      ? true : false;
}
else
{
  $form_details = false;
}
function bucks($amount) {
  if ($amount) echo oeFormatMoney($amount);
}

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

function thisLineItem($patient_id, $encounter_id, $rowcat, $description, $transdate, $qty, $amount, $irnumber='') {
  global $product, $category, $producttotal, $productqty, $cattotal, $catqty, $grandtotal, $grandqty;
  global $productleft, $catleft;

  $invnumber = $irnumber ? $irnumber : "$patient_id.$encounter_id";
  $rowamount = sprintf('%01.2f', $amount);
  
   $patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
  "p.street, p.city, p.state, p.postal_code, " .
  "p.ss, p.sex, p.status, p.phone_home, " .
  "p.phone_biz, p.phone_cell, p.hipaa_notice " .
  "FROM patient_data AS p " .
  "WHERE p.pid = ? LIMIT 1", array($patient_id));
  
  $pat_name = $patdata['fname'] . ' ' . $patdata['mname'] . ' ' . $patdata['lname'];
  
  if (empty($rowcat)) $rowcat = xl('None');
  $rowproduct = $description;
  if (! $rowproduct) $rowproduct = xl('Unknown');

  if ($product != $rowproduct || $category != $rowcat) {
    if ($product) {
      // Print product total.
      if ($_POST['form_csvexport']) {
        if (! $_POST['form_details']) {
          echo '"' . display_desc($category) . '",';
          echo '"' . display_desc($product)  . '",';
          echo '"' . $productqty             . '",';
          echo '"'; bucks($producttotal); echo '"' . "\n";
        }
      }
      else {
?>
 <tr bgcolor="#ddddff">
  <td class="detail">
   <?php echo text(display_desc($catleft)); $catleft = " "; ?>
  </td>
  <td class="detail" colspan="3">
   <?php if ($_POST['form_details']) echo xlt('Total for') . ' '; echo text(display_desc($product)); ?>
  </td>
  <?php if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
  &nbsp;
  </td>
  <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right">
   <?php echo text($productqty); ?>
  </td>
  <td align="right">
   <?php text(bucks($producttotal)); ?>
  </td>
 </tr>
<?php
      } // End not csv export
    }
    $producttotal = 0;
    $productqty = 0;
    $product = $rowproduct;
    $productleft = $product;
  }

  if ($category != $rowcat) {
    if ($category) {
      // Print category total.
      if (!$_POST['form_csvexport']) {
?>

 <tr bgcolor="#ffdddd">
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail" colspan="3">
   <?php echo xlt('Total for category') . ' '; echo text(display_desc($category)); ?>
  </td>
  <?php if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
  <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right">
   <?php echo text($catqty); ?>
  </td>
  <td align="right">
   <?php text(bucks($cattotal)); ?>
  </td>
 </tr>
<?php
      } // End not csv export
    }
    $cattotal = 0;
    $catqty = 0;
    $category = $rowcat;
    $catleft = $category;
  }

  if ($_POST['form_details']) {
    if ($_POST['form_csvexport']) {
      echo '"' . display_desc($category ) . '",';
      echo '"' . display_desc($product  ) . '",';
      echo '"' . oeFormatShortDate(display_desc($transdate)) . '",';
      if($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2 ) { 
       echo '"' . $pat_name . '",'; 
      }
      if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) { 
        echo '"' . display_desc($invnumber) . '",';
      } 
      if($GLOBALS['sales_report_invoice'] == 1) { 
        echo '"' . $patient_id . '",';  
      } 
     // echo '"' . display_desc($invnumber) . '",';
      echo '"' . display_desc($qty      ) . '",';
      echo '"'; bucks($rowamount); echo '"' . "\n";
    }
    else {
?>

 <tr>
  <td class="detail">
   <?php echo text(display_desc($catleft)); $catleft = " "; ?>
  </td>
  <td class="detail">
   <?php echo text(display_desc($productleft)); $productleft = " "; ?>
  </td>
  <td>
   <?php echo text(oeFormatShortDate($transdate)); ?>
  </td>
   <?php if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
   <?php } if($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2 ) { ?>
  <td>
   <?php echo text($pat_name); ?>
  </td>
   <?php } ?>  
  <td class="detail">
  <?php if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) { ?>
   <a href='../patient_file/pos_checkout.php?ptid=<?php echo attr($patient_id); ?>&enc=<?php echo attr($encounter_id); ?>'>
   <?php echo text($invnumber); ?></a>
   <?php } 
   if($GLOBALS['sales_report_invoice'] == 1 ) { 
     echo text($patient_id); 
    } 
    ?>
  </td>
  <?php if($GLOBALS['sales_report_invoice'] == 0) {?>
  <td>
   &nbsp;
  </td>
  <?php } ?>
  <td align="right">
   <?php echo text($qty); ?>
  </td>
  <td align="right">
   <?php text(bucks($rowamount)); ?>
  </td>
 </tr>
<?php

    } // End not csv export
  } // end details
  $producttotal += $rowamount;
  $cattotal     += $rowamount;
  $grandtotal   += $rowamount;
  $productqty   += $qty;
  $catqty       += $qty;
  $grandqty     += $qty;
} // end function

  if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

  $INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;

  if (!$INTEGRATED_AR) SLConnect();

  $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
  $form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
  $form_facility  = $_POST['form_facility'];

  if ($_POST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=sales_by_item.csv");
    header("Content-Description: File Transfer");
    // CSV headers:
    if ($_POST['form_details']) {
      echo '"Category",';
      echo '"Item",';
      echo '"Date",';
      if($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2 ) { 
       echo '"Name",'; 
      }
      if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) { 
        echo '"Invoice",'; 
      } 
      if($GLOBALS['sales_report_invoice'] == 1) { 
         echo '"ID",'; 
      } 
      echo '"Qty",';
      echo '"Amount"' . "\n";
    }
    else {
      echo '"Category",';
      echo '"Item",';
      echo '"Qty",';
      echo '"Total"' . "\n";
    }
  } // end export
  else {
?>
<html>
<head>
<?php html_header_show();?>
<style type="text/css">
/* specifically include & exclude from printing */
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

<title><?php echo xlt('Sales by Item') ?></title>

<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script language="JavaScript">
 $(document).ready(function() {
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));
 });
</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Sales by Item'); ?></span>

<form method='post' action='sales_by_item.php' id='theform'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
  <td width='630px'>
    <div style='float:left'>
    <table class='text'>
        <tr>
            <td class='label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
            <?php dropdown_facility($form_facility, 'form_facility', true); ?>
            </td>
            <td class='label'>
                <?php echo xlt('From'); ?>:
            </td>
            <td>
                <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr($form_from_date) ?>'
                onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
                <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
                id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
                title='<?php echo xla('Click here to choose a date'); ?>'>
            </td>
            <td class='label'>
                <?php echo xlt('To'); ?>:
            </td>
            <td>
                <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr($form_to_date) ?>'
                onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
                <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
                id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
                title='<?php echo xla('Click here to choose a date'); ?>'>
            </td>
        </tr>
    </table>
    <table class='text'>
        <tr>
          <td class='label'>
            <?php echo xlt('Provider'); ?>:
          </td>
          <td>
            <?php
                if (acl_check('acct', 'rep_a')) {
                    // Build a drop-down list of providers.
                    $query = "select id, lname, fname from users where " .
                        "authorized = 1 order by lname, fname";
                    $res = sqlStatement($query);
                    echo "   &nbsp;<select name='form_provider'>\n";
                    echo "    <option value=''>-- " . xlt('All Providers') . " --\n";
                    while ($row = sqlFetchArray($res)) {
                        $provid = $row['id'];
                        echo "    <option value='". attr($provid) ."'";
                        if ($provid == $_REQUEST['form_provider']) echo " selected";
                        echo ">" . text($row['lname']) . ", " . text($row['fname']) . "\n";
                    }
                    echo "   </select>\n";
                    } else {
                    echo "<input type='hidden' name='form_provider' value='" . attr($_SESSION['authUserID']) . "'>";
                    }
            ?>
            &nbsp;
          </td>
          <td>
               <label><input type='checkbox' name='form_details'<?php  if ($form_details) echo ' checked'; ?>>
               <?php echo xlt('Details'); ?></label>
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
                    <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#theform").submit();'>
                    <span>
                       <?php echo xlt('Submit'); ?>
                    </span>
                    </a>

                    <?php if ($_POST['form_refresh'] || $_POST['form_csvexport']) { ?>
                    <a href='#' class='css_button' id='printbutton'>
                    <span>
                        <?php echo xlt('Print'); ?>
                    </span>
                    </a>
                    <a href='#' class='css_button' onclick='$("#form_refresh").attr("value",""); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
                    <span>
                        <?php echo xlt('CSV Export'); ?>
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

</div> <!-- end of parameters -->

<?php
 if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
?>
<div id="report_results">
<table >
 <thead>
  <th>
   <?php echo xlt('Category'); ?>
  </th>
  <th>
   <?php echo xlt('Item'); ?>
  </th>
  <th>
   <?php if ($form_details) echo xlt('Date'); ?>
  </th>
    <?php if($GLOBALS['sales_report_invoice'] == 2) {?>
  <th>
   &nbsp;
  </th>
  <?php } ?>
  <th>
   <?php 
   if($GLOBALS['sales_report_invoice'] == 0) {
    if ($form_details) echo ' ';
   ?>
  </th>
  <th>
   <?php 
   if ($form_details) echo xlt('Invoice');  } 
    if($GLOBALS['sales_report_invoice'] == 1 || $GLOBALS['sales_report_invoice'] == 2 ) { 
     if ($form_details) echo xlt('Name'); 
    } ?>
  </th>
  <th>
   <?php 
   if($GLOBALS['sales_report_invoice'] == 2) { 
    if ($form_details) echo xlt('Invoice'); 
   } 
   if($GLOBALS['sales_report_invoice'] == 1) { 
    if ($form_details) echo xlt('ID'); 
    } 
   ?>
  </th>
  <th align="right">
   <?php echo xlt('Qty'); ?>
  </th>
  <th align="right">
   <?php echo xlt('Amount'); ?>
  </th>
 </thead>
<?php
  } // end not export
}

  if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
    $from_date = $form_from_date . ' 00:00:00';
    $to_date = $form_to_date . ' 23:59:59';
    $category = "";
    $catleft = "";
    $cattotal = 0;
    $catqty = 0;
    $product = "";
    $productleft = "";
    $producttotal = 0;
    $productqty = 0;
    $grandtotal = 0;
    $grandqty = 0;

    if ($INTEGRATED_AR) {
      $sqlBindArray = array();
      $query = "SELECT b.fee, b.pid, b.encounter, b.code_type, b.code, b.units, " .
        "b.code_text, fe.date, fe.facility_id, fe.provider_id, fe.invoice_refno, lo.title " .
        "FROM billing AS b " .
        "JOIN code_types AS ct ON ct.ct_key = b.code_type " .
        "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
        "LEFT JOIN codes AS c ON c.code_type = ct.ct_id AND c.code = b.code AND c.modifier = b.modifier " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'superbill' AND lo.option_id = c.superbill " .
        "WHERE b.code_type != 'COPAY' AND b.activity = 1 AND b.fee != 0 AND " .
        "fe.date >= ? AND fe.date <= ?";
        array_push($sqlBindArray,$from_date,$to_date);
      // If a facility was specified.
      if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray,$form_facility);
      }
      if ($form_provider) {
        $query .= " AND fe.provider_id = ?";
        array_push($sqlBindArray,$form_provider);
      }
      $query .= " ORDER BY lo.title, b.code, fe.date, fe.id";
      //
      $res = sqlStatement($query,$sqlBindArray);
      while ($row = sqlFetchArray($res)) {
        thisLineItem($row['pid'], $row['encounter'],
          $row['title'], $row['code'] . ' ' . $row['code_text'],
          substr($row['date'], 0, 10), $row['units'], $row['fee'], $row['invoice_refno']);
      }
      //
      $sqlBindArray = array();
      $query = "SELECT s.sale_date, s.fee, s.quantity, s.pid, s.encounter, " .
        "d.name, fe.date, fe.facility_id, fe.provider_id, fe.invoice_refno " .
        "FROM drug_sales AS s " .
        "JOIN drugs AS d ON d.drug_id = s.drug_id " .
        "JOIN form_encounter AS fe ON " .
        "fe.pid = s.pid AND fe.encounter = s.encounter AND " .
        "fe.date >= ? AND fe.date <= ? " .
        "WHERE s.fee != 0";
        array_push($sqlBindArray,$from_date,$to_date);
      // If a facility was specified.
      if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
         array_push($sqlBindArray,$form_facility);
      }
      if ($form_provider) {
        $query .= " AND fe.provider_id = ?";
        array_push($sqlBindArray,$form_provider);
      }
      $query .= " ORDER BY d.name, fe.date, fe.id";
      //
      $res = sqlStatement($query,$sqlBindArray);
      while ($row = sqlFetchArray($res)) {
        thisLineItem($row['pid'], $row['encounter'], xl('Products'), $row['name'],
          substr($row['date'], 0, 10), $row['quantity'], $row['fee'], $row['invoice_refno']);
      }
    }
    else {
      $query = "SELECT ar.invnumber, ar.transdate, " .
        "invoice.description, invoice.qty, invoice.sellprice " .
        "FROM ar, invoice WHERE " .
        "ar.transdate >= '$from_date' AND ar.transdate <= '$to_date' " .
        "AND invoice.trans_id = ar.id " .
        "ORDER BY invoice.description, ar.transdate, ar.id";
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
        thisLineItem($patient_id, $encounter_id, '', $row['description'],
          $row['transdate'], $row['qty'], $row['sellprice'] * $row['qty']);
      } // end for
    } // end not $INTEGRATED_AR

    if ($_POST['form_csvexport']) {
      if (! $_POST['form_details']) {
        echo '"' . display_desc($product) . '",';
        echo '"' . $productqty            . '",';
        echo '"'; bucks($producttotal); echo '"' . "\n";
      }
    }
    else {
?>

 <tr bgcolor="#ddddff">
  <td class="detail">
   <?php echo text(display_desc($catleft)); $catleft = " "; ?>
  </td>
  <td class="detail" colspan="3">
   <?php if ($_POST['form_details']) echo xlt('Total for') . ' '; echo text(display_desc($product)); ?>
  </td>
  <?php if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
  <?php } ?>
  <td align="right">
   &nbsp;
  </td>
  <td align="right">
   <?php echo text($productqty); ?>
  </td>
  <td align="right">
   <?php text(bucks($producttotal)); ?>
  </td>
 </tr>

 <tr bgcolor="#ffdddd">
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail" colspan="3"><b>
   <?php echo xlt('Total for category') . ' '; echo text(display_desc($category)); ?>
  </b></td>
  <?php if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
  <?php } ?>
  <td align="right">
   &nbsp;
  </td>  
  <td align="right"><b>
   <?php echo text($catqty); ?>
  </b></td>
  <td align="right"><b>
   <?php text(bucks($cattotal)); ?>
  </b></td>
 </tr>

 <tr>
  <td class="detail" colspan="4"><b>
   <?php echo xlt('Grand Total'); ?>
  </b></td>
  <?php if($GLOBALS['sales_report_invoice'] == 0 || $GLOBALS['sales_report_invoice'] == 2) {?>
  <td>
   &nbsp;
  </td>
  <?php } ?>
  <td align="right">
   &nbsp;
  </td>  
  <td align="right"><b>
   <?php echo text($grandqty); ?>
  </b></td>
  <td align="right"><b>
   <?php text(bucks($grandtotal)); ?>
  </b></td>
 </tr>
 <?php $report_from_date = oeFormatShortDate($form_from_date)  ;
       $report_to_date = oeFormatShortDate($form_to_date)  ;
 ?>
<div align='right'><span class='title' ><?php echo xlt('Report Date'). ' '; ?><?php echo text($report_from_date);?> - <?php echo text($report_to_date);?></span></div>
<?php

    } // End not csv export
  }
  if (!$INTEGRATED_AR) SLClose();

  if (! $_POST['form_csvexport']) {
      if($_POST['form_refresh']){
?>

</table>
</div> <!-- report results -->
<?php } else { ?>
<div class='text'>
 	<?php echo xlt('Please input search criteria above, and click Submit to view results.' ); ?>
</div>
<?php } ?>

</form>

</body>

<!-- stuff for the popup calendar -->
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>

<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
<?php
  } // End not csv export
?>
