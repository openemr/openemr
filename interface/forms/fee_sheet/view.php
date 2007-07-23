<?php
//////////////////////////////////////////////////////////////////////
// ------------------ DO NOT MODIFY VIEW.PHP !!! ---------------------
// View.php is an exact duplicate of new.php.  If you wish to make
// any changes, then change new.php and either (recommended) make
// view.php a symbolic link to new.php, or copy new.php to view.php.
//
// And if you check in a change to either module, be sure to check
// in the other (identical) module also.
//
// This nonsense will go away if we ever move to subversion.
//////////////////////////////////////////////////////////////////////

// Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("codes.php");
include_once("../../../custom/code_types.inc.php");

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

// If Save was clicked, save the new and modified billing lines;
// then if no error, redirect to $returnurl.
//
if ($_POST['bn_save']) {
	$provid = $_POST['ProviderID'];
	if (! $provid) $provid = $_SESSION["authUserID"];
	$bill = $_POST['bill'];
	for ($lino = 1; $bill["$lino"]['code_type']; ++$lino) {
		$iter = $bill["$lino"];

		// Skip disabled (billed) line items.
		if ($iter['billed']) continue;

		$id        = $iter['id'];
		$code_type = $iter['code_type'];
		$code      = $iter['code'];
		$modifier  = trim($iter['mod']);
		$units     = max(1, intval(trim($iter['units'])));
		$fee       = 0 + trim($iter['fee']);
    if ($code_type == 'COPAY') {
      if ($fee > 0) $fee = 0 - $fee;
      $code = sprintf('%01.2f', 0 - $fee);
    }
    $justify   = trim($iter['justify']);
    if ($justify) $justify = str_replace(',', ':', $justify) . ':';
		// $auth      = $iter['auth'] ? "1" : "0";
		$auth      = "1";
		$del       = $iter['del'];

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
			$query = "select code_text from codes where code_type = '" .
				$code_types[$code_type]['id'] . "' and " .
				"code = '$code' and ";
			if ($modifier) {
				$query .= "modifier = '$modifier'";
			} else {
				$query .= "(modifier is null or modifier = '')";
			}
			$result = sqlQuery($query);
			$code_text = addslashes($result['code_text']);
			addBilling($encounter, $code_type, $code, $code_text, $pid, $auth,
				$provid, $modifier, $units, $fee, $ndc_info, $justify);
		}
	} // end for

	formHeader("Redirecting....");
	formJump();
	formFooter();
	exit;
}

$billresult = getBillingByEncounter($pid, $encounter, "*");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style>
.billcell { font-family: sans-serif; font-size: 10pt }
</style>
<script language="JavaScript">

var diags = new Array();

<?php
// Generate JavaScript to build the array of diagnoses.
function genDiagJS($code_type, $code) {
  if ($code_type == 'ICD9') {
    echo "diags.push('$code');\n";
  }
}
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
if ($_POST['newtype']) {
  list($code, $modifier) = explode("-", $_POST['newcode']);
  genDiagJS($_POST['newtype'], $code);
}
?>

function codeselect(selobj, newtype) {
 var i = selobj.selectedIndex;
 if (i > 0) {
  var f = document.forms[0];
  f.newcode.value = selobj.options[i].value;
  f.newtype.value = newtype;
  f.submit();
 }
}

function copayselect() {
 var f = document.forms[0];
 f.newtype.value = 'COPAY';
 f.submit();
}

function validate(f) {
 for (var lino = 1; f['bill[' + lino + '][code_type]']; ++lino) {
  var pfx = 'bill[' + lino + ']';
  if (f[pfx + '[ndcnum]'] && f[pfx + '[ndcnum]'].value) {
   if (isNaN(f[pfx + '[ndcqty]'].value - 0)) {
    alert('<?php xl('Quantity for NDC ','e') ?>' + f[pfx + '[ndcnum]'].value +
     '<?php xl(' is not numeric (decimal fractions are OK).','e') ?>');
    if (f[pfx + '[ndcqty]'].focus) f[pfx + '[ndcqty]'].focus();
    return false;
   }
  }
 }
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

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2"
 bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir; ?>/forms/fee_sheet/new.php"
 onsubmit="return validate(this)">
<span class="title"><? echo ($GLOBALS['phone_country_code'] == '1') ? 'Fee' : 'Coding' ?> <?php xl('Sheet','e');?></span><br>
<input type='hidden' name='newtype' value=''>
<input type='hidden' name='newcode' value=''>

<center>
<table width='95%'>
<?
$i = 0;

// Create all the drop-lists of preselected codes.
//
foreach ($bcodes as $key0 => $value0) {
	foreach ($value0 as $key1 => $value1) {
		++$i;
		echo ($i <= 1) ? " <tr>\n" : "";
		echo "  <td width='50%' align='center' nowrap>\n";
		echo "   <select name='$key1' style='width:96%' onchange='codeselect(this, \"$key0\")'>\n";
		echo "    <option value=''> $key1\n";
		foreach ($value0[$key1] as $key2 => $value2) {
			echo "    <option value='$key2'>$key2 $value2\n";
		}
		echo "   </select>\n";
		echo "  </td>\n";
		if ($i >= $FEE_SHEET_COLUMNS) {
			echo " </tr>\n";
			$i = 0;
		}
	}
}

$search_type = $default_search_type;
if ($_POST['search_type']) $search_type = $_POST['search_type'];

/****
// Find out if the patient has any insurance that requires NDC numbers.
// Currently this is just Medicaid, type 3.
//
$tmp = sqlQuery("SELECT c.id FROM " .
  "insurance_data AS i, insurance_companies AS c WHERE " .
  "i.pid = '$pid' AND i.provider != '' AND " .
  "c.id = i.provider AND c.freeb_type = 3 " .
  "LIMIT 1");
$ndc_applies = !empty($tmp);
****/
$ndc_applies = true; // Looks like more payers are starting to require this.

echo $i ? "  <td></td>\n </tr>\n" : "";
echo " <tr>\n";
echo "  <td colspan='$FEE_SHEET_COLUMNS' align='center' nowrap>\n";

// If Search was clicked, do it and write the list of results here.
// There's no limit on the number of results!
//
$numrows = 0;
if ($_POST['bn_search'] && $_POST['search_term']) {
	$query = "select code, modifier, code_text from codes where " .
		"(code_text like '%" . $_POST['search_term'] . "%' or " .
		"code like '%" . $_POST['search_term'] . "%') and " .
		"code_type = '" . $code_types[$search_type]['id'] . "' " .
		"order by code";
	$res = sqlStatement($query);
	$numrows = mysql_num_rows($res); // FIXME - not portable!
}

echo "   <select name='Search Results' style='width:98%' " .
	"onchange='codeselect(this, \"$search_type\")'";
if (! $numrows) echo ' disabled';
echo ">\n";
echo "    <option value=''> Search Results ($numrows items)\n";

if ($numrows) {
	while ($row = sqlFetchArray($res)) {
		$code = $row['code'];
		if ($row['modifier']) $code .= "-" . $row['modifier'];
		echo "    <option value='$code'>$code " . ucfirst(strtolower($row['code_text'])) . "\n";
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
<?
	foreach ($code_types as $key => $value) {
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
<table cellspacing='5'>
 <tr>
  <td class='billcell'><b><?php xl('Type','e');?></b></td>
  <td class='billcell'><b><?php xl('Code','e');?></b></td>
<? if (modifiers_are_used()) { ?>
  <td class='billcell'><b><?php xl('Mod','e');?></b></td>
<? } ?>
<? if (fees_are_used()) { ?>
  <td class='billcell' align='center'><b><?php xl('Units','e');?></b></td>
  <td class='billcell' align='right'><b><?php xl('Fee','e');?></b>&nbsp;</td>
  <td class='billcell' align='center'><b><?php xl('Justify','e');?></b></td>
<? } ?>
  <td class='billcell' align='center'><b><?php xl('Auth','e');?></b></td>
  <td class='billcell' align='center'><b><?php xl('Delete','e');?></b></td>
  <td class='billcell'><b><?php xl('Description','e');?></b></td>
 </tr>

<?php
$justinit = "var f = document.forms[0];\n";

// This writes a billing line item to the output page.
//
function echoLine($lino, $codetype, $code, $modifier, $ndc_info='',
  $auth = TRUE, $del = FALSE, $units = NULL, $fee = NULL, $id = NULL,
  $billed = FALSE, $code_text = NULL, $justify = NULL)
{
  global $code_types, $ndc_applies, $ndc_uom_choices, $justinit;

  if ($codetype == 'COPAY') {
    if (!$code_text) $code_text = 'Cash';
    if ($fee > 0) $fee = 0 - $fee;
  }
	if (! $code_text) {
		$query = "select units, fee, code_text from codes where code_type = '" .
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
		if (!isset($fee)) $fee = $result['fee'];
	}
  $fee = sprintf('%01.2f', $fee);
	$strike1 = ($id && $del) ? "<strike>" : "";
	$strike2 = ($id && $del) ? "</strike>" : "";
	echo " <tr>\n";
	echo "  <td class='billcell'>$strike1$codetype$strike2";
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
    if (modifiers_are_used()) {
      echo "  <td class='billcell'>$strike1$modifier$strike2" .
        "<input type='hidden' name='bill[$lino][mod]' value='$modifier'></td>\n";
    }
    if (fees_are_used()) {
      if ($codetype != 'COPAY') {
        echo "  <td class='billcell' align='center'>$units</td>\n";
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
      }
      echo "  <td class='billcell' align='right'>$fee</td>\n";
      echo "  <td class='billcell' align='center'>$justify</td>\n";
    }
    echo "  <td class='billcell' align='center'><input type='checkbox'" .
      ($auth ? " checked" : "") . " disabled /></td>\n";
    echo "  <td class='billcell' align='center'><input type='checkbox'" .
      " disabled /></td>\n";
  } else {
    if (modifiers_are_used()) {
      if ($codetype != 'COPAY' && ($code_types[$codetype]['mod'] || $modifier)) {
        echo "  <td class='billcell'><input type='text' name='bill[$lino][mod]' " .
          "value='$modifier' size='" . $code_types[$codetype]['mod'] . "'></td>\n";
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
      }
    }
    if (fees_are_used()) {
      if ($codetype == 'COPAY' || $code_types[$codetype]['fee'] || $fee != 0) {
        echo "  <td class='billcell' align='center'>";
        if ($codetype != 'COPAY') {
          echo "<input type='text' name='bill[$lino][units]' " .
          "value='$units' size='2' style='text-align:right'>";
        } else {
          echo "<input type='hidden' name='bill[$lino][units]' value='$units'>";
        }
        echo "</td>\n";
        echo "  <td class='billcell' align='right'>" .
          "<input type='text' name='bill[$lino][fee]' " .
          "value='$fee' size='6' style='text-align:right'></td>\n";
        if ($code_types[$codetype]['just'] || $justify) {
          echo "  <td class='billcell' align='center'>";
          echo "<select name='bill[$lino][justify]' onchange='setJustify(this)'>";
          echo "<option value='$justify'>$justify</option></select>";
          echo "</td>\n";
          $justinit .= "setJustify(f['bill[$lino][justify]']);\n";
        } else {
          echo "  <td class='billcell'>&nbsp;</td>\n";
        }
      } else {
        echo "  <td class='billcell'>&nbsp;</td>\n";
        echo "  <td class='billcell'>&nbsp;</td>\n";
        echo "  <td class='billcell'>&nbsp;</td>\n";
      }
    }
    echo "  <td class='billcell' align='center'><input type='checkbox' name='bill[$lino][auth]' " .
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
}

// Try setting the default provider to that of the new encounter form.
//
$encounter_provid = -1;
$tmp = sqlQuery("SELECT users.id FROM forms, users WHERE " .
	"forms.pid = '$pid' AND forms.encounter = '$encounter' AND " .
	"forms.formdir='newpatient' AND users.username = forms.user AND " .
	"users.authorized = 1");
if ($tmp['id']) $encounter_provid = $tmp['id'];

// Generate lines for items already in the database.
//
$lino = 0;
if ($billresult) {
  foreach ($billresult as $iter) {
    ++$lino;
    $bline = $_POST['bill']["$lino"];
    $del = $bline['del']; // preserve Delete if checked

    $modifier   = trim($iter["modifier"]);
    $units      = $iter["units"];
    $fee        = $iter["fee"];
    $authorized = $iter["authorized"];
    $ndc_info   = $iter["ndc_info"];
    $justify    = trim($iter['justify']);
    if ($justify) $justify = substr(str_replace(':', ',', $justify), 0, strlen($justify) - 1);

    // Also preserve other items from the form, if present.
    if ($bline['id'] && !$iter["billed"]) {
      $modifier   = trim($bline['mod']);
      $units      = trim($bline['units']);
      $fee        = trim($bline['fee']);
      $authorized = $bline['auth'];
      $ndc_info   = '';
      if ($bline['ndcnum']) {
        $ndc_info = 'N4' . trim($bline['ndcnum']) . '   ' . $bline['ndcuom'] .
        trim($bline['ndcqty']);
      }
      $justify    = $bline['justify'];
    }

    // list($code, $modifier) = explode("-", $iter["code"]);
    echoLine($lino, $iter["code_type"], trim($iter["code"]),
      $modifier, $ndc_info,  $authorized,
      $del, $units, $fee, $iter["id"], $iter["billed"],
      $iter["code_text"], $justify);
    // If no default provider yet then try this one.
    if ($encounter_provid < 0 && ! $del) $encounter_provid = $iter["provider_id"];
  }
}

// If still no default provider then make it the logged-in user.
//
if ($encounter_provid < 0) $encounter_provid = $_SESSION["authUserID"];

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
    $fee = 0 + trim($iter['fee']);
    if ($iter['code_type'] == 'COPAY' && $fee > 0) $fee = 0 - $fee;
    echoLine(++$lino, $iter["code_type"], $iter["code"], trim($iter["mod"]),
      $ndc_info, $iter["auth"], $iter["del"], trim($iter["units"]),
      $fee, NULL, FALSE, NULL, $iter["justify"]);
	}
}

// If a new billing code was <select>ed, add its line here.  As a special
// case allow HCPCS codes to be included in the CPT drop-lists, and
// CPT4 codes included in OPCS drop-lists.
//
if ($_POST['newtype']) {
  $newtype = $_POST['newtype'];
  if ($newtype == 'COPAY') {
    $tmp = sqlQuery("SELECT copay FROM insurance_data WHERE pid = '$pid' " .
      "AND type = 'primary' ORDER BY date DESC LIMIT 1");
    $code = sprintf('%01.2f', 0 + $tmp['copay']);
    echoLine(++$lino, $newtype, $code, '', '', '1', '0', '1',
      sprintf('%01.2f', 0 - $code));
  }
  else {
    list($code, $modifier) = explode("-", $_POST['newcode']);
    if ($newtype == "CPT4" && preg_match("/^[A-Z]/", $code))
      $newtype = "HCPCS";
    else if ($newtype == "OPCS" && preg_match("/^[0-9]/", $code))
      $newtype = "CPT4";
    $ndc_info = '';
    // If HCPCS, find last NDC string used for this code.
    if ($newtype == 'HCPCS' && $ndc_applies) {
      $tmp = sqlQuery("SELECT ndc_info FROM billing WHERE " .
        "code_type = '$newtype' AND code = '$code' AND ndc_info LIKE 'N4%' " .
        "ORDER BY date DESC LIMIT 1");
      if (!empty($tmp)) $ndc_info = $tmp['ndc_info'];
    }
    echoLine(++$lino, $newtype, $code, trim($modifier), $ndc_info);
  }

}

?>
</table>
</p>

<br>
&nbsp;

<span class="billcell"><?php xl('PROVIDER:','e');?></span>

<?
// Build a drop-down list of providers.  This includes users who
// have the word "provider" anywhere in their "additional info"
// field, so that we can define providers (for billing purposes)
// who do not appear in the calendar.
//
$query = "SELECT id, lname, fname FROM users WHERE " .
	"( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
	"ORDER BY lname, fname";
$res = sqlStatement($query);

echo "   <select name='ProviderID'>\n";
echo "    <option value=''>-- Please Select --\n";

while ($row = sqlFetchArray($res)) {
	$provid = $row['id'];
	echo "    <option value='$provid'";
	if ($provid == $encounter_provid) echo " selected";
	echo ">" . $row['lname'] . ", " . $row['fname'] . "\n";
}

echo "   </select>\n";
?>

&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;

<input type='submit' name='bn_save' value='<?php xl('Save','e');?>' />
&nbsp;
<input type='submit' name='bn_refresh' value='<?php xl('Refresh','e');?>'>
&nbsp;
<input type='button' value='<?php xl('Cancel','e');?>' onclick="location='<? echo "$rootdir/patient_file/encounter/$returnurl" ?>'" />

<?php if ($code_types['UCSMC']) { ?>
<p style='font-family:sans-serif;font-size:8pt;color:#666666;'>
&nbsp;<br>
<?php xl('UCSMC codes provided by the University of Calgary Sports Medicine Centre','e');?>
</p>
<? } ?>

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
