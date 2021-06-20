<?php

/**
 * diagnosis.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$mode              = $_REQUEST['mode'];
$type              = $_REQUEST['type'];
$code              = $_REQUEST['code'];
$modifier          = $_REQUEST['modifier'];
$units             = $_REQUEST['units'];
$fee               = $_REQUEST['fee'];
$text              = $_REQUEST['text'];
$payment_method    = $_REQUEST['payment_method'];
$insurance_company = $_REQUEST['insurance_company'];

$target = '_parent';

// Possible units of measure for NDC drug quantities.
$ndc_uom_choices = array(
  'ML' => 'ML',
  'GR' => 'Grams',
  'ME' => 'Milligrams',
  'F2' => 'I.U.',
  'UN' => 'Units'
);

if ($payment_method == "insurance") {
    $payment_method = "insurance: " . $insurance_company;
}

if (isset($mode)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($mode == "add") {
        // Get the provider ID from the new encounter form if possible, otherwise
        // it's the logged-in user.
        $tmp = sqlQuery("SELECT users.id FROM forms, users WHERE " .
            "forms.pid = ? AND forms.encounter = ? AND " .
            "forms.formdir='newpatient' AND users.username = forms.user AND " .
            "users.authorized = 1", array($pid, $encounter));
        $provid = $tmp['id'] ? $tmp['id'] : $_SESSION["authUserID"];

        if (strtolower($type) == "copay") {
            BillingUtilities::addBilling(
                $encounter,
                $type,
                sprintf("%01.2f", $code),
                $payment_method,
                $pid,
                $userauthorized,
                $provid,
                $modifier,
                $units,
                sprintf("%01.2f", 0 - $code)
            );
        } elseif (strtolower($type) == "other") {
            BillingUtilities::addBilling(
                $encounter,
                $type,
                $code,
                $text,
                $pid,
                $userauthorized,
                $provid,
                $modifier,
                $units,
                sprintf("%01.2f", $fee)
            );
        } else {
            $ndc_info = '';
      // If HCPCS, get and save default NDC data.
            if (strtolower($type) == "hcpcs") {
                    $tmp = sqlQuery("SELECT ndc_info FROM billing WHERE " .
                "code_type = 'HCPCS' AND code = ? AND ndc_info LIKE 'N4%' " .
                "ORDER BY date DESC LIMIT 1", array($code));
                if (!empty($tmp)) {
                    $ndc_info = $tmp['ndc_info'];
                }
            }

            BillingUtilities::addBilling(
                $encounter,
                $type,
                $code,
                $text,
                $pid,
                $userauthorized,
                $provid,
                $modifier,
                $units,
                $fee,
                $ndc_info
            );
        }
    } elseif ($mode == "justify") {
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

                $sql[] = "UPDATE billing set justify = concat(justify,'" . add_escape_custom($justify_string)  . "') where encounter = '" . add_escape_custom($_POST['encounter_id']) . "' and pid = '" . add_escape_custom($_POST['patient_id']) . "' and code = '" . add_escape_custom($proc) . "'";
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

              sqlStatement("UPDATE billing SET ndc_info = ? WHERE " .
                "encounter = ? AND " .
                "pid = ? AND " .
                "code = ?", array($ndc_info, $_POST['encounter_id'], $_POST['patient_id'], $ndc['code']));
        }
    }
}

?>
<html>
<head>
<?php Header::setupHeader(); ?>

<script>

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
    alert(<?php echo xlj('Format incorrect for NDC'); ?> + ' ' + ndc +
     ', ' + <?php echo xlj('should be like nnnnn-nnnn-nn'); ?>);
    if (f[pfx+'[ndcnum]'].focus) f[pfx+'[ndcnum]'].focus();
    return false;
   }
   // Check for valid quantity.
   var qty = f[pfx+'[ndcqty]'].value - 0;
   if (isNaN(qty) || qty <= 0) {
    alert(<?php echo xlj('Quantity for NDC'); ?> + ' ' + ndc +
     ' ' + <?php echo xlj('is not valid (decimal fractions are OK).'); ?>);
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
 $thisauth = AclMain::aclCheckCore('encounters', 'coding_a');
if (!$thisauth) {
    $erow = sqlQuery("SELECT user FROM forms WHERE " .
    "encounter = ? AND formdir = 'newpatient' LIMIT 1", array($encounter));
    if ($erow['user'] == $_SESSION['authUser']) {
        $thisauth = AclMain::aclCheckCore('encounters', 'coding');
    }
}

if ($thisauth) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) {
        $thisauth = 0;
    }
}

if (!$thisauth) {
    echo "<p>(" . xlt('Coding not authorized') . ")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}
?>

<form name="diagnosis" method="post" action="diagnosis.php?mode=justify&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>"
 onsubmit="return validate(this)">
<table class="table-borderless h-100" cellspacing='0' cellpadding='0'>
<tr>

<td class="align-top">

<dl>
<dt>
<a href="diagnosis_full.php" target="<?php echo attr($target); ?>" onclick="top.restoreSession()">
<span class='title'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('Billing') : xlt('Coding'); ?></span>
<span class='more'><?php echo text($tmore); ?></span></a>

<?php
if (!empty($_GET["back"]) || !empty($_POST["back"])) {
    print "&nbsp;<a href=\"superbill_codes.php\" target=\"" . attr($target) . "\" onclick=\"top.restoreSession()\"><span class='more'>" . text($tback) . "</span></a>";
    print "<input type=\"hidden\" name=\"back\" value=\"1\">";
}
?>
<?php if (!$GLOBALS['weight_loss_clinic']) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="justify" value="<?php echo xla('Justify/Save');?>">
<?php } ?>
</dt>
</dl>

<a href="cash_receipt.php?csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" class='link_submit' target='new' onclick='top.restoreSession()'>
[<?php echo xlt('Receipt'); ?>]
</a>
<table class="table-borderless">
<?php
if ($result = BillingUtilities::getBillingByEncounter($pid, $encounter, "*")) {
    $billing_html = array();
    $total = 0.0;
    $ndclino = 0;
    foreach ($result as $iter) {
        if ($iter["code_type"] == "ICD9") {
                $html = "<tr>";
                $html .= "<td class='align-middle'>" .
                    '<input  style="width: 11px; height: 11px;" name="code[diag][' .
                    attr($iter["code"]) . ']" type="checkbox" value="' . attr($iter["code"]) . '">' .
                    "</td><td><div><a target='" . attr($target) . "' class='small' " .
            "href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
                    text($iter["code"]) . "</b> " . text($iter["code_text"]) .
                    "</a></div></td></tr>\n";
                $billing_html[$iter["code_type"]] .= $html;
                $counter++;
        } elseif ($iter["code_type"] == "COPAY") {
            $billing_html[$iter["code_type"]] .=
                "<tr><td></td><td><a target='" . attr($target) . "' class='small' " .
            "href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
                text(oeFormatMoney($iter['code'])) . "</b> " .
                text(ucwords(strtolower($iter['code_text']))) .
                ' ' . xlt('payment entered on') . ' ' .
                text(oeFormatShortDate(substr($iter['date'], 0, 10))) . text(substr($iter['date'], 10, 6)) . "</a></td></tr>\n";
        } else {
            $billing_html[$iter["code_type"]] .=
                "<tr><td>" . '<input  style="width: 11px; height: 11px;" name="code[proc][' .
                attr($iter["code"]) . ']" type="checkbox" value="' . attr($iter["code"]) . '">' .
                "</td><td><a target='$target' class='small' " .
            "href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
                text($iter["code"]) . ' ' . text($iter['modifier']) . "</b> " .
                text(ucwords(strtolower($iter["code_text"]))) . ' ' . text(oeFormatMoney($iter['fee'])) .
                "</a><span class=\"small\">";
            $total += $iter['fee'];
            $js = explode(":", $iter['justify']);
            $counter = 0;
            foreach ($js as $j) {
                if (!empty($j)) {
                    if ($counter == 0) {
                        $billing_html[$iter["code_type"]] .= " (<b>" . text($j) . "</b>)";
                    } else {
                        $billing_html[$iter["code_type"]] .= " (" . text($j) . ")";
                    }

                    $counter++;
                }
            }

            $billing_html[$iter["code_type"]] .= "</span></td></tr>\n";

      // If this is HCPCS, write NDC line.
            if ($iter['code_type'] == 'HCPCS') {
                    ++$ndclino;
                    $ndcnum = '';
                $ndcuom = '';
                $ndcqty = '';
                if (preg_match('/^N4(\S+)\s+(\S\S)(.*)/', $iter['ndc_info'], $tmp)) {
                    $ndcnum = $tmp[1];
                    $ndcuom = $tmp[2];
                    $ndcqty = $tmp[3];
                }

                    $billing_html[$iter["code_type"]] .=
                      "<tr><td>&nbsp;</td><td class='small'>NDC:&nbsp;\n" .
                      "<input type='hidden' name='ndc[" . attr($ndclino) . "][code]' value='" . attr($iter["code"]) . "'>" .
                      "<input type='text' name='ndc[" . attr($ndclino) . "][ndcnum]' value='" . attr($ndcnum) . "' " .
                      "size='11' class='bg-transparent'>" .
                      " &nbsp;Qty:&nbsp;" .
                      "<input type='text' name='ndc[" . attr($ndclino) . "][ndcqty]' value='" . attr($ndcqty) . "' " .
                      "size='3' class='bg-transparent text-right'> " .
                      "<select name='ndc[" . attr($ndclino) . "][ndcuom]' class='bg-transparent'>";
                foreach ($ndc_uom_choices as $key => $value) {
                    $billing_html[$iter["code_type"]] .= "<option value='" . attr($key) . "'";
                    if ($key == $ndcuom) {
                        $billing_html[$iter["code_type"]] .= " selected";
                    }

                    $billing_html[$iter["code_type"]] .= ">" . text($value) . "</option>";
                }

                    $billing_html[$iter["code_type"]] .= "</select></td></tr>\n";
            }
        }
    }

    $billing_html["CPT4"] .= "<tr><td>" . xlt('total') . ":</td><td>" . text(oeFormatMoney($total)) . "</td></tr>\n";
    foreach ($billing_html as $key => $val) {
        print "<tr><td>" . text($key) . "</td><td><table>" . $val . "</table><td></tr><tr><td height=\"5\"></td></tr>\n";
    }
}
?>
</tr></table>
</td>
</tr>
<input type="hidden" name="encounter_id" value="<?php echo attr($encounter); ?>" />
<input type="hidden" name="patient_id" value="<?php echo attr($pid); ?>" />
</form>
</table>

</body>
</html>
