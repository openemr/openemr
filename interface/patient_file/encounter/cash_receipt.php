<?php

/**
 * cash_receipt.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: Code cleanup

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$N = 6;
$first_issue = 1;
?>
<html>
<head>
<?php Header::setupHeader(); ?>
</head>

<body class="bg-white ml-1">
<p>
<?php
$titleres = getPatientData($pid, "fname,lname,providerID");
// $sql = "select * from facility where billing_location = 1";
$sql = "select f.* from facility f " .
    "LEFT JOIN form_encounter fe on fe.facility_id = f.id " .
    "where fe.encounter = ?";
$db = $GLOBALS['adodb']['db'];
$results = $db->Execute($sql, array($encounter));
$facility = array();
if (!$results->EOF) {
    $facility = $results->fields;
}

$practice_logo = "../../../custom/practice_logo.gif";
if (file_exists($practice_logo)) {
    echo "<img src='$practice_logo' align='left'>\n";
}
?>
<h2><?php echo text($facility['name']); ?></h2>
<?php echo text($facility['street']); ?><br />
<?php echo text($facility['city']); ?>, <?php echo text($facility['state']); ?> <?php echo text($facility['postal_code']); ?><div class="clearfix"></div>
<?php echo text($facility['phone']); ?><br />

</p>

<a href="javascript:window.close();"><span class='title'><?php print text($titleres["fname"]) . " " . text($titleres["lname"]); ?></span></a><br /><br />

<table>
<tr><td><?php echo xlt('Generated on'); ?>:</td><td> <?php print text(oeFormatShortDate(date("Y-m-d")));?></td></tr>
<?php
if ($date_result = sqlQuery("select date from form_encounter where encounter=? and pid=?", array($encounter, $pid))) {
    $encounter_date = date("D F jS", strtotime($date_result["date"]));
    $raw_encounter_date = date("Y-m-d", strtotime($date_result["date"]));
}
?>
<tr><td><?php echo xlt('Date Of Service'); ?>: </td><td> <?php print text(oeFormatShortDate($raw_encounter_date));?></td></tr>
</table>
<br /><br />
<?php
 //$provider = getProviderName($titleres['providerID']);

 //print "Provider: " . $provider  . "<br />";

 $inclookupres = sqlStatement("select distinct formdir from forms where pid=?", array($pid));
while ($result = sqlFetchArray($inclookupres)) {
    include_once("{$GLOBALS['incdir']}/forms/" . $result["formdir"] . "/report.php");
}

 $printed = false;

//borrowed from diagnosis.php

?>
<table class="table-bordered" cellpadding="5">
<?php
if ($result = BillingUtilities::getBillingByEncounter($pid, $encounter, "*")) {
    $billing_html = array();
        $total = 0.0;
    $copay = 0.0;

//test
//  foreach ($result as $key => $val) {
//      print "<h2>$key</h2>";
//      foreach($val as $key2 => $val2) {
//          print "<p> $key2 = $val2 </p>\n";
//      }
//  }
//end test

    foreach ($result as $iter) {
        $html = '';
        if ($iter["code_type"] == "ICD9") {
            $html .= "<tr><td>" . text($iter['code_type']) .
                "</td><td>" . text($iter['code']) . "</td><td>"
                . text($iter["code_text"]) . "</td></tr>\n";
            $billing_html[$iter["code_type"]] .= $html;
            $counter++;
        } elseif ($iter["code_type"] == "COPAY") {
            $html .= "<tr><td>" . xlt('Payment') . ":</td><td>" . xlt('Thank You') . "!</td><td>"
                . text($iter["code_text"]) . "</td><td>"
                . text(oeFormatMoney($iter["code"])) . "</td></tr>\n";
            if ($iter["code"] > 0.00) {
                $copay += $iter["code"];
                $billing_html[$iter["code_type"]] .= $html;
            }
        } else {
            $html .= "<tr><td>" . text($iter['code_type']) .
                "</td><td>" . text($iter['code']) . "</td><td>"
                . text($iter["code_text"]) . ' ' . text($iter['modifier'])
                . "</td><td>" . text(oeFormatMoney($iter['fee'])) . "</td></tr>\n";
            $billing_html[$iter["code_type"]] .= $html;
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
        }
    }

    $billing_html["CPT4"] .= "<tr><td>" . xlt('total') . "</td><td></td><td></td><td>" . text(oeFormatMoney($total)) . "</td></tr>\n";
    ?>
<tr><td><?php echo xlt('code type'); ?></td><td><?php echo xlt('code'); ?></td><td><?php echo xlt('description'); ?></td><td><?php echo xlt('fee'); ?></td></tr>
    <?php
    $key = "ICD9";
    $val = $billing_html[$key];
        print $val;
    $key = "CPT4";
    $val = $billing_html[$key];
        print $val;
    $key = "COPAY";
    $val = $billing_html[$key];
        print $val;
    $balance = $total - $copay;
    if ($balance != 0.00) {
        print "<tr><td>" . xlt('balance') . "</td><td></td><td>" . xlt('Please pay this amount') . ":</td><td>" . text(oeFormatMoney($balance)) . "</td></tr>\n";
    }
}
?>
</tr></table>
<?php
//if ($balance != 0.00) {
//  print "<p>Note: The balance recorded above only reflects the encounter described by this statement.  It does not reflect the balance of the entire account.  A negative number in the balance field indicates a credit due to overpayment</p>";
//}
?>

</body>
</html>
