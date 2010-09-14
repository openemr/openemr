<?php
// Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/api.inc");
require_once("codes.php");
require_once("../../../custom/code_types.inc.php");
require_once("../../drugs/drugs.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");

// Some table cells will not be displayed unless insurance billing is used.
$usbillstyle = $GLOBALS['ippf_specific'] ? " style='display:none'" : "";

function alphaCodeType($id) {
  global $code_types;
  foreach ($code_types as $key => $value) {
    if ($value['id'] == $id) return $key;
  }
  return '';
}

// Helper function for creating drop-lists.
function endFSCategory() {
  global $i, $last_category, $FEE_SHEET_COLUMNS;
  if (! $last_category) return;
  echo "   </select>\n";
  echo "  </td>\n";
  if ($i >= $FEE_SHEET_COLUMNS) {
    echo " </tr>\n";
    $i = 0;
  }
}

// Generate JavaScript to build the array of diagnoses.
function genDiagJS($code_type, $code) {
  if ($code_type == 'ICD9') {
    echo "diags.push('$code');\n";
  }
}

// For IPPF only.  Returns 0 = none, 1 = nonsurgical, 2 = surgical.
//
function contraceptionClass($code_type, $code) {
  global $code_types;
  if (!$GLOBALS['ippf_specific']) return 0;
  $contra = 0;
  // Get the related service codes.
  $codesrow = sqlQuery("SELECT related_code FROM codes WHERE " .
    "code_type = '" . $code_types[$code_type]['id'] .
    "' AND code = '$code' LIMIT 1");
  if (!empty($codesrow['related_code']) && $code_type == 'MA') {
    $relcodes = explode(';', $codesrow['related_code']);
    foreach ($relcodes as $relstring) {
      if ($relstring === '') continue;
      list($reltype, $relcode) = explode(':', $relstring);
      if ($reltype !== 'IPPF') continue;
      if      (preg_match('/^11....110/'    , $relcode)) $contra |= 1;
      else if (preg_match('/^11....999/'    , $relcode)) $contra |= 1;
      else if (preg_match('/^112152010/'    , $relcode)) $contra |= 1;
      else if (preg_match('/^11317[1-2]111/', $relcode)) $contra |= 1;
      else if (preg_match('/^12118[1-2].13/', $relcode)) $contra |= 2;
      else if (preg_match('/^12118[1-2]999/', $relcode)) $contra |= 2;
    }
  }
  return $contra;
}

// This writes a billing line item to the output page.
//
function echoLine($lino, $codetype, $code, $modifier, $ndc_info='',
  $auth = TRUE, $del = FALSE, $units = NULL, $fee = NULL, $id = NULL,
  $billed = FALSE, $code_text = NULL, $justify = NULL, $provider_id = 0)
{
  global $code_types, $ndc_applies, $ndc_uom_choices, $justinit, $pid;
  global $contraception, $usbillstyle;

  if ($codetype == 'COPAY') {
    if (!$code_text) $code_text = 'Cash';
    if ($fee > 0) $fee = 0 - $fee;
  }
  if (! $code_text) {
    $query = "select id, units, code_text from codes where code_type = '" .
      $code_types[$codetype]['id'] . "' and " .
      "code = '$code' and ";
    if ($modifier) {
      $query .= "modifier = '$modifier'";
    } else {
      $query .= "(modifier is null or modifier = '')";
    }
    $result = sqlQuery($query);
    $code_text = $result['code_text'];
    if (empty($units)) $units = max(1, intval($result['units']));
    if (!isset($fee)) {
      // Fees come from the prices table now.
      $query = "SELECT prices.pr_price " .
        "FROM patient_data, prices WHERE " .
        "patient_data.pid = '$pid' AND " .
        "prices.pr_id = '" . $result['id'] . "' AND " .
        "prices.pr_selector = '' AND " .
        "prices.pr_level = patient_data.pricelevel " .
        "LIMIT 1";
      echo "\n<!-- $query -->\n"; // debugging
      $prrow = sqlQuery($query);
      $fee = empty($prrow) ? 0 : $prrow['pr_price'];
    }
  }
  $fee = sprintf('%01.2f', $fee);
  if (empty($units)) $units = 1;
  $units = max(1, intval($units));
  // We put unit price on the screen, not the total line item fee.
  $price = $fee / $units;
  $strike1 = ($id && $del) ? "<strike>" : "";
  $strike2 = ($id && $del) ? "</strike>" : "";
  echo " <tr>\n";
  echo "  <td class='billcell'>$strike1" .
    ($codetype == 'COPAY' ? xl($codetype) : $codetype) . $strike2;
  if ($id) {
    echo "<input type='hidden' name='bill[$lino][id]' value='$id'>";
  }
  echo "<input type='hidden' name='bill[$lino][code_type]' value='$codetype'>";
  echo "<input type='hidden' name='bill[$lino][code]' value='$code'>";
  echo "<input type='hidden' name='bill[$lino][billed]' value='$billed'>";
  echo "</td>\n";
  if ($codetype != 'COPAY') {
    echo "  <td class='billcell'>$strike1$code$strike2</td>\n";
  } else {
    echo "  <td class='billcell'>&nbsp;</td>\n";
  }
  if ($billed) {
    if (modifiers_are_used(true)) {
      echo "  <td class='billcell'>$strike1$modifier$strike2" .
        "<input type='hidden' name='bill[$lino][mod]' value='$modifier'></td>\n";
    }
    if (fees_are_used()) {
      echo "  <td class='billcell' align='right'>" . oeFormatMoney($price) . "</td>\n";
      if ($codetype != 'COPAY') {
        echo "  <td class='billcell' align='center'>$units</td>\n";
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
      }
      echo "  <td class='billcell' align='center'$usbillstyle>$justify</td>\n";
    }

    // Show provider for this line.
    echo "  <td class='billcell' align='center'>";
    genProviderSelect('', '-- Default --', $provider_id, true);
    echo "</td>\n";
    echo "  <td class='billcell' align='center'$usbillstyle><input type='checkbox'" .
      ($auth ? " checked" : "") . " disabled /></td>\n";
    echo "  <td class='billcell' align='center'><input type='checkbox'" .
      " disabled /></td>\n";
  }
  else { // not billed
    if (modifiers_are_used(true)) {
      if ($codetype != 'COPAY' && ($code_types[$codetype]['mod'] || $modifier)) {
        echo "  <td class='billcell'><input type='text' name='bill[$lino][mod]' " .
          "value='$modifier' size='" . $code_types[$codetype]['mod'] . "'></td>\n";
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
      }
    }
    if (fees_are_used()) {
      if ($codetype == 'COPAY' || $code_types[$codetype]['fee'] || $fee != 0) {
        echo "  <td class='billcell' align='right'>" .
          "<input type='text' name='bill[$lino][price]' " .
          "value='$price' size='6'";
        if (acl_check('acct','disc'))
          echo " style='text-align:right'";
        else
          echo " style='text-align:right;background-color:transparent' readonly";
        echo "></td>\n";
        echo "  <td class='billcell' align='center'>";
        if ($codetype != 'COPAY') {
          echo "<input type='text' name='bill[$lino][units]' " .
          "value='$units' size='2' style='text-align:right'>";
        } else {
          echo "<input type='hidden' name='bill[$lino][units]' value='$units'>";
        }
        echo "</td>\n";
        if ($code_types[$codetype]['just'] || $justify) {
          echo "  <td class='billcell' align='center'$usbillstyle>";
          echo "<select name='bill[$lino][justify]' onchange='setJustify(this)'>";
          echo "<option value='$justify'>$justify</option></select>";
          echo "</td>\n";
          $justinit .= "setJustify(f['bill[$lino][justify]']);\n";
        } else {
          echo "  <td class='billcell'$usbillstyle>&nbsp;</td>\n";
        }
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
        echo "  <td class='billcell'>&nbsp;</td>\n";
        echo "  <td class='billcell'$usbillstyle>&nbsp;</td>\n"; // justify
      }
    }

    // Provider drop-list for this line.
    echo "  <td class='billcell' align='center'>";
    genProviderSelect("bill[$lino][provid]", '-- Default --', $provider_id);
    echo "</td>\n";
    echo "  <td class='billcell' align='center'$usbillstyle><input type='checkbox' name='bill[$lino][auth]' " .
      "value='1'" . ($auth ? " checked" : "") . " /></td>\n";
    echo "  <td class='billcell' align='center'><input type='checkbox' name='bill[$lino][del]' " .
      "value='1'" . ($del ? " checked" : "") . " /></td>\n";
  }

  echo "  <td class='billcell'>$strike1" . ucfirst(strtolower($code_text)) . "$strike2</td>\n";
  echo " </tr>\n";

  // If NDC info exists or may be required, add a line for it.
  if ($codetype == 'HCPCS' && $ndc_applies && !$billed) {
    $ndcnum = ''; $ndcuom = ''; $ndcqty = '';
    if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $ndc_info, $tmp)) {
      $ndcnum = $tmp[1]; $ndcuom = $tmp[2]; $ndcqty = $tmp[3];
    }
    echo " <tr>\n";
    echo "  <td class='billcell' colspan='2'>&nbsp;</td>\n";
    echo "  <td class='billcell' colspan='6'>&nbsp;NDC:&nbsp;";
    echo "<input type='text' name='bill[$lino][ndcnum]' value='$ndcnum' " .
      "size='11' style='background-color:transparent'>";
    echo " &nbsp;Qty:&nbsp;";
    echo "<input type='text' name='bill[$lino][ndcqty]' value='$ndcqty' " .
      "size='3' style='background-color:transparent;text-align:right'>";
    echo " ";
    echo "<select name='bill[$lino][ndcuom]' style='background-color:transparent'>";
    foreach ($ndc_uom_choices as $key => $value) {
      echo "<option value='$key'";
      if ($key == $ndcuom) echo " selected";
      echo ">$value</option>";
    }
    echo "</select>";
    echo "</td>\n";
    echo " </tr>\n";
  }
  else if ($ndc_info) {
    echo " <tr>\n";
    echo "  <td class='billcell' colspan='2'>&nbsp;</td>\n";
    echo "  <td class='billcell' colspan='6'>&nbsp;NDC Data: $ndc_info</td>\n";
    echo " </tr>\n";
  }

  // For IPPF.  Track contraceptive services.
  if (!$del) $contraception |= contraceptionClass($codetype, $code);
}

// This writes a product (drug_sales) line item to the output page.
//
function echoProdLine($lino, $drug_id, $del = FALSE, $units = NULL,
  $fee = NULL, $sale_id = 0, $billed = FALSE)
{
  global $code_types, $ndc_applies, $pid, $usbillstyle;

  $drow = sqlQuery("SELECT name FROM drugs WHERE drug_id = '$drug_id'");
  $code_text = $drow['name'];

  $fee = sprintf('%01.2f', $fee);
  if (empty($units)) $units = 1;
  $units = max(1, intval($units));
  // We put unit price on the screen, not the total line item fee.
  $price = $fee / $units;
  $strike1 = ($sale_id && $del) ? "<strike>" : "";
  $strike2 = ($sale_id && $del) ? "</strike>" : "";
  echo " <tr>\n";
  echo "  <td class='billcell'>{$strike1}Product$strike2";
  echo "<input type='hidden' name='prod[$lino][sale_id]' value='$sale_id'>";
  echo "<input type='hidden' name='prod[$lino][drug_id]' value='$drug_id'>";
  echo "<input type='hidden' name='prod[$lino][billed]' value='$billed'>";
  echo "</td>\n";
  echo "  <td class='billcell'>$strike1$drug_id$strike2</td>\n";
  if (modifiers_are_used(true)) {
    echo "  <td class='billcell'>&nbsp;</td>\n";
  }
  if ($billed) {
    if (fees_are_used()) {
      echo "  <td class='billcell' align='right'>" . oeFormatMoney($price) . "</td>\n";
      echo "  <td class='billcell' align='center'>$units</td>\n";
      echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // justify
    }
    echo "  <td class='billcell' align='center'>&nbsp;</td>\n";             // provider
    echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // auth
    echo "  <td class='billcell' align='center'><input type='checkbox'" .   // del
      " disabled /></td>\n";
  } else {
    if (fees_are_used()) {
      echo "  <td class='billcell' align='right'>" .
        "<input type='text' name='prod[$lino][price]' " .
        "value='$price' size='6'";
      if (acl_check('acct','disc'))
        echo " style='text-align:right'";
      else
        echo " style='text-align:right;background-color:transparent' readonly";
      echo "></td>\n";
      echo "  <td class='billcell' align='center'>";
      echo "<input type='text' name='prod[$lino][units]' " .
        "value='$units' size='2' style='text-align:right'>";
      echo "</td>\n";
      echo "  <td class='billcell'$usbillstyle>&nbsp;</td>\n"; // justify
    }
    echo "  <td class='billcell' align='center'>&nbsp;</td>\n"; // provider
    echo "  <td class='billcell' align='center'$usbillstyle>&nbsp;</td>\n"; // auth
    echo "  <td class='billcell' align='center'><input type='checkbox' name='prod[$lino][del]' " .
      "value='1'" . ($del ? " checked" : "") . " /></td>\n";
  }

  echo "  <td class='billcell'>$strike1" . ucfirst(strtolower($code_text)) . "$strike2</td>\n";
  echo " </tr>\n";
}

// Build a drop-down list of providers.  This includes users who
// have the word "provider" anywhere in their "additional info"
// field, so that we can define providers (for billing purposes)
// who do not appear in the calendar.
//
function genProviderSelect($selname, $toptext, $default=0, $disabled=false) {
  $query = "SELECT id, lname, fname FROM users WHERE " .
    "( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
    "AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
    "ORDER BY lname, fname";
  $res = sqlStatement($query);
  echo "   <select name='$selname'";
  if ($disabled) echo " disabled";
  echo ">\n";
  echo "    <option value=''>$toptext\n";
  while ($row = sqlFetchArray($res)) {
    $provid = $row['id'];
    echo "    <option value='$provid'";
    if ($provid == $default) echo " selected";
    echo ">" . $row['lname'] . ", " . $row['fname'] . "\n";
  }
  echo "   </select>\n";
}

// This is just for IPPF, to indicate if the visit includes contraceptive services.
$contraception = 0;

// Possible units of measure for NDC drug quantities.
//
$ndc_uom_choices = array(
  'ML' => 'ML',
  'GR' => 'Grams',
  'F2' => 'I.U.',
  'UN' => 'Units'
);

// $FEE_SHEET_COLUMNS should be defined in codes.php.
if (empty($FEE_SHEET_COLUMNS)) $FEE_SHEET_COLUMNS = 2;

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

// Update price level in patient demographics.
if (!empty($_POST['pricelevel'])) {
  sqlStatement("UPDATE patient_data SET pricelevel = '" .
    $_POST['pricelevel'] . "' WHERE pid = '$pid'");
}

// Get some info about this visit.
$visit_row = sqlQuery("SELECT fe.date, opc.pc_catname " .
  "FROM form_encounter AS fe " .
  "LEFT JOIN openemr_postcalendar_categories AS opc ON opc.pc_catid = fe.pc_catid " .
  "WHERE fe.pid = '$pid' AND fe.encounter = '$encounter' LIMIT 1");

// If Save was clicked, save the new and modified billing lines;
// then if no error, redirect to $returnurl.
//
if ($_POST['bn_save']) {
  $main_provid = 0 + $_POST['ProviderID'];
  $main_supid  = 0 + $_POST['SupervisorID'];
  if ($main_supid == $main_provid) $main_supid = 0;
  $default_warehouse = $_POST['default_warehouse'];

  $bill = $_POST['bill'];
  for ($lino = 1; $bill["$lino"]['code_type']; ++$lino) {
    $iter = $bill["$lino"];
    $code_type = $iter['code_type'];
    $code      = $iter['code'];
    $del       = $iter['del'];

    // Get some information about this service code.
    $codesrow = sqlQuery("SELECT code_text FROM codes WHERE " .
      "code_type = '" . $code_types[$code_type]['id'] .
      "' AND code = '$code' LIMIT 1");

    // Skip disabled (billed) line items.
    if ($iter['billed']) continue;

    $id        = $iter['id'];
    $modifier  = trim($iter['mod']);
    $units     = max(1, intval(trim($iter['units'])));
    $fee       = sprintf('%01.2f',(0 + trim($iter['price'])) * $units);
    if ($code_type == 'COPAY') {
      if ($fee > 0) $fee = 0 - $fee;
      $code = sprintf('%01.2f', 0 - $fee);
    }
    $justify   = trim($iter['justify']);
    if ($justify) $justify = str_replace(',', ':', $justify) . ':';
    // $auth      = $iter['auth'] ? "1" : "0";
    $auth      = "1";
    $provid    = 0 + $iter['provid'];

    $ndc_info = '';
    if ($iter['ndcnum']) {
    $ndc_info = 'N4' . trim($iter['ndcnum']) . '   ' . $iter['ndcuom'] .
      trim($iter['ndcqty']);
    }

    // If the item is already in the database...
    if ($id) {
      if ($del) {
        deleteBilling($id);
      }
      else {
        // authorizeBilling($id, $auth);
        sqlQuery("UPDATE billing SET code = '$code', " .
          "units = '$units', fee = '$fee', modifier = '$modifier', " .
          "authorized = $auth, provider_id = '$provid', " .
          "ndc_info = '$ndc_info', justify = '$justify' WHERE " .
          "id = '$id' AND billed = 0 AND activity = 1");
      }
    }

    // Otherwise it's a new item...
    else if (! $del) {
      $code_text = addslashes($codesrow['code_text']);
      addBilling($encounter, $code_type, $code, $code_text, $pid, $auth,
        $provid, $modifier, $units, $fee, $ndc_info, $justify);
    }
  } // end for

  // Doing similarly to the above but for products.
  $prod = $_POST['prod'];
  for ($lino = 1; $prod["$lino"]['drug_id']; ++$lino) {
    $iter = $prod["$lino"];

    if (!empty($iter['billed'])) continue;

    $drug_id   = $iter['drug_id'];
    $sale_id   = $iter['sale_id']; // present only if already saved
    $units     = max(1, intval(trim($iter['units'])));
    $fee       = sprintf('%01.2f',(0 + trim($iter['price'])) * $units);
    $del       = $iter['del'];

    // If the item is already in the database...
    if ($sale_id) {
      if ($del) {
        // Zero out this sale and reverse its inventory update.  We bring in
        // drug_sales twice so that the original quantity can be referenced
        // unambiguously.
        sqlStatement("UPDATE drug_sales AS dsr, drug_sales AS ds, " .
          "drug_inventory AS di " .
          "SET di.on_hand = di.on_hand + dsr.quantity, " .
          "ds.quantity = 0, ds.fee = 0 WHERE " .
          "dsr.sale_id = '$sale_id' AND ds.sale_id = dsr.sale_id AND " .
          "di.inventory_id = ds.inventory_id");
        // And delete the sale for good measure.
        sqlStatement("DELETE FROM drug_sales WHERE sale_id = '$sale_id'");
      }
      else {
        // Modify the sale and adjust inventory accordingly.
        $query = "UPDATE drug_sales AS dsr, drug_sales AS ds, " .
          "drug_inventory AS di " .
          "SET di.on_hand = di.on_hand + dsr.quantity - $units, " .
          "ds.quantity = '$units', ds.fee = '$fee' WHERE " .
          "dsr.sale_id = '$sale_id' AND ds.sale_id = dsr.sale_id AND " .
          "di.inventory_id = ds.inventory_id";
        sqlStatement($query);
      }
    }

    // Otherwise it's a new item...
    else if (! $del) {
      $sale_id = sellDrug($drug_id, $units, $fee, $pid, $encounter, 0, '', '',
        $default_warehouse);
      if (!$sale_id) die("Insufficient inventory for product ID \"$drug_id\".");
    }
  } // end for

  // Set the main/default service provider in the new-encounter form.
  /*******************************************************************
  sqlStatement("UPDATE forms, users SET forms.user = users.username WHERE " .
    "forms.pid = '$pid' AND forms.encounter = '$encounter' AND " .
    "forms.formdir = 'newpatient' AND users.id = '$provid'");
  *******************************************************************/
  sqlStatement("UPDATE form_encounter SET provider_id = '$main_provid', " .
    "supervisor_id = '$main_supid'  WHERE " .
    "pid = '$pid' AND encounter = '$encounter'");

  // More IPPF stuff.
  if (!empty($_POST['contrastart'])) {
    $contrastart = $_POST['contrastart'];
    sqlStatement("UPDATE patient_data SET contrastart = '" .
      $contrastart . "' WHERE pid = '$pid'");
  }

  // Note: Taxes are computed at checkout time (in pos_checkout.php which
  // also posts to SL).  Currently taxes with insurance claims make no sense,
  // so for now we'll ignore tax computation in the insurance billing logic.

  formHeader("Redirecting....");
  formJump();
  formFooter();
  exit;
}

$billresult = getBillingByEncounter($pid, $encounter, "*");
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style>
.billcell { font-family: sans-serif; font-size: 10pt }
</style>
<script language="JavaScript">

var diags = new Array();

<?php
if ($billresult) {
  foreach ($billresult as $iter) {
    genDiagJS($iter["code_type"], trim($iter["code"]));
  }
}
if ($_POST['bill']) {
  foreach ($_POST['bill'] as $iter) {
    if ($iter["del"]) continue; // skip if Delete was checked
    if ($iter["id"])  continue; // skip if it came from the database
    genDiagJS($iter["code_type"], $iter["code"]);
  }
}
if ($_POST['newcodes']) {
  $arrcodes = explode('~', $_POST['newcodes']);
  foreach ($arrcodes as $codestring) {
    if ($codestring === '') continue;
    $arrcode = explode('|', $codestring);
    list($code, $modifier) = explode(":", $arrcode[1]);
    genDiagJS($arrcode[0], $code);
  }
}
?>

// This is invoked by <select onchange> for the various dropdowns,
// including search results.
function codeselect(selobj) {
 var i = selobj.selectedIndex;
 if (i > 0) {
  top.restoreSession();
  var f = document.forms[0];
  f.newcodes.value = selobj.options[i].value;
  f.submit();
 }
}

function copayselect() {
 top.restoreSession();
 var f = document.forms[0];
 f.newcodes.value = 'COPAY||';
 f.submit();
}

function validate(f) {
 for (var lino = 1; f['bill['+lino+'][code_type]']; ++lino) {
  var pfx = 'bill['+lino+']';
  if (f[pfx+'[ndcnum]'] && f[pfx+'[ndcnum]'].value) {
   // Check NDC number format.
   var ndcok = true;
   var ndc = f[pfx+'[ndcnum]'].value;
   var a = ndc.split('-');
   if (a.length != 3) {
    ndcok = false;
   }
   else if (a[0].length < 1 || a[1].length < 1 || a[2].length < 1 ||
    a[0].length > 5 || a[1].length > 4 || a[2].length > 2) {
    ndcok = false;
   }
   else {
    for (var i = 0; i < 3; ++i) {
     for (var j = 0; j < a[i].length; ++j) {
      var c = a[i].charAt(j);
      if (c < '0' || c > '9') ndcok = false;
     }
    }
   }
   if (!ndcok) {
    alert('<?php xl('Format incorrect for NDC','e') ?> "' + ndc +
     '", <?php xl('should be like nnnnn-nnnn-nn','e') ?>');
    if (f[pfx+'[ndcnum]'].focus) f[pfx+'[ndcnum]'].focus();
    return false;
   }
   // Check for valid quantity.
   var qty = f[pfx+'[ndcqty]'].value - 0;
   if (isNaN(qty) || qty <= 0) {
    alert('<?php xl('Quantity for NDC','e') ?> "' + ndc +
     '" <?php xl('is not valid (decimal fractions are OK).','e') ?>');
    if (f[pfx+'[ndcqty]'].focus) f[pfx+'[ndcqty]'].focus();
    return false;
   }
  }
 }
 top.restoreSession();
 return true;
}

// When a justify selection is made, apply it to the current list for
// this procedure and then rebuild its selection list.
//
function setJustify(seljust) {
 var theopts = seljust.options;
 var jdisplay = theopts[0].text;
 // Compute revised justification string.  Note this does nothing if
 // the first entry is still selected, which is handy at startup.
 if (seljust.selectedIndex > 0) {
  var newdiag = seljust.value;
  if (newdiag.length == 0) {
   jdisplay = '';
  }
  else {
   if (jdisplay.length) jdisplay += ',';
   jdisplay += newdiag;
  }
 }
 // Rebuild selection list.
 var jhaystack = ',' + jdisplay + ',';
 var j = 0;
 theopts.length = 0;
 theopts[j++] = new Option(jdisplay,jdisplay,true,true);
 for (var i = 0; i < diags.length; ++i) {
  if (jhaystack.indexOf(',' + diags[i] + ',') < 0) {
   theopts[j++] = new Option(diags[i],diags[i],false,false);
  }
 }
 theopts[j++] = new Option('Clear','',false,false);
}

</script>
</head>

<body class="body_top">
<form method="post" action="<?php echo $rootdir; ?>/forms/fee_sheet/new.php"
 onsubmit="return validate(this)">
<span class="title"><?php xl('Fee Sheet','e'); ?></span><br>
<input type='hidden' name='newcodes' value=''>

<center>

<?php
$isBilled = isEncounterBilled($pid, $encounter);
if ($isBilled) {
  echo "<p><font color='green'>This encounter has been billed. If you " .
    "need to change it, it must be re-opened.</font></p>\n";
}
else { // the encounter is not yet billed
?>

<table width='95%'>
<?php
$i = 0;
$last_category = '';

// Create drop-lists based on the fee_sheet_options table.
$res = sqlStatement("SELECT * FROM fee_sheet_options " .
  "ORDER BY fs_category, fs_option");
while ($row = sqlFetchArray($res)) {
  $fs_category = $row['fs_category'];
  $fs_option   = $row['fs_option'];
  $fs_codes    = $row['fs_codes'];
  if($fs_category !== $last_category) {
    endFSCategory();
    $last_category = $fs_category;
    ++$i;
    echo ($i <= 1) ? " <tr>\n" : "";
    echo "  <td width='50%' align='center' nowrap>\n";
    echo "   <select style='width:96%' onchange='codeselect(this)'>\n";
    echo "    <option value=''> " . substr($fs_category, 1) . "</option>\n";
  }
  echo "    <option value='$fs_codes'>" . substr($fs_option, 1) . "</option>\n";
}
endFSCategory();

// Create drop-lists based on categories defined within the codes.
$pres = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'superbill' ORDER BY seq");
while ($prow = sqlFetchArray($pres)) {
  ++$i;
  echo ($i <= 1) ? " <tr>\n" : "";
  echo "  <td width='50%' align='center' nowrap>\n";
  echo "   <select style='width:96%' onchange='codeselect(this)'>\n";
  echo "    <option value=''> " . $prow['title'] . "\n";
  $res = sqlStatement("SELECT code_type, code, code_text,modifier FROM codes " .
    "WHERE superbill = '" . $prow['option_id'] . "' AND active = 1 " .
    "ORDER BY code_text");
  while ($row = sqlFetchArray($res)) {
    echo "    <option value='" . alphaCodeType($row['code_type']) . '|' .
      $row['code'] . ':'. $row['modifier']. "|'>" . $row['code_text'] . "</option>\n";
  }
  echo "   </select>\n";
  echo "  </td>\n";
  if ($i >= $FEE_SHEET_COLUMNS) {
    echo " </tr>\n";
    $i = 0;
  }
}

// Create one more drop-list, for Products.
if ($GLOBALS['sell_non_drug_products']) {
  ++$i;
  echo ($i <= 1) ? " <tr>\n" : "";
  echo "  <td width='50%' align='center' nowrap>\n";
  echo "   <select name='Products' style='width:96%' onchange='codeselect(this)'>\n";
  echo "    <option value=''> " . xl('Products') . "\n";
  $tres = sqlStatement("SELECT dt.drug_id, dt.selector, d.name " .
    "FROM drug_templates AS dt, drugs AS d WHERE " .
    "d.drug_id = dt.drug_id AND d.active = 1 " .
    "ORDER BY d.name, dt.selector, dt.drug_id");
  while ($trow = sqlFetchArray($tres)) {
    echo "    <option value='PROD|" . $trow['drug_id'] . '|' . $trow['selector'] . "'>" .
      $trow['drug_id'] . ':' . $trow['selector'];
    if ($trow['name'] !== $trow['selector']) echo ' ' . $trow['name'];
    echo "</option>\n";
  }
  echo "   </select>\n";
  echo "  </td>\n";
  if ($i >= $FEE_SHEET_COLUMNS) {
    echo " </tr>\n";
    $i = 0;
  }
}

$search_type = $default_search_type;
if ($_POST['search_type']) $search_type = $_POST['search_type'];

$ndc_applies = true; // Assume all payers require NDC info.

echo $i ? "  <td></td>\n </tr>\n" : "";
echo " <tr>\n";
echo "  <td colspan='$FEE_SHEET_COLUMNS' align='center' nowrap>\n";

// If Search was clicked, do it and write the list of results here.
// There's no limit on the number of results!
//
$numrows = 0;
if ($_POST['bn_search'] && $_POST['search_term']) {
  $query = "SELECT code, modifier, code_text FROM codes WHERE " .
    "(code_text LIKE '%" . $_POST['search_term'] . "%' OR " .
    "code LIKE '%" . $_POST['search_term'] . "%') AND " .
    "code_type = '" . $code_types[$search_type]['id'] . "' " .
    "AND active = 1 ORDER BY code";
  $res = sqlStatement($query);
  $numrows = mysql_num_rows($res); // FIXME - not portable!
}

echo "   <select name='Search Results' style='width:98%' " .
  "onchange='codeselect(this)'";
if (! $numrows) echo ' disabled';
echo ">\n";
echo "    <option value=''> Search Results ($numrows items)\n";

if ($numrows) {
  while ($row = sqlFetchArray($res)) {
    $code = $row['code'];
    if ($row['modifier']) $code .= ":" . $row['modifier'];
    echo "    <option value='$search_type|$code|'>$code " .
      ucfirst(strtolower($row['code_text'])) . "</option>\n";
  }
}

echo "   </select>\n";
echo "  </td>\n";
echo " </tr>\n";
?>

</table>

<p style='margin-top:8px;margin-bottom:8px'>
<table>
 <tr>
  <td>
   <input type='button' value='<?php xl('Add Copay','e');?>'
    onclick="copayselect()" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </td>
  <td>
   <?php xl('Search','e'); ?>&nbsp;
<?php
  foreach ($code_types as $key => $value) {
    if (!empty($value['nofs'])) continue;
    echo "   <input type='radio' name='search_type' value='$key'";
    if ($key == $default_search_type) echo " checked";
    echo " />$key&nbsp;\n";
  }
?>
   <?php xl('for','e'); ?>&nbsp;
  </td>
  <td>
   <input type='text' name='search_term' value=''> &nbsp;
  </td>
  <td>
   <input type='submit' name='bn_search' value='<?php xl('Search','e');?>'>
  </td>
 </tr>
</table>
</p>
<p style='margin-top:16px;margin-bottom:8px'>

<?php } // end encounter not billed ?>

<table cellspacing='5'>
 <tr>
  <td class='billcell'><b><?php xl('Type','e');?></b></td>
  <td class='billcell'><b><?php xl('Code','e');?></b></td>
<?php if (modifiers_are_used(true)) { ?>
  <td class='billcell'><b><?php xl('Mod','e');?></b></td>
<?php } ?>
<?php if (fees_are_used()) { ?>
  <td class='billcell' align='right'><b><?php xl('Price','e');?></b>&nbsp;</td>
  <td class='billcell' align='center'><b><?php xl('Units','e');?></b></td>
  <td class='billcell' align='center'<?php echo $usbillstyle; ?>><b><?php xl('Justify','e');?></b></td>
<?php } ?>
  <td class='billcell' align='center'><b><?php xl('Provider','e');?></b></td>
  <td class='billcell' align='center'<?php echo $usbillstyle; ?>><b><?php xl('Auth','e');?></b></td>
  <td class='billcell' align='center'><b><?php xl('Delete','e');?></b></td>
  <td class='billcell'><b><?php xl('Description','e');?></b></td>
 </tr>

<?php
$justinit = "var f = document.forms[0];\n";

// $encounter_provid = -1;

// Generate lines for items already in the billing table for this encounter,
// and also set the rendering provider if we come across one.
//
$bill_lino = 0;
if ($billresult) {
  foreach ($billresult as $iter) {
    ++$bill_lino;
    $bline = $_POST['bill']["$bill_lino"];
    $del = $bline['del']; // preserve Delete if checked

    $modifier   = trim($iter["modifier"]);
    $units      = $iter["units"];
    $fee        = $iter["fee"];
    $authorized = $iter["authorized"];
    $ndc_info   = $iter["ndc_info"];
    $justify    = trim($iter['justify']);
    if ($justify) $justify = substr(str_replace(':', ',', $justify), 0, strlen($justify) - 1);
    $provider_id = $iter['provider_id'];

    // Also preserve other items from the form, if present.
    if ($bline['id'] && !$iter["billed"]) {
      $modifier   = trim($bline['mod']);
      $units      = max(1, intval(trim($bline['units'])));
      $fee        = sprintf('%01.2f',(0 + trim($bline['price'])) * $units);
      $authorized = $bline['auth'];
      $ndc_info   = '';
      if ($bline['ndcnum']) {
        $ndc_info = 'N4' . trim($bline['ndcnum']) . '   ' . $bline['ndcuom'] .
        trim($bline['ndcqty']);
      }
      $justify    = $bline['justify'];
      $provider_id = 0 + $bline['provid'];
    }

    // list($code, $modifier) = explode("-", $iter["code"]);
    echoLine($bill_lino, $iter["code_type"], trim($iter["code"]),
      $modifier, $ndc_info,  $authorized,
      $del, $units, $fee, $iter["id"], $iter["billed"],
      $iter["code_text"], $justify, $provider_id);
  }
}

// Echo new billing items from this form here, but omit any line
// whose Delete checkbox is checked.
//
if ($_POST['bill']) {
  foreach ($_POST['bill'] as $key => $iter) {
    if ($iter["id"])  continue; // skip if it came from the database
    if ($iter["del"]) continue; // skip if Delete was checked
    $ndc_info = '';
    if ($iter['ndcnum']) {
      $ndc_info = 'N4' . trim($iter['ndcnum']) . '   ' . $iter['ndcuom'] .
      trim($iter['ndcqty']);
    }
    // $fee = 0 + trim($iter['fee']);
    $units = max(1, intval(trim($iter['units'])));
    $fee = sprintf('%01.2f',(0 + trim($iter['price'])) * $units);
    if ($iter['code_type'] == 'COPAY' && $fee > 0) $fee = 0 - $fee;
    echoLine(++$bill_lino, $iter["code_type"], $iter["code"], trim($iter["mod"]),
      $ndc_info, $iter["auth"], $iter["del"], $units,
      $fee, NULL, FALSE, NULL, $iter["justify"], 0 + $iter['provid']);
  }
}

// Generate lines for items already in the drug_sales table for this encounter.
//
$query = "SELECT * FROM drug_sales WHERE " .
  "pid = '$pid' AND encounter = '$encounter' " .
  "ORDER BY sale_id";
$sres = sqlStatement($query);
$prod_lino = 0;
while ($srow = sqlFetchArray($sres)) {
  ++$prod_lino;
  $pline = $_POST['prod']["$prod_lino"];
  $del   = $pline['del']; // preserve Delete if checked
  $sale_id = $srow['sale_id'];
  $drug_id = $srow['drug_id'];
  $units   = $srow['quantity'];
  $fee     = $srow['fee'];
  $billed  = $srow['billed'];
  // Also preserve other items from the form, if present and unbilled.
  if ($pline['sale_id'] && !$srow['billed']) {
    // $units      = trim($pline['units']);
    // $fee        = trim($pline['fee']);
    $units = max(1, intval(trim($pline['units'])));
    $fee   = sprintf('%01.2f',(0 + trim($pline['price'])) * $units);
  }
  echoProdLine($prod_lino, $drug_id, $del, $units, $fee, $sale_id, $billed);
}

// Echo new product items from this form here, but omit any line
// whose Delete checkbox is checked.
//
if ($_POST['prod']) {
  foreach ($_POST['prod'] as $key => $iter) {
    if ($iter["sale_id"])  continue; // skip if it came from the database
    if ($iter["del"]) continue; // skip if Delete was checked
    // $fee = 0 + trim($iter['fee']);
    $units = max(1, intval(trim($iter['units'])));
    $fee   = sprintf('%01.2f',(0 + trim($iter['price'])) * $units);
    echoProdLine(++$prod_lino, $iter['drug_id'], FALSE, $units, $fee);
  }
}

// If new billing code(s) were <select>ed, add their line(s) here.
//
if ($_POST['newcodes']) {
  $arrcodes = explode('~', $_POST['newcodes']);
  foreach ($arrcodes as $codestring) {
    if ($codestring === '') continue;
    $arrcode = explode('|', $codestring);
    $newtype = $arrcode[0];
    $newcode = $arrcode[1];
    $newsel  = $arrcode[2];
    if ($newtype == 'COPAY') {
      $tmp = sqlQuery("SELECT copay FROM insurance_data WHERE pid = '$pid' " .
        "AND type = 'primary' ORDER BY date DESC LIMIT 1");
      $code = sprintf('%01.2f', 0 + $tmp['copay']);
      echoLine(++$bill_lino, $newtype, $code, '', '', '1', '0', '1',
        sprintf('%01.2f', 0 - $code));
    }
    else if ($newtype == 'PROD') {
      $result = sqlQuery("SELECT * FROM drug_templates WHERE " .
        "drug_id = '$newcode' AND selector = '$newsel'");
      $units = max(1, intval($result['quantity']));
      $prrow = sqlQuery("SELECT prices.pr_price " .
        "FROM patient_data, prices WHERE " .
        "patient_data.pid = '$pid' AND " .
        "prices.pr_id = '$newcode' AND " .
        "prices.pr_selector = '$newsel' AND " .
        "prices.pr_level = patient_data.pricelevel " .
        "LIMIT 1");
      $fee = empty($prrow) ? 0 : $prrow['pr_price'];
      echoProdLine(++$prod_lino, $newcode, FALSE, $units, $fee);
    }
    else {
      list($code, $modifier) = explode(":", $newcode);
      $ndc_info = '';
      // If HCPCS, find last NDC string used for this code.
      if ($newtype == 'HCPCS' && $ndc_applies) {
        $tmp = sqlQuery("SELECT ndc_info FROM billing WHERE " .
          "code_type = '$newtype' AND code = '$code' AND ndc_info LIKE 'N4%' " .
          "ORDER BY date DESC LIMIT 1");
        if (!empty($tmp)) $ndc_info = $tmp['ndc_info'];
      }
      echoLine(++$bill_lino, $newtype, $code, trim($modifier), $ndc_info);
    }
  }
}

$tmp = sqlQuery("SELECT provider_id, supervisor_id FROM form_encounter " .
  "WHERE pid = '$pid' AND encounter = '$encounter' " .
  "ORDER BY id DESC LIMIT 1");
$encounter_provid = 0 + $tmp['provider_id'];
$encounter_supid  = 0 + $tmp['supervisor_id'];
?>
</table>
</p>

<br />
&nbsp;

<?php
// Choose rendering and supervising providers.
echo "<span class='billcell'><b>\n";
echo xl('Providers') . ": &nbsp;";

echo "&nbsp;&nbsp;" . xl('Rendering') . "\n";
genProviderSelect('ProviderID', '-- Please Select --', $encounter_provid, $isBilled);

if (!$GLOBALS['ippf_specific']) {
  echo "&nbsp;&nbsp;" . xl('Supervising') . "\n";
  genProviderSelect('SupervisorID', '-- N/A --', $encounter_supid, $isBilled);
}

echo "</b></span>\n";
?>

<p>
&nbsp;

<?php
// If applicable, ask for the contraceptive services start date.
$trow = sqlQuery("SELECT count(*) AS count FROM layout_options WHERE " .
  "form_id = 'DEM' AND field_id = 'contrastart' AND uor > 0");
if ($trow['count'] && $contraception && !$isBilled) {
  $date1 = substr($visit_row['date'], 0, 10);
  // If admission or surgical, then force contrastart.
  if ($contraception > 1 ||
    strpos(strtolower($visit_row['pc_catname']), 'admission') !== false)
  {
    echo "   <input type='hidden' name='contrastart' value='$date1' />\n";
  }
  else {
    // echo "<!-- contraception = $contraception -->\n"; // debugging
    $trow = sqlQuery("SELECT contrastart " .
      "FROM patient_data WHERE " .
      "pid = '$pid' LIMIT 1");
    if (empty($trow['contrastart']) || substr($trow['contrastart'], 0, 4) == '0000') {
      $date0 = date('Y-m-d', strtotime($date1) - (60 * 60 * 24));
      echo "   <select name='contrastart'>\n";
      echo "    <option value='$date1'>" . xl('This visit begins new contraceptive use') . "</option>\n";
      echo "    <option value='$date0'>" . xl('Contraceptive services previously started') . "</option>\n";
      echo "    <option value=''>" . xl('None of the above') . "</option>\n";
      echo "   </select>\n";
      echo "&nbsp; &nbsp; &nbsp;\n";
    }
  }
}

// If there is a choice of warehouses, allow override of user default.
if ($prod_lino > 0) { // if any products are in this form
  $trow = sqlQuery("SELECT count(*) AS count FROM list_options WHERE list_id = 'warehouse'");
  if ($trow['count'] > 1) {
    $trow = sqlQuery("SELECT default_warehouse FROM users WHERE username = '" .
      $_SESSION['authUser'] . "'");
    echo "   <span class='billcell'><b>" . xl('Warehouse') . ":</b></span>\n";
    echo generate_select_list('default_warehouse', 'warehouse',
      $trow['default_warehouse'], '');
    echo "&nbsp; &nbsp; &nbsp;\n";
  }
}

// Allow the patient price level to be fixed here.
$plres = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'pricelevel' ORDER BY seq");
if (true) {
  $trow = sqlQuery("SELECT pricelevel FROM patient_data WHERE " .
    "pid = '$pid' LIMIT 1");
  $pricelevel = $trow['pricelevel'];
  echo "   <span class='billcell'><b>" . xl('Price Level') . ":</b></span>\n";
  echo "   <select name='pricelevel'";
  if ($isBilled) echo " disabled";
  echo ">\n";
  while ($plrow = sqlFetchArray($plres)) {
    $key = $plrow['option_id'];
    $val = $plrow['title'];
    echo "    <option value='$key'";
    if ($key == $pricelevel) echo ' selected';
    echo ">$val</option>\n";
  }
  echo "   </select>\n";
}
?>

&nbsp; &nbsp; &nbsp;

<?php if (!$isBilled) { ?>
<input type='submit' name='bn_save' value='<?php xl('Save','e');?>' />
&nbsp;
<input type='submit' name='bn_refresh' value='<?php xl('Refresh','e');?>'>
&nbsp;
<?php } ?>

<input type='button' value='<?php xl('Cancel','e');?>'
 onclick="top.restoreSession();location='<?php echo "$rootdir/patient_file/encounter/$returnurl" ?>'" />

<?php if ($code_types['UCSMC']) { ?>
<p style='font-family:sans-serif;font-size:8pt;color:#666666;'>
&nbsp;<br>
<?php xl('UCSMC codes provided by the University of Calgary Sports Medicine Centre','e');?>
</p>
<?php } ?>

</center>

</form>

<?php
// TBD: If $alertmsg, display it with a JavaScript alert().
?>

<script language='JavaScript'>
<?php echo $justinit; ?>
</script>

</body>
</html>
