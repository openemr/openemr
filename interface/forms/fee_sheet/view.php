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
		$fee       = trim($iter['fee']);
		$auth      = $iter['auth'] ? "1" : "0";
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
        sqlQuery("UPDATE billing SET " .
          "units = '$units', fee = '$fee', modifier = '$modifier', " .
          "authorized = $auth, provider_id = '$provid', " .
          "ndc_info = '$ndc_info' WHERE " .
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
				$provid, $modifier, $units, $fee, $ndc_info);
		}
	}

	formHeader("Redirecting....");
	formJump();
	formFooter();
	exit;
}
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style>
.billcell { font-family: sans-serif; font-size: 10pt }
</style>
<script language="JavaScript">

function codeselect(selobj, newtype) {
 var i = selobj.selectedIndex;
 if (i > 0) {
  var f = document.forms[0];
  f.newcode.value = selobj.options[i].value;
  f.newtype.value = newtype;
  f.submit();
 }
}

</script>
</head>

<body <?echo $top_bg_line;?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?echo $rootdir;?>/forms/fee_sheet/new.php">
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

// Find out if the patient has any insurance that requires NDC numbers.
// Currently this is just Medicaid, type 3.
//
$tmp = sqlQuery("SELECT c.id FROM " .
  "insurance_data AS i, insurance_companies AS c WHERE " .
  "i.pid = '$pid' AND i.provider != '' AND " .
  "c.id = i.provider AND c.freeb_type = 3 " .
  "LIMIT 1");
$ndc_applies = !empty($tmp);

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
<? } ?>
  <td class='billcell' align='center'><b><?php xl('Auth','e');?></b></td>
  <td class='billcell' align='center'><b><?php xl('Delete','e');?></b></td>
  <td class='billcell'><b><?php xl('Description','e');?></b></td>
 </tr>
<?

// This writes a billing line item to the output page.
//
function echoLine($lino, $codetype, $code, $modifier, $ndc_info='',
  $auth = TRUE, $del = FALSE, $units = NULL, $fee = NULL, $id = NULL,
  $billed = FALSE, $code_text = NULL)
{
  global $code_types, $ndc_applies, $ndc_uom_choices;

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
			echo "  <td class='billcell' align='center'>$units</td>\n";
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
				echo "  <td class='billcell' align='center'><input type='text' name='bill[$lino][units]' " .
					"value='$units' size='2' style='text-align:right'></td>\n";
				echo "  <td class='billcell' align='right'><input type='text' name='bill[$lino][fee]' " .
					"value='$fee' size='6' style='text-align:right'></td>\n";
			} else {
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
if ($result = getBillingByEncounter($pid, $encounter, "*") ) {
	foreach ($result as $iter) {
		++$lino;
		$del = $_POST['bill']["$lino"]['del']; // preserve Delete if checked
		// list($code, $modifier) = explode("-", $iter["code"]);
    echoLine($lino, $iter["code_type"], trim($iter["code"]),
      trim($iter["modifier"]), $iter["ndc_info"],  $iter["authorized"],
      $del, $iter["units"], $iter["fee"], $iter["id"], $iter["billed"],
      $iter["code_text"]);
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
    echoLine(++$lino, $iter["code_type"], $iter["code"], trim($iter["mod"]),
      $ndc_info, $iter["auth"], $iter["del"], $iter["units"],
      $iter["fee"]);
	}
}

// If a new billing code was <select>ed, add its line here.  As a special
// case allow HCPCS codes to be included in the CPT drop-lists, and
// CPT4 codes included in OPCS drop-lists.
//
if ($_POST['newcode']) {
	list($code, $modifier) = explode("-", $_POST['newcode']);
	$newtype = $_POST['newtype'];
	if ($newtype == "CPT4" && preg_match("/^[A-Z]/", $code))
		$newtype = "HCPCS";
	else if ($newtype == "OPCS" && preg_match("/^[0-9]/", $code))
		$newtype = "CPT4";

  $ndc_info = '';
  // If HCPCS and Medicaid, find last NDC string used for this code.
  if ($newtype == 'HCPCS' && $ndc_applies) {
    $tmp = sqlQuery("SELECT ndc_info FROM billing WHERE " .
      "code_type = '$newtype' AND code = '$code' AND ndc_info LIKE 'N4%' " .
      "ORDER BY date DESC LIMIT 1");
    if (!empty($tmp)) $ndc_info = $tmp['ndc_info'];
  }

  echoLine(++$lino, $newtype, $code, trim($modifier), $ndc_info);
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
</body>
</html>
