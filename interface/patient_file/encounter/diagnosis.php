<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once("$srcdir/billing.inc");
include_once("$srcdir/sql.inc");
include_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");

$mode              = $_REQUEST['mode'];
$type              = $_REQUEST['type'];
$code              = $_REQUEST['code'];
$modifier          = $_REQUEST['modifier'];
$units             = $_REQUEST['units'];
$fee               = $_REQUEST['fee'];
$text              = $_REQUEST['text'];
$payment_method    = $_REQUEST['payment_method'];
$insurance_company = $_REQUEST['insurance_company'];

$target = $GLOBALS['concurrent_layout'] ? '_parent' : 'Main';

// Possible units of measure for NDC drug quantities.
$ndc_uom_choices = array(
  'ML' => 'ML',
  'GR' => 'Grams',
  'F2' => 'I.U.',
  'UN' => 'Units'
);

if ($payment_method == "insurance") {
	$payment_method = "insurance: " . $insurance_company;
}
if (isset($mode)) {
	if ($mode == "add") {

		// Get the provider ID from the new encounter form if possible, otherwise
		// it's the logged-in user.
		$tmp = sqlQuery("SELECT users.id FROM forms, users WHERE " .
			"forms.pid = '$pid' AND forms.encounter = '$encounter' AND " .
			"forms.formdir='newpatient' AND users.username = forms.user AND " .
			"users.authorized = 1");
		$provid = $tmp['id'] ? $tmp['id'] : $_SESSION["authUserID"];

		if (strtolower($type) == "copay") {
			addBilling($encounter, $type, sprintf("%01.2f", $code), $payment_method,
				$pid, $userauthorized, $provid, $modifier, $units,
				sprintf("%01.2f", 0 - $code));
		}
		elseif (strtolower($type) == "other") {
			addBilling($encounter, $type, $code, $text, $pid, $userauthorized,
				$provid, $modifier, $units, sprintf("%01.2f", $fee));
		}
		else {
      $ndc_info = '';
      // If HCPCS, get and save default NDC data.
      if (strtolower($type) == "hcpcs") {
        $tmp = sqlQuery("SELECT ndc_info FROM billing WHERE " .
          "code_type = 'HCPCS' AND code = '$code' AND ndc_info LIKE 'N4%' " .
          "ORDER BY date DESC LIMIT 1");
        if (!empty($tmp)) $ndc_info = $tmp['ndc_info'];
      }
      addBilling($encounter, $type, $code, $text, $pid, $userauthorized,
        $provid, $modifier, $units, $fee, $ndc_info);
		}
	}
	elseif ($mode == "justify") {
		$diags = $_POST['code']['diag'];
		$procs = $_POST['code']['proc'];
		$sql = array();
		if (!empty($procs) && !empty($diags)) {
			$sql = array();
			foreach ($procs as $proc) {
				$justify_string = "";
				foreach ($diags as $diag) {
					$justify_string .= $diag . ":"; 
				}
				$sql[] = "UPDATE billing set justify = concat(justify,'" . mysql_real_escape_string($justify_string)  ."') where encounter = '" . mysql_real_escape_string($_POST['encounter_id']) . "' and pid = '" . mysql_real_escape_string($_POST['patient_id']) . "' and code = '" . mysql_real_escape_string($proc) . "'";
			}
		
		}
		if (!empty($sql)) {
			foreach ($sql as $q) {
				$results = sqlQ($q);
			}
		}

    // Save NDC fields, if present.
    $ndcarr = $_POST['ndc'];
    for ($lino = 1; !empty($ndcarr["$lino"]['code']); ++$lino) {
      $ndc = $ndcarr["$lino"];
      $ndc_info = '';
      if ($ndc['ndcnum']) {
        $ndc_info = 'N4' . trim($ndc['ndcnum']) . '   ' . $ndc['ndcuom'] .
          trim($ndc['ndcqty']);
      }
      sqlStatement("UPDATE billing SET ndc_info = '$ndc_info' WHERE " .
        "encounter = '" . mysql_real_escape_string($_POST['encounter_id']) . "' AND " .
        "pid = '" . mysql_real_escape_string($_POST['patient_id']) . "' AND " .
        "code = '" . mysql_real_escape_string($ndc['code']) . "'");
    }

  }
}

?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<script language="JavaScript">

function validate(f) {
 for (var lino = 1; f['ndc['+lino+'][code]']; ++lino) {
  var pfx = 'ndc['+lino+']';
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

</script>

</head>

<body class="body_bottom">

<?php
 $thisauth = acl_check('encounters', 'coding_a');
 if (!$thisauth) {
  $erow = sqlQuery("SELECT user FROM forms WHERE " .
   "encounter = '$encounter' AND formdir = 'newpatient' LIMIT 1");
  if ($erow['user'] == $_SESSION['authUser'])
   $thisauth = acl_check('encounters', 'coding');
 }
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(".xl('Coding not authorized').")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>

<form name="diagnosis" method="post" action="diagnosis.php?mode=justify"
 onsubmit="return validate(this)">

<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>

<td valign=top>

<dl>
<dt>
<a href="diagnosis_full.php" target="<?php echo $target; ?>" onclick="top.restoreSession()">
<span class=title><?php echo ($GLOBALS['phone_country_code'] == '1') ? xl('Billing') : xl('Coding'); ?></span>
<font class=more><?php echo $tmore;?></font></a>

<?php
if( !empty( $_GET["back"] ) || !empty( $_POST["back"] ) ){
	print "&nbsp;<a href=\"superbill_codes.php\" target=\"$target\" onclick=\"top.restoreSession()\"><font class=more>$tback</font></a>";
	print "<input type=\"hidden\" name=\"back\" value=\"1\">";
}
?>
<?php if (!$GLOBALS['weight_loss_clinic']) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="justify" value="<?php xl('Justify/Save','e');?>">
<?php } ?>
</dt>
</dl>

<a href="cash_receipt.php?" class='link_submit' target='new' onclick='top.restoreSession()'>
[<?php xl('Receipt','e'); ?>]
</a>
<table border="0">
<?php
if ($result = getBillingByEncounter($pid,$encounter,"*") ) {
	$billing_html = array();
	$total = 0.0;
  $ndclino = 0;
	foreach ($result as $iter) {
		if ($iter["code_type"] == "ICD9") {
				$html = "<tr>";
				$html .= "<td valign=\"middle\">" .
					'<input  style="width: 11px;height: 11px;" name="code[diag][' .
					$iter["code"] . ']" type="checkbox" value="' . $iter[code] . '">' .
					"</td><td><div><a target='$target' class='small' " .
          "href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
					$iter{"code"} . "</b> " . ucwords(strtolower($iter{"code_text"})) .
					"</a></div></td></tr>\n";
				$billing_html[$iter["code_type"]] .= $html;
				$counter++;
		}
		elseif ($iter["code_type"] == "COPAY") {
			$billing_html[$iter["code_type"]] .=
				"<tr><td></td><td><a target='$target' class='small' " .
        "href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
				oeFormatMoney($iter['code']) . "</b> " .
				ucwords(strtolower($iter['code_text'])) .
				' ' . xl('payment entered on') . ' ' .
				oeFormatShortDate(substr($iter['date'], 0, 10)) . substr($iter['date'], 10, 6) . "</a></td></tr>\n";
		}
		else {
			$billing_html[$iter["code_type"]] .=
				"<tr><td>" . '<input  style="width: 11px;height: 11px;" name="code[proc][' .
				$iter["code"] . ']" type="checkbox" value="' . $iter[code] . '">' .
				"</td><td><a target='$target' class='small' " .
        "href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
				$iter{"code"} . ' ' . $iter['modifier'] . "</b> " .
				ucwords(strtolower($iter{"code_text"})) . ' ' . oeFormatMoney($iter['fee']) .
				"</a><span class=\"small\">";
			$total += $iter['fee'];
			$js = split(":",$iter['justify']);
			$counter = 0;
			foreach ($js as $j) {
				if(!empty($j)) {
					if ($counter == 0) {
						$billing_html[$iter["code_type"]] .= " (<b>$j</b>)";
					}
					else {
						$billing_html[$iter["code_type"]] .= " ($j)";
					}
					$counter++;
				}
			}
			$billing_html[$iter["code_type"]] .= "</span></td></tr>\n";

      // If this is HCPCS, write NDC line.
      if ($iter['code_type'] == 'HCPCS') {
        ++$ndclino;
        $ndcnum = ''; $ndcuom = ''; $ndcqty = '';
        if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $iter['ndc_info'], $tmp)) {
          $ndcnum = $tmp[1]; $ndcuom = $tmp[2]; $ndcqty = $tmp[3];
        }
        $billing_html[$iter["code_type"]] .=
          "<tr><td>&nbsp;</td><td class='small'>NDC:&nbsp;\n" .
          "<input type='hidden' name='ndc[$ndclino][code]' value='" . $iter[code] . "'>" .
          "<input type='text' name='ndc[$ndclino][ndcnum]' value='$ndcnum' " .
          "size='11' style='background-color:transparent'>" .
          " &nbsp;Qty:&nbsp;" .
          "<input type='text' name='ndc[$ndclino][ndcqty]' value='$ndcqty' " .
          "size='3' style='background-color:transparent;text-align:right'> " .
          "<select name='ndc[$ndclino][ndcuom]' style='background-color:transparent'>";
        foreach ($ndc_uom_choices as $key => $value) {
          $billing_html[$iter["code_type"]] .= "<option value='$key'";
          if ($key == $ndcuom) $billing_html[$iter["code_type"]] .= " selected";
          $billing_html[$iter["code_type"]] .= ">$value</option>";
        }
        $billing_html[$iter["code_type"]] .= "</select></td></tr>\n";
      }

		}
	}

	$billing_html["CPT4"] .= "<tr><td>" . xl('total') . ":</td><td>" . oeFormatMoney($total) . "</td></tr>\n";
	foreach ($billing_html as $key => $val) {
		print "<tr><td>$key</td><td><table>$val</table><td></tr><tr><td height=\"5\"></td></tr>\n";
	}
}
?>
</tr></table>
</td>
</tr>
<input type="hidden" name="encounter_id" value="<?php echo  $encounter?>">
<input type="hidden" name="patient_id" value="<?php echo $pid?>">
</form>
</table>

</body>
</html>
