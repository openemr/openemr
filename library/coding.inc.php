<?php
// Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once(dirname(__FILE__) . '/api.inc');
include_once(dirname(__FILE__) . '/../interface/forms/fee_sheet/codes.php');
include_once(dirname(__FILE__) . '/../custom/code_types.inc.php');

// $FEE_SHEET_COLUMNS should be defined in codes.php.
if (empty($FEE_SHEET_COLUMNS)) $FEE_SHEET_COLUMNS = 2;

// If Save was clicked, save the new and modified billing lines;
// then if no error, redirect to patient_encounter.php.
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
		$fee       = trim($iter['fee']);
		$auth      = $iter['auth'] ? "1" : "0";
		$del       = $iter['del'];

		// If the item is already in the database...
		if ($id) {
			if ($del) {
				deleteBilling($id);
			}
			else {
				// authorizeBilling($id, $auth);
				sqlQuery("update billing set fee = '$fee', modifier = '$modifier', " .
					"authorized = $auth, provider_id = '$provid' where " .
					"id = '$id' and billed = 0 and activity = 1");
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
				$provid, $modifier, "", $fee);
		}
	}

	terminate_coding();
	exit;
}
?>

<style>
.billcell { font-family: sans-serif; font-size: 10pt }
</style>
<script language="JavaScript">

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

</script>

<form method="post" action="<?php echo coding_form_action(); ?>">
<span class="title"><?php echo ($GLOBALS['phone_country_code'] == '1') ? 'Fee' : 'Coding' ?> Sheet</span><br>
<input type='hidden' name='newcodes' value=''>

<center>
<table width='95%'>
<?php
$i = 0;
$last_category = '';

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

// Create all the drop-lists of preselected service codes.
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
    echo "    <option value=''> " . substr($fs_category, 1) . "\n";
  }
  echo "    <option value='$fs_codes'>" . substr($fs_option, 1) . "\n";
}
endFSCategory();

$search_type = $default_search_type;
if ($_POST['search_type']) $search_type = $_POST['search_type'];

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
   Search&nbsp;
<?php

	foreach ($code_types as $key => $value) {
		echo "   <input type='radio' name='search_type' value='$key'";
		if ($key == $default_search_type) echo " checked";
		echo " />$key&nbsp;\n";
	}
?>
   for&nbsp;
  </td>
  <td>
   <input type='text' name='search_term' value=''> &nbsp;
  </td>
  <td>
   <input type='submit' name='bn_search' value='Search'>
  </td>
 </tr>
</table>
</p>

<p style='margin-top:16px;margin-bottom:8px'>
<table cellspacing='5'>
 <tr>
  <td class='billcell'><b>Type</b></td>
  <td class='billcell'><b>Code</b></td>
<?php if (modifiers_are_used()) { ?>
  <td class='billcell'><b>Mod</b></td>
<?php } ?>
<?php if (fees_are_used()) { ?>
  <td class='billcell' align='right'><b>Fee</b>&nbsp;</td>
<?php } ?>
  <td class='billcell' align='center'><b>Auth</b></td>
  <td class='billcell' align='center'><b>Delete</b></td>
  <td class='billcell'><b>Description</b></td>
 </tr>
<?php


// This writes a billing line item to the output page.
//
function echoLine($lino, $codetype, $code, $modifier, $auth = TRUE, $del = FALSE,
	$fee = NULL, $id = NULL, $billed = FALSE, $code_text = NULL)
{
	global $code_types;
	if (! $code_text) {
		$query = "select fee, code_text from codes where code_type = '" .
			$code_types[$codetype]['id'] . "' and " .
			"code = '$code' and ";
		if ($modifier) {
			$query .= "modifier = '$modifier'";
		} else {
			$query .= "(modifier is null or modifier = '')";
		}
		$result = sqlQuery($query);
		$code_text = $result['code_text'];
		if (!isset($fee)) $fee = $result['fee'];
	}
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
	echo "  <td class='billcell'>$strike1$code$strike2</td>\n";
	if ($billed) {
		if (modifiers_are_used()) {
			echo "  <td class='billcell'>$strike1$modifier$strike2" .
				"<input type='hidden' name='bill[$lino][mod]' value='$modifier'></td>\n";
		}
		if (fees_are_used()) {
			echo "  <td class='billcell' align='right'>$fee</td>\n";
		}
		echo "  <td class='billcell' align='center'><input type='checkbox'" .
			($auth ? " checked" : "") . " disabled /></td>\n";
		echo "  <td class='billcell' align='center'><input type='checkbox'" .
			" disabled /></td>\n";
	} else {
		if (modifiers_are_used()) {
			if ($code_types[$codetype]['mod'] || $modifier) {
				echo "  <td class='billcell'><input type='text' name='bill[$lino][mod]' " .
					"value='$modifier' size='" . $code_types[$codetype]['mod'] . "'></td>\n";
			} else {
				echo "  <td class='billcell'>&nbsp;</td>\n";
			}
		}
		if (fees_are_used()) {
			if ($code_types[$codetype]['fee'] || $fee != 0) {
				echo "  <td class='billcell' align='right'><input type='text' name='bill[$lino][fee]' " .
					"value='$fee' size='6' style='text-align:right'></td>\n";
			} else {
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
}

// Generate lines for items already in the database.
//
$lino = 0;
$encounter_provid = -1;
if ($result = getBillingByEncounter($pid, $encounter, "*") ) {
	foreach ($result as $iter) {
		++$lino;
		$del = $_POST['bill']["$lino"]['del']; // preserve Delete if checked
		// list($code, $modifier) = explode("-", $iter["code"]);
		echoLine($lino, $iter["code_type"], trim($iter["code"]), trim($iter["modifier"]),
			$iter["authorized"], $del, $iter["fee"], $iter["id"], $iter["billed"], $iter["code_text"]);
		if ($encounter_provid < 0 && ! $del) $encounter_provid = $iter["provider_id"];
	}
}

// If there were no billing items then the default provider is the logged-in user.
//
if ($encounter_provid < 0) $encounter_provid = $_SESSION["authUserID"];

// Echo new billing items from this form here, but omit any line
// whose Delete checkbox is checked.
//
if ($_POST['bill']) {
	foreach ($_POST['bill'] as $key => $iter) {
		if ($iter["id"])  continue; // skip if it came from the database
		if ($iter["del"]) continue; // skip if Delete was checked
		echoLine(++$lino, $iter["code_type"], $iter["code"], trim($iter["mod"]),
			$iter["auth"], $iter["del"], $iter["fee"]);
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
?>
</table>
</p>

<br>
&nbsp;

<span class="billcell">PROVIDER:</span>

<?php

// Build a drop-down list of providers.  This includes users who
// have the word "provider" anywhere in their "additional info"
// field, so that we can define providers (for billing purposes)
// who do not appear in the calendar.
//
$query = "SELECT id, lname, fname FROM users WHERE " .
	"authorized = 1 OR info LIKE '%provider%' ORDER BY lname, fname";
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

<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='submit' name='bn_refresh' value='Refresh'>
&nbsp;
<input type='button' value='Cancel' onclick='docancel()' />

<?php if ($code_types['UCSMC']) { ?>
<p style='font-family:sans-serif;font-size:8pt;color:#666666;'>
&nbsp;<br>
UCSMC codes provided by the University of Calgary Sports Medicine Centre
</p>
<?php } ?>

</center>

</form>
<?php
// TBD: If $alertmsg, display it with a JavaScript alert().
?>
