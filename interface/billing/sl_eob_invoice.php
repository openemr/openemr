<?php
/**
 * This provides for manual posting of EOBs.  It is invoked from
 * sl_eob_search.php.  For automated (X12 835) remittance posting
 * see sl_eob_process.php.
 *
 * Copyright (C) 2005-2016 Rod Roark <rod@sunsetsystems.com>
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
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */
    use OpenEMR\Core\Header;
    
    require_once("../globals.php");
    require_once("$srcdir/log.inc");
    require_once("$srcdir/patient.inc");
    require_once("$srcdir/forms.inc");
    require_once("$srcdir/sl_eob.inc.php");
    require_once("$srcdir/invoice_summary.inc.php");
    require_once("../../custom/code_types.inc.php");

    $debug = 0; // set to 1 for debugging mode


  // If we permit deletion of transactions.  Might change this later.
  $ALLOW_DELETE = true;

  $info_msg = "";

  // Format money for display.
  //
function bucks($amount)
{
    if ($amount) {
        printf("%.2f", $amount);
    }
}

  // Delete rows, with logging, for the specified table using the
  // specified WHERE clause.  Borrowed from deleter.php.
  //
function row_delete($table, $where)
{
    $tres = sqlStatement("SELECT * FROM $table WHERE $where");
    $count = 0;
    while ($trow = sqlFetchArray($tres)) {
        $logstring = "";
        foreach ($trow as $key => $value) {
            if (! $value || $value == '0000-00-00 00:00:00') {
                continue;
            }

            if ($logstring) {
                $logstring .= " ";
            }

            $logstring .= $key . "='" . addslashes($value) . "'";
        }

        newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
        ++$count;
    }

    if ($count) {
        $query = "DELETE FROM $table WHERE $where";
        echo $query . "<br>\n";
        sqlStatement($query);
    }
}
?>
<html>
<head>
<?php Header::setupHeader(['datetime-picker']);?>
    <title><?php xl('EOB Posting - Invoice', 'e')?></title>
<script language="JavaScript">

// An insurance radio button is selected.
function setins(istr) {
 return true;
}

// Compute an adjustment that writes off the balance:
function writeoff(code) {
 var f = document.forms[0];
 var belement = f['form_line[' + code + '][bal]'];
 var pelement = f['form_line[' + code + '][pay]'];
 var aelement = f['form_line[' + code + '][adj]'];
 var relement = f['form_line[' + code + '][reason]'];
 var tmp = belement.value - pelement.value;
 aelement.value = Number(tmp).toFixed(2);
 if (aelement.value && ! relement.value) relement.selectedIndex = 1;
 return false;
}

// Onsubmit handler.  A good excuse to write some JavaScript.
function validate(f) {
    var delcount = 0; var allempty = true;
    for (var i = 0; i < f.elements.length; ++i) {
        var ename = f.elements[i].name;
        // Count deletes.
        if (ename.substring(0, 9) == 'form_del[') {
            if (f.elements[i].checked) ++delcount;
            continue;
        }
        var pfxlen = ename.indexOf('[pay]');
        if (pfxlen < 0) continue;
        var pfx = ename.substring(0, pfxlen);
        var code = pfx.substring(pfx.indexOf('[') + 1, pfxlen - 1);
        if (f[pfx + '[pay]'].value || f[pfx + '[adj]'].value) {
            allempty = false;
            if (!f[pfx + '[date]'].value) {
                alert('<?php xl('Date is missing for code ', 'e')?>' + code);
                return false;
            }
        }
        if (f[pfx + '[pay]'].value && isNaN(parseFloat(f[pfx + '[pay]'].value))) {
            alert('<?php xl('Payment value for code ', 'e') ?>' + code + '<?php xl(' is not a number', 'e') ?>');
            return false;
        }
        if (f[pfx + '[adj]'].value && isNaN(parseFloat(f[pfx + '[adj]'].value))) {
            alert('<?php xl('Adjustment value for code ', 'e') ?>' + code + '<?php xl(' is not a number', 'e') ?>');
            return false;
        }
        if (f[pfx + '[adj]'].value && !f[pfx + '[reason]'].value) {
            alert('<?php xl('Please select an adjustment reason for code ', 'e') ?>' + code);
            return false;
        }
        // TBD: validate the date format
    }
    // Check if save is clicked with nothing to post.
    if (allempty && delcount === 0) {
        alert('<?php xl('Nothing to Post! Please review entries or use Cancel to exit transaction', 'e')?>');
        return false;
    }
    // Demand confirmation if deleting anything.
    if (delcount > 0) {
        if (!confirm('<?php echo xl('Really delete'); ?> ' + delcount +
                ' <?php echo xl('transactions'); ?>?' +
                ' <?php echo xl('This action will be logged'); ?>!')
        ) return false;
    }
    return true;
}

<!-- Get current date -->

function getFormattedToday()
{
   var today = new Date();
   var dd = today.getDate();
   var mm = today.getMonth()+1; //January is 0!
   var yyyy = today.getFullYear();
   if(dd<10){dd='0'+dd}
   if(mm<10){mm='0'+mm}

   return (yyyy + '-' + mm + '-' + dd);
}

<!-- Update Payment Fields -->

function updateFields(payField, adjField, balField, coPayField, isFirstProcCode)
{
   var payAmount = 0.0;
   var adjAmount = 0.0;
   var balAmount = 0.0;
   var coPayAmount = 0.0;

   // coPayFiled will be null if there is no co-pay entry in the fee sheet
   if (coPayField)
      coPayAmount = coPayField.value;

   // if balance field is 0.00, its value comes back as null, so check for nul-ness first
   if (balField)
      balAmount = (balField.value) ? balField.value : 0;
   if (payField)
      payAmount = (payField.value) ? payField.value : 0;

   //alert('balance = >' + balAmount +'<  payAmount = ' + payAmount + '  copay = ' + coPayAmount + '  isFirstProcCode = ' + isFirstProcCode);

   // subtract the co-pay only from the first procedure code
   if (isFirstProcCode == 1)
      balAmount = parseFloat(balAmount) + parseFloat(coPayAmount);

   adjAmount = balAmount - payAmount;

   // Assign rounded adjustment value back to TextField
   adjField.value = adjAmount = Math.round(adjAmount*100)/100;
}
$(document).ready(function() {
   $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
   });
});

</script>
<style>
    @media only screen and (max-width: 768px) {
       [class*="col-"] {
       width: 100%;
       text-align:left!Important;
    }
    }
    .table {
       margin: auto;
       width: 90% !important; 
    }
    @media (min-width: 992px){
        .modal-lg {
            width: 1000px !Important;
        }
    }
    /*.modalclass {
        overflow-x: hidden !Important;
    }
    .oe-ckbox-label{
        padding-left: 30px;
        font-weight: 500;
    }*/
</style>
</head>
<body>
<?php
        $trans_id = 0 + $_GET['id'];
if (!$trans_id) {
    die(xl("You cannot access this page directly."));
}

        // A/R case, $trans_id matches form_encounter.id.
        $ferow = sqlQuery("SELECT e.*, p.fname, p.mname, p.lname " . "FROM form_encounter AS e, patient_data AS p WHERE " . "e.id = '$trans_id' AND p.pid = e.pid");
if (empty($ferow)) {
    die("There is no encounter with form_encounter.id = '$trans_id'.");
}
        $patient_id        = 0 + $ferow['pid'];
        $encounter_id      = 0 + $ferow['encounter'];
        $svcdate           = substr($ferow['date'], 0, 10);
        $form_payer_id     = 0 + $_POST['form_payer_id'];
        $form_reference    = $_POST['form_reference'];
        $form_check_date   = fixDate($_POST['form_check_date'], date('Y-m-d'));
        $form_deposit_date = fixDate($_POST['form_deposit_date'], $form_check_date);
        $form_pay_total    = 0 + $_POST['form_pay_total'];

        $payer_type = 0;
if (preg_match('/^Ins(\d)/i', $_POST['form_insurance'], $matches)) {
    $payer_type = $matches[1];
}

if ($_POST['form_save'] || $_POST['form_cancel']) {
    if ($_POST['form_save']) {
        if ($debug) {
            echo xl("This module is in test mode. The database will not be changed.", '', '<p><b>', "</b><p>\n");
        }
                
        $session_id = arGetSession($form_payer_id, $form_reference, $form_check_date, $form_deposit_date, $form_pay_total);
        // The sl_eob_search page needs its invoice links modified to invoke
        // javascript to load form parms for all the above and submit.
        // At the same time that page would be modified to work off the
        // openemr database exclusively.
        // And back to the sl_eob_invoice page, I think we may want to move
        // the source input fields from row level to header level.
                
        // Handle deletes. row_delete() is borrowed from deleter.php.
        if ($ALLOW_DELETE && !$debug) {
            if (is_array($_POST['form_del'])) {
                foreach ($_POST['form_del'] as $arseq => $dummy) {
                    row_delete("ar_activity", "pid = '$patient_id' AND " . "encounter = '$encounter_id' AND sequence_no = '$arseq'");
                }
            }
        }
                
        $paytotal = 0;
        foreach ($_POST['form_line'] as $code => $cdata) {
            $thispay      = trim($cdata['pay']);
            $thisadj      = trim($cdata['adj']);
            $thisins      = trim($cdata['ins']);
            $thiscodetype = trim($cdata['code_type']);
            $reason       = strip_escape_custom($cdata['reason']);
                    
            // Get the adjustment reason type.  Possible values are:
            // 1 = Charge adjustment
            // 2 = Coinsurance
            // 3 = Deductible
            // 4 = Other pt resp
            // 5 = Comment
            $reason_type = '1';
            if ($reason) {
                $tmp = sqlQuery("SELECT option_value FROM list_options WHERE " . "list_id = 'adjreason' AND activity = 1 AND " . "option_id = '" . add_escape_custom($reason) . "'");
                if (empty($tmp['option_value'])) {
                    // This should not happen but if it does, apply old logic.
                    if (preg_match("/To copay/", $reason)) {
                        $reason_type = 2;
                    } elseif (preg_match("/To ded'ble/", $reason)) {
                        $reason_type = 3;
                    }
                    $info_msg .= xl("No adjustment reason type found for") . " \"$reason\". ";
                } else {
                    $reason_type = $tmp['option_value'];
                }
            }
                    
            if (!$thisins) {
                $thisins = 0;
            }
                    
            if ($thispay) {
                arPostPayment($patient_id, $encounter_id, $session_id, $thispay, $code, $payer_type, '', $debug, '', $thiscodetype);
                $paytotal += $thispay;
            }
                    
            // Be sure to record adjustment reasons, even for zero adjustments if
            // they happen to be comments.
            if ($thisadj || ($reason && $reason_type == 5)) {
                // "To copay" and "To ded'ble" need to become a comment in a zero
                // adjustment, formatted just like sl_eob_process.php.
                if ($reason_type == '2') {
                    $reason  = $_POST['form_insurance'] . " coins: $thisadj";
                    $thisadj = 0;
                } elseif ($reason_type == '3') {
                    $reason  = $_POST['form_insurance'] . " dedbl: $thisadj";
                    $thisadj = 0;
                } elseif ($reason_type == '4') {
                    $reason  = $_POST['form_insurance'] . " ptresp: $thisadj $reason";
                    $thisadj = 0;
                } elseif ($reason_type == '5') {
                    $reason  = $_POST['form_insurance'] . " note: $thisadj $reason";
                    $thisadj = 0;
                } else {
                    // An adjustment reason including "Ins" is assumed to be assigned by
                    // insurance, and in that case we identify which one by appending
                    // Ins1, Ins2 or Ins3.
                    if (strpos(strtolower($reason), 'ins') !== false) {
                        $reason .= ' ' . $_POST['form_insurance'];
                    }
                }
                arPostAdjustment($patient_id, $encounter_id, $session_id, $thisadj, $code, $payer_type, $reason, $debug, '', $thiscodetype);
            }
        }
                
        // Maintain which insurances are marked as finished.
                
        $form_done       = 0 + $_POST['form_done'];
        $form_stmt_count = 0 + $_POST['form_stmt_count'];
        sqlStatement("UPDATE form_encounter " . "SET last_level_closed = $form_done, " . "stmt_count = $form_stmt_count WHERE " . "pid = '$patient_id' AND encounter = '$encounter_id'");
                
        if ($_POST['form_secondary']) {
            arSetupSecondary($patient_id, $encounter_id, $debug);
        }
        echo "<script language='JavaScript'>\n";
        echo " if (opener.document.forms[0] !== undefined) {\n";
        echo "   if (opener.document.forms[0].form_amount) {\n";
        echo "     var tmp = opener.document.forms[0].form_amount.value - $paytotal;\n";
        echo "     opener.document.forms[0].form_amount.value = Number(tmp).toFixed(2);\n";
        echo "   }\n";
        echo " }\n";
    } else {
        echo "<script language='JavaScript'>\n";
    }
    if ($info_msg) {
        echo " alert('" . addslashes($info_msg) . "');\n";
    }
    if (!$debug) {
        echo " window.close();\n";
    }
    echo "</script></body></html>\n";
    exit();
}

        // Get invoice charge details.
        $codes = ar_get_invoice_summary($patient_id, $encounter_id, true);

        $pdrow = sqlQuery("select billing_note " . "from patient_data where pid = '$patient_id' limit 1");
    ?>

    <div class = "container">
        <div class="row">
            <div class="page-header">
                <h2><?php echo xlt('EOB Invoice'); ?></h2>
            </div>
        </div>
        <div class="row">
            <form action='sl_eob_invoice.php?id=<?php echo $trans_id ?>' method='post' onsubmit='return validate(this)'>
                <fieldset>
                    <legend><?php echo xlt('Invoice Particulars'); ?></legend>
                    <div class="col-xs-12 oe-custom-line">
                        <div class="col-xs-3">
                            <label class="control-label" for="form_name"><?php echo xlt('Patient'); ?>:</label>
                            <input type="text" class="form-control" id='form_name' name='form_name'   value="<?php echo $ferow['fname'] . ' ' . $ferow['mname'] . ' ' . $ferow['lname']; ?>" disabled>
                        </div>
                        <div class="col-xs-3">
                            <label class="control-label" for="form_provider"><?php echo xlt('Provider'); ?>:</label>
                            <?php
                                $tmp = sqlQuery("SELECT fname, mname, lname " .
                                "FROM users WHERE id = " . $ferow['provider_id']);
                                $provider = text($tmp['fname']) . ' ' . text($tmp['mname']) . ' ' . text($tmp['lname']);
                                $tmp = sqlQuery("SELECT bill_date FROM billing WHERE " .
                                "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
                                "activity = 1 ORDER BY fee DESC, id ASC LIMIT 1");
                                $billdate = substr(($tmp['bill_date'] . "Not Billed"), 0, 10);
                            ?>
                            <input type="text" class="form-control" id='form_provider' name='form_provider'   value="<?php echo $provider; ?>" disabled>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_invoice"><?php echo xlt('Invoice'); ?>:</label>
                            <input type="text" class="form-control" id='form_provider' name='form_provider'   value='<?php echo $patient_id.".".$encounter_id; ?>' disabled >
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="svc_date"><?php echo xlt('Svc Date'); ?>:</label>
                            <input type="text" class="form-control" id='svc_date' name='form_provider'   value='<?php echo $svcdate; ?>' disabled >
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="insurance_name"><?php echo xlt('Insurance'); ?>:</label>
                            <?php
                            for ($i = 1; $i <= 3; ++$i) {
                                $payerid = arGetPayerID($patient_id, $svcdate, $i);
                                if ($payerid) {
                                    $tmp = sqlQuery("SELECT name FROM insurance_companies WHERE id = $payerid");
                                    $insurance .= "$i: " . $tmp['name'] . "\n";
                                }
                            }
                            ?>
                            <textarea  name="insurance_name" id="insurance_name" class="form-control" cols="5" rows="2" readonly ><?php echo $insurance; ?></textarea>
                        </div>
                    </div>
                    <div class="col-xs-12 oe-custom-line">
                        <div class="col-xs-3">
                            <label class="control-label" for="form_stmt_count"><?php echo xlt('Statements Sent'); ?>:</label>
                            <input type='text' name='form_stmt_count' id='form_stmt_count' class="form-control" value='<?php echo (0 + $ferow['stmt_count']) ?>' />
                        </div>
                        <div class="col-xs-3">
                            <label class="control-label" for="form_reference"><?php echo xlt('Check/EOB No.'); ?>:</label>
                            <input type='text' name='form_reference' id='form_reference' class="form-control" value='' />
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_check_date"><?php echo xlt('Check/EOB Date'); ?>:</label>
                            <input type='text' name='form_check_date'  class='form-control datepicker' value='' />
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="form_deposit_date"><?php echo xlt('Deposit Date'); ?>:</label>
                            <input type='text' name='form_deposit_date' id='form_deposit_date' class='form-control datepicker' value='' />
                            <input type='hidden' name='form_payer_id' value='' />
                            <input type='hidden' name='form_orig_reference' value='' />
                            <input type='hidden' name='form_orig_check_date' value='' />
                            <input type='hidden' name='form_orig_deposit_date' value='' />
                            <input type='hidden' name='form_pay_total' value='' />
                        </div>
                    </div>
                    <div class="col-xs-12 oe-custom-line">
                        <div class="col-xs-3">
                            <label class="control-label" for="type_code"><?php echo xlt('Now posting for'); ?>:</label>
                            <div style="padding-left:15px">
                                <?php
                                  // TBD: check the first not-done-with insurance, not always Ins1!
                                ?> 
                                    <label class="radio-inline">
                                      <input checked name='form_insurance' onclick='setins("Ins1")' type='radio' value='Ins1'><?php xl('Ins1', 'e')?>
                                    </label>
                                    <label class="radio-inline">
                                      <input name='form_insurance' onclick='setins("Ins2")' type='radio' value='Ins2'><?php xl('Ins2', 'e')?>
                                    </label>
                                    <label class="radio-inline">
                                      <input name='form_insurance' onclick='setins("Ins3")' type='radio' value='Ins3'><?php xl('Ins3', 'e')?>
                                    </label>
                                    <label class="radio-inline">
                                      <input name='form_insurance' onclick='setins("Pt")' type='radio' value='Pt'><?php xl('Patient', 'e')?> 
                                    </label>
                                <?php
                                  // TBD: I think the following is unused and can be removed.
                                ?> 
                                <input name='form_eobs' type='hidden'value='<?php echo addslashes($arrow['shipvia']) ?>'/>
                            </div>
                        </div>
                        <div class="col-xs-3">
                            <label class="control-label" for=""><?php echo xlt('Done with'); ?>:</label>
                            <div style="padding-left:15px">
                                <?php
                                  // Write a checkbox for each insurance.  It is to be checked when
                                  // we no longer expect any payments from that company for the claim.
                                    $last_level_closed = 0 + $ferow['last_level_closed'];
                                foreach (array(0 => 'None', 1 => 'Ins1', 2 => 'Ins2', 3 => 'Ins3') as $key => $value) {
                                    if ($key && !arGetPayerID($patient_id, $svcdate, $key)) {
                                        continue;
                                    }
                                    $checked = ($last_level_closed == $key) ? " checked" : "";
                                    echo "<label class='radio-inline'>";
                                    echo "<input type='radio' name='form_done' value='$key'$checked />$value";
                                    echo "</label>";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-xs-3 clearfix">
                            <label class="control-label" for="form_secondary"><?php echo xlt('Secondary billing'); ?>:</label>
                            <label class="checkbox" style="margin-left:15px">
                                <input name="form_secondary" type="checkbox" value="1"><span style="font-weight:400"><?php xl('Needs secondary billing', 'e')?></span>
                            </label>
                        </div>
                    </div>
                    
                </fieldset>
                <fieldset>
                    <legend><?php echo xlt('Invoice Details'); ?></legend>
                    <div class="table-responsive">
                        <table class= "table">
                            <thead>
                                <tr bgcolor="#CCCCCC">
                                    <th class="dehead"><?php xl('Code', 'e')?></th>
                                    <th align="right" class="dehead"><?php xl('Charge', 'e')?></th>
                                    <th align="right" class="dehead"><?php xl('Balance', 'e')?>&nbsp;</th>
                                    <th class="dehead"><?php xl('By/Source', 'e')?></th>
                                    <th class="dehead"><?php xl('Date', 'e')?></th>
                                    <th class="dehead"><?php xl('Pay', 'e')?></th>
                                    <th class="dehead"><?php xl('Adjust', 'e')?></th>
                                    <th class="dehead"><?php xl('Reason', 'e')?></th>
                                    <?php
                                    if ($ALLOW_DELETE) { ?>
                                    <th class="dehead"><?php xl('Del', 'e')?></th>
                                    <?php
                                    } ?>
                                </tr>
                            </thead>
                                <?php
                                    $firstProcCodeIndex = -1;
                                    $encount = 0;
                                foreach ($codes as $code => $cdata) {
                                    ++$encount;
                                    $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
                                    $dispcode = $code;
                                    
                                    // remember the index of the first entry whose code is not "CO-PAY", i.e. it's a legitimate proc code
                                    if ($firstProcCodeIndex == -1 && strcmp($code, "CO-PAY") !=0) {
                                        $firstProcCodeIndex = $encount;
                                    }

                                    // this sorts the details more or less chronologically:
                                    ksort($cdata['dtl']);
                                    foreach ($cdata['dtl'] as $dkey => $ddata) {
                                        $ddate = substr($dkey, 0, 10);
                                        if (preg_match('/^(\d\d\d\d)(\d\d)(\d\d)\s*$/', $ddate, $matches)) {
                                            $ddate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
                                        }
                                        $tmpchg = "";
                                        $tmpadj = "";
                                        /*****************************************************************
                                        if ($ddata['chg'] > 0)
                                         $tmpchg = $ddata['chg'];
                                        else if ($ddata['chg'] < 0)
                                         $tmpadj = 0 - $ddata['chg'];
                                        *****************************************************************/
                                        if ($ddata['chg'] != 0) {
                                            if (isset($ddata['rsn'])) {
                                                $tmpadj = 0 - $ddata['chg'];
                                            } else {
                                                $tmpchg = $ddata['chg'];
                                            }
                                        }
                                    ?>
                                <tr bgcolor='<?php echo $bgcolor ?>'>
                                    <td class="detail"><?php echo $dispcode; $dispcode = "" ?></td>
                                    <td align="right" class="detail"><?php bucks($tmpchg) ?></td>
                                    <td align="right" class="detail">&nbsp;</td>
                                    <td class="detail">
                                        <?php
                                        if (isset($ddata['plv'])) {
                                            if (!$ddata['plv']) {
                                                echo 'Pt/';
                                            } else {
                                                echo 'Ins' . $ddata['plv'] . '/';
                                            }
                                        }
                                            echo $ddata['src'];
                                        ?>
                                    </td>
                                    <td class="detail"><?php echo $ddate ?></td>
                                    <td class="detail"><?php bucks($ddata['pmt']) ?></td>
                                    <td class="detail"><?php bucks($tmpadj) ?></td>
                                    <td class="detail"><?php echo $ddata['rsn'] ?></td>
                                        <?php
                                        if ($ALLOW_DELETE) { ?>
                                    <td class="detail">
                                        <?php
                                        if (!empty($ddata['arseq'])) { ?>
                                                <input name="form_del[<?php echo $ddata['arseq']; ?>]" type="checkbox">
                                            <?php
                                        } else {
                                            ?> &nbsp;
                                            <?php
                                        } ?>
                                    </td>
                                        <?php
                                        } ?>
                                </tr><?php
                                    } // end of prior detail line
                            ?>
                            <tr bgcolor='<?php echo $bgcolor ?>'>
                            <td class="detail"><?php echo $dispcode; $dispcode = "" ?></td>
                            <td align="right" class="detail">&nbsp;</td>
                            <td align="right" class="detail"><input name="form_line[<?php echo $code ?>][bal]" type="hidden" value="<?php bucks($cdata['bal']) ?>"> <input name="form_line[<?php echo $code ?>][ins]" type="hidden" value="<?php echo $cdata['ins'] ?>"> <input name="form_line[<?php echo $code ?>][code_type]" type="hidden" value="<?php echo $cdata['code_type'] ?>"> <?php printf("%.2f", $cdata['bal']) ?>&nbsp;</td>
                            <td class="detail"></td>
                            <td class="detail"></td>
                            <td class="detail"><input name="form_line[<?php echo $code ?>][pay]" onkeyup="updateFields(document.forms[0]['form_line[<?php echo $code ?>][pay]'], document.forms[0]['form_line[<?php echo $code ?>][adj]'], document.forms[0]['form_line[<?php echo $code ?>][bal]'], document.forms[0]['form_line[CO-PAY][bal]'], <?php echo ($firstProcCodeIndex == $encount) ? 1 : 0 ?>)" size="10" style="background-color:<?php echo $bgcolor ?>" type="text"></td>
                            <td class="detail">
                                <input name="form_line[<?php echo $code ?>][adj]" size="10" style="background-color:<?php echo $bgcolor ?>" type="text" value='<?php echo $totalAdjAmount ?>'> &nbsp; <a href="" onclick="return writeoff('<?php echo $code ?>')">W</a>
                            </td>
                            <td class="detail">
                                <select name="form_line[<?php echo $code ?>][reason]" style="background-color:<?php echo $bgcolor ?>">
                                    <?php
                                    // Adjustment reasons are now taken from the list_options table.
                                    echo "    <option value=''></option>\n";
                                        $ores = sqlStatement("SELECT option_id, title, is_default FROM list_options " .
                                        "WHERE list_id = 'adjreason' AND activity = 1 ORDER BY seq, title");
                                    while ($orow = sqlFetchArray($ores)) {
                                        echo "    <option value='" . htmlspecialchars($orow['option_id'], ENT_QUOTES) . "'";
                                        if ($orow['is_default']) {
                                            echo " selected";
                                        }
                                        echo ">" . htmlspecialchars($orow['title']) . "</option>\n";
                                    }
                                    ?>
                                    </select> 
                                    <?php
                                    // TBD: Maybe a comment field would be good here, for appending
                                    // to the reason.
                                    ?>
                                    </td>
                                    <?php if ($ALLOW_DELETE) { ?>
                                    <td class="detail">&nbsp;</td>
                                    <?php } ?>
                                </tr>
                            <?php
                                } // end of code
                            ?>
                        </table>
                    </div>
                </fieldset>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                <div class="form-group clearfix">
                    <div class="col-sm-12 text-left position-override" id="search-btn">
                        <div class="btn-group" role="group">
                            <button type='submit' class="btn btn-default btn-save" name='form_save' id="btn-save" ><?php  echo xlt("Save"); ?></button>
                            <button type='submit' class="btn btn-link btn-cancel btn-separate-left" name='form_cancel' id="btn-cancel"  onclick='window.close();'><?php echo xlt("Cancel"); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div><!--End of container div-->
    <script language="JavaScript">
    var f1 = opener.document.forms[0];
    var f2 = document.forms[0];
    if (f1.form_source) {
    <?php
       // These support creation and lookup of ar_session table entries:
       echo "  f2.form_reference.value         = f1.form_source.value;\n";
       echo "  f2.form_check_date.value        = f1.form_paydate.value;\n";
       echo "  //f2.form_deposit_date.value      = f1.form_deposit_date.value;\n";
       echo "  if (f1.form_deposit_date.value != '')\n";
       echo "     f2.form_deposit_date.value      = f1.form_deposit_date.value;\n";
       echo "  else\n";
       echo "     f2.form_deposit_date.value      = getFormattedToday();\n";
       echo "  f2.form_payer_id.value          = f1.form_payer_id.value;\n";
       echo "  f2.form_pay_total.value         = f1.form_amount.value;\n";
       echo "  f2.form_orig_reference.value    = f1.form_source.value;\n";
       echo "  f2.form_orig_check_date.value   = f1.form_paydate.value;\n";
       echo "  f2.form_orig_deposit_date.value = f1.form_deposit_date.value;\n";

       // While I'm thinking about it, some notes about eob sessions.
       // If they do not have all of the session key fields in the search
       // page, then show a warning at the top of the invoice page.
       // Also when they go to save the invoice page and a session key
       // field has changed, alert them to that and allow a cancel.

       // Another point... when posting EOBs, the incoming payer ID might
       // not match the payer ID for the patient's insurance.  This is
       // because the same payer might be entered more than once into the
       // insurance_companies table.  I don't think it matters much.
    ?>
    }
    setins("Ins1");
    </script>
</body>
</html>
