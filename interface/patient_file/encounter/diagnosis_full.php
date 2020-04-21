<?php

/**
 * diagnosis_full.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$mode = $_GET['mode'];
$id   = $_GET['id'];

if (isset($mode)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($mode == "add") {
        BillingUtilities::addBilling($encounter, $type, $code, $text, $pid, $userauthorized, $_SESSION['authUserID']);
    } elseif ($mode == "delete") {
        BillingUtilities::deleteBilling($id);
    } elseif ($mode == "clear") {
        BillingUtilities::clearBilling($id);
    }
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
</head>

<body class="body_top">

<a href="encounter_bottom.php" onclick="top.restoreSession()">

<span class='title'><?php echo xlt('Billing'); ?></span>
<span class='more'><?php echo text($tback); ?></span></a>

<table class='table-borderless' cellpadding='3' cellspacing='0'>

<?php
if ($result = BillingUtilities::getBillingByEncounter($pid, $encounter, "*")) {
    $billing_html = array();
    foreach ($result as $iter) {
        if ($iter["code_type"] == "ICD9") {
            $html = "<tr>";
            $html .= "<td class='align-middle'></td>" .
                "<td><div><a class='small' href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
                text($iter["code"]) . "</b> " . text(ucwords(strtolower($iter["code_text"]))) .
                "</a></div></td>\n";
            $billing_html[$iter["code_type"]] .= $html;
            $counter++;
        } elseif ($iter["code_type"] == "COPAY") {
            $billing_html[$iter["code_type"]] .= "<tr><td></td>" .
                "<td><a class='small' href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
                text($iter["code"]) . "</b> " . text(ucwords(strtolower($iter["code_text"]))) .
                "</a></td>\n";
        } else {
            $billing_html[$iter["code_type"]] .= "<tr><td></td>" .
                "<td><a class='small' href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
                text($iter["code"]) . "</b> " . text(ucwords(strtolower($iter["code_text"]))) .
                "</a><span class=\"small\">";
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

            $billing_html[$iter["code_type"]] .= "</span></td>";
            $billing_html[$iter["code_type"]] .= "<td>" .
                "<a class=\"link_submit\" href='diagnosis_full.php?mode=clear&id=" .
                attr_url($iter["id"]) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' class='link' onclick='top.restoreSession()'>[" . xlt('Clear Justification') .
                "]</a></td>";
        }

        $billing_html[$iter["code_type"]] .= "<td>" .
            "<a class=\"link_submit\" href='diagnosis_full.php?mode=delete&id=" .
            attr_url($iter["id"]) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' class='link' onclick='top.restoreSession()'>[Delete]</a></td>";
        $billing_html[$iter["code_type"]] .= "</tr>\n";
    }

    foreach ($billing_html as $key => $val) {
        print "<tr><td>" . text($key) . "</td><td><table>" . $val . "</table><td></tr><tr><td height=\"5\"></td></tr>\n";
    }
}

?>
</table>

</body>
</html>
