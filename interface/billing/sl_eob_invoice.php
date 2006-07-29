<?php
  // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  // This provides for manual posting of EOBs.  It is invoked from
  // sl_eob_search.php.  For automated (X12 835) remittance posting
  // see sl_eob_process.php.

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/forms.inc");
  include_once("../../library/sl_eob.inc.php");
  include_once("../../library/invoice_summary.inc.php");
  include_once("../../custom/code_types.inc.php");

  $debug = 0; // set to 1 for debugging mode

  $reasons = array(
    "", // not choosing this allows a reason with no adjustment amount
    xl("Ins adjust"),
    xl("Coll w/o"),
    xl("Pt released"),
    xl("Sm debt w/o"),
    xl("To ded'ble"),
    xl("To copay"),
    xl("Bad check"),
    xl("Bad debt"),
    xl("Discount"),
    xl("Hardship w/o"),
    xl("Ins refund"),
    xl("Pt refund"),
    xl("Ins overpaid"),
    xl("Pt overpaid")
  );

  $info_msg = "";

  // Format money for display.
  //
  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?xl('EOB Posting - Invoice','e')?></title>
<script language="JavaScript">

// An insurance radio button is selected.
function setins(istr) {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  if (ename.indexOf('[src]') < 0) continue;
  var evalue = f.elements[i].value;
  var tmp = evalue.substring(0, 4).toLowerCase();
  if (tmp >= 'ins1' && tmp <= 'ins3')
   evalue = evalue.substring(4);
  else if (evalue.substring(0, 2).toLowerCase() == 'pt')
   evalue = evalue.substring(2);
  while (evalue.substring(0, 1) == '/')
   evalue = evalue.substring(1);
  f.elements[i].value = istr + '/' + evalue;
 }
 return true;
}

// Compute an adjustment that writes off the balance:
function writeoff(code) {
 var f = document.forms[0];
 var tmp =
  f['form_line[' + code + '][bal]'].value -
  f['form_line[' + code + '][pay]'].value;
 f['form_line[' + code + '][adj]'].value = Number(tmp).toFixed(2);
 return false;
}

// Onsubmit handler.  A good excuse to write some JavaScript.
function validate(f) {
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  var pfxlen = ename.indexOf('[pay]');
  if (pfxlen < 0) continue;
  var pfx = ename.substring(0, pfxlen);
  var code = pfx.substring(pfx.indexOf('[')+1, pfxlen-1);
  if (f[pfx+'[pay]'].value || f[pfx+'[adj]'].value) {
   var srcobj = f[pfx+'[src]'];
   while (srcobj.value.length) {
    var tmp = srcobj.value.substring(srcobj.value.length - 1);
    if (tmp > ' ' && tmp != '/') break;
    srcobj.value = srcobj.value.substring(0, srcobj.value.length - 1);
   }
   var svalue = srcobj.value;
   if (! svalue) {
    alert('<?php xl('Source is missing for code ','e') ?>' + code);
    return false;
   } else {
    var tmp = svalue.substring(0, 4).toLowerCase();
    if (tmp >= 'ins1' && tmp <= 'ins3') {
     svalue = svalue.substring(4);
    } else if (svalue.substring(0, 2).toLowerCase() == 'pt') {
     svalue = svalue.substring(2);
    } else {
     alert('<?xl('Invalid or missing payer in source for code ','e')?>' + code);
     return false;
    }
    if (svalue) {
     if (svalue.substring(0, 1) != '/') {
      alert('<?xl('Missing slash after payer in source for code ','e')?>' + code);
      return false;
     }
     if (false) { // Please keep this, Oakland Clinic wants it.  -- Rod
      tmp = svalue.substring(1, 3).toLowerCase();
      if (tmp != 'nm' && tmp != 'ci' && tmp != 'cp' && tmp != 'ne' &&
          tmp != 'it' && tmp != 'pf' && tmp != 'pp' && tmp != 'ok')
      {
       alert('<?php xl('Invalid source designation "','e') ?>' + tmp + '<?php xl('" for code ','e') ?>' + code);
       return false;
      }
     } // End of OC code
    }
   }
   if (! f[pfx+'[date]'].value) {
    alert('<?xl('Date is missing for code ','e')?>' + code);
    return false;
   }
  }
  if (f[pfx+'[pay]'].value && isNaN(parseFloat(f[pfx+'[pay]'].value))) {
   alert('<?php xl('Payment value for code ','e') ?>' + code + '<?php xl(' is not a number','e') ?>');
   return false;
  }
  if (f[pfx+'[adj]'].value && isNaN(parseFloat(f[pfx+'[adj]'].value))) {
   alert('<?php xl('Adjustment value for code ','e') ?>' + code + '<?php xl(' is not a number','e') ?>');
   return false;
  }
  if (f[pfx+'[adj]'].value && ! f[pfx+'[reason]'].value) {
   alert('<?php xl('Please select an adjustment reason for code ','e') ?>' + code);
   return false;
  }
  // TBD: validate the date format
 }
 return true;
}

</script>
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<?php
  $trans_id = $_GET['id'];
  if (! $trans_id) die(xl("You cannot access this page directly."));

  slInitialize();

  if ($_POST['form_save'] || $_POST['form_cancel']) {
    if ($_POST['form_save']) {
      if ($debug) {
        echo xl("This module is in test mode. The database will not be changed.",'','<p><b>',"</b><p>\n");
      }
      $paytotal = 0;
      foreach ($_POST['form_line'] as $code => $cdata) {
        $thissrc  = trim($cdata['src']);
        $thisdate = trim($cdata['date']);
        $thispay  = trim($cdata['pay']);
        $thisadj  = trim($cdata['adj']);
        $thisins  = trim($cdata['ins']);
        $reason   = trim($cdata['reason']);
        if (strpos(strtolower($reason), 'ins') !== false)
          $reason .= ' ' . $_POST['form_insurance'];
        if (! $thisins) $thisins = 0;

        if ($thispay) {
          slPostPayment($trans_id, $thispay, $thisdate, $thissrc, $code, $thisins, $debug);
          $paytotal += $thispay;
        }

        // Be sure to record adjustment reasons even for zero adjustments.
        if ($thisadj || $reason) {
          slPostAdjustment($trans_id, $thisadj, $thisdate, $thissrc, $code, $thisins, $reason, $debug);
        }
      }

      $form_duedate = fixDate($_POST['form_duedate']);
      $form_notes = trim($_POST['form_notes']);

      // Maintain the list of insurances that we mark as finished.
      // We use the "Ship Via" field of the invoice to hold these.
      //
      $form_eobs = "";
      foreach (array('Ins1', 'Ins2', 'Ins3') as $value) {
        if ($_POST["form_done_$value"]) {
          if ($form_eobs) $form_eobs .= ","; else $form_eobs = "Done: ";
          $form_eobs .= $value;
        }
      }

      $query = "UPDATE ar SET duedate = '$form_duedate', notes = '$form_notes', " .
        "shipvia = '$form_eobs' WHERE id = $trans_id";

      if ($debug) {
        echo $query . "<br>\n";
      } else {
        SLQuery($query);
        if ($sl_err) die($sl_err);
      }
      if ($_POST['form_secondary']) {
        slSetupSecondary($trans_id, $debug);
      }
      echo "<script language='JavaScript'>\n";
      echo " var tmp = opener.document.forms[0].form_amount.value - $paytotal;\n";
      echo " opener.document.forms[0].form_amount.value = Number(tmp).toFixed(2);\n";
    } else {
      echo "<script language='JavaScript'>\n";
    }
    if ($info_msg) echo " alert('$info_msg');\n";
    if (! $debug) echo " window.close();\n";
    echo "</script></body></html>\n";
    SLClose();
    exit();
  }

  // Get invoice data into $arrow.
  $arres = SLQuery("select ar.*, customer.name, employee.name as doctor " .
    "from ar, customer, employee where ar.id = $trans_id and " .
    "customer.id = ar.customer_id and employee.id = ar.employee_id");
  if ($sl_err) die($sl_err);
  $arrow = SLGetRow($arres, 0);
  if (! $arrow) die(xl("There is no match for invoice id = ") . $trans_id);

  // Determine the date of service.  An 8-digit encounter number is
  // presumed to be a date of service imported during conversion.
  // Otherwise look it up in the form_encounter table.
  //
  $svcdate = "";
  list($trash, $encounter) = explode(".", $arrow['invnumber']);
  if (strlen($encounter) == 8) {
    $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
      "-" . substr($encounter, 6, 2);
  }
  else if ($encounter) {
    $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
      "encounter = $encounter");
    $svcdate = substr($tmp['date'], 0, 10);
  }

  // Get invoice charge details.
  $codes = get_invoice_summary($trans_id, true);
?>
<center>

<form method='post' action='sl_eob_invoice.php?id=<?php echo $trans_id ?>'
 onsubmit='return validate(this)'>

<table border='0' cellpadding='3'>
 <tr>
  <td>
   <?xl('Patient:','e')?>
  </td>
  <td>
   <?echo $arrow['name'] ?>
  </td>
  <td colspan="2" rowspan="3">
   <textarea name="form_notes" cols="50" style="height:100%"><?echo $arrow['notes'] ?></textarea>
  </td>
 </tr>
 <tr>
  <td>
   <?xl('Provider:','e')?>
  </td>
  <td>
   <?echo $arrow['doctor'] ?>
  </td>
 </tr>
 <tr>
  <td>
   <?xl('Invoice:','e')?>
  </td>
  <td>
   <?echo $arrow['invnumber'] ?>
  </td>
 </tr>

 <tr>
  <td>
   <?xl('Svc Date:','e')?>
  </td>
  <td>
   <?echo $svcdate ?>
  </td>
  <td colspan="2">
   <!-- <?echo $arrow['shipvia'] ?> -->
   <?xl('Done with:','e','',"&nbsp")?>;
<?php
 // Write a checkbox for each insurance.  It is to be checked when
 // we no longer expect any payments from that company for the claim.
 // The information is stored in the 'shipvia' field of the invoice.
 //
 $insgot  = strtolower($arrow['notes']);
 $insdone = strtolower($arrow['shipvia']);
 foreach (array('Ins1', 'Ins2', 'Ins3') as $value) {
  $lcvalue = strtolower($value);
  $checked  = (strpos($insdone, $lcvalue) === false) ? "" : " checked";
  if (strpos($insgot, $lcvalue) !== false) {
   echo "   <input type='checkbox' name='form_done_$value' value='1'$checked />$value&nbsp;\n";
  }
 }
?>
  </td>
 </tr>

 <tr>
  <td>
   <?php xl('Bill Date:','e') ?>
  </td>
  <td>
   <?echo $arrow['transdate'] ?>
  </td>
  <td colspan="2">
   <?xl('Now posting for:','e','',"&nbsp")?>;
   <input type='radio' name='form_insurance' value='Ins1' onclick='setins("Ins1")' checked /><?xl('Ins1','e')?>&nbsp;
   <input type='radio' name='form_insurance' value='Ins2' onclick='setins("Ins2")' /><?xl('Ins2','e')?>&nbsp;
   <input type='radio' name='form_insurance' value='Ins3' onclick='setins("Ins3")' /><?xl('Ins3','e')?>&nbsp;
   <input type='radio' name='form_insurance' value='Pt'   onclick='setins("Pt")'   /><?xl('Patient','e')?>
   <input type='hidden' name='form_eobs' value='<?echo addslashes($arrow['shipvia']) ?>' />
  </td>
 </tr>
 <tr>
  <td>
   <?xl('Due Date:','e')?>
  </td>
  <td>
   <input type='text' name='form_duedate' size='10' value='<?echo $arrow['duedate'] ?>'
    title='<?xl('Due date mm/dd/yyyy or yyyy-mm-dd','e')?>'>
  </td>
  <td colspan="2">
   <input type="checkbox" name="form_secondary" value="1"> <?xl('Needs secondary billing','e')?>
   &nbsp;&nbsp;
   <input type='submit' name='form_save' value='<?xl('Save','e')?>'>
   &nbsp;
   <input type='button' value='<?xl('Cancel','e')?>' onclick='window.close()'>
  </td>
 </tr>
 <tr>
  <td height="1">
  </td>
 </tr>
</table>

<table border='0' cellpadding='2' cellspacing='0' width='98%'>

 <tr bgcolor="#cccccc">
  <td class="dehead">
   <?xl('Code','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Charge','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Balance','e')?>&nbsp;
  </td>
  <td class="dehead">
   <?xl('Source','e')?>
  </td>
  <td class="dehead">
   <?xl('Date','e')?>
  </td>
  <td class="dehead">
   <?xl('Pay','e')?>
  </td>
  <td class="dehead">
   <?xl('Adjust','e')?>
  </td>
  <td class="dehead">
   <?xl('Reason','e')?>
  </td>
 </tr>
<?php
  $encount = 0;
  foreach ($codes as $code => $cdata) {
   ++$encount;
   $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
   $dispcode = $code;
   // this sorts the details more or less chronologically:
   ksort($cdata['dtl']);
   foreach ($cdata['dtl'] as $dkey => $ddata) {
    $ddate = substr($dkey, 0, 10);
    if (preg_match('/^(\d\d\d\d)(\d\d)(\d\d)\s*$/', $ddate, $matches)) {
     $ddate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
    }
    $tmpchg = "";
    $tmpadj = "";
    if ($ddata['chg'] > 0)
     $tmpchg = $ddata['chg'];
    else if ($ddata['chg'] < 0)
     $tmpadj = 0 - $ddata['chg'];
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td class="detail">
   <?php echo $dispcode; $dispcode = "" ?>
  </td>
  <td class="detail" align="right">
   <?php bucks($tmpchg) ?>
  </td>
  <td class="detail" align="right">
   &nbsp;
  </td>
  <td class="detail">
   <?php echo $ddata['src'] ?>
  </td>
  <td class="detail">
   <?php echo $ddate ?>
  </td>
  <td class="detail">
   <?php bucks($ddata['pmt']) ?>
  </td>
  <td class="detail">
   <?php bucks($tmpadj) ?>
  </td>
  <td class="detail">
   <?php echo $ddata['rsn'] ?>
  </td>
 </tr>
<?php
   } // end of prior detail line
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td class="detail">
   <?php echo $dispcode; $dispcode = "" ?>
  </td>
  <td class="detail" align="right">
   &nbsp;
  </td>
  <td class="detail" align="right">
   <input type="hidden" name="form_line[<?php echo $code ?>][bal]" value="<?php bucks($cdata['bal']) ?>">
   <input type="hidden" name="form_line[<?php echo $code ?>][ins]" value="<?php echo $cdata['ins'] ?>">
   <?php printf("%.2f", $cdata['bal']) ?>&nbsp;
  </td>
  <td class="detail">
   <input type="text" name="form_line[<?php echo $code ?>][src]" size="10"
    style="background-color:<?php echo $bgcolor ?>"
<?php if (false) { ?>
    title="NM=notmet, CI=coins, CP=copay, NE=notelig, IT=insterm, PF=ptfull, PP=ptpart"
<?php } ?>
   />
  </td>
  <td class="detail">
   <input type="text" name="form_line[<?php echo $code ?>][date]" size="10" style="background-color:<?php echo $bgcolor ?>" />
  </td>
  <td class="detail">
   <input type="text" name="form_line[<?php echo $code ?>][pay]" size="10" style="background-color:<?php echo $bgcolor ?>" />
  </td>
  <td class="detail">
   <input type="text" name="form_line[<?php echo $code ?>][adj]" size="10" style="background-color:<?php echo $bgcolor ?>" />
   &nbsp; <a href="" onclick="return writeoff('<?php echo $code ?>')">W</a>
  </td>
  <td class="detail">
   <select name="form_line[<?php echo $code ?>][reason]" style="background-color:<?php echo $bgcolor ?>">
<?php
 foreach ($reasons as $value) {
  echo "    <option value=\"$value\">$value</option>\n";
 }
?>
   </select>
  </td>
 </tr>
<?php
  } // end of code
  SLClose();
?>

</table>
</form>
</center>
<script language="JavaScript">
 var f1 = opener.document.forms[0];
 var f2 = document.forms[0];
<?php
  foreach ($codes as $code => $cdata) {
    echo " f2['form_line[$code][src]'].value  = f1.form_source.value;\n";
    echo " f2['form_line[$code][date]'].value = f1.form_paydate.value;\n";
  }
?>
 setins("Ins1");
</script>
</body>
</html>
